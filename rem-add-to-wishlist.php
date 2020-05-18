<?php 
/*
 * Plugin Name: Wishlist - Real Estate Manager Extension
 * Plugin URI: https://webcodingplace.com/real-estate-manager-wordpress-plugin/
 * Description: Add properties to wishlist and then bulk contact.
 * Version: 1.5
 * Author: WebCodingPlace
 * Author URI: https://webcodingplace.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wishlist-real-estate-manager-extension
 * Domain Path: /languages
*/
 if( ! defined('ABSPATH' ) ){
	exit;
}

define( 'REM_WISHLIST_PATH', untrailingslashit(plugin_dir_path( __FILE__ )) );
define( 'REM_WISHLIST_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );


class REM_WISHLIST {

	function __construct(){

		// adding wishlist button
		add_action( 'rem_listing_footer', array( $this, 'add_wishlist_button' ) , 10, 3 );
        add_action( 'rem_single_property_slider', array($this, 'add_wishlist_button_in_single_property' ), 10, 1 );
        add_filter( 'rem_admin_settings_fields', array($this, 'wishlist_settings_menu') );
        // addning menu
        add_action( 'admin_menu', array( $this, 'menu_pages' ) );

		// shortcode for wishlist
		add_shortcode( 'rem_wishlist', array( $this, 'rem_wishlist') );

		// ajax calbackes
		add_action( 'wp_ajax_rem_get_wishlist_properties', array( $this, 'get_wishlist_properties' ) );
		add_action( 'wp_ajax_nopriv_rem_get_wishlist_properties', array( $this, 'get_wishlist_properties' ) );
		add_action( 'wp_ajax_rem_wishlist_properties_inquiry', array( $this, 'send_email_about_wishlist_properties' ) );
		add_action( 'wp_ajax_nopriv_rem_wishlist_properties_inquiry', array( $this, 'send_email_about_wishlist_properties' ) );
		add_action('wp_ajax_is_user_logged_in', array( $this, 'rem_ajax_check_user_logged_in') );
		add_action('wp_ajax_nopriv_is_user_logged_in', array( $this, 'rem_ajax_check_user_logged_in') );
		add_action('wp_ajax_wishlist_in_user_profile', array( $this, 'rem_adding_wishlist_property_in_profile') );
		
		// add scripts for plugin
		add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_scripts' ) );
	}

	function add_wishlist_button(  $property_id, $style = '' , $target = '' ) {

	    if ( ($style != '1' && $style != '2' && REM_VERSION >= '10.7.0')  ) {
	    	
			echo '<a href="#" title="'.rem_get_option('wl_added_tooltip', 'Add to wishlist').'" class="btn btn-default rem-wishlist-btn" data-id="'.$property_id.'" ><i class="far fa-heart"></i></a>';
	    }
	}

	function add_wishlist_button_in_single_property(  $property_id ) {
	    
		echo '<p class="text-center" style="margin-top: 5px;">';
		echo ' <img class="rem-loading-img" src="'.REM_WISHLIST_URL.'/loading-icon.gif">';
		echo '<a href="#" title="'.rem_get_option('wl_added_tooltip', 'Add to wishlist').'" class="btn btn-default btn-center rem-wishlist-btn" data-id="'.$property_id.'" ><i class="far fa-heart"></i>';
		echo '</a>';
		echo '<p>';
	}

	function menu_pages(){
		add_submenu_page( 'edit.php?post_type=rem_property', 'Agents Wishlist', __( 'Agents Wishlist', 'real-estate-manager' ), 'manage_options', 'rem_wishlisted_property', array($this, 'render_wishlisting_page') );
	}

	function render_wishlisting_page(){
		include_once REM_WISHLIST_PATH. '/templates/wishlist-menu-page.php';
	}

	function rem_wishlist() {
		
        wp_enqueue_style( 'font-awesome-rem', REM_URL . '/assets/front/css/font-awesome.min.css' );
        wp_enqueue_style( 'rem-bs', REM_URL . '/assets/admin/css/bootstrap.min.css' );
		ob_start();
		include REM_WISHLIST_PATH . '/templates/wishlist.php';
		return ob_get_clean();
	}

	function load_frontend_scripts() {

		wp_enqueue_script( 'rem-sweet-alerts', REM_URL . '/assets/admin/js/sweetalert.min.js' , array('jquery'));
		wp_enqueue_style( 'rem-wishlist-css', REM_WISHLIST_URL . '/css/styles.css' );
		wp_enqueue_script( 'rem-scripts', REM_WISHLIST_URL . '/js/scripts.js' , array('jquery') );
		wp_enqueue_script( 'rem-store2-script', REM_WISHLIST_URL . '/js/store2.js' , array('jquery') );
		$localize_vars = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'empty_list_msg' => __('Your wishlist is empty.', "wishlist-real-estate-manager-extension"),
            'already_exist' => __('Your wishlist is empty.', "wishlist-real-estate-manager-extension"),
            'already_exist_title' => __("Already Added", "wishlist-real-estate-manager-extension"),
            'already_exist_text' => __("Property already added.", "wishlist-real-estate-manager-extension"),
            'removed_property_title' => rem_get_option( 'wl_removing_heading', "Removed" ),
            'removed_property_text' => rem_get_option( 'wl_removing_description', "Property removed from wishlist."),
            'add_property_title' => rem_get_option( 'wl_added_heading', 'Added'),
            'add_property_text' => rem_get_option('wl_added_description', 'Property added into wishlist.'),
            'icon_title_attr_remove' => rem_get_option('wl_removing_tooltip', 'Remove form wishlist.'),
            'icon_title_attr_added' => rem_get_option('wl_added_tooltip', 'Add to wishlist.'),
            'form_property_empty_title' => rem_get_option('wl_empty_heading', "Empty"),
            'form_property_empty_text' => rem_get_option( 'wl_empty_description', 'Please select properties to contact'),
        );
        wp_localize_script( 'rem-scripts', 'rem_wishlist_var', $localize_vars );
		
	}

	function get_wishlist_properties() {
		$user = wp_get_current_user();
		$wishlistings = get_user_meta( $user->ID, "rem_wishlist_properties", true );
		$prop_ids = isset($_REQUEST['property_ids']) ? $_REQUEST['property_ids'] : $wishlistings;
		$html = '';
		if ($prop_ids != '') {
			
			$args = array(
				'post_type' => 'rem_property',
				'posts_per_page' => -1,
			    'post__in' => $prop_ids
			);
			$posts = get_posts($args);
			foreach ($posts as $post) {
						
				$html .= 	"<tr>";
					$html .= 	"<td class='img-wrap'>";
						$html .= '<label class="product-check-label">';
							$html .=   "<input type='checkbox' class='property-check' value='" .esc_attr($post->ID)."'>";
								$html .=   "<span class='checkmark'></span>";
							$html .=  "</label>";
						$html .=  get_the_post_thumbnail( $post->ID, array( '50', '50' ));
					$html .= 	"</td>";
					$html .= 	"<td><a href='". get_the_permalink($post->ID)."'>". $post->post_title. "</a> ". get_post_meta($post->ID,'rem_property_address', true)."</td>";
					$html .= 	"<td class='hidden-xs'>". ucfirst(get_post_meta($post->ID,"rem_property_type", true )) ."</td>";
					$html .= 	"<td>";
						$html .= 	"<img class='rem-loading-img' src='". REM_WISHLIST_URL ."/loading-icon.gif'>";
						$html .= 	"<a href='' class='remove-property-btn' data-id='". $post->ID ."'><i class='fa fa-trash'></i></a>";
					$html .= 	"</td>";
				$html .= 	"</tr>";
			}
		}
		$resp = array("ids" => $prop_ids, "html"=>$html );
		// var_dump($html);
		wp_send_json( $resp );
	}

	function send_email_about_wishlist_properties(){

		$client_name = sanitize_text_field( $_POST['client_name'] );
		$client_email = sanitize_email($_POST['client_email']);
		$message = "Sender: ".sanitize_email( $_POST['client_email'] )."\n";
		$message .= "Subject: 'Property Inquiry'\n";
			
		$wishlist_properties = explode(",",sanitize_text_field($_POST['ids']));
		$resp = array();
		foreach ($wishlist_properties as $key => $property_id) {

			$property_src = get_permalink( $property_id );
			$property_title = get_the_title( $property_id );
			
			$message .= "Content 'Information about: ".$property_title."', '".sanitize_text_field( $_POST['message'] );
			$mail_status = $this->send_email_agent( $property_id, $client_name, $client_email, $message );
			
			$resp[$property_id] = array(
				'status' => $mail_status,
				'msg'	=> __( 'Email for '. $property_title. ' '. $mail_status, 'rem-wishlist'),
			);
			
		}
		wp_send_json($resp);
		die(0);
	}
	function send_email_agent( $property_id, $client_name, $client_email, $message ){

		$property = get_post($property_id);
		$agent_id = $property->post_author;
        $agent_info = get_userdata($agent_id);
        $agent_email = $agent_info->user_email;

        $headers = array();
        $headers[] = 'From: '.$client_name.'  <'.$client_email.'>' . "\r\n";
        $subject = 'Inquiry form '.$client_name;
        
        if (wp_mail( $agent_email, $subject, $message, $headers )) {
            $resp = 'Sent';
        } else {
            $resp = 'Fail';
        }

        return $resp;
    }

    function rem_ajax_check_user_logged_in() {
	    echo is_user_logged_in()?  true: false;
	    die();
	}

	function rem_adding_wishlist_property_in_profile(){
		
		$property_ids = $_POST['property_ids'];
		$user_id = get_current_user_id();
		$wishlistings_old = get_user_meta( $user_id, "rem_wishlist_properties", true );
		$updated = update_user_meta( $user_id, "rem_wishlist_properties", $property_ids, $wishlistings_old );
		return $updated;
		die();
	}
	function wishlist_settings_menu($settings){
	    $settings[] = array(
	        'panel_title'   =>  __( 'Wishlist', 'real-estate-manager' ),
	        'panel_name'   =>  'wishlist',
	        'icon'   		=>  '<span class="glyphicon glyphicon-heart"></span>',
	        'fields'        => $this->get_wishlist_fields(),
	    );

		return $settings;
	}
	function get_wishlist_fields(){
		$fields = array(

	            array(
	                'type' => 'text',
	                'name' => 'wl_added_tooltip',
	                'title' => __( 'Add to Wishlist Button Title', 'real-estate-manager' ),
	                'help' => __( 'Provide button title text. Default value is "Add to wishlist"', 'real-estate-manager' ),
	            ),

	            array(
	                'type' => 'text',
	                'name' => 'wl_removing_tooltip',
	                'title' => __( 'Remove form wishlist button', 'real-estate-manager' ),
	                'help' => __( 'Provide button title text. Default value is "Remove form wishlist"', 'real-estate-manager' ),
	            ),

	            array(
	                'type' => 'text',
	                'name' => 'wl_added_heading',
	                'title' => __( 'Added Heading', 'real-estate-manager' ),
	                'help' => __( 'Provide heading text. Default  value is "Added".', 'real-estate-manager' ),
	            ),

	            array(
	                'type' => 'text',
	                'name' => 'wl_added_description',
	                'title' => __( 'Added Description', 'real-estate-manager' ),
	                'help' => __( 'Provide Description text.Default value is "Property added into wishlist"', 'real-estate-manager' ),
	            ),

	            array(
	                'type' => 'text',
	                'name' => 'wl_removing_heading',
	                'title' => __( 'Remove Heading', 'real-estate-manager' ),
	                'help' => __( 'Provide heading text. Default value is "Removed"', 'real-estate-manager' ),
	            ),

	            array(
	                'type' => 'text',
	                'name' => 'wl_removing_description',
	                'title' => __( 'Remove Description', 'real-estate-manager' ),
	                'help' => __( 'Provide Description text. Default value is "Property removed from wishlist"', 'real-estate-manager' ),
	            ),

	            array(
	                'type' => 'text',
	                'name' => 'wl_inquiry_heading',
	                'title' => __( 'Inquiry form heading', 'real-estate-manager' ),
	                'help' => __( 'Provide form heading text. Default value is "Contact Agents"', 'real-estate-manager' ),
	            ),

	            array(
	                'type' => 'text',
	                'name' => 'wl_empty_heading',
	                'title' => __( 'Heading text to without selected property', 'real-estate-manager' ),
	                'help' => __( 'Provide alert heading text to form submit without any property selected. Default value is "Empty"', 'real-estate-manager' ),
	            ),

	            array(
	                'type' => 'text',
	                'name' => 'wl_empty_description',
	                'title' => __( 'Description text to without selected property.', 'real-estate-manager' ),
	                'help' => __( 'Provide alert description text to form submit without any property selected. Default value is "Please select properties to contact"', 'real-estate-manager' ),
	            ),              

	    );
		$fields = apply_filters( 'rem_wishlist_settings_field', $fields );
		return $fields;
	}
}
add_action('plugins_loaded', 'rem_wishlist_start');
function rem_wishlist_start() {
	load_plugin_textdomain( 'wishlist-real-estate-manager-extension', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	return new REM_WISHLIST();
}

require_once( 'inc/update.php' );
if ( is_admin() ) {
    new REM_WISHLIST_PLUGIN_UPDATER( __FILE__, 'rameezwp', "rem-wishlist" );
}
