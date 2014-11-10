<?php

add_action( 'customize_register', 'va_customize_color_scheme' );

function va_customize_color_scheme( $wp_customize ){
	global $va_options;

	$wp_customize->add_setting( 'va_options[color]', array(
		'default' => $va_options->color,
		'type' => 'option'
	) );

	$wp_customize->add_control( 'va_color_scheme', array(
		'label'      => __( 'Color Scheme', APP_TD ),
		'section'    => 'colors',
		'settings'   => 'va_options[color]',
		'type'       => 'radio',
		'choices' => _va_get_color_choices(),
	) );

}

add_action( 'customize_register', 'va_customize_listings' );

function va_customize_listings( $wp_customize ){
	global $va_options;

	$wp_customize->add_section( 'va_listings', array(
		'title' => __( 'Listings', APP_TD ),
		'priority' => 35
	));

	$wp_customize->add_setting( 'va_options[listings_per_page]', array(
		'default' => $va_options->listings_per_page,
		'type' => 'option'
	) );

	$wp_customize->add_setting( 'va_options[featured_per_page]', array(
		'default' => $va_options->featured_per_page,
		'type' => 'option'
	) );

	$wp_customize->add_control( 'va_listings_per_page', array(
		'label'      => __( 'Listings Per Page', APP_TD ),
		'section'    => 'va_listings',
		'settings'   => 'va_options[listings_per_page]',
		'type'       => 'text',
	) );

	$wp_customize->add_control( 'va_featured_per_page', array(
		'label'      => __( 'Featured Per Page', APP_TD ),
		'section'    => 'va_listings',
		'settings'   => 'va_options[featured_per_page]',
		'type'       => 'text',
	) );

}

add_action( 'customize_register', 'va_customize_categories' );

function va_customize_categories( $wp_customize ){
	global $va_options;
	categories_options( 'categories_menu', __( 'Categories Page Options', APP_TD ), $wp_customize );
	categories_options( 'categories_dir', __( 'Categories Menu Item Options', APP_TD ), $wp_customize );

}

function categories_options( $prefix, $title, $wp_customize ) {
	global $va_options;

	$wp_customize->add_section( 'va_'.$prefix.'_categories', array(
		'title' => __( $title, APP_TD ),
		'priority' => 999,
	));

	$wp_customize->add_setting( 'va_options['.$prefix.'][count]', array(
		'default' => $va_options->listings_per_page,
		'type' => 'option'
	) );

	$wp_customize->add_control( 'va_'.$prefix.'_count', array(
		'label'      => __( 'Count Listings in Category', APP_TD ),
		'section'    => 'va_'.$prefix.'_categories',
		'settings'   => 'va_options['.$prefix.'][count]',
		'type'       => 'checkbox',
	) );

	$wp_customize->add_setting( 'va_options['.$prefix.'][hide_empty]', array(
		'default' => $va_options->listings_per_page,
		'type' => 'option'
	) );

	$wp_customize->add_control( 'va_'.$prefix.'_hide_empty', array(
		'label'      => __( 'Hide Empty Categories', APP_TD ),
		'section'    => 'va_'.$prefix.'_categories',
		'settings'   => 'va_options['.$prefix.'][hide_empty]',
		'type'       => 'checkbox',
	) );

	$wp_customize->add_setting( 'va_options['.$prefix.'][depth]', array(
		'default' => $va_options->listings_per_page,
		'type' => 'option'
	) );

	$wp_customize->add_control( 'va_'.$prefix.'_depth', array(
		'label'      => __( 'Category Depth', APP_TD ),
		'section'    => 'va_'.$prefix.'_categories',
		'settings'   => 'va_options['.$prefix.'][depth]',
		'type'       => 'select',
		'choices' => array(
			'999' => __( 'Show All', APP_TD ),
			'0' => '0',
			'1' => '1',
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'5' => '5',
			'6' => '6',
			'7' => '7',
			'8' => '8',
			'9' => '9',
			'10' => '10',
		),
	) );

	$wp_customize->add_setting( 'va_options['.$prefix.'][subnum]', array(
		'default' => $va_options->listings_per_page,
		'type' => 'option'
	) );

	$wp_customize->add_control( 'va_'.$prefix.'_subnum', array(
		'label'      => __( 'Number of Sub-Categories', APP_TD ),
		'section'    => 'va_'.$prefix.'_categories',
		'settings'   => 'va_options['.$prefix.'][subnum]',
		'type'       => 'select',
		'choices' => array(
			'999' => __( 'Show All', APP_TD ),
			'0' => '0',
			'1' => '1',
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'5' => '5',
			'6' => '6',
			'7' => '7',
			'8' => '8',
			'9' => '9',
			'10' => '10',
		),
	) );

}
