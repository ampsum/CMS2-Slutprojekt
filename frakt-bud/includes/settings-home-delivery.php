<?php
defined( 'ABSPATH' ) || exit;

$settings = array(
	'title' => array(
		'title' 		=> __( 'Title', 'woocommerce' ),
		'type' 			=> 'text',
		'description' 	=> __( 'Namnet på fraktmodulen som syns för användaren', 'woocommerce' ),
		'default'		=> __( 'Frakt med bud', 'woocommerce' ),
	),
	'price' => array(
		'title' => __( 'Pris', 'price' ),
		'type' => 'number',
		'description' => __( 'Baspris för frakt med bud', 'price' ),
		'default' => 50
	),
);

$shipping_classes = WC()->shipping()->get_shipping_classes();

if ( ! empty( $shipping_classes ) ) {
	$settings['class_costs'] = array(
		'title'       => __( 'Kostnad för fraktklasser', 'woocommerce' ),
		'type'        => 'title'
	);
	foreach ( $shipping_classes as $shipping_class ) {
		if ( ! isset( $shipping_class->term_id ) ) {
			continue;
		}
		$settings[ 'class_cost_' . $shipping_class->term_id ] = array(
			'title'             => sprintf( __( '"%s"', 'woocommerce' ), esc_html( $shipping_class->name ) ),
			'type'              => 'text',
			'description'       => $cost_desc,
			'default'           => $this->get_option( 'class_cost_' . $shipping_class->slug ),
			'desc_tip'          => true,
			'sanitize_callback' => array( $this, 'sanitize_cost' ),
		);
	}
}

return $settings;
