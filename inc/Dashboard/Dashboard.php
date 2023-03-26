<?php
/**
 *	Dashboard module for settings related stuff
 *
 * 	@package Toolbox
 */

namespace ToolboxSync\Dashboard;


/**
 * 	The Dashboard class
 */
class Dashboard {

	/**
	 * errors that can be outputted to the dashboard
	 * @var [type]
	 */
	public static $errors = array();

	// create dashboard pages for toolboxsync
	private static $dash_prefix = "toolboxsync";  

	/**
	 * initialize the dashboard
	 *
	 * @return [type] [description]
	 */
	public function __construct() {

		add_action( 'after_setup_theme', __CLASS__ . '::init_hooks', 11 );

		add_action( self::$dash_prefix."_on_init" , __CLASS__ . '::check_for_application_authorize' );

	}

	public static function check_for_application_authorize() {
		$user_login = filter_input( INPUT_GET , 'user_login' );
		$password = filter_input( INPUT_GET , 'password' );
		$site_url = filter_input( INPUT_GET , 'site_url' );

		if ( !$user_login || !$password || !$site_url ) return;

		update_option( 'tsync_remotesite' , $site_url );
		update_option( 'tsync_user_login' , $user_login );
		update_option( 'tsync_password' , $password );
	}

	public static function prefix() {
		return self::$dash_prefix;
	}

	/**
	 * Init the correct hooks
	 * @return [type] [description]
	 */
	public static function init_hooks() {

		// return early if not executed by admin
		if ( ! is_admin() ) return;

		// add the settings menu
		add_action( 'admin_menu', __CLASS__ . '::add_dashboard_menu' );


		// check for save action
		if ( isset( $_REQUEST['page'] ) && self::$dash_prefix . '-settings' == $_REQUEST['page'] ) {

			add_action( 'admin_enqueue_scripts', __CLASS__ . '::dashboard_styles_scripts' );

			// do actions so we can store our returned credentials
			do_action( self::$dash_prefix."_on_init" );

			self::save();

		}

	}

	/**
	 * Load the styles and scripts for the dashboard
	 * @return [type] [description]
	 */
	public static function dashboard_styles_scripts() {

		// load the jquery-tabs css and js
		wp_enqueue_style( 'jquery-tabs'	, TOOLBOXSYNC_URL . 'css/jquery.tabs.min.css'	, array(), TOOLBOXSYNC_VERSION );
		wp_enqueue_script( 'jquery-tabs', TOOLBOXSYNC_URL . 'js/jquery.tabs.min.js'		, array(), TOOLBOXSYNC_VERSION );

		// toolbox-dashboard css and js
		wp_enqueue_style( 'toolboxsync-dashboard'	, TOOLBOXSYNC_URL . 'css/toolbox-dashboard.css'	, array(), TOOLBOXSYNC_VERSION );
		wp_enqueue_script( 'toolboxsync-dashboard'	, TOOLBOXSYNC_URL . 'js/toolbox-dashboard.js'	, array(), TOOLBOXSYNC_VERSION );

	}


	/**
	 * Show an admin notice on the update
	 * @return [type] [description]
	 */
	public static function toolbox_settings_update_notice() {

		if ( 1 == $_REQUEST['status'] ) $class = 'notice notice-success';
		if ( 0 == $_REQUEST['status'] ) $class = 'notice notice-error';
		printf( '<div class="%s is-dismissible"><p>%s</p></div>', esc_attr($class), esc_html($_REQUEST['message']) );
	}

