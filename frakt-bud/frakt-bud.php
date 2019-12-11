<?php
/**
 * Plugin Name: Frakt med bud
 * Description: Frakt med bud där priset baseras på produkternas fraktklass
 * Author: Sara, Maija
 * Version: 1.0
 */

 defined( 'ABSPATH' ) or die();

 $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
 if ( in_array( 'woocommerce/woocommerce.php',  $active_plugins) ) {

     function add_my_shipping_method()
     {
         class WC_Shipping_Home_Delivery extends WC_Shipping_Method
         {
           public function __construct( $instance_id = 0 )
           {
             $this->id = 'home_delivery_shipping';
             $this->instance_id = absint( $instance_id );
             $this->method_title = __( 'Frakt med bud', 'woocommerce' );
             $this->method_description = __( 'Frakt med bud där priset baseras på produkternas fraktklass', 'woocommerce' );
             // $this->enabled	= $this->get_option( 'enabled' );
             // $this->title = $this->get_option( 'title' );
             // $this->cost = $this->get_option( 'cost' );
             // $this->type = $this->get_option( 'type', 'class' );
             $this->supports = array(
           			'shipping-zones',
            			'instance-settings',
            			'instance-settings-modal',
           		);
             $this->init();

             add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
           }

           public function init() {
         		// $this->init_form_fields();
         		// $this->init_settings();
            $this->instance_form_fields = include 'includes/settings-home-delivery.php';
          //  $this->enabled	= $this->get_option( 'enabled' );
            $this->title = $this->get_option( 'title' );
            $this->cost = $this->get_option( 'cost' );
            $this->type = $this->get_option( 'type', 'class' );

         	}

          // public function init_form_fields() {
          //   $this->form_fields = array(
          //     'enabled' => array(
          //       'title' 		=> __( 'Aktivera/Avaktivera', 'woocommerce' ),
          //       'type' 			=> 'checkbox',
          //       'label' 		=> __( 'Aktivera', 'woocommerce' ),
          //       'default' 		=> 'yes'
          //     ),
          //     'title' => array(
          //       'title' 		=> __( 'Title', 'woocommerce' ),
          //       'type' 			=> 'text',
          //       'description' 	=> __( 'Namnet på fraktmodulen som syns för användaren', 'woocommerce' ),
          //       'default'		=> __( 'Frakt med bud', 'woocommerce' ),
          //     ),
          //     'price' => array(
          //       'title' => __( 'Pris', 'price' ),
          //       'type' => 'number',
          //       'description' => __( 'Baspris för frakt med bud', 'price' ),
          //       'default' => 50
          //     ),
          //   );
          // }


          public function calculate_shipping( $package = array() ) {
          //  $cost = $this->settings['price'];
            $cost      = $this->get_option( 'cost' );



            //lägg till det hämtade baspriset till fraktklassen!
            $shipping_class = $this->find_shipping_classes( $package ); //array



            $this->add_rate(
              array(
                'label'   => $this->title,
                'cost'    => $cost,
                'taxes'   => false,
                'package' => $package,
              )
            );


            }
        }

        function load_new_shipping($methods)
        {
            $methods['home_delivery_shipping'] = "WC_Shipping_Home_Delivery";
            return $methods;
        }
        add_filter('woocommerce_shipping_methods', 'load_new_shipping');

     }
     add_action('woocommerce_shipping_init', 'add_my_shipping_method');
   }

 ?>
