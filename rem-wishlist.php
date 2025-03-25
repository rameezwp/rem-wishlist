<?php 
/*
 * Plugin Name: REM - Wishlist
 * Plugin URI: https://wp-rem.com/addons/rem-wish-list/
 * Description: Add properties into wishlist and then bulk contact.
 * Version: 2.1
 * Author: WebCodingPlace
 * Author URI: https://webcodingplace.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rem-wishlist
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
        add_action( 'rem_single_property_page_slider', array($this, 'add_wishlist_button_in_single_property' ), 10, 1 );
        add_filter( 'rem_admin_settings_fields', array($this, 'wishlist_settings_menu') );
        // addning menu
        add_action( 'admin_menu', array( $this, 'menu_pages' ) );
        add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts' ) );

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
		add_shortcode('rem_wishlist_button', array($this, 'add_wishlist_button_in_single_property' ) );
	}

	function add_wishlist_button_in_single_property(  $property_id ) {

		if ($property_id == '') {
			global $post;
			$property_id = $post->ID;
		}
	    
		echo '<p class="text-right" style="margin-top: 5px;">';
		echo '<a href="#" title="'.rem_get_option('wl_added_tooltip', 'Add to wishlist').'" style="color: #777;" class="rem-wishlist-btn" data-id="'.$property_id.'" ><i class="far fa-heart"></i> '.rem_get_option('wl_added_tooltip', 'Add to wishlist');
		echo '</a>';
		echo '</p>';
	}

	function menu_pages(){
		add_submenu_page( 'edit.php?post_type=rem_property', 'Agents Wishlist', __( 'Agents Wishlist', 'real-estate-manager' ), 'manage_options', 'rem_wishlisted_property', array($this, 'render_wishlisting_page') );
	}

	function render_wishlisting_page(){
		include_once REM_WISHLIST_PATH. '/templates/wishlist-menu-page.php';
	}

    function admin_scripts($check){
    	
    	if ($check == 'rem_property_page_rem_wishlisted_property') {
    		wp_enqueue_style( 'rem-bootstrap', REM_URL . '/assets/admin/css/bootstrap.min.css' );
    		wp_enqueue_style( 'font-awesome-rem', REM_URL . '/assets/front/css/font-awesome.min.css' );
    	}
    }
	function rem_wishlist() {

		$fields = rem_get_option('wl_fields', 'thumbnail,property_title,property_type,property_status');
		$fields = explode(",", $fields);
		
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
            'empty_list_msg' => __('Your wishlist is empty.', "rem-wishlist"),
            'already_exist' => __('Your wishlist is empty.', "rem-wishlist"),
            'already_exist_title' => __("Already Added", "rem-wishlist"),
            'already_exist_text' => __("Property already added.", "rem-wishlist"),
            'removed_property_title' => rem_get_option( 'wl_removing_heading', "Removed" ),
            'removed_property_text' => rem_get_option( 'wl_removing_description', "Property removed from wishlist."),
            'add_property_title' => rem_get_option( 'wl_added_heading', 'Added'),
            'add_property_text' => rem_get_option('wl_added_description', 'Property added into wishlist.'),
            'icon_title_attr_remove' => rem_get_option('wl_removing_tooltip', 'Remove from wishlist.'),
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
			
			$fields = rem_get_option('wl_fields', 'thumbnail,property_title,property_type,property_status');
			$fields = explode(",", $fields);

			$args = array(
				'post_type' => 'rem_property',
				'posts_per_page' => 299,
			    'post__in' => $prop_ids
			);

			$posts = get_posts($args);

			foreach ($posts as $property) {
				ob_start();
				include REM_WISHLIST_PATH . '/templates/wishlist-single-row.php';
				$html .= ob_get_clean();
			}
		}

		$resp = array("ids" => $prop_ids, "html"=>$html );
		// var_dump($html);
		wp_send_json( $resp );
	}

	function send_email_about_wishlist_properties(){

		$client_name = sanitize_text_field( $_POST['client_name'] );
		$client_phone = sanitize_text_field( $_POST['client_phone'] );
		$client_email = sanitize_email($_POST['client_email']);

		$message = rem_get_option('wl_email_mkp', 'Property Inquiry: '.sanitize_text_field( $_POST['message'] ));

		$message = nl2br(stripcslashes($message));
			
		$wishlist_properties = explode(",",sanitize_text_field($_POST['ids']));
		$resp = array();
		foreach ($wishlist_properties as $key => $property_id) {

			$property_src = get_permalink( $property_id );
			$property_title = get_the_title( $property_id );
			

			$message = str_replace("%property_link%", $property_src, $message);
			$message = str_replace("%property_title%", $property_title, $message);
			$message = str_replace("%phone%", $client_phone, $message);
			$message = str_replace("%name%", $client_name, $message);
			$message = str_replace("%email%", $client_email, $message);
			$message = str_replace("%message%", sanitize_text_field( $_POST['message']) , $message);

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
        $headers[] = "From: {$client_name} <{$client_email}>";
        $headers[] = "Content-Type: text/html";
        $headers[] = "MIME-Version: 1.0\r\n";

        $subject = rem_get_option('wl_email_sbj', 'Inquiry from %name%');

		$subject = str_replace("%name%", $client_name, $subject);
		$subject = str_replace("%email%", $client_email, $subject);

		$subject = apply_filters('rem_wl_email_subject', $subject);
		$message = apply_filters('rem_wl_email_message', $message);
		$headers = apply_filters('rem_wl_email_headers', $headers);
        
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
	                'title' => __( 'Remove from wishlist button', 'real-estate-manager' ),
	                'help' => __( 'Provide button title text. Default value is "Remove from wishlist"', 'real-estate-manager' ),
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
	                'title' => __( 'Inquiry from heading', 'real-estate-manager' ),
	                'help' => __( 'Provide from heading text. Default value is "Contact Agents"', 'real-estate-manager' ),
	            ),

	            array(
	                'type' => 'text',
	                'name' => 'wl_empty_heading',
	                'title' => __( 'Heading text to without selected property', 'real-estate-manager' ),
	                'help' => __( 'Provide alert heading text to from submit without any property selected. Default value is "Empty"', 'real-estate-manager' ),
	            ),

	            array(
	                'type' => 'text',
	                'name' => 'wl_empty_description',
	                'title' => __( 'Description text to without selected property', 'real-estate-manager' ),
	                'help' => __( 'Provide alert description text to from submit without any property selected. Default value is "Please select properties to contact"', 'real-estate-manager' ),
	            ),

	            array(
	                'type' => 'text',
	                'name' => 'wl_fields',
	                'title' => __( 'Fields in Wishlist Page', 'real-estate-manager' ),
	                'help' => __( 'Provide comma separated list of field names to display those fields in the wishlist page', 'real-estate-manager' ),
	                'default'	=> 'thumbnail,property_title,property_type,property_status'
	            ),

	            array(
	                'type' => 'text',
	                'name' => 'wl_email_sbj',
	                'title' => __( 'Email Subject', 'real-estate-manager' ),
	                'help' => __( 'Provide subject for the contact email. You can use these shortcodes', 'real-estate-manager' ).'<code>%name%</code>,<code>%email%</code>',
	            ),

	            array(
	                'type' => 'textarea',
	                'name' => 'wl_email_mkp',
	                'title' => __( 'Email Content Markup', 'real-estate-manager' ),
	                'help' => __( 'Provide email markup. You can use these shortcodes.', 'real-estate-manager' ).' <code>%name%</code>, <code>%email%</code>, <code>%phone%</code>, <code>%message%</code>, <code>%property_title%</code>, <code>%property_link%</code>',
	            ),

	    );
		$fields = apply_filters( 'rem_wishlist_settings_field', $fields );
		return $fields;
	}
}
add_action('plugins_loaded', 'rem_wishlist_start');
function rem_wishlist_start() {
	if (defined('REM_PATH')) {
		require_once REM_PATH.'/inc/update/wp-package-updater/class-wp-package-updater.php';
		$wishlist_updater = new WP_Package_Updater(
			'https://kb.webcodingplace.com/',
			wp_normalize_path( __FILE__ ),
			wp_normalize_path( plugin_dir_path( __FILE__ ) )
		);
	}
	load_plugin_textdomain( 'rem-wishlist', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	return new REM_WISHLIST();
}
