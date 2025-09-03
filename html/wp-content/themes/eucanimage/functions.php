<?php
/**
 * eucanimage functions and definitions
 *
**/

// add custom menu
function register_my_menus_tururu() {
  register_nav_menus(
    array(
      'header-menu' => __( 'Header Menu' ),
      'extra-menu' => __( 'Extra Menu' )
    )
  );


}
add_action( 'init', 'register_my_menus_tururu' );

// add menu class filter on <LI>
function add_additional_class_on_li($classes, $item, $args) {
    if(isset($args->li_class)) {
        $classes[] = $args->li_class;
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'add_additional_class_on_li', 1, 3);

// add menu class filter on <A>
function add_additional_class_on_a($classes, $item, $args) {
    if(isset($args->a_class)) {
        $classes['class'] = $args->a_class;
    }
    return $classes;
}
add_filter('nav_menu_link_attributes', 'add_additional_class_on_a', 1, 3);



// add CSS
function my_custom_theme_enqueue() {
	wp_enqueue_style( 'my-custom-theme', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'my_custom_theme_enqueue' );

