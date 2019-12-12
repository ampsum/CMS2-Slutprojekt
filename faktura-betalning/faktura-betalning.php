<?php
/**
 * Plugin Name: Betalning med faktura
 * Description: Betalning mot faktura genom att man anger ett giltigt personnummer
 * Author: Sara, Maija
 * Version: 1.0
 */

 defined( 'ABSPATH' ) or die();

 if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
 {
    add_action('plugins_loaded', 'add_my_payment_gateway');

     function add_my_payment_gateway()
     {
         class WC_Invoice_Payment_Gateway extends WC_Payment_Gateway
         {
           function __construct()
           {
               $this->id = "invoice-payment";
               $this->method_title = __("Invoice payment", "invoice");
               $this->method_description = __("Betalning mot faktura genom att man anger ett giltigt personnummer", "invoice");
               $this->title = $this->method_title;

               $this->icon = null;
               //$this->has_fields = true;
               $this->has_fields = false;

               $this->init_form_fields();
               $this->init_settings();

               add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
           }

           function init_form_fields(){
             $this->form_fields = [
                 'enabled' => [
                     'title' => __('Aktivera/Avaktivera', 'invoice'),
                     'label' => __('Aktivera', 'invoice'),
                     'type' => 'checkbox',
                     'default' => 'no'
                 ],
                 'title'      => array(
                   'title'       => __( 'Title', 'woocommerce' ),
                   'type'        => 'text',
                   'description' => __( 'Namnet på betalningsmetoden som syns för användaren', 'woocommerce' ),
                   'default'     => $this->method_title,
                   'desc_tip'    => true,
                 ),
                 'description'        => array(
           				'title'       => __( 'Description', 'woocommerce' ),
           				'type'        => 'textarea',
           				'description' => __( 'Beskrivning till betalningsmetoden som syns för användaren', 'woocommerce' ),
           				'default'     => __( 'Betala mot faktura genom att fylla i personnummer', 'woocommerce' ),
           				'desc_tip'    => true,
           			)
             ];
           }

            function payment_fields(){
              ?>
             <form class="" action="" method="post">
               <label>Personnummer:
                 <abbr class="required" title="required">*</abbr>
               </label>
               <input required type="text" name="payment_personnummer">
             </form>
              <?php
           }

           // function validate_fields(){
           //   if(empty($_REQUEST['payment_personnummer'])){
           //     wc_add_notice( var_dump($customer_order), 'error' );
           //     //wc_add_notice( 'Du måste fylla i ett personnummer!', 'error' );
           //
           //   }
           //
           //   else {
           //     $message = $_REQUEST['payment_personnummer'];
           //     wc_add_notice( $message, 'error' );
           //   }
           // }

           function process_payment($order_id) {
             global $woocommerce;

             $order = new WC_Order($order_id);

             $idnumber = get_post_meta( $order_id, '_additional_wooccm0', true );

             function isValid($num) {
               settype($num, 'string');
               $sumTable = array(
               array(0,1,2,3,4,5,6,7,8,9),
               array(0,2,4,6,8,1,3,5,7,9));
               $sum = 0;
               $flip = 0;
               for ($i = strlen($num) - 1; $i >= 0; $i--) {
                 $sum += $sumTable[$flip++ & 0x1][$num[$i]];
               }
               return $sum % 10 === 0;
            }
            $response = isValid($idnumber);

            if($response == true){
              $order->add_order_note(__("Betalningen lyckades"));
              $order->payment_complete();
              $woocommerce->cart->empty_cart();
              return [
               'result'   => 'success',
               'redirect' => $this->get_return_url( $order ),
              ];
            }
            else {
               $error = __("Ogiltigt personnummer");
               wc_add_notice( $error, 'error' );
               $order->add_order_note( 'Error: '. $error );
            }
          }

         }

         add_filter('woocommerce_payment_gateways', 'load_invoice_payment_gateway');
         function load_invoice_payment_gateway($methods)
         {
             $methods[] = "WC_Invoice_Payment_Gateway";
             return $methods;
         }
     }
  }

 ?>
