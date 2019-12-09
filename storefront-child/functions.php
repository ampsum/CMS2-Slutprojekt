<?php
add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );

function enqueue_parent_styles() {
   wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}

//Registrar custom post type - butiker
function create_post_type(){
  register_post_type( 'Butiker' , array(
    'labels' => array(
      'name' => 'Butiker',
      'singular_name' => 'Butik'
    ),
    'description' => 'Plats för alla butiker',
    'public' => true,
    'show_in_admin_bar' => true,
    'hierarchical' => false,
    'supports' => array(
      'title',
      'editor',
    ),
    'has_archive' => true,
  ));
}
add_action( 'init' , 'create_post_type' );





 ?>
