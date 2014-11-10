<?php
// Template Name: Listings
?>

<div id="main" class="list">
	<div class="section-head">
		<?php if ( is_tax( VA_LISTING_CATEGORY ) || is_tax( VA_LISTING_TAG ) ) { ?>
			<h1><?php printf( __( 'Business Listings - %s', APP_TD ), single_term_title( '', false )); ?></h1> 
		<?php } else { ?>
			<h1><?php _e( 'Business Listings', APP_TD ); ?></h1>
		<?php } ?>
	</div>

<?php
if ( $featured = va_get_featured_listings() ) :
	while ( $featured->have_posts() ) : $featured->the_post();
?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'featured' ); ?>>
		<div class="featured-head">
			<h3><?php _e( 'Featured', APP_TD ); ?></h3>
		</div>

		<?php get_template_part( 'content-listing' ); ?>
	</article>
<?php
	endwhile;
endif;

if ( $featured || is_page() ) :
	$args = $wp_query->query;

	// The template is loaded as a page, not as an archive
	if ( is_page() )
		$args['post_type'] = VA_LISTING_PTYPE;

	// Don't want to show featured listings a second time
	if ( $featured )
		$args['post__not_in'] = wp_list_pluck( $featured->posts, 'ID' );

	$args['posts_per_page'] = $va_options->listings_per_page;

	query_posts( $args );
endif;

if ( have_posts() ) : ?>
	
	<?php if ( is_search() ) : ?>
	<article class="listing">
		<h2><?php printf( __( 'Listings found for "%s" near "%s"', APP_TD ), va_get_search_query_var( 'ls' ), va_get_search_query_var( 'location' ) ); ?></h2>
	</article>
	<?php endif; ?>

<?php appthemes_before_loop( VA_LISTING_PTYPE ); ?>
<?php while ( have_posts() ) : the_post(); ?>
	<?php appthemes_before_post( VA_LISTING_PTYPE ); ?>
	<?php if ( is_search() && va_is_listing_featured(get_the_ID())) : ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'featured' ); ?>>
		<div class="featured-head">
			<h3><?php _e( 'Featured', APP_TD ); ?></h3>
		</div>
	<?php else: ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php endif; ?>
			<?php get_template_part( 'content-listing' ); ?>
	</article>
	<?php appthemes_after_post( VA_LISTING_PTYPE ); ?>
<?php endwhile; ?>

<?php elseif ( !$featured ) : ?>
	<?php if ( is_search() ) : ?>
	<article class="listing">
		<h2><?php printf( __( 'Sorry, no listings were found for "%s" near "%s"', APP_TD ), va_get_search_query_var( 'ls' ), va_get_search_query_var( 'location' ) ); ?></h2>
	</article>
	<?php elseif ( is_archive() ) : ?>	
	<article class="listing">
		<h2><?php printf( __( 'Sorry there are no listings for %s "%s"', APP_TD ), ( is_tax( VA_LISTING_CATEGORY ) ? __( 'category', APP_TD ) : __( 'tag', APP_TD ) ), single_term_title( '', false ) ); ?></h2>
	</article>
	<?php endif; ?>
<?php endif; ?>
	<div class="advert">
		<?php dynamic_sidebar( 'va-listings-ad' ); ?>
	</div>
<?php if ( $wp_query->max_num_pages > 1 ) : ?>
	<nav class="pagination">
		<?php appthemes_pagenavi(); ?>
	</nav>
<?php endif; ?>

</div>

<div id="sidebar">
	<?php get_sidebar( app_template_base() ); ?>
</div>
