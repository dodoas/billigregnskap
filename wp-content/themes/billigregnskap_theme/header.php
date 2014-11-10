<div id="masthead" class="container">
	<div class="row">
		<hgroup>
			<?php va_display_logo(); ?>
		</hgroup>
		<?php if ( is_active_sidebar( 'va-header' ) ) : ?>
			<div class="advert">
				<?php dynamic_sidebar( 'va-header' ); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
<div id="main-navigation" class="container">
	<div class="row">
		<div id="rounded-nav-box" class="rounded">
			<div id="rounded-nav-box-overlay">
				<?php va_display_navigation_menu(); ?>
				<?php if ( !is_page_template( 'create-listing.php' ) ) : ?>
				<form method="get" action="<?php echo trailingslashit( get_bloginfo( 'url' ) ); ?>">
					<div id="main-search">
						<div class="search-for">
							<div class="input-cont h39">
								<div class="left h39"></div>
								<div class="mid h39">
									<input type="text" name="ls" id="search-text" class="text" value="<?php va_show_search_query_var( 'ls' ); ?>" />
								</div>
								<div class="right h39"></div>
							</div>
						</div>
						<!-- Høyre søkefelt
						<div class="search-location">
							<label for="search-location">
								<span class="search-title"><?php _e( 'Near ', APP_TD ); ?></span><span class="search-help"><?php _e( '(city, country)', APP_TD ); ?></span>
							</label>
							<div class="input-cont h39">
								<div class="left h39"></div>
								<div class="mid h39">
									<input type="text" name="location" id="search-location" class="text" value="<?php va_show_search_query_var( 'location' ); ?>" />
								</div>
								<div class="right h39"></div>
							</div>
						</div>
						-->
						<div style="float: right;color: #555; border: 1px solid #bbb; background: white; padding: 4px;" class="">
							Legg til opplysninger om ditt firma her
						</div>

						<div class="search-button">
							<button type="submit" id="search-submit" class="rounded-small">Søk<!-- <?php _e( 'Search', APP_TD ); ?>--></button>
						</div>
					</div>
					<?php the_search_refinements(); ?>
				</form>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<div id="breadcrumbs" class="container">
	<!-- Category Path
	<div class="row">
		<?php breadcrumb_trail( array(
			'separator' => '&raquo;',
			'before' => '',
			'show_home' => '<img src="' . get_template_directory_uri() . '/images/breadcrumb-home.png" />',
		) ); ?>
	</div>
	-->
</div>

