<?php if ( is_search() ) : ?>
<aside class="widget" id="refine-search">
<form method="get" action="<?php bloginfo( 'url' ); ?>">
	<input type="hidden" name="ls" value="<?php va_show_search_query_var( 'ls' ); ?>" />
	<input type="hidden" name="location" value="<?php va_show_search_query_var( 'location' ); ?>" />

	<div class="section-head"><h3 class="widget-title"><?php _e ( 'Refine Search', APP_TD ); ?></h3></div>

	<div id="refine-order">
		<h4><?php _e ( 'Sorting', APP_TD ); ?></h4>
		<?php the_refine_order_ui(); ?>
	</div>

<?php if ( get_query_var( 'app_geo_query' ) ) : ?>
	<div id="refine-distance">
		<h4><?php _e ( 'Distance', APP_TD ); ?></h4>
		<?php the_refine_distance_ui(); ?>
	</div>
<?php endif; ?>

	<div id="refine-categories">
		<h4><?php _e ( 'Categories', APP_TD ); ?></h4>
		<?php the_refine_category_ui(); ?>
	</div>

	<input type="submit" value="<?php esc_attr_e( 'Update', APP_TD ); ?>" />
</form>
</aside>

<?php dynamic_sidebar( 'search-listing' ); ?>

<?php else: ?>

<?php dynamic_sidebar( 'main' ); ?>

<?php endif; ?>
