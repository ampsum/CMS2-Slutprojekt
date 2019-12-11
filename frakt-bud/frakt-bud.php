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
             $this->supports = array(
           			'shipping-zones',
            			'instance-settings',
            			'instance-settings-modal',
           		);
             $this->init();

             add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
           }

           public function init() {
            $this->instance_form_fields = include 'includes/settings-frakt-bud.php';
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
          protected function evaluate_cost( $sum, $args = array() ) {
        		include_once WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php';

        		// Allow 3rd parties to process shipping cost arguments.
        		$args           = apply_filters( 'woocommerce_evaluate_shipping_cost_args', $args, $sum, $this );
        		$locale         = localeconv();
        		$decimals       = array( wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'], ',' );
        		$this->fee_cost = $args['cost'];

        		// Expand shortcodes.
        		add_shortcode( 'fee', array( $this, 'fee' ) );

        		$sum = do_shortcode(
        			str_replace(
        				array(
        					'[qty]',
        					'[cost]',
        				),
        				array(
        					$args['qty'],
        					$args['cost'],
        				),
        				$sum
        			)
        		);

        		remove_shortcode( 'fee', array( $this, 'fee' ) );

        		// Remove whitespace from string.
        		$sum = preg_replace( '/\s+/', '', $sum );

        		// Remove locale from string.
        		$sum = str_replace( $decimals, '.', $sum );

        		// Trim invalid start/end characters.
        		$sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );

        		// Do the math.
        		return $sum ? WC_Eval_Math::evaluate( $sum ) : 0;
        	}

          public function get_package_item_qty( $package ) {
        		$total_quantity = 0;
        		foreach ( $package['contents'] as $item_id => $values ) {
        			if ( $values['quantity'] > 0 && $values['data']->needs_shipping() ) {
        				$total_quantity += $values['quantity'];
        			}
        		}
        		return $total_quantity;
        	}
          public function find_shipping_classes( $package ) {
            $found_shipping_classes = array();

            foreach ( $package['contents'] as $item_id => $values ) {
              if ( $values['data']->needs_shipping() ) {
                $found_class = $values['data']->get_shipping_class();

                if ( ! isset( $found_shipping_classes[ $found_class ] ) ) {
                  $found_shipping_classes[ $found_class ] = array();
                }

                $found_shipping_classes[ $found_class ][ $item_id ] = $values;
              }
            }

            return $found_shipping_classes;
          }

          public function calculate_shipping( $package = array() ) {
            $rate = array(
        			'id'      => $this->get_rate_id(),
        			'label'   => $this->title,
        			'cost'    => 0,
        			'package' => $package,
        		);
        		// Calculate the costs.
            $has_costs = false; // True when a cost is set. False if all costs are blank strings.
          //   $cost = $this->settings['cost'];

        		$cost      = $this->get_option( 'cost' );

        		if ( '' !== $cost ) {
        			$has_costs    = true;
        			$rate['cost'] = $this->evaluate_cost(
        				$cost,
        				array(
        					'qty'  => $this->get_package_item_qty( $package ),
        					'cost' => $package['contents_cost'],
        				)
        			);
        		}

        		// Add shipping class costs.
        		$shipping_classes = WC()->shipping()->get_shipping_classes();

        		if ( ! empty( $shipping_classes ) ) {
        			$found_shipping_classes = $this->find_shipping_classes( $package );
        			$highest_class_cost     = 0;

        			foreach ( $found_shipping_classes as $shipping_class => $products ) {
        				// Also handles BW compatibility when slugs were used instead of ids.
        				$shipping_class_term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
        				$class_cost_string   = $shipping_class_term && $shipping_class_term->term_id ? $this->get_option( 'class_cost_' . $shipping_class_term->term_id, $this->get_option( 'class_cost_' . $shipping_class, '' ) ) : $this->get_option( 'no_class_cost', '' );

        				if ( '' === $class_cost_string ) {
        					continue;
        				}

        				$has_costs  = true;
        				$class_cost = $this->evaluate_cost(
        					$class_cost_string,
        					array(
        						'qty'  => array_sum( wp_list_pluck( $products, 'quantity' ) ),
        						'cost' => array_sum( wp_list_pluck( $products, 'line_total' ) ),
        					)
        				);

        				if ( 'class' === $this->type ) {
        					$rate['cost'] += $class_cost;
        				} else {
        					$highest_class_cost = $class_cost > $highest_class_cost ? $class_cost : $highest_class_cost;
        				}
        			}

        			if ( 'order' === $this->type && $highest_class_cost ) {
        				$rate['cost'] += $highest_class_cost;
        			}
        		}

        		if ( $has_costs ) {
        			$this->add_rate( $rate );
            }

          //  $cost = $this->settings['price'];

            //lägg till det hämtade baspriset till fraktklassen!
            // $this->add_rate(
            //   array(
            //     'label'   => $this->title,
            //     'cost'    => $cost,
            //     'taxes'   => false,
            //     'package' => $package,
            //   )
            // );
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
