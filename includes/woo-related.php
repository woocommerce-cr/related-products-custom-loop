<?php
/**
 * Related Products
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $woocommerce_loop;

if ( empty( $product ) || ! $product->exists() ) {
	return;
}

//If product tag, get the terms
if ($product_tags = get_the_terms($product->id, 'product_tag')) {

	//Product Tags IDs array
	$product_tag_ids = array();

	foreach($product_tags as $product_tag) {
		$product_tag_ids[] = $product_tag->term_id;
	}

	//Create the args
	$count_args = array(
		'post_type'				=> 'product',
		'ignore_sticky_posts'	=> 1,
		'posts_per_page'		=> 4,
		'no_found_rows' 		=> 1,
		'orderby' 				=> $orderby,
		'post__not_in'			=> array($product->id),
		'tax_query'				=> array(array(
										'taxonomy'	=> 'product_tag',
										'field'		=> 'id',
										'terms'		=> $product_tag_ids,
									)),
	);

	$query = new WP_Query( $count_args );

	//count products with tags
	$count = (int)$query->post_count;

	if ( $count == 0 ){

		//if no related tags, try categories
		if ( $product_cats = get_the_terms( $product->id, 'product_cat' ) ) {

			//Product Tags IDs array
			$product_cat_ids = array();

			foreach( $product_cats as $product_cat ) {
				$product_cat_ids[] = $product_cat->term_id;
			}

			//Create the args
			$count_args = array(
				'post_type'				=> 'product',
				'ignore_sticky_posts'	=> 1,
				'posts_per_page'		=> 4,
				'no_found_rows' 		=> 1,
				'orderby' 				=> $orderby,
				'post__not_in'			=> array($product->id),
				'tax_query'				=> array(array(
												'taxonomy'	=> 'product_cat',
												'field'		=> 'id',
												'terms'		=> $product_cat_ids,
											)),
			);

			$query = new WP_Query( $count_args );
			$count = (int)$query->post_count;

			//if no related categories, do nothing.
			if ( $count == 0 ){

				return;

			} else {

				//if related categories, show them
				$args = $count_args;

			}

		} else {

			//if product has tags but no related, and no categories, run default related query
			$related = $product->get_related( 4 );

			if ( sizeof( $related ) == 0 ) return;

			$args = apply_filters( 'woocommerce_related_products_args', array(
				'post_type'            => 'product',
				'ignore_sticky_posts'  => 1,
				'no_found_rows'        => 1,
				'posts_per_page'       => 4,
				'orderby'              => $orderby,
				'post__in'             => $related,
				'post__not_in'         => array( $product->id )
			) );

		}

	} else {

		//if related tags found, show them
		$args = $count_args;

	}

} else {

	//product has no tags, run default query
	$related = $product->get_related( 4 );

	if ( sizeof( $related ) == 0 ) return;

	$args = apply_filters( 'woocommerce_related_products_args', array(
		'post_type'            => 'product',
		'ignore_sticky_posts'  => 1,
		'no_found_rows'        => 1,
		'posts_per_page'       => 4,
		'orderby'              => $orderby,
		'post__in'             => $related,
		'post__not_in'         => array( $product->id )
	) );

}

$products = new WP_Query( $args );

$woocommerce_loop['columns'] = $columns;

if ( $products->have_posts() ) : ?>

	<div class="related-products">

		<h4><?php _e( 'Related Attractions', 'woocommerce' ); ?></h4>

		<div class="grid">

		<?php woocommerce_product_loop_start(); ?>

			<?php while ( $products->have_posts() ) : $products->the_post(); ?>

				<li class="inl">
					<span class="content-tag">Save <?php the_field('post_type_save'); ?></span>
					<i class="preview-image" style="background-image: url(<?php the_post_thumbnail_url(); ?>);"></i>
					<h3>
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h3>
					<a href="<?php the_permalink(); ?>" class="inl btn-2">View Details</a>
				</li><!--Inline-->

			<?php endwhile; // end of the loop. ?>

		<?php woocommerce_product_loop_end(); ?>

		</div>

	</div>

<?php endif;

wp_reset_postdata();
