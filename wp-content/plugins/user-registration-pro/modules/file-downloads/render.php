<?php
/**
 * Template for rendering the Files block.
 *
 * @var array $attributes Block attributes.
 */

use WPEverest\URM\Pro\FileDownloads\Repositories\FileRepository;
use WPEverest\URM\Pro\FileDownloads\Taxonomies\Taxonomy;
use WPEverest\URM\Pro\FileDownloads\Models\File;

$file_ids       = $attributes['fileIds'] ?? [];
$category_ids   = $attributes['categoryIds'] ?? [];
$display_mode   = $attributes['displayMode'] ?? 'ids';
$show_file_size = $attributes['showFileSize'] ?? false;
$show_file_type = $attributes['showFileType'] ?? false;

$validation_errors = [
	'ids'        => empty( $file_ids ) ? __( 'Please select at least one file.', 'user-registration' ) : null,
	'categories' => empty( $category_ids ) ? __( 'Please select at least one category.', 'user-registration' ) : null,
	'both'       => ( empty( $file_ids ) && empty( $category_ids ) ) ? __( 'Please select at least one file or category.', 'user-registration' ) : null,
];

if ( isset( $validation_errors[ $display_mode ] ) && $validation_errors[ $display_mode ] ) {
	echo '<p>' . esc_html( $validation_errors[ $display_mode ] ) . '</p>';
	return;
}

$file_repository = new FileRepository();
$query_args_map  = [
	'ids'        => function () use ( $file_ids ) {
		return [ 'post__in' => array_map( 'absint', $file_ids ) ]; },
	'categories' => function () use ( $category_ids ) {
		return [
			'tax_query' => [
				[
					'taxonomy' => Taxonomy::FILE_CATEGORY,
					'field'    => 'term_id',
					'terms'    => array_map( 'absint', $category_ids ),
					'operator' => 'IN',
				],
			],
		];},
	'both'       => function () {
		return null;
	},
];

if ( $display_mode === 'both' ) {
	$category_files = ! empty( $category_ids ) ? $file_repository->all( $query_args_map['categories']() ) : [];
	$id_files       = ! empty( $file_ids ) ? $file_repository->all( $query_args_map['ids']() ) : [];
	$files          = array_unique( array_merge( $category_files, $id_files ), SORT_REGULAR );
} else {
	$files = $file_repository->all( $query_args_map[ $display_mode ]() );
}

if ( empty( $files ) ) {
	echo '<p>' . esc_html__( 'No files found' ) . '</p>';
	return;
}

$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => 'urfd-files',
	]
);


/**
 * @param File $file
 * @return string
 */
$get_file_extension = function ( $file ) {
	$mime_type = $file->get_file_mime_type();
	if ( ! $mime_type ) {
		return '';
	}
	$file_name = $file->get_file_name();
	if ( $file_name ) {
		$ext = pathinfo( $file_name, PATHINFO_EXTENSION );
		if ( $ext ) {
			return strtoupper( $ext );
		}
	}
	$parts = explode( '/', $mime_type );
	if ( ! empty( $parts[1] ) ) {
		$ext = str_replace( [ 'x-', 'vnd.' ], '', $parts[1] );
		return strtoupper( $ext );
	}
	return '';
};

$is_rest = defined( 'REST_REQUEST' ) && REST_REQUEST;
?>

<div <?php echo ! $is_rest ? wp_kses_data( $wrapper_attributes ) : 'class="urfd-files"'; ?>>
	<ul class="urfd-files__list">
		<?php foreach ( $files as $file ) : ?>
			<?php
				$download_url = $file->get_download_url();
				$file_name    = $file->get_name();
				$file_size    = $file->get_file_size();
				$file_type    = $get_file_extension( $file );
			?>
		<li class="urfd-files__item">
			<?php if ( $show_file_type && $file_type ) : ?>
			<div class="urfd-files__badge">
				<span class="urfd-files__type"><?php echo esc_html( $file_type ); ?></span>
			</div>
			<?php endif; ?>
			<div class="urfd-files__content">
				<div class="urfd-files__name"><?php echo esc_html( $file_name ); ?></div>
				<?php if ( $show_file_size && $file_size > 0 ) : ?>
				<div class="urfd-files__size"><?php echo esc_html( size_format( $file_size, 2 ) ); ?></div>
				<?php endif; ?>
			</div>
			<a class="urfd-files__download"
				href="<?php echo $is_rest ? 'javascript:void(0)' : esc_url( $download_url ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
					stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
					class="urfd-files__icon">
					<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
					<polyline points="7 10 12 15 17 10" />
					<line x1="12" y1="15" x2="12" y2="3" />
				</svg>
				<span>
					<?php esc_html_e( 'Download', 'user-registration' ); ?>
				</span>
			</a>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
