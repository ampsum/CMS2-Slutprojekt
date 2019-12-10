<?php
/*
Plugin Name: Kontaktformulär
Description: Kontaktformulär med möjlighet att bifoga bildfil
Author: Sara, Maija
Version: 1.0
*/

defined( 'ABSPATH' ) or die();

class ContactForm
{
  function __construct()
  {
    add_action('wp_enqueue_scripts', array ($this, 'add_styles'));
    add_shortcode('contact', array($this, 'create_form'));
    add_action('admin_post_send_data', array ($this, 'form_submit' ));
    add_action('admin_post_nopriv_send_data', array ($this, 'form_submit' ));
  }

  function add_styles(){
    wp_register_style('styling', plugin_dir_url(__FILE__).'css/form-style.css' );
    wp_enqueue_style('styling');
  }

  function create_form ($atts) {

    if( !empty( $_GET['form_success'] ) ) {
      $form = '<div>Tack för ditt meddelande. Vi återkommer till dig inom 48 timmar.</div>';
    }

    else {
        $form = '
              <form id="bakery-form" action="' . admin_url('admin-post.php') . '" method="post">
                <label>Välj ärende: </label>
                <select name="contact-type" >
              		<option value="kontakt">Kontakt</option>
              		<option value="reklamation">Reklamation</option>
              		<option value="faktura">Faktura</option>
          	    </select>
                <br>
                <br>
                <label>Namn: </label>
                <input type="text" name="name">
                <br>
                <br>
                <label>Email: </label>
                <input type="email" name="email"">
                <br>
                <br>
                <label>Meddelande: </label>
                <textarea name="message"></textarea>
                <br>
                <br>
                <input type="file" name="attachment" accept=".jpg, .jpeg, .png">
                <br>
                <br>
                <input type="submit" name="submit-form" value="Skicka">
				        <input type="hidden" name="action" value="send_data">
              </form>
          ';
       }
       return $form ;
  }

    function form_submit() {
      if ( ! isset( $_POST['submit-form'] ) ) {
        return;
      }
      else {
        if ( !empty($_POST['attachment'])) {
          $allowed_extensions = array( 'jpg', 'jpeg', 'png' );
          $file_type = wp_check_filetype( $_POST['attachment'] );
          $file_extension = $file_type['ext'];
          if ( ! in_array( $file_extension, $allowed_extensions ) ) {
            wp_die( 'Otillåten fil. Du kan bara bifoga bilder i formaten jpg, jpeg eller png.');
          }
        }

        if ( empty( $_POST['name'] ) || empty( $_POST['email'] ) || empty( $_POST['message'] )) {
          wp_die('Du måste fylla i alla fält innan du kan skicka!');
          return;
        }
        else {
          wp_mail(get_bloginfo('admin_email'), 'Nytt mail: ' . $_POST['contact-type'], $_POST['message']);

          $url = add_query_arg( 'form_success', 'true', $_SERVER['HTTP_REFERER'] );
          wp_redirect( $url );
          die();
        }
      }
    }
}

$contactform = new ContactForm();

?>
