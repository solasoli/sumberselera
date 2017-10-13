<?php
/*
* Plugin Name: DHVC Form
* Plugin URI: http://sitesao.com/dhvcform/
* Description: Easy Form Bulder for Wordpress with Visual Composer
* Version: 1.4.30	
* Author: Sitesao
* Author URI: http://sitesao.com/
* License: License GNU General Public License version 2 or later;
* Copyright 2014  Sitesao
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!defined('DHVC_FORM'))
	define('DHVC_FORM','dhvc-form');

if(!defined('DHVC_FORM_FILE'))
	define('DHVC_FORM_FILE',__FILE__);

if(!defined('DHVC_FORM_VERSION'))
	define('DHVC_FORM_VERSION','1.4.30');

if(!defined('DHVC_FORM_URL'))
	define('DHVC_FORM_URL',untrailingslashit( plugins_url( '/', __FILE__ ) ));

if(!defined('DHVC_FORM_DIR'))
	define('DHVC_FORM_DIR',untrailingslashit( plugin_dir_path(__FILE__ ) ));

if(!defined('DHVC_FORM_TEMPLATE_DIR'))
	define('DHVC_FORM_TEMPLATE_DIR',DHVC_FORM_DIR .'/templates/');


global $dhvc_form_popup;
$dhvc_form_popup = null;
class DHVCForm {
	
	protected $_form_data = array();
	
	public function __construct(){
		
		add_action('init',array(&$this,'init'));
		add_action('init',array(&$this,'register_post_type'),0);
		
		//includes
		require_once (DHVC_FORM_DIR.'/includes/query.php');
		require_once (DHVC_FORM_DIR.'/includes/functions.php') ;
		
		register_activation_hook(DHVC_FORM_FILE,array(&$this, 'activate'));
		register_deactivation_hook(DHVC_FORM_FILE,array(&$this, 'deactivate'));
	}
	
	public function init(){
		if( !session_id() )
		{
			session_start();
		}
		if(class_exists('WYSIJA')){
			define('DHVC_FORM_SUPORT_WYSIJA', true);
		}
		
		if(defined('MYMAIL_DIR')){
			define('DHVC_FORM_SUPORT_MYMAIL', true);
		}
		
		if (!defined('WPB_VC_VERSION')) {
        	add_action('admin_notices', array(&$this,'notice'));
        	return;
    	}
		
		load_plugin_textdomain( 'dhvc-form', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		if(is_admin()){
			require_once (DHVC_FORM_DIR.'/includes/admin.php');
		}
		add_action('wp_ajax_dhvc_form_recaptcha', array($this,'check_recaptcha'));
		add_action('wp_ajax_nopriv_dhvc_form_recaptcha', array($this,'check_recaptcha'));
		
		add_action('wp_ajax_dhvc_form_recaptcha2', array($this,'dhvc_form_recaptcha2'));
		add_action('wp_ajax_nopriv_dhvc_form_recaptcha2', array($this,'dhvc_form_recaptcha2'));
		
		add_action('wp_ajax_dhvc_form_captcha', array($this,'check_captcha'));
		add_action('wp_ajax_nopriv_dhvc_form_captcha', array($this,'check_captcha'));
		
		add_action('wp_ajax_dhvc_form_ajax', array($this,'ajax_processor'));
		add_action('wp_ajax_nopriv_dhvc_form_ajax', array($this,'ajax_processor'));
		
		add_filter('login_url', array(&$this,'login_url'));
		add_filter('logout_url', array(&$this,'logout_url'));
		add_filter('register_url', array(&$this,'register_url'));
		add_filter('lostpassword_url', array(&$this,'lostpassword_url'));

		require_once (DHVC_FORM_DIR.'/includes/shortcodes.php') ;
		require_once (DHVC_FORM_DIR.'/includes/map.php');
		if(is_admin()){
			$this->register_assets();
		}else{
			add_action( 'template_redirect', array( &$this, 'register_assets' ) );
		}
		if(!is_admin()){
			add_action('wp_enqueue_scripts', array(&$this, 'frontend_assets'));
			add_action( 'wp_head', array(&$this,'dhvc_form_popup'), 100 );
			add_action( 'wp_footer',array(&$this,'dhvc_form_print_form_popup'), 50 );
			add_action( 'wp_footer', 'dhvc_form_print_js_declaration', 100 );
			add_action('template_redirect', array(&$this,'override_woocommerce_my_account_shortcode'));
		}
		if (!is_admin() && isset($_REQUEST['dhvc_form']) && !isset($_REQUEST['_dhvc_form_is_ajax_call'])) {
			$this->processor();
		}
	}
	
	public function activate(){
		global $dhvcform_db;
		$dhvcform_db->create_table();
		
		$this->create_roles();
		$this->register_post_type();	
	}
	
	public function create_roles(){
		global $wp_roles;
		
		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}
		
		if ( is_object( $wp_roles ) ) {

			$capability = array(
					"edit_dhvcform",
					"read_dhvcform",
					"delete_dhvcform",
					"edit_dhvcforms",
					"edit_others_dhvcforms",
					"publish_dhvcforms",
					"read_private_dhvcforms",
					"delete_dhvcforms",
					"delete_private_dhvcforms",
					"delete_published_dhvcforms",
					"delete_others_dhvcforms",
					"edit_private_dhvcforms",
					"edit_published_dhvcforms",
			);
			foreach ( $capability as $cap ) {
				$wp_roles->add_cap( 'administrator', $cap );
			}
		}
	}
	
	public function deactivate(){
		global $wp_roles;
		
		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}
		
		if ( is_object( $wp_roles ) ) {
		
			$capability = array(
					"edit_dhvcform",
					"read_dhvcform",
					"delete_dhvcform",
					"edit_dhvcforms",
					"edit_others_dhvcforms",
					"publish_dhvcforms",
					"read_private_dhvcforms",
					"delete_dhvcforms",
					"delete_private_dhvcforms",
					"delete_published_dhvcforms",
					"delete_others_dhvcforms",
					"edit_private_dhvcforms",
					"edit_published_dhvcforms",
			);
			foreach ( $capability as $cap ) {
				$wp_roles->remove_cap( 'administrator', $cap );
			}
		}
	}
	
	public function register_post_type(){
		if(post_type_exists('dhvcform'))
			return;
		
		register_post_type( "dhvcform",
			apply_filters( 'dhvc_form_register_post_type',
				array(
					'labels' => array(
					'name' 					=> __( 'Forms', 'dhvc-form' ),
					'singular_name' 		=> __( 'Form', 'dhvc-form' ),
					'menu_name'				=> _x( 'Forms', 'Admin menu name', 'dhvc-form' ),
					'add_new' 				=> __( 'Add Form', 'dhvc-form' ),
					'add_new_item' 			=> __( 'Add New Form', 'dhvc-form' ),
					'edit' 					=> __( 'Edit', 'dhvc-form' ),
					'edit_item' 			=> __( 'Edit Form', 'dhvc-form' ),
					'new_item' 				=> __( 'New Form', 'dhvc-form' ),
					'view' 					=> __( 'View Form', 'dhvc-form' ),
					'view_item' 			=> __( 'View Form', 'dhvc-form' ),
					'search_items' 			=> __( 'Search Forms', 'dhvc-form' ),
					'not_found' 			=> __( 'No Forms found', 'dhvc-form' ),
					'not_found_in_trash' 	=> __( 'No Forms found in trash', 'dhvc-form' ),
					'parent' 				=> __( 'Parent Form', 'dhvc-form' )
				),
				'description' 			=> __( 'This is where you can add new form.', 'dhvc-form' ),
				'public' 				=> true,
				'show_ui' 				=> true,
				'capability_type' 		=> 'dhvcform',
				'map_meta_cap'			=> true,
				'publicly_queryable' 	=> false,
				'exclude_from_search' 	=> false,
				'show_in_menu' 			=> 'dhvc-form',
				'hierarchical' 			=> false, // Hierarchical causes memory issues - WP loads all records!
				'rewrite' 				=> false,
				'query_var' 			=> false,
				'supports' 				=> array( 'title', 'editor', 'custom-fields'),
				'show_in_nav_menus' 	=> false,
				'show_in_admin_bar'     => false
				)
			)
		);
	}
	
	public function login_url($login_url){
		$user_login = dhvc_form_get_option('user_login');
		if($user_login)
			$login_url = get_permalink($user_login);
		return $login_url;
	}
	
	public function register_url($register_url){
		$user_regiter = dhvc_form_get_option('user_regiter');
		if($user_regiter)
			$register_url = get_permalink($user_regiter);
		return $register_url;
	}
	
	public function logout_url($logout_url,$redirect=''){
		$user_logout = dhvc_form_get_option('user_logout_redirect_to');
		$args = array();
		if($user_logout){
			$redirect_to = get_permalink($user_logout);
			$args['redirect_to'] = urlencode( $redirect_to );
		}
		return  add_query_arg($args, $logout_url);
	}
	
	public function lostpassword_url($lostpassword_url){
		$user_forgotten = dhvc_form_get_option('user_forgotten');
		if($user_forgotten)
			$lostpassword_url = get_permalink($user_forgotten);
		return $lostpassword_url;
	}
	
	public function register_assets(){
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( is_ssl() ) {
			$protocol_to_be_used = 'https://';
		} else {
			$protocol_to_be_used = 'http://';
		}
		
		$language = apply_filters('dhvc_form_language_code','en');
		
		wp_register_script( 'dhvc-form-recaptcha2', "{$protocol_to_be_used}www.google.com/recaptcha/api.js?onload=dhreCatptcha_onloadCallback&render=explicit&hl=$language", array('jquery'), '1.0.0', false );
		wp_register_style('dhvc-form-font-awesome',DHVC_FORM_URL.'/assets/fonts/font-awesome/css/font-awesome'.$suffix.'.css', array(), '4.1.0');
		wp_register_style('dhvc-form-datetimepicker',DHVC_FORM_URL.'/assets/datetimepicker/jquery.datetimepicker.css', array(),'2.2.9');
		wp_register_script('dhvc-form-datetimepicker',DHVC_FORM_URL.'/assets/datetimepicker/jquery.datetimepicker'.$suffix.'.js',array('jquery'),'2.4.6');
		wp_register_style('dhvc-form-minicolor',DHVC_FORM_URL.'/assets/minicolors/jquery.minicolors'.$suffix.'.css', array(),'2.1');
		wp_register_script('dhvc-form-minicolor',DHVC_FORM_URL.'/assets/minicolors/jquery.minicolors'.$suffix.'.js',array('jquery'),'2.1');
		
		wp_register_script('dhvc-form-bootstrap-tooltip',DHVC_FORM_URL.'/assets/js/bootstrap-tooltip.js',array('jquery'),'2.1');
		
		
		wp_register_script('dhvc-form-validate-methods',DHVC_FORM_URL.'/assets/validate/additional-methods'.$suffix.'.js',array('jquery'),'1.12.0');
		wp_register_script('dhvc-form-validate',DHVC_FORM_URL.'/assets/validate/jquery.validate'.$suffix.'.js',array('jquery'),'1.12.0');
		
		wp_register_script('dhvc-form-recaptcha','http://www.google.com/recaptcha/api/js/recaptcha_ajax.js',array('jquery'));
	
	}
	
	public static function form_custom_css($form_id){
		if(!apply_filters('dhvc_form_custom_css_in_head', false)){
		$inline_style='';
		$post = get_post($form_id);
		if($label_color = get_post_meta( $post->ID, '_label_color', true )){
			$inline_style .= '
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-group .dhvc-form-label,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-group label:not(.dhvc-form-rate-star){
	color:'.$label_color.'
}';
		}
		if($placeholder_color = get_post_meta( $post->ID, '_placeholder_color', true )){
			$inline_style .='
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-group .dhvc-form-add-on{
	color:'.$placeholder_color.';
}
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-group .dhvc-form-control::-webkit-input-placeholder {
	color:'.$placeholder_color.';
}
		
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-group .dhvc-form-control:-moz-placeholder {
	color:'.$placeholder_color.';
}
		
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-group .dhvc-form-control::-moz-placeholder {
	color:'.$placeholder_color.';
}
		
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-group .dhvc-form-control:-ms-input-placeholder {
	color:'.$placeholder_color.';
}
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-group .dhvc-form-control:focus::-webkit-input-placeholder {
	color: transparent;
}';
		}
		if($input_height = get_post_meta( $post->ID, '_input_height', true )){
			$inline_style .='
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-input input,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-file input[type="text"],
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-captcha input,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-select select,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-group .dhvc-form-add-on,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-file-button i,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-select i{
 height:'.$input_height.';
}
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-select i,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-file-button i,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-group .dhvc-form-add-on{
  line-height:'.$input_height.';
}
';
		}
			
		if($input_bg_color = get_post_meta( $post->ID, '_input_bg_color', true )){
			$inline_style .= '
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-input input,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-file input[type="text"],
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-captcha input,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-select select,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-radio i,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-checkbox i,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-textarea textarea{
	background-color:'.$input_bg_color.';
}';
		}
		if($input_text_color = get_post_meta( $post->ID, '_input_text_color', true )){
			$inline_style .= '
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-input input,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-file input[type="text"],
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-captcha input,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-select select,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-textarea textarea{
	color:'.$input_text_color.';
}';
		}
		if($input_border_size = get_post_meta( $post->ID, '_input_border_size', true )){
			$inline_style .= '
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-input input,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-file input[type="text"],
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-captcha input,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-select select,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-radio i,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-checkbox i,
#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-textarea textarea{
	border-width:'.$input_border_size.';
}';
		}
		if($input_border_color = get_post_meta( $post->ID, '_input_border_color', true )){
			$inline_style .='#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-input input,#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-captcha input, #dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-file input[type="text"], #dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-select select, #dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-textarea textarea, #dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-radio i, #dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-checkbox i,#dhvcform-'.$post->ID.'.dhvc-form-flat .ui-slider{border-color:'.$input_border_color.'}';
		}
		if($input_hover_border_color = get_post_meta( $post->ID, '_input_hover_border_color', true )){
			$inline_style .='#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-input:hover input,#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-captcha:hover input, #dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-file:hover input[type="text"], #dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-select:hover select, #dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-textarea:hover textarea, #dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-radio label:hover i, #dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-checkbox label:hover i,#dhvcform-'.$post->ID.'.dhvc-form-flat .ui-slider-range{border-color:'.$input_hover_border_color.'}';
		}
		if($input_focus_border_color = get_post_meta( $post->ID, '_input_focus_border_color', true )){
			$inline_style .='#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-input input:focus,#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-captcha input:focus,  #dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-file:hover input[type="text"]:focus, #dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-select select:focus, #dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-textarea textarea:focus, #dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-radio input:checked + i, #dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-checkbox input:checked + i{border-color:'.$input_focus_border_color.'}';
			$inline_style .='#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-radio input + i:after{background-color:'.$input_focus_border_color.'}';
			$inline_style .='#dhvcform-'.$post->ID.'.dhvc-form-flat .dhvc-form-checkbox input + i:after{color:'.$input_focus_border_color.'}';
		}
		if($button_height = get_post_meta( $post->ID, '_button_height', true )){
			$inline_style .= '.dhvc-form-submit,.dhvc-form-submit:focus,.dhvc-form-submit:hover,.dhvc-form-submit:active{
				height:'.$button_height.';
			}';
		}
			
		if($button_bg_color = get_post_meta( $post->ID, '_button_bg_color', true )){
			$inline_style .='#dhvcform-'.$post->ID.' .dhvc-form-submit, #dhvcform-'.$post->ID.' .dhvc-form-submit:hover,#dhvcform-'.$post->ID.' .dhvc-form-submit:active,#dhvcform-'.$post->ID.' .dhvc-form-submit:focus,#dhvcform-'.$post->ID.' .dhvc-form-file-button{background:'.$button_bg_color.'}';
		}
		if($wpb_custom_css = get_post_meta( $post->ID, '_wpb_post_custom_css', true )){
			$inline_style .= $wpb_custom_css;
		}
		$inline_style = dhvc_form_css_minify($inline_style);
		return '<style type="text/css">'.$inline_style.'</style>';
		}
	}
	
	public function frontend_assets(){
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_style('dhvc-form',DHVC_FORM_URL.'/assets/css/style.css', array(), DHVC_FORM_VERSION);
		wp_register_script('dhvc-form',DHVC_FORM_URL.'/assets/js/script.js',array('jquery','dhvc-form-validate'),DHVC_FORM_VERSION,true);
		
		if(apply_filters('dhvc_form_custom_css_in_head', false)){
			$args = array(
				'post_type'=>'dhvcform',
				'posts_per_page'=> -1,
				'post_status'=>'publish',
			);
			$form = new WP_Query($args);
			$inline_style='';
			if($form->have_posts()){
				while ($form->have_posts()):
					$form->the_post();
					global $post;
				
				if($label_color = get_post_meta( get_the_ID(), '_label_color', true )){
					$inline_style .= '
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-group .dhvc-form-label,
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-group label:not(.dhvc-form-rate-star){
		color:'.$label_color.'
	}';
				}
				if($placeholder_color = get_post_meta( get_the_ID(), '_placeholder_color', true )){
	$inline_style .='
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-group .dhvc-form-add-on{
		color:'.$placeholder_color.';
	}
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-group .dhvc-form-control::-webkit-input-placeholder {
		color:'.$placeholder_color.';
	}
	
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-group .dhvc-form-control:-moz-placeholder {
		color:'.$placeholder_color.';
	}
	
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-group .dhvc-form-control::-moz-placeholder {
		color:'.$placeholder_color.';
	}
	
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-group .dhvc-form-control:-ms-input-placeholder {
		color:'.$placeholder_color.';
	}
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-group .dhvc-form-control:focus::-webkit-input-placeholder {
		color: transparent;
	}';		
				}
				if($input_height = get_post_meta( get_the_ID(), '_input_height', true )){
	$inline_style .='
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-input input, 
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-file input[type="text"], 
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-captcha input, 
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-select select,
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-group .dhvc-form-add-on,
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-file-button i,
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-select i{
	 height:'.$input_height.';
	}
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-select i,
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-file-button i,
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-group .dhvc-form-add-on{
	  line-height:'.$input_height.';
	}
	';			
				}
				
				if($input_bg_color = get_post_meta( get_the_ID(), '_input_bg_color', true )){
						$inline_style .= '
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-input input, 
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-file input[type="text"], 
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-captcha input, 
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-select select,
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-radio i,
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-checkbox i,
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-textarea textarea{
		background-color:'.$input_bg_color.';			
	}';
				}
				if($input_text_color = get_post_meta( get_the_ID(), '_input_text_color', true )){
	$inline_style .= '
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-input input, 
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-file input[type="text"], 
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-captcha input, 
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-select select,
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-textarea textarea{
		color:'.$input_text_color.';			
	}';
				}
				if($input_border_size = get_post_meta( get_the_ID(), '_input_border_size', true )){
	$inline_style .= '
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-input input, 
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-file input[type="text"], 
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-captcha input, 
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-select select, 
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-radio i, 
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-checkbox i,
	#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-textarea textarea{
		border-width:'.$input_border_size.';
	}';
				}
				if($input_border_color = get_post_meta( get_the_ID(), '_input_border_color', true )){
					$inline_style .='#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-input input,#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-captcha input, #dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-file input[type="text"], #dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-select select, #dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-textarea textarea, #dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-radio i, #dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-checkbox i,#dhvcform-'.get_the_ID().'.dhvc-form-flat .ui-slider{border-color:'.$input_border_color.'}';
				}
				if($input_hover_border_color = get_post_meta( get_the_ID(), '_input_hover_border_color', true )){
					$inline_style .='#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-input:hover input,#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-captcha:hover input, #dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-file:hover input[type="text"], #dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-select:hover select, #dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-textarea:hover textarea, #dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-radio label:hover i, #dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-checkbox label:hover i,#dhvcform-'.get_the_ID().'.dhvc-form-flat .ui-slider-range{border-color:'.$input_hover_border_color.'}';
				}
				if($input_focus_border_color = get_post_meta( get_the_ID(), '_input_focus_border_color', true )){
					$inline_style .='#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-input input:focus,#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-captcha input:focus,  #dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-file:hover input[type="text"]:focus, #dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-select select:focus, #dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-textarea textarea:focus, #dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-radio input:checked + i, #dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-checkbox input:checked + i{border-color:'.$input_focus_border_color.'}';
					$inline_style .='#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-radio input + i:after{background-color:'.$input_focus_border_color.'}';
					$inline_style .='#dhvcform-'.get_the_ID().'.dhvc-form-flat .dhvc-form-checkbox input + i:after{color:'.$input_focus_border_color.'}';
				}
				if($button_height = get_post_meta( get_the_ID(), '_button_height', true )){
					$inline_style .= '.dhvc-form-submit,.dhvc-form-submit:focus,.dhvc-form-submit:hover,.dhvc-form-submit:active{
					height:'.$button_height.';
				}';
				}
				
				if($button_bg_color = get_post_meta( get_the_ID(), '_button_bg_color', true )){
					$inline_style .='#dhvcform-'.get_the_ID().' .dhvc-form-submit, #dhvcform-'.get_the_ID().' .dhvc-form-submit:hover,#dhvcform-'.get_the_ID().' .dhvc-form-submit:active,#dhvcform-'.get_the_ID().' .dhvc-form-submit:focus,#dhvcform-'.get_the_ID().' .dhvc-form-file-button{background:'.$button_bg_color.'}';
				}
				if($wpb_custom_css = get_post_meta( get_the_ID(), '_wpb_post_custom_css', true )){
					$inline_style .= $wpb_custom_css;
				}
				
				endwhile;
			}
			wp_reset_postdata();
			
			wp_add_inline_style('dhvc-form',dhvc_form_css_minify($inline_style));
		}
		
		
		wp_enqueue_style('dhvc-form-font-awesome');
		wp_enqueue_style('dhvc-form');
		$dhvcformL10n = array(
			'ajax_url'=>admin_url( 'admin-ajax.php', 'relative' ),
			'plugin_url'=>DHVC_FORM_URL,
			'recaptcha_ajax_verify'=>apply_filters('dhvc_form_recaptcha_use_ajax_verify', false) ? 'yes':'no',
			'_ajax_nonce'=>wp_create_nonce( 'dhvc_form_ajax_nonce' ),
			'allowed_file_extension'=>str_replace(',', '|', dhvc_form_get_option('allowed_file_extension','zip,rar,tar,7z,jpg,jpeg,png,gif,pdf,doc,docx,ppt,pptx,xls,xlsx')),
			'date_format'=>dhvc_form_get_option('date_format','Y/m/d'),
			'time_format'=>dhvc_form_get_option('time_format','H:i'),
			'time_picker_step'=>dhvc_form_get_option('time_picker_step',60),
			'dayofweekstart'=>apply_filters('dhvc_form_dayofweekstart',1),
			'datetimepicker_lang'=>dhvc_form_get_option('datetimepicker_lang','en'),
			'container_class'=>dhvc_form_get_option('container_class','.vc_row-fluid'),
			'validate_error_sroll_offset'=>apply_filters('dhvc_form_validate_error_sroll_offset', 0),
			'validate_messages'=>apply_filters('dhvc_form_validate_messages',array(
				'required'=>__("This field is required.",'dhvc-form'),
				'remote'=>__("Please fix this field.",'dhvc-form'),
				'email'=>__("Please enter a valid email address.",'dhvc-form'),
				'url'=>__("Please enter a valid URL.",'dhvc-form'),
				'date'=>__("Please enter a valid date.",'dhvc-form'),
				'dateISO'=>__("Please enter a valid date (ISO).",'dhvc-form'),
				'number'=>__("Please enter a valid number.",'dhvc-form'),
				'number2'=>__("Please use only numbers (0-9) or brackets (), dashes – and plus +",'dhvc-form'),
				'digits'=>__("Please enter only digits.",'dhvc-form'),
				'creditcard'=>__("Please enter a valid credit card number.",'dhvc-form'),
				'equalTo'=>__("Please enter the same value again.",'dhvc-form'),
				'maxlength'=>__("Please enter no more than {0} characters.",'dhvc-form'),
				'minlength'=>__("Please enter at least {0} characters.",'dhvc-form'),
				'rangelength'=>__("Please enter a value between {0} and {1} characters long.",'dhvc-form'),
				'range'=>__("Please enter a value between {0} and {1}.",'dhvc-form'),
				'max'=>__("Please enter a value less than or equal to {0}.",'dhvc-form'),
				'min'=>__("Please enter a value greater than or equal to {0}.",'dhvc-form'),
				'alpha'=>__('Please use letters only (a-z or A-Z) in this field.','dhvc-form'),
				'alphanum'=>__('Please use only letters (a-z or A-Z) or numbers (0-9) only in this field. No spaces or other characters are allowed.','dhvc-form'),
				'url'=>__('Please enter a valid URL. Protocol is required (http://, https:// or ftp://)','dhvc-form'),
				'zip'=>__('Please enter a valid zip code. For example 90602 or 90602-1234.','dhvc-form'),
				'fax'=>__('Please enter a valid fax number. For example (123) 456-7890 or 123-456-7890.','dhvc-form'),
				'cpassword'=>__('Please make sure your passwords match.','dhvc-form'),
				'select'=>__('Please select an option','dhvc-form'),
				'recaptcha'=>__('Please enter captcha words correctly','dhvc-form'),
				'captcha'=>__('Please enter captcha words correctly','dhvc-form'),
				'extension'=>__('Please enter a value with a valid extension.','dhvc-form')
			))
		);
		
		wp_localize_script('dhvc-form', 'dhvcformL10n', $dhvcformL10n);
		//wp_enqueue_script('dhvc-form');
		return false;
	}
	
	public function override_woocommerce_my_account_shortcode(){
		global $wp,$dhvc_form_woocommerce_login,$dhvc_form_woocommerce_lost_password;
		if(defined('WC_VERSION')){
			$woocommerce_lost_password_page_id = absint(dhvc_form_get_option('woocommerce_lost_password_page_id'));
			$woocommerce_login_page_id = absint(dhvc_form_get_option('woocommerce_login_page_id'));
			if(! is_user_logged_in() && ($woocommerce_lost_password_page_id || $woocommerce_login_page_id)){
				if (isset( $wp->query_vars['lost-password'] ) ) {
					if($woocommerce_lost_password_page_id && $woocommerce_lost_password_page = get_post($woocommerce_lost_password_page_id)){
						$dhvc_form_woocommerce_lost_password = $woocommerce_lost_password_page;
						remove_shortcode('woocommerce_my_account');
						add_shortcode('woocommerce_my_account', array(&$this,'woocommerce_lost_password'));
					}
				}else{
					if($woocommerce_login_page_id && $woocommerce_my_login_page = get_post($woocommerce_login_page_id)){
						$dhvc_form_woocommerce_login = $woocommerce_my_login_page;
						remove_shortcode('woocommerce_my_account');
						add_shortcode('woocommerce_my_account', array(&$this,'woocommerce_login'));
					}
				}
			}
		}
	}
	
	public function woocommerce_lost_password(){
		global $dhvc_form_woocommerce_lost_password;
		$content = $dhvc_form_woocommerce_lost_password->post_content;
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );
		$content = apply_filters('dhvc_form_woocommerce_lost_password_page',$content);
		ob_start();
		if($wpb_custom_css = get_post_meta( $dhvc_form_woocommerce_lost_password->ID, '_wpb_post_custom_css', true )){
			echo '<style type="text/css">'.$wpb_custom_css.'</style>';
		}
		if($wpb_shortcodes_custom_css = get_post_meta( $dhvc_form_woocommerce_lost_password->ID, '_wpb_shortcodes_custom_css', true )){
			echo '<style type="text/css">'.$wpb_shortcodes_custom_css.'</style>';
		}
		echo do_shortcode($content);
		return ob_get_clean();
	}
	
	public function woocommerce_login(){
		global $dhvc_form_woocommerce_login;
		$content = $dhvc_form_woocommerce_login->post_content;
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );
		$content = apply_filters('dhvc_form_woocommerce_login_page',$content);
		ob_start();
		if($wpb_custom_css = get_post_meta( $dhvc_form_woocommerce_login->ID, '_wpb_post_custom_css', true )){
			echo '<style type="text/css">'.$wpb_custom_css.'</style>';
		}
		if($wpb_shortcodes_custom_css = get_post_meta( $dhvc_form_woocommerce_login->ID, '_wpb_shortcodes_custom_css', true )){
			echo '<style type="text/css">'.$wpb_shortcodes_custom_css.'</style>';
		}
		echo do_shortcode($content); 
		return ob_get_clean();
	}
	
	public function dhvc_form_popup(){
		global $dhvc_form_popup;
		$args = array(
				'post_type'=>'dhvcform',
				'posts_per_page'=> -1,
				'post_status'=>'publish',
		);
		$form = new WP_Query($args);
		$popup = array();
		if($form->have_posts()):
			while ($form->have_posts()):
				$form->the_post();
				
			if(get_post_meta(get_the_ID(),'_form_popup',true) && apply_filters('dhvc_form_display', true, get_the_ID())){
				
				$auto_open = get_post_meta(get_the_ID(),'_form_popup_auto_open',true);
				
				$one = get_post_meta(get_the_ID(),'_form_popup_one',true);
				$close = get_post_meta(get_the_ID(),'_form_popup_auto_close',true);
				$title = get_post_meta(get_the_ID(),'_form_popup_title',true);
				$data_attr = '';
				if(!empty($auto_open)){
					$data_attr = 'data-auto-open="1" data-open-delay="'.absint(get_post_meta(get_the_ID(),'_form_popup_auto_open_delay',true)).'" '.(!empty($one) ? 'data-one-time="1"' : 'data-one-time="0"').' '.(!empty($close ) ? 'data-auto-close="1" data-close-delay="'.absint(get_post_meta(get_the_ID(),'_form_popup_auto_close_delay',true)).'"':'data-auto-close="0"');
				}
				$popup[] = '<div id="dhvcformpopup-'.get_the_ID().'" data-id="'.get_the_ID().'" class="dhvc-form-popup" '.$data_attr.' style="display:none">';
				$popup[] = '<div class="dhvc-form-popup-container" style="width:'.absint(get_post_meta(get_the_ID(),'_form_popup_width',true)).'px">';
				$popup[] = '<div class="dhvc-form-popup-header">';
				if(!empty($title)){
					$popup[] = '<h3>'.get_the_title(get_the_ID()).'</h3>';
				}
				$popup[] = '<a class="dhvc-form-popup-close"><span aria-hidden="true">&times;</span></a>';
				$popup[] = '</div>';
				$popup[] = '<div class="dhvc-form-popup-body">';
				$popup[] = do_shortcode('[dhvc_form id="'.get_the_ID().'"]');
				$popup[] = '</div>';
				$popup[] = '</div>';
				$popup[] = '</div>';
			}
			endwhile;
		endif;
		$dhvc_form_popup = implode("\n", $popup);
		if(!empty($popup))
			$dhvc_form_popup .= '<div class="dhvc-form-pop-overlay"></div>';
		wp_reset_postdata();
		return false;
	}
	
	public function dhvc_form_print_form_popup(){
		global $dhvc_form_popup;
		echo $dhvc_form_popup;
		return false;
	}
	
	public function dhvc_form_recaptcha2(){
		if( !check_ajax_referer('dhvc_form_ajax_nonce', '_ajax_nonce', false) ) {
			$result = array(
				'success' => false,
				'message' => '<span class="dhvc-form-error">'.__('It seems that you are a bot, Please try again.','dhvc-form').'</span>',
			);
			wp_send_json($result);
		}
		$recaptcha_value = isset( $_POST[ 'recaptcha2_response' ] ) ? (string) $_POST[ 'recaptcha2_response' ] : '';
		$site_key = dhvc_form_get_option('recaptcha_public_key');
		$secret_key	 = dhvc_form_get_option('recaptcha_private_key');
		$result = array();
		$use_ajax_verify = apply_filters('dhvc_form_recaptcha_use_ajax_verify', false);
		if ( ! empty( $site_key ) && ! empty( $secret_key ) ) {
			if ( 0 == strlen( trim( $recaptcha_value ) ) ) {   //recaptcha is uncheked
				$result['success'] = false;
				$result['message'] = '<span class="dhvc-form-error">'.__('No CAPTCHA reCAPTCHA is unchecked, Check to proceed','dhvc-form').'</span>';
			} elseif($use_ajax_verify){
				$captcha_value = $this->_check_recaptcha2( $recaptcha_value );
				if ( ! $captcha_value ) {  //google returned false
					$result['success'] = false;
					$result['message'] = '<span class="dhvc-form-error">'.__('It seems that you are a bot, Please try again.','dhvc-form').'</span>';
				}
				if ( $captcha_value) {
					$result['success'] = true;
				}
			}
		}
		wp_send_json($result);
	}
	
	private function _check_recaptcha2( $response_token ) {
		$is_human = false;

		if ( empty( $response_token ) ) {
			return $is_human;
		}

		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$sitekey = dhvc_form_get_option('recaptcha_public_key');
		$secret = dhvc_form_get_option('recaptcha_private_key');
		$response = wp_safe_remote_post( $url, array(
			'body' => array(
				'secret' => $secret,
				'response' => $response_token,
				'remoteip' => $_SERVER['REMOTE_ADDR'] ) ) );

		if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
			return $is_human;
		}

		$response = wp_remote_retrieve_body( $response );
		$response = json_decode( $response, true );
		$is_human = isset( $response['success'] ) && true == $response['success'];
		return $is_human;
	}
	
	
	public function check_recaptcha(){
		if( !check_ajax_referer('dhvc_form_ajax_nonce', '_ajax_nonce', false) ) {
			echo 0;
			die;
		}
		$recaptcha_challenge_field  = isset($_POST["recaptcha_challenge_field"]) ? $_POST["recaptcha_challenge_field"] : ''; 
		$recaptcha_response_field =isset($_POST["recaptcha_response_field"]) ? $_POST["recaptcha_response_field"] : '';
		// reCaptcha looks for the POST to confirm
		$result = $this->_check_recaptcha($recaptcha_challenge_field, $recaptcha_response_field);
		if($result){
			echo 1;
		}else{
			echo 0;
		}
		die;
	}
	
	private function _check_recaptcha($recaptcha_challenge_field,$recaptcha_response_field){
		require_once (DHVC_FORM_DIR.'/includes/recaptchalib.php') ;
		$privatekey =  dhvc_form_get_option('recaptcha_private_key');
		$check_answer = recaptcha_check_answer ($privatekey,$_SERVER["REMOTE_ADDR"],$recaptcha_challenge_field,$recaptcha_response_field);
		if($check_answer->is_valid)
			return true;
		else
			return false;
		
	}
	
	public function check_captcha(){
		if( !check_ajax_referer('dhvc_form_ajax_nonce', '_ajax_nonce', false) ) {
			echo 0;
			die;
		}
		$answer = isset($_POST['answer']) ? $_POST['answer'] : '';
		if(empty($_SESSION['dhvcformsecret']) || trim(strtolower($answer)) !== $_SESSION['dhvcformsecret']){
			unset($_SESSION['dhvcformsecret']);
			echo 0;
		}else{
			echo 1;
		}
		die;
	}
	
	public function notice(){
		$plugin = get_plugin_data(__FILE__);
		echo '<div class="updated">
			    <p>' . sprintf(__('<strong>%s</strong> requires <strong><a href="http://bit.ly/1gKaeh5" target="_blank">Visual Composer</a></strong> plugin to be installed and activated on your site.', 'dhvc-form'), $plugin['Name']) . '</p>
			  </div>';
	}
	
	
	public function processor($is_ajax=false){
		if(apply_filters('dhvc_form_only_use_post_request',true) && !dhvc_form_is_post_request()){
			return;
		}
		global $dhvc_form_messages;
		// Strip slashes from the submitted data (WP adds them automatically)
		$_POST = stripslashes_deep($_POST);
		$_REQUEST = stripslashes_deep($_REQUEST);
		$_GET = stripslashes_deep($_GET);
		
		if($_REQUEST['dhvc_form']){
			
			$nonce = $_REQUEST['_dhvc_form_nonce'];
			$form_id = $_REQUEST['dhvc_form'];
			dhvc_form_clear_messages($form_id);
			$recaptcha_verify = true;
			if(!apply_filters('dhvc_form_recaptcha_use_ajax_verify', false)){
				if(isset($_REQUEST['g-recaptcha-response'])){
					$recaptcha_verify = $this->_check_recaptcha2($_REQUEST['g-recaptcha-response']);
				}elseif (isset($_REQUEST['recaptcha_challenge_field']) && isset($_REQUEST['recaptcha_response_field'])){
					$recaptcha_verify = $this->_check_recaptcha($_REQUEST['recaptcha_challenge_field'],$_REQUEST['recaptcha_response_field']);
				}
				if(!$recaptcha_verify){
					$message = __('reCaptcha Invalid','dhvc-form');
					if($is_ajax){
						$ajax_result = array();
						$ajax_result['message'] = $message;
						$ajax_result['success']  = 0;
						$ajax_result['redirect_url'] = $_REQUEST['form_url'];
						return $ajax_result;
					}else{
						dhvc_form_add_messages($form_id,$message,'error');
						wp_safe_redirect($_REQUEST['form_url']);
						exit;
					}
				}
			}
			
			$result = wp_verify_nonce( $nonce, 'dhvc-form-'.$form_id );
			if(false != $result && (get_post_meta($form_id,'_action_type',true) === 'default')){
				//processor
				do_action('dhvc_form_before_processor',$form_id);
				$_on_success = get_post_meta($form_id,'_on_success',true);
				$_form_control = get_post_meta($form_id,'_form_control',true);
				$form_controls = json_decode($_form_control);
				
				$default_action = dhvc_form_get_actions();
				$additional_setting = get_post_meta($form_id,'_additional_setting',true);
				$additional_setting = dhvc_form_additional_setting('on_sent_ok', $additional_setting,false);
				$additional_setting = apply_filters('dhvc_form_on_sent_ok', $additional_setting);
				
				//get from data
				$form_data =$this->_form_handler($form_id, $form_controls, false, false, false, true);
				
				if(isset($_REQUEST['_dhvc_form_action']) && in_array($_REQUEST['_dhvc_form_action'], $default_action)){
					$action = '_'.$_REQUEST['_dhvc_form_action'];
					if(method_exists($this,$action)){
						$ajax_result = array();
						if(!empty($additional_setting)){
							$ajax_result['scripts_on_sent_ok'] = array_map('dhvc_form_strip_quote', $additional_setting);
						}
						$ajax_result['on_success'] = $_on_success;
						$ajax_result['redirect_url'] = '';
						$result = $this->$action($_REQUEST);
						if(!$result['success'] && !$is_ajax){
							dhvc_form_add_messages($form_id,$result['message'],'error');
						}
						if($result['success'] && $_on_success === 'message'){
							$message = get_post_meta($form_id,'_message',true);
							$message = dhvc_form_translate_variable($message,$form_data);
							$message = apply_filters('dhvc_form_success_message', $message,$form_id);
							$result['message'] = $message;
							if(!$is_ajax)
								dhvc_form_add_messages($form_id,$result['message']);
						}
						
						
						$ajax_result['message'] = $result['message'];
						$redirect_url = $_REQUEST['form_url'];
						$redirect_url .='#dhvcform-'.$form_id;
						if($result['success']){
							//save form register data
							if($_form_control){
								$this->_form_handler($form_id, $form_controls);
							}
							do_action('dhvc_form_after_processor',$form_id);
							//end save data
							if($_on_success === 'message'){
								$redirect_url = $_REQUEST['form_url'];
								$redirect_url .='#dhvcform-'.$form_id;
							}else{
								$redirect_to = get_post_meta($form_id,'_redirect_to',true);
								if($redirect_to === 'to_url'){
									$redirect_url = get_post_meta($form_id,'_url',true);
								}else{
									if($redirect_to === 'to_page'){
										$redirect_url = get_permalink(get_post_meta($form_id,'_page',true));
									}else{
										$redirect_url = get_permalink(get_post_meta($form_id,'_post',true));
									}
								}
								dhvc_form_clear_messages($form_id);
							}
							$redirect_url = apply_filters('dhvc_form_redirect_url',$redirect_url);
							$ajax_result['redirect_url'] = $redirect_url;
							if($is_ajax)
								return $ajax_result;
								
							wp_redirect($redirect_url);
							exit;
						}
						$ajax_result['success']  = 0;
						if($is_ajax)
							return $ajax_result;
						wp_redirect($redirect_url);
						exit;
						
					}
				}else{
					if($_form_control){
						$result = $this->_form_handler($form_id, $form_controls);
						if(!$result){
							wp_safe_redirect($_REQUEST['form_url']);
							exit;
						}
					}
					do_action('dhvc_form_after_processor',$form_id);
					
					$ajax_result = array();
					$ajax_result['on_success'] = $_on_success;
					if(!empty($additional_setting)){
						$ajax_result['scripts_on_sent_ok'] = array_map('dhvc_form_strip_quote', $additional_setting);
					}
					if($_on_success === 'message'){
						$message = get_post_meta($form_id,'_message',true);
						$message = dhvc_form_translate_variable($message,$form_data);
						$message = apply_filters('dhvc_form_success_message', $message,$form_id);
						$ajax_result['message'] = $message;
						
						if(!$is_ajax)
							dhvc_form_add_messages($form_id,$message);
					}
					$redirect_url = '';
					if($_on_success === 'message'){
						$redirect_url = $_REQUEST['form_url'];
						$redirect_url .='#dhvcform-'.$form_id;
					}else{
						$redirect_to = get_post_meta($form_id,'_redirect_to',true);
						if($redirect_to === 'to_url'){
							$redirect_url = get_post_meta($form_id,'_url',true);
						}else{
							if($redirect_to === 'to_page'){
								$redirect_url = get_permalink(get_post_meta($form_id,'_page',true));
							}else{
								$redirect_url = get_permalink(get_post_meta($form_id,'_post',true));
							}
						}
						dhvc_form_clear_messages($form_id);
					}
					$redirect_url = apply_filters('dhvc_form_redirect_url',$redirect_url);
					$ajax_result['redirect_url'] = $redirect_url;
					if($is_ajax)
						return $ajax_result;
						
					wp_redirect($redirect_url);
					exit;
					
				}
			}//
		}
		return false;
	}
	
	
	/**
	 * Form Hander
	 * 
	 * @param int $form_id
	 * @param array $form_controls
	 * @param bool $save_entery
	 * @param bool $send_notice
	 * @param bool $autoreply
	 * @throws phpmailerException
	 */
	private function _form_handler($form_id, $form_controls, $save_entry = true, $send_notice = true, $autoreply = true, $get_data=false){
		$current_user = wp_get_current_user();
		if(!empty($this->_form_data)){
			$entry_data = $this->_form_data['entry_data'];
			$email_data = $this->_form_data['email_data'];
			$attachments = $this->_form_data['attachments'];
			$submited_data = $this->_form_data['submited_data'];
			$posted_data = $this->_form_data['posted_data'];
		}else{
			$entry_data = array();
			$email_data = array();
			$attachments = array();
			$submited_data = (array) $_REQUEST;
			$submited_data = apply_filters('dhvc_form_submited_data', $submited_data,$form_id);
			foreach ($form_controls as $form_control){
				if($form_control->tag == 'dhvc_form_captcha' || $form_control->tag == 'dhvc_form_recaptcha' || $form_control->tag == 'dhvc_form_submit_button')
					continue;
				$email_data_key = !empty($form_control->control_label) ? $form_control->control_label : $form_control->control_name;
				$email_data_key = ucfirst($email_data_key);
				if (array_key_exists($form_control->control_name, $_FILES) && is_array($_FILES[$form_control->control_name])) {
					$file = $_FILES[$form_control->control_name];
			
					$entry_data[$form_control->control_name] = '';
					//$email_data[$email_data_key] = '';
					if (is_array($file['error'])) {
							
					}else{
						if ($file['error'] === UPLOAD_ERR_OK) {
							$result = dhvc_form_upload($file);
							if(is_string($result) && !empty($result)){
								dhvc_form_add_messages($form_id,$result,'error');
								return false;	
							}
							if (is_array($result)) {
								$fullpath = $result['fullpath'];
								$filename = $result['filename'];
			
								$value = array(
									'filename' => $filename,
									'url'=>$result['url']
								);
								$attachments[] = $result['fullpath'];
								$email_data[] = array('label'=>$email_data_key,'value'=>$filename);
								$entry_data[$form_control->control_name] = $value;
							}
			
						}
					}
				}else{
					$entry_data[$form_control->control_name] = isset($submited_data[$form_control->control_name]) ? dhvc_form_format_value($submited_data[$form_control->control_name]) : '';
					$email_data[] = array('label'=>$email_data_key,'value'=>$entry_data[$form_control->control_name]);
				}
			}
			
			$entry_data = apply_filters('dhvc_form_entry_data', $entry_data,$form_id);
			$email_data = apply_filters('dhvc_form_email_data', $email_data,$form_id);
			
			$posted_data = $entry_data;
			$posted_data['site_url']  = get_site_url();
			$posted_data['ip_address'] = dhvc_form_get_user_ip();
			$posted_data['user_display_name'] = ( isset( $current_user->ID ) ? $current_user->display_name : '' );
			$posted_data['user_email'] = ( isset( $current_user->ID ) ? $current_user->user_email : '' );
			$posted_data['user_login'] = ( isset( $current_user->ID ) ? $current_user->ID : 0 );
			$posted_data['form_url'] = isset($submited_data['form_url']) ? $submited_data['form_url'] : '' ;
			$posted_data['form_id'] = $form_id;
			$posted_data['form_title'] = get_the_title($form_id);
			$posted_data['post_id'] = isset($submited_data['post_id']) ? $submited_data['post_id'] : 0;
			$posted_data['post_title'] = get_the_title($posted_data['post_id']);
			$posted_data['submitted'] = date_i18n(dhvc_form_get_option('date_format','Y/m/d')).' '.date_i18n(dhvc_form_get_option('time_format','H:i'));
			
			$email_form_body = '';
			$newline = dhvc_form_email_newline();
			$dhvc_form_use_email_empty_field_value = apply_filters('dhvc_form_use_email_empty_field_value', true,$form_id);
			foreach ($email_data as $k=>$v){
				if(!$dhvc_form_use_email_empty_field_value && empty($v['value']))
					continue;
				
				$email_form_body .= '<strong>'.$v['label'].':</strong> '.$v['value'].$newline;
			}
			
			$posted_data['form_body'] = $email_form_body;
			$posted_data = apply_filters('dhvc_form_posted_data', $posted_data, $form_id);
			$this->_form_data['entry_data'] = $entry_data;
			$this->_form_data['email_data'] = $email_data;
			$this->_form_data['attachments'] = $attachments;
			$this->_form_data['submited_data'] = $submited_data;
			$this->_form_data['posted_data'] = $posted_data;
		}
		if($get_data)
			return $this->_form_data['posted_data'];
		$save_data = get_post_meta($form_id,'_save_data',true);
		//save entry
		if($save_data && $save_entry){
			global $dhvcform_db;
			$data = array(
				'entry_data'=>maybe_serialize($entry_data),
				'submitted'=> current_time('mysql'),
				'ip_address' => dhvc_form_get_user_ip(),
				'form_id'=>$form_id,
				'post_id' => $posted_data['post_id'],
				'form_url' => $posted_data['form_url'],
				'referer' => isset($submited_data['referer']) ? $submited_data['referer'] : '',
				'user_id'=>( isset( $current_user->ID ) ? (int) $current_user->ID : 0 )
			);
			$dhvcform_db->insert_entry_data($data);
		}
		
		
		$notice = get_post_meta($form_id,'_notice',true);
		if($notice && $send_notice){
			$mailer_from = '';
			$notice_email_type = dhvc_get_post_meta($form_id,'_notice_email_type','email_text');
			if($notice_email_type == 'email_field'){
				$notice_variables = dhvc_get_post_meta($form_id,'_notice_variables');
				if($notice_variables){
					if(isset($posted_data[$notice_variables]) && dhvc_form_validate_email($posted_data[$notice_variables])){
						$mailer_from = trim((string)$posted_data[$notice_variables]);
					}
				}
			}else{
				$mailer_from = trim((string)dhvc_get_post_meta($form_id,'_notice_email',get_option('admin_email')));
			}
			$mailer_from = dhvc_form_translate_variable($mailer_from,$posted_data);
			$mailer_from = apply_filters('dhvc_form_notice_sender_email', $mailer_from,$form_id,$posted_data);
		
			$FromName = trim((string)dhvc_get_post_meta($form_id,'_notice_name',get_option('blogname')));
			$mailer_from_name = dhvc_form_translate_variable($FromName,$posted_data);
			
			$recipients = get_post_meta($form_id,'_notice_recipients',true);
			
			$recipients = apply_filters('dhvc_form_notice_recipient_email', $recipients,$form_id);
			
			$mailer_to = array();
			if(is_array($recipients) && !empty($recipients)){
				foreach ((array)$recipients as $recipient){
					$recipient_email = trim($recipient);
					$recipient_email = dhvc_form_translate_variable($recipient,$posted_data);
					if(dhvc_form_validate_email($recipient_email)){
						$mailer_to[] = $recipient_email;
					}
				}
			}
				
			$subject = get_post_meta($form_id,'_notice_subject',true);
			$mailer_subject = dhvc_form_translate_variable($subject,$posted_data);
			
			$body_template = get_post_meta($form_id,'_notice_body',true);
			$body = apply_filters('dhvc_form_notice_email_body_template',$body_template,$form_id,$posted_data);
			$html = false;
			if(get_post_meta($form_id,'_notice_html',true))
				$html = true;
			
			$body = dhvc_form_translate_variable($body,$posted_data,$html);
			$body = apply_filters('dhvc_form_notice_body',$body,$form_id,$posted_data);

			$headers = "From: $mailer_from_name <$mailer_from>\r\n";
			
			$notice_reply_to = get_post_meta($form_id,'_notice_reply_to',true);
			if(isset($posted_data[$notice_reply_to]))
				$notice_reply_to = $posted_data[$notice_reply_to];
			$notice_reply_to = apply_filters('dhvc_form_notice_reply_to', $notice_reply_to,$form_id);
			if($notice_reply_to && dhvc_form_validate_email($notice_reply_to)){
				$headers .= "Reply-To: $notice_reply_to\r\n";
			}
			if($html){
				$body = wpautop( $body );
				$headers .= "Content-Type: text/html\r\n";
			}
			$headers = apply_filters('dhvc_form_notice_header',$headers,$form_id,$posted_data);
			//send email notice
			dhvc_form_email($mailer_to, $mailer_subject, $body, $headers, $attachments);
				
		}
		
		$reply = get_post_meta($form_id,'_reply',true);
		//send reply
		if($reply && $autoreply){
			//send email reply
				
			$recipients = get_post_meta($form_id,'_reply_recipients',true);
			$recipients = apply_filters('dhvc_form_reply_recipient', $recipients,$form_id);
			if($recipients){
				if(isset($posted_data[$recipients]) && dhvc_form_validate_email($posted_data[$recipients])){
					$reply_from = trim((string)dhvc_get_post_meta($form_id,'_reply_email',get_option('admin_email')));
					$reply_FromName = trim((string)dhvc_get_post_meta($form_id,'_reply_name',get_option('blogname')));
					$reply_FromName = apply_filters('dhvc_form_reply_from_name', $reply_FromName,$form_id);
						
					$reply_recipients = $posted_data[$recipients];
					
					$headers = "From: $reply_FromName <$reply_from>\r\n";
					
					$subject = get_post_meta($form_id,'_reply_subject',true);
					$subject = apply_filters('dhvc_form_reply_from_subject',$subject,$form_id);
					$subject = dhvc_form_translate_variable($subject,$posted_data);
					$reply_subject = trim((string)$subject);
						
					$body_template = get_post_meta($form_id,'_reply_body',true);
					$body = apply_filters('dhvc_form_reply_email_body_template',$body_template,$form_id,$posted_data);
					$html = false;
					if(get_post_meta($form_id,'_reply_html',true))
						$headers .= "Content-Type: text/html\r\n";
						
					$body = dhvc_form_translate_variable($body,$posted_data,$html);
					$body = apply_filters('dhvc_form_reply_body',$body,$form_id);
					
					$headers = apply_filters('dhvc_form_reply_header',$headers,$form_id,$posted_data);
					
					if($html){
						$body = wpautop( $body );
					}
					//TODO
					dhvc_form_email($reply_recipients, $reply_subject, $body, $headers);
						
				}
			}
		}
		return true;
	}
	
	
	public function ajax_processor(){
		if(dhvc_form_is_xhr() && $_SERVER['REQUEST_METHOD'] == 'POST'){
			$result = $this->processor(true);
			$echo = array();
			if(false === $result){
				$echo['success'] = isset($result['success']) ? $result['success'] : 0;
			}else{
				$echo = $result;
				$echo['success'] = isset($result['success']) ? $result['success'] : 1;
			}
			
			echo json_encode($echo);
			exit;
		}
	}
	
	protected function _mymail($data){
		if(!defined('MYMAIL_DIR'))
			return array(
				'success' => false,
				'message'  => __( 'myMail Newsletters not exists!.','dhvc-form' ),
			);
		$form_id = $_REQUEST['dhvc_form'];
		$lists = dhvc_get_post_meta($form_id,'_mymail',array());
		$double_opt_in = dhvc_get_post_meta($form_id,'_mymail_double_opt_in',0) == '1' ? true : false ;
		$userdata['firstname'] = isset($data['firstname']) ? trim(preg_replace('/\s*\[[^)]*\]/', '', $data['firstname'])) : '';
		$userdata['lastname'] = isset($data['lastname']) ? trim(preg_replace('/\s*\[[^)]*\]/', '', $data['lastname'])) : '';
		$email = isset($data['email']) ? $data['email'] : '';
		if(!is_email($email)){
			return array(
				'success' => false,
				'message'  => __( 'The email address isn\'t correct.','dhvc-form' ),
			);
		}
		$ret = mymail_subscribe( $email, $userdata, $lists, $double_opt_in, true);
		if (!$ret ) {
			return array(
				'success' => false,
				'message'  => __( 'Not Subscribe to our Newsletters!','dhvc-form' ),
			);
		} else {
			return array(
				'success' => true,
				'message'  => __( 'Subscribe to our Newsletters successful!', 'dhvc-form' ),
			);
		}
	}
	
	protected function _mailpoet($data){
		if(!class_exists('WYSIJA'))
			return array(
				'success' => false,
				'message'  => __( 'MailPoet Newsletters not exists!.','dhvc-form' ),
			);
		$form_id = $_REQUEST['dhvc_form'];
		$list_submit['user_list']['list_ids'] = dhvc_get_post_meta($form_id,'_mailpoet',array());
		$list_submit['user']['firstname'] = isset($data['firstname']) ? trim(preg_replace('/\s*\[[^)]*\]/', '', $data['firstname'])) : '';
		$list_submit['user']['lastname'] = isset($data['lastname']) ? trim(preg_replace('/\s*\[[^)]*\]/', '', $data['lastname'])) : '';
		$list_submit['user']['email'] = isset($data['email']) ? $data['email'] : '';
		if(!is_email($list_submit['user']['email'])){
			return array(
				'success' => false,
				'message'  => __( 'The email address isn\'t correct.','dhvc-form' ),
			);
		}
		//WYSIJA_help_user
		$helper_user = WYSIJA::get('user','helper');
		$result = $helper_user->addSubscriber($list_submit);
		if(!$result){
			$message = $helper_user->getMsgs();
			return array(
				'success' => false,
				'message'  => implode('<br>',$message['error']),
			);
		}else{
			return array(
				'success' => true,
				'message'  => sprintf(__( 'MailPoet Added: %s to list <strong>%s</strong>','dhvc-form' ),$list_submit['user']['email'],implode(', ',dhvc_form_get_mailpoet_subscribers_list($list_submit['user']['list_ids']))),
			);
		}
	}
	
	protected function _mailchimp($data){
		$mailchimp_api = dhvc_form_get_option('mailchimp_api',false);
		$success=false;
		$message='';
		if($mailchimp_api){
			if(!class_exists('MCAPI'))
				require_once DHVC_FORM_DIR.'/includes/MCAPI.class.php';
			
			$api = new MCAPI($mailchimp_api);
			$fname=isset($data['name']) ? '':'';
			$list_id = dhvc_form_get_option('mailchimp_list',0);
			$list_id = apply_filters('dhvc_form_mailchimp_list', $list_id,$data);
			$first_name = isset($data['first_name']) ? $data['first_name']:(isset($data['name']) ? $data['name'] : '');
			$last_name = isset($data['last_name']) ? $data['last_name']:(isset($data['name']) ? $data['name'] : '');
			$email_address = isset($data['email']) ? $data['email'] : '';
			$merge_vars = array(
					'FNAME' => $first_name,
					'LNAME' => $last_name,
			);
			$mailchimp_group_name = dhvc_form_get_option('mailchimp_group_name','');
			$mailchimp_group = dhvc_form_get_option('mailchimp_group','');
			if(!empty($mailchimp_group) && !empty($mailchimp_group_name)){
				$merge_vars['GROUPINGS'] = array(
						array('name'=>$mailchimp_group_name, 'groups'=>$mailchimp_group),
				);
			}
			$merge_vars = apply_filters('dhvc_form_mailchimp_merge_vars', $merge_vars, $data);
			$double_optin = dhvc_form_get_option('mailchimp_opt_in','') === '1' ? true : false;
			$replace_interests = dhvc_form_get_option('mailchimp_replace_interests','') === '1' ? true : false;
			$send_welcome = dhvc_form_get_option('mailchimp_welcome_email','') === '1' ? true : false;
			
			try{
				$retval = $api->listSubscribe($list_id, $email_address, $merge_vars, $email_type='html', $double_optin,false, $replace_interests, $send_welcome);
				if($retval){
					$success = true;
				}else{
					if (!empty($api->errorMessage))
						$message = $api->errorMessage;
				}
			}catch (Exception $e){
				if ($e->getCode() == 214){
					$success = true;
				}else{
					$message = $e->getMessage();
				}
			}
			
			
		}
		// Check the results of our Subscribe and provide the needed feedback
		if (!$success ) {
			return array(
				'success' => false,
				'message'  => !empty($message) ? $message : __( 'Not Subscribe to Mailchimp!','dhvc-form' ),
			);
		} else {
			return array(
				'success' => true,
				'message'  => __( 'Subscribe to Mailchimp Successful!', 'dhvc-form' ),
			);
		}
	}
	
	protected function _login($data){
		$data['user_login']         = isset($data['username']) ?  $data['username'] : '';
		$data['user_password']      = isset($data['password']) ?  $data['password']: '';
		$data['remember']           = isset($data['rememberme']) ? $data['rememberme']:'';
		$secure_cookie = is_ssl() ? true : false;;
		$secure_cookie = apply_filters('dhvc_form_login_secure_cookie', $secure_cookie);
		$user = wp_signon( $data, $secure_cookie );
		// Check the results of our login and provide the needed feedback
		if ( is_wp_error( $user ) ) {
			return array(
				'success' => false,
				'message'  => __( 'Wrong Username or Password!','dhvc-form' ),
			);
		} else {
			return array(
				'success' => true,
				'message'  => __( 'Login Successful!', 'dhvc-form' ),
			);
		}
	}
	
	protected function _register($data){
		if(get_option( 'users_can_register' )){
			$user_login = isset($data['user_login']) ? $data['user_login'] : '';
			$user_email = isset($data['user_email']) ? $data['user_email'] : '';
			$user_password  = isset($data['user_password']) ? $data['user_password'] : '';
			$cuser_password = isset($data['cuser_password']) ? $data['cuser_password'] : '';
			
			$ret = $this->_register_new_user($user_login, $user_email,$user_password,$cuser_password,$data);

				
			if ( is_wp_error( $ret ) ) {
				return array(
						'success' => false,
						'message'   => $ret->get_error_message(),
				);
			} else {
				if ( apply_filters( 'dhvc_form_registration_auth_new_customer', true, $ret ) ) {
					wp_set_auth_cookie( $ret );
				}
				return array(
						'success'     => true,
						'message'	=> __( 'Registration complete.', 'dhvc-form' )
				);
			}
		}else {
			return array(
					'success' => false,
					'message'   =>__( 'Not allow register in site.', 'dhvc-form' ),
			);
		}
	}
	
	private function _register_new_user( $user_login, $user_email, $user_password='', $cuser_password='',$data=array()) {
		
		$errors = new WP_Error();
		$sanitized_user_login = sanitize_user( $user_login );
		$user_email = apply_filters( 'user_registration_email', $user_email );
	
		// Check the username was sanitized
		if ( $sanitized_user_login == '' ) {
			$errors->add( 'empty_username', __( 'Please enter a username.', 'dhvc-form' ) );
		} elseif ( ! validate_username( $user_login ) ) {
			$errors->add( 'invalid_username', __( 'This username is invalid because it uses illegal characters. Please enter a valid username.', 'dhvc-form' ) );
			$sanitized_user_login = '';
		} elseif ( username_exists( $sanitized_user_login ) ) {
			$errors->add( 'username_exists', __( 'This username is already registered. Please choose another one.', 'dhvc-form' ) );
		}
	
		// Check the email address
		if ( $user_email == '' ) {
			$errors->add( 'empty_email', __( 'Please type your email address.', 'dhvc-form' ) );
		} elseif ( ! is_email( $user_email ) ) {
			$errors->add( 'invalid_email', __( 'The email address isn\'t correct.', 'dhvc-form' ) );
			$user_email = '';
		} elseif ( email_exists( $user_email ) ) {
			$errors->add( 'email_exists', __( 'This email is already registered, please choose another one.', 'dhvc-form' ) );
		}
		$form_has_password = false;
		//Check the password
		if(empty($user_password)){
			$user_password = wp_generate_password( 12, false );
		}else{
			$form_has_password = true;
			if(strlen($user_password) < 6){
				$errors->add( 'minlength_password', __( 'Password must be 6 character long.', 'dhvc-form' ) );
			}elseif (empty($cuser_password)){
				$errors->add( 'not_cpassword', __( 'Not see password confirmation field.', 'dhvc-form' ) );
			}elseif ($user_password != $cuser_password){
				$errors->add( 'unequal_password', __( 'Passwords do not match.', 'dhvc-form' ) );
			}
		}
	
		$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );
	
		if ( $errors->get_error_code() )
			return $errors;
		
		$new_user_data = array(
			'user_login' => wp_slash($sanitized_user_login),
			'user_pass'  => $user_password,
			'user_email' => wp_slash($user_email),
		);
		$optional_user_data = array('user_nicename','user_url','user_email','display_name','nickname','first_name','last_name','description','rich_editing','user_registered','role','jabber','aim','yim','show_admin_bar_front');
		foreach ($optional_user_data as $v)
			if(isset($data[$v]) && !empty($data[$v]))
				$new_user_data[$v] = $data[$v];
		$new_user_data = apply_filters('dhvc_form_new_user_data', $new_user_data);
		$user_id = wp_insert_user( $new_user_data );
		//$user_id = wp_create_user( $sanitized_user_login, $user_password, $user_email );
	
		if ( ! $user_id ) {
			$errors->add( 'registerfail', __( 'Couldn\'t register you... please contact the site administrator', 'dhvc-form' ) );
	
			return $errors;
		}
		if(apply_filters('dhvc_form_user_password_nag', false))
			update_user_option( $user_id, 'default_password_nag', true, true ); // Set up the Password change nag.
		
		do_action( 'register_new_user', $user_id );
		
		$user = get_userdata( $user_id );
		//@todo
		$notify = $form_has_password ? 'admin' : '';
		$notify = apply_filters('dhvc_form_new_user_notify', $notify);
		
		wp_new_user_notification( $user_id, null, $notify );
		
		if(!empty($user_password)){
			$data_login['user_login']             = $user->user_login;
            $data_login['user_password']          = $user_password;
			$user_login                    	      = wp_signon( $data_login, false );
		}
		
		
		return $user_id;
	}
	
	protected function _forgotten($data){
		$user_login = isset($data['user_login']) ? $data['user_login'] : '';
// 		if ( dhvc_form_validate_email($user_login) ) {
// 			$username = sanitize_email( $user_login );
// 		} else {
// 			$username = sanitize_user( $user_login );
// 		}
		
		$user_forgotten = $this->_retrieve_password( $user_login );
		
		if ( is_wp_error( $user_forgotten ) ) {
			return array(
					'success' 	 => false,
					'message' => $user_forgotten->get_error_message(),
			);
		} else {
			return array(
					'success'   => true,
					'message' => __( 'Password Reset. Please check your email.', 'dhvc-form' ),
			);
		}
		
	}
	
	protected function _retrieve_password($post_user_login) {
		global $wpdb, $wp_hasher;

		$errors = new WP_Error();
	
		if ( empty( $post_user_login ) ) {
			$errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or email address.'));
		} elseif ( strpos( $post_user_login, '@' ) ) {
			$user_data = get_user_by( 'email', trim( $post_user_login ) );
			if ( empty( $user_data ) )
				$errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.'));
		} else {
			$login = trim($post_user_login);
			$user_data = get_user_by('login', $login);
		}
		
		do_action( 'lostpassword_post', $errors );
	
		if ( $errors->get_error_code() )
			return $errors;
	
		if ( !$user_data ) {
			$errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or email.'));
			return $errors;
		}
	
		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;
		
		do_action( 'retreive_password', $user_login );
		
		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );
		
		if ( ! $allow )
			return new WP_Error('no_password_reset', __('Password reset is not allowed for this user'));
		else if ( is_wp_error($allow) )
			return $allow;
		if(function_exists('get_password_reset_key')){
			$key = get_password_reset_key( $user_data );
		}else{
			$key = wp_generate_password( 20, false );
			do_action( 'retrieve_password_key', $user_login, $key );
			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . 'wp-includes/class-phpass.php';
				$wp_hasher = new PasswordHash( 8, true );
			}
			$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
			$key_saved = $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );
			if ( false === $key_saved ) {
				return new WP_Error( 'no_password_key_update', __( 'Could not save password reset key to database.' ) );
			}
		}
		
	
		if ( is_wp_error( $key ) ) {
			return $key;
		}
	
		$message = __('Someone has requested a password reset for the following account:') . "\r\n\r\n";
		$message .= network_home_url( '/' ) . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
		$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
		$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
		$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";
	
		if ( is_multisite() )
			$blogname = $GLOBALS['current_site']->site_name;
		else
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	
		$title = sprintf( __('[%s] Password Reset'), $blogname );
	
		$title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );
	
		$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );
	
		if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) )
			return new WP_Error( __('The email could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.') );
	
		return true;
	}
	
}

new DHVCForm();
