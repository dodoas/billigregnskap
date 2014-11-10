<!-- 
<div id="footer" class="container">
	<div class="row">
		<?php dynamic_sidebar( 'va-footer' ); ?>
	</div>
</div>
-->
<div id="post-footer" class="container">
	<div class="row">
		<?php wp_nav_menu( array(
			'container' => false,
			'theme_location' => 'footer',
			'fallback_cb' => false
		) ); ?>

		<div id="theme-info">BilligRegnskap.no<a href="http://www.billigregnskap.no" target="_blank"></a></div>
	</div>
</div>
