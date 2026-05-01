<?php
namespace ULTP_PRO;

defined('ABSPATH') || exit;

class RequestAPI{

    public function __construct() {
        add_action('wp_ajax_ultp_new_post', array($this, 'ultp_new_post_callback'));
        add_action('wp_ajax_ultp_search', array($this, 'ultp_search_callback'));
        add_action('wp_ajax_ultp_edit', array($this, 'ultp_ultp_edit_callback'));
        add_action('delete_post', array($this, 'delete_option_meta_action'));
    }

    public function delete_option_meta_action( $post_id ) {
        if (get_post_type( $post_id ) != 'ultp_builder') {
            return;
        }

        $conditions = get_option('ultp_builder_conditions', array());
        if($conditions){
            if(isset($conditions['archive'][$post_id])) {
                unset($conditions['archive'][$post_id]);
                update_option('ultp_builder_conditions', $conditions);
            }
        }
        delete_post_meta($post_id, '_ultp_active');
    }

    public function ultp_ultp_edit_callback() {
        if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'ultp-nonce')) {
            return ;
        }
        $results = array();
        $results['title'] = get_the_title(sanitize_title($_POST['post_id']));
        $results['type'] = get_post_meta(sanitize_key($_POST['post_id']), '_ultp_builder_type', true);

        $options = get_option('ultp_builder_conditions', array());
        if (!empty($options)) {
            $temp = isset($options[$results['type']][sanitize_key($_POST['post_id'])]) ? $options[$results['type']][sanitize_key($_POST['post_id'])] : array();
            $results['conditions'] = $temp;
            
            $temp_tax = array();
            $taxonomy_list = ultimate_post_pro()->get_taxonomy_list();
            foreach ($temp as $val) {
                $val = explode('/', $val);
                if (isset($val[3])) {
                    if ($val[2] == 'author') {
                        $user = get_user_by('id', $val[3]);
                        $author_id[] = array('id' => $val[3], 'text' => $user->user_login );
                    } else if (in_array($val[2], $taxonomy_list)) {
                        $term = get_term( $val[3], $val[2] );
                        $temp_tax[$val[2]][] = array('id' => $val[3], 'text' => $term->name );
                    }
                }
            }
            if (!empty($author_id)) {
                $results['author_id'] = $author_id;
            }
            if (!empty($temp_tax)) {
                foreach ($temp_tax as $key => $value) {
                    $results['taxonomy'][$key] = $value;
                }
            }
        }
        
        echo json_encode($results);
        die();
    }


    public function ultp_search_callback() {
        if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'ultp-nonce')) {
            return ;
        }

        $results = array();
        if(sanitize_text_field($_POST['type']) == 'author') {
            $users = new \WP_User_Query( array(
                'search'         => '*'.sanitize_text_field( $_POST['term'] ).'*',
                'search_columns' => array(
                    'user_login',
                    'user_nicename',
                    'user_email'
                ),
            ) );
            $users_found = $users->get_results();
            if (!empty($users_found)) {
                foreach ($users_found as $user) {
                    $results[] = array( 'id' => $user->data->ID, 'text' => $user->data->user_login );
                }
            }
            echo json_encode($results);
            die();
        } else {
            $args = array(
                'taxonomy'      => array( sanitize_text_field($_POST['type']) ),
                'orderby'       => 'id', 
                'order'         => 'ASC',
                'hide_empty'    => true,
                'fields'        => 'all',
                'name__like'    => sanitize_text_field( $_POST['term'] )
            );

            $terms = get_terms( $args );
            if (!empty($terms)) {
                foreach ($terms as $term) {
                    $results[] = array( 'id' => $term->term_id, 'text' => $term->name );
                }
            }
            echo json_encode($results);
            die();
        }
        die();
    }




    public function ultp_new_post_callback() {
        if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'ultp-nonce')){
            return ;
        }

        $post_id = '';
        if (sanitize_text_field($_POST['operation']) == 'insert') {
            $post_id = wp_insert_post(
                array(
                    'post_title'   => sanitize_text_field($_POST['post_title']),
                    'post_content' => '',
                    'post_type'    => 'ultp_builder',
                    'post_status'  =>  'draft'    
                )
            );
        } else {
            $post_id = sanitize_text_field($_POST['post_id']);
            wp_update_post( 
                array(
                    'ID'         => $post_id,
                    'post_title' => sanitize_text_field($_POST['post_title'])
                )
            );
        }

        $conditions = get_option('ultp_builder_conditions', array());
        if (empty($conditions)) {
            $conditions = array(
                'archive' => array()
            );
        }
    
        if ($post_id) {
            $temp = array();
            if (isset($_POST['archive'])) {
                $temp[] = 'include/archive';
            }
            if (isset($_POST['author'])) {
                $temp[] = 'include/archive/author';
            }
            if (isset($_POST['date'])) {
                $temp[] = 'include/archive/date';
            }
            if (isset($_POST['search'])) {
                $temp[] = 'include/archive/search';
            }
            if (isset($_POST['error'])) {
                $temp[] = 'include/archive/error';
            }
            if (isset($_POST['author_id'])) {
                if(!empty($_POST['author_id'])){
                    foreach ($_POST['author_id'] as $val) {
                        $temp[] = 'include/archive/author/'.$val;
                    }
                }
            }
            $taxonomy_list = ultimate_post_pro()->get_taxonomy_list();
            foreach ($taxonomy_list as $value) {
                if (isset($_POST[$value])) {
                    $temp[] = 'include/archive/'.$value;
                } 
            }
            foreach ($taxonomy_list as $value) {
                if (isset($_POST[$value.'_id'])) {
                    if (!empty($_POST[$value.'_id'])) {
                        foreach ($_POST[$value.'_id'] as $val) {
                            $temp[] = 'include/archive/'.$value.'/'.sanitize_text_field($val);
                        }
                    }
                }
            }

            $conditions['archive'][$post_id] = $temp;

            update_option('ultp_builder_conditions', $conditions);
            update_post_meta($post_id, '_ultp_builder_type', sanitize_text_field($_POST['post_filter']) );

            echo get_edit_post_link($post_id);
            die();
        }
    }


}