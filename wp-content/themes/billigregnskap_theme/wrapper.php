<!DOCTYPE html>
<!--[if lt IE 7 ]> <html class="ie6" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7 ]>    <html class="ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]>    <html class="ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 9 ]>    <html class="ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html <?php language_attributes(); ?>> <!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />

	<title><?php wp_title(''); ?></title>

	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />

	<?php wp_head(); ?>

	<!--[if lte IE 9]><link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/styles/ie.css" type="text/css" media="screen" /><![endif]-->
	<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
</head>

<body <?php body_class(); ?>>

	<?php appthemes_before(); ?>

	<?php appthemes_before_header(); ?>
	<?php get_header( app_template_base() ); ?>
	<?php appthemes_after_header(); ?>

	<div id="content" class="container">
		<div id="content-mid" class="row rounded">
			<div id="content-inner" class="rounded">

				<?php include app_template_path(); ?>

				<div class="clear"></div>
            </div> <!-- /content-inner -->
		</div> <!-- /content-mid -->
	</div> <!-- /content -->

	<?php appthemes_before_footer(); ?>
	<?php get_footer( app_template_base() ); ?>
	<?php appthemes_after_footer(); ?>

	<?php appthemes_after(); ?>

	<?php wp_footer();?>

</body>
</html>
