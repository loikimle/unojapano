<?php

namespace WPEverest\URM\Pro\FileDownloads\Services;

use WPEverest\URMembership\Admin\Repositories\MembersRepository;
use WPEverest\URM\Pro\FileDownloads\Models\File;
use WPEverest\URM\Pro\FileDownloads\Repositories\FileRepository;
use WPEverest\URM\Pro\FileDownloads\Taxonomies\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ContentRulesIntegrationService {

	private const CONTENT_RULE_POST_TYPE = 'urcr_access_rule';

	public function init_hooks() {
		add_filter( 'urcr_localized_data', [ $this, 'modify_content_rules_localized_data' ] );
		add_filter( 'urcr_type_labels', [ $this, 'add_type_labels' ] );
	}

	public function add_type_labels( $labels ) {
		$labels['files'] = __( 'Files', 'user-registration' );
		return $labels;
	}

	/**
	 * @param array $localized
	 * @return array
	 */
	public function modify_content_rules_localized_data( $localized ) {
		unset( $localized['post_types']['urfd_file'] );
		$files              = array_reduce(
			( new FileRepository() )->all(),
			function ( $acc, $curr ) {
				$acc[ $curr->get_id() ] = $curr->get_name();
				return $acc;
			},
			[]
		);
		$localized['files'] = $files;

		$localized['content_type_options'][] = [
			'value' => 'files',
			'label' => esc_html__( 'Files', 'user-registration' ),
		];
		return $localized;
	}

	/**

	 * @param File   $file
	 * @return array{access:boolean,message:string}
	 */
	public function check_file_access( File $file ) {
		$access_rules = $file->get_access_rules();

		$category_ids = array_map(
			function ( $cat ) {
				return $cat->term_id;
			},
			$file->get_categories()
		);

		if ( ! empty( $category_ids ) ) {
			$category_access_rules = $this->find_access_rules_by_file_categories( $category_ids );
			$access_rules          = array_replace( $access_rules, $category_access_rules );
		}

		// No rules = no access.
		if ( empty( $access_rules ) ) {
			return [
				'access'  => false,
				'message' => '',
			];
		}

		// Rules exists = default no access.
		$access    = false;
		$file_post = get_post( $file->get_id() );
		$message   = '';

		foreach ( $access_rules as $access_rule ) {
			if ( ! $this->is_valid_access_rule( $access_rule ) ) {
				continue;
			}
			$is_allowed = urcr_is_allow_access( $access_rule['logic_map'], $file_post );
			$message    = current( $access_rule['actions'] )['message'] ?? '';
			if ( $is_allowed && 'restrict' === $access_rule['access_control'] ) {
				$access = false;
				break;
			}

			if ( $is_allowed && 'access' === $access_rule['access_control'] ) {
				$access = true;
			}
		}

		return apply_filters(
			'urfd_content_rules_file_access',
			[
				'access'  => $access,
				'message' => $message,
			],
			$file
		);
	}

	/**
	 * @param array|null $access_rule
	 * @return bool
	 */
	private function is_valid_access_rule( $access_rule ) {
		if ( ! is_array( $access_rule ) ) {
			return false;
		}

		if ( empty( $access_rule['logic_map'] ) || empty( $access_rule['target_contents'] ) || empty( $access_rule['actions'] ) ) {
			return false;
		}

		if ( ! is_array( $access_rule['logic_map'] ) ) {
			return false;
		}

		if ( empty( $access_rule['logic_map']['conditions'] ) ) {
			return false;
		}

		return apply_filters( 'urfd_content_rules_is_valid_rule', true, $access_rule );
	}

	/**
	 * @param int|array<int,int> $file_ids
	 * @return array
	 */
	public function find_access_rules_by_file_ids( $file_ids ) {
		return $this->find_access_rules_by_target(
			$file_ids,
			'files',
			'%"type":"files"%"value":%"%s"%'
		);
	}

	/**
	 * @param int|array<int,int> $category_ids
	 * @return array
	 */
	public function find_access_rules_by_file_categories( $category_ids ) {
		return $this->find_access_rules_by_target(
			$category_ids,
			'taxonomy',
			'%"type":"taxonomy"%"taxonomy":"' . Taxonomy::FILE_CATEGORY . '"%"value":%"%s"%',
			[ 'taxonomy' => Taxonomy::FILE_CATEGORY ]
		);
	}

	/**
	 * @param int|array<int,int> $ids
	 * @param string $target_type
	 * @param string $like_pattern
	 * @param array $additional_conditions
	 * @return array
	 */
	private function find_access_rules_by_target( $ids, $target_type, $like_pattern, $additional_conditions = [] ) {
		/** @var \wpdb $wpdb */
		global $wpdb;

		if ( ! is_array( $ids ) ) {
			$ids = [ $ids ];
		}

		if ( empty( $ids ) ) {
			return [];
		}

		$like_conditions = [];
		foreach ( $ids as $id ) {
			$like_conditions[] = $wpdb->prepare(
				'post_content LIKE %s',
				str_replace( '%s', $wpdb->esc_like( (string) $id ), $like_pattern )
			);
		}

		$where_clause = implode( ' OR ', $like_conditions );

		$query = sprintf(
			"SELECT ID, post_content
			FROM {$wpdb->posts}
			WHERE post_type = '%s'
			AND post_status = '%s'
			AND post_content LIKE '%s'
			AND (%s)",
			esc_sql( self::CONTENT_RULE_POST_TYPE ),
			esc_sql( 'publish' ),
			esc_sql( '%"enabled":true%' ),
			$where_clause
		);

		$results = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return $this->verify_target_matches( $results, $ids, $target_type, $additional_conditions );
	}

	/**
	 * @param array $results
	 * @param array $ids
	 * @param string $target_type
	 * @param array $additional_conditions
	 * @return array
	 */
	private function verify_target_matches( $results, $ids, $target_type, $additional_conditions = [] ) {
		$matched_posts = [];

		foreach ( $results as $post ) {
			$content = json_decode( $post->post_content, true );

			if ( ! isset( $content['enabled'] ) || $content['enabled'] !== true ) {
				continue;
			}
			if ( ! isset( $content['target_contents'] ) ) {
				continue;
			}
			foreach ( $content['target_contents'] as $target ) {
				if ( $target['type'] !== $target_type ) {
					continue;
				}
				$conditions_met = true;
				foreach ( $additional_conditions as $key => $expected_value ) {
					if ( ! isset( $target[ $key ] ) || $target[ $key ] !== $expected_value ) {
						$conditions_met = false;
						break;
					}
				}
				if ( ! $conditions_met ) {
					continue;
				}
				if ( isset( $target['value'] ) && ! empty( array_intersect( $target['value'], $ids ) ) ) {
					$matched_posts[ $post->ID ] = $content;
					break;
				}
			}
		}
		return array_unique( $matched_posts, $target_type === 'files' ? SORT_REGULAR : SORT_NUMERIC );
	}

	/**
	 * @param File $file
	 * @param int  $user_id
	 * @return bool
	 */
	public function user_has_membership_access_to_file( File $file, $user_id ) {
		if ( ! $user_id ) {
			return false;
		}

		$members_repository = new MembersRepository();
		$user_memberships   = $members_repository->get_member_memberships_by_id( $user_id );

		if ( empty( $user_memberships ) ) {
			return false;
		}

		$access_rules = $file->get_access_rules();

		$category_ids = array_map(
			function ( $cat ) {
				return $cat->term_id;
			},
			$file->get_categories()
		);

		if ( ! empty( $category_ids ) ) {
			$category_access_rules = $this->find_access_rules_by_file_categories( $category_ids );
			$access_rules          = array_replace( $access_rules, $category_access_rules );
		}

		if ( empty( $access_rules ) ) {
			return false;
		}

		foreach ( $access_rules as $access_rule ) {
			if ( ! $this->is_valid_access_rule( $access_rule ) ) {
				continue;
			}

			if ( ! isset( $access_rule['access_control'] ) || 'access' !== $access_rule['access_control'] ) {
				continue;
			}

			$rule_membership_ids = $this->get_membership_ids_from_logic_map( $access_rule['logic_map'] );

			if ( empty( $rule_membership_ids ) ) {
				continue;
			}

			$has_matching_membership = ! empty(
				array_intersect(
					array_map(
						function ( $membership ) {
							return $membership['post_id'];
						},
						$user_memberships
					),
					$rule_membership_ids
				)
			);

			if ( $has_matching_membership ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array $logic_map
	 * @return array<int>
	 */
	private function get_membership_ids_from_logic_map( $logic_map ) {
		$membership_ids = [];

		if ( ! isset( $logic_map['conditions'] ) || ! is_array( $logic_map['conditions'] ) ) {
			return $membership_ids;
		}

		foreach ( $logic_map['conditions'] as $condition ) {
			if ( isset( $condition['type'] ) && 'membership' === $condition['type'] ) {
				if ( isset( $condition['value'] ) && is_array( $condition['value'] ) ) {
					$membership_ids = array_merge( $membership_ids, array_map( 'intval', $condition['value'] ) );
				}
			}

			if ( isset( $condition['conditions'] ) && is_array( $condition['conditions'] ) ) {
				$nested_ids     = $this->get_membership_ids_from_logic_map( $condition );
				$membership_ids = array_merge( $membership_ids, $nested_ids );
			}
		}

		return array_unique( $membership_ids );
	}
}