	/**
	 * Add the dashboard menu links and structure
	 */
	public static function add_dashboard_menu() {

		// check minimum capability of delete_users
		if( !current_user_can('delete_users') ) return;

		// define as parameters first for better readability

		$parent_slug 	= 'options-general.php';	// settings page

		$page_title 	= __( 'Toolbox Sync' , 'toolbox-sync' );
		$menu_title		= __( 'Toolbox Sync' , 'toolbox-sync' );
		$capability		= 'delete_users';
		$menu_slug		= self::$dash_prefix . '-settings';
		$callback 		= __CLASS__ . '::render_options';

		add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback );

	}

	/**
	 * render the options-template to the browser
	 * @return [type] [description]
	 */
	public static function render_options() {

		include_once( TOOLBOXSYNC_DIR . 'includes/dashboard/admin-options.php' );
	}

	/**
	 * Render the settings tabs
	 * @return [type] [description]
	 */
	public static function render_settings_tabs() {

		$tabtemplate = '<div class="jq-tab-title" data-tab="%s">%s</div>';
		$tabtemplate_active = '<div class="jq-tab-title" data-tab="%s">%s</div>';

		echo '<div class="jq-tab-menu">';

		printf( $tabtemplate, 'default', esc_attr__( 'Main' , 'toolbox') );
		printf( $tabtemplate, 'push', esc_attr__( 'Push' , 'toolbox') );
		printf( $tabtemplate, 'pull', esc_attr__( 'Pull' , 'toolbox') );

		echo '</div>';
	}


	/**
	 * get the form(s)
	 * @return [type] [description]
	 */
	public static function render_forms() {

		self::render_form( 'default' );
		self::render_form( 'push' );
		self::render_form( 'pull' );

		// self::render_form( 'timber-templates' );
	}

	/**
	 * Render a singular form
	 * @param  [type] $type [description]
	 * @return [type]       [description]
	 */
	public static function render_form( $type ) {

		include TOOLBOXSYNC_DIR . 'includes/dashboard/admin-options-' . $type . '.php';
	}

	/**
	 * Render the heading to the browser
	 * @return [type] [description]
	 */
	public static function render_heading() {

		echo '<h1>' . esc_attr__( 'Toolbox Sync Settings' , 'toolbox-sync' ) . '</h1>';
	}

	/**
	 * Render the action for a form
	 * @param  string $type [description]
	 * @return [type]       [description]
	 */
	static public function render_form_action( $type = '' ) {

		if ( is_network_admin() ) {
			echo network_admin_url( "/settings.php?page=".self::$dash_prefix."-settings#" . $type );
		} else {
			echo admin_url( "/options-general.php?page=".self::$dash_prefix."-settings#" . $type );
		}
	}

	/**
	 * Render the action for a form
	 * @param  string $type [description]
	 * @return [type]       [description]
	 */
	static public function get_form_action( $type = '' ) {

		if ( is_network_admin() ) {
			return network_admin_url( "/settings.php?page=".self::$dash_prefix."-settings#" . $type );
		} else {
			return admin_url( "/options-general.php?page=".self::$dash_prefix."-settings#" . $type );
		}
	}

	/**
	 * Render the errors or update message
	 * @return [type] [description]
	 */
	public static function render_update_message() {

		if ( !empty( self::$errors ) ) {

			// display the errors
			foreach ( self::$errors as $message ) {
				echo '<div class="error"><p>'.$message.'</p></div>';
			}

		} elseif (! empty( $_POST ) && ! isset( $_POST['email'] ) ){

			echo '<div class="updated"><p>' . __( 'Settings Updated!' , 'toolbox-sync' ) . '<p></div>';

		}
	}

	/**
	 * Add an error to the array
	 * @param [type] $message [description]
	 */
	public static function add_error( $message ) {

		self::$errors[] = $message;
	}

	/**
	 * Saves the admin settings.
	 * @return [type] [description]
	 */
	static public function save() {

		// Only admins can save settings.
		if ( ! current_user_can( 'delete_users' ) ) {
			return;
		}

		self::save_toolbox_defaults();
		// self::save_toolbox_timber_templates();
		// self::save_toolbox_uikit();
		// self::save_toolbox_beaverbuilder();
		// self::save_conditional_filters_settings();
		// self::save_license_key();
		// self::action_export_posts_to_files();
		// self::action_import_files_to_posts();

		do_action( self::$dash_prefix."_dashboard_on_panel_save" );

	}

	/**
	 * Helper functions for common input types for the dashboard
	 * @param  [type] $type  [description]
	 * @param  [type] $id    [description]
	 * @param  [type] $value [description]
	 * @return [type]        [description]
	 */
	public static function input( $type , $options = [] ) {

		$type 	= esc_html( $type );
		//$options = array_map( 'esc_attr' , $options );

		switch ($type):
			case "text":

				$options = wp_parse_args( $options ,
											self::key_defaults( [ 'value' , 'id' , 'placeholder' ] )
				);

				return "<input type=\"{$type}\" id=\"{$options['id']}\" name=\"{$options['id']}\" value=\"{$options['value']}\" placeholder=\"{$options['placeholder']}\">";

			break;

			case "checkbox":

				$options = wp_parse_args( $options ,
											self::key_defaults( [ 'value' , 'id' , 'checked' ] )
				);

				return "<input type=\"${type}\" value=\"{$options['value']}\" name=\"{$options['id']}\" id=\"{$options['id']}\" {$options['checked']}>";

			break;

			case "dropdown":

				$options = wp_parse_args( $options ,
								self::key_defaults( [ 'value' , 'id' , 'options' ] )
							
				);

				//ternary
				$sel_options = 
					is_array($options[ 'options' ]) 
					? 
						implode( '' , array_map( function($k,$v) use ($options) { 
							$checked = ( $v == $options[ 'value' ] ) ? " CHECKED" : "";
							return "<option value=\"{$k}\"{$checked}>{$v}</option>"; 
						} , array_keys($options[ 'options' ]), array_values($options['options']) ) )
					: 
						"";



				return "<select value=\"{$options['value']}\" name=\"{$options['id']}\" id=\"{$options['id']}\">{$sel_options}</select>";

			break;

			case "button":

				$options = wp_parse_args( $options ,
											self::key_defaults( [ 'value' , 'id' , 'label' ] )
				);

				return "<button name=\"{$options['id']}\" id=\"{$options['id']}\" class=\"button-primary\">{$options['label']}</button>";
				
			break;

			case "submit":

				$options = wp_parse_args( $options ,
											self::key_defaults( [ 'value' ] )
				);

				return "<input type=\"${type}\" name=\"update\" class=\"button-primary\" value=\"{$options['value']}\" />";

			break;

		endswitch;

		return '';
	}

	/**
	 * return key => value pairs for wp_parse_args so that no array_key exists
	 *
	 * @param  [type] $keys    [description]
	 * @param  string $default [description]
	 * @return [type]          [description]
	 */
	public static function key_defaults( $keys , $default = "" ) {
		$key_pairs = [];
		foreach ($keys as $key ) {

			$key_pairs[ $key ] = $default ;

		}

		return $key_pairs;
	}

	private static function save_toolbox_defaults() {

		$admin_dashboard_name = 'default';

		// // check our form nonce
		// if ( isset( $_POST[ self::$dash_prefix."-{$admin_dashboard_name}-nonce" ] ) && wp_verify_nonce( $_POST[ self::$dash_prefix."-{$admin_dashboard_name}-nonce" ], $admin_dashboard_name ) ) {

		// 	if ( isset( $_POST['add_postid_to_fieldtypes'] ) ) {
		// 		update_option( 'toolbox_enable_postid'	, 'true' );
		// 	} else {
		// 		update_option( 'toolbox_enable_postid'	, false );
		// 	}

		// 	/**
		// 	 * Option to show both fieldlabel and fieldname in dropdown. If false only show label (descriptive)
		// 	 */
		// 	if ( isset( $_POST['display_label_and_name'] ) ) {
		// 		update_option( 'toolbox_display_fieldname_dropdown'	, 'true' );
		// 	} else {
		// 		update_option( 'toolbox_display_fieldname_dropdown'	, false );
		// 	}

		// 	update_option( 'toolbox_maps_api_key', $_POST['maps_api_key'] );

		// }

	}

}