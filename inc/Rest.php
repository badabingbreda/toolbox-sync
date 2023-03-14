<?php
namespace ToolboxSync;

//use ToolboxHelper\ScanDir;
//use ToolboxHelper\Helpers\AcfHelper;
//use ToolboxHelper\Helpers\MBHelper;

/**
 * REST API methods to retreive data for WordPress rules.
 *
 * @since 0.1
 */
final class Rest {

	/**
	 * REST API namespace
	 *
	 * @since 0.1
	 * @var string $namespace
	 */
	static protected $namespace = 'toolboxsync/v1';

	public function __construct() {

		// new AcfHelper();
		
		// new MBHelper();
		
		add_action( 'rest_api_init' 			, __CLASS__ . '::register_routes' );

	}


	/**
	 * Register routes.
	 *
	 * @since  0.1
	 * @return void
	 */
	static public function register_routes() {

		register_rest_route(
			self::$namespace, '/posts/', array(
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'permission_callback' => 'is_user_logged_in', //'__return_true',
					'callback' => __CLASS__ . '::posts',
				),
			)
		);

	}
	
	/**
	 * check_application_password
	 *
	 * @return void
	 */
	public static function check_application_password() {

		$user_credentials = base64_decode($_SERVER['HTTP_AUTHORIZATION']);
		$username_password = explode(':', $user_credentials);
		
		// Extract the username and password from the credentials
		$username = $username_password[0];
		$password = $username_password[1];
		
		$user = \wp_authenticate_application_password( null, $username, $password);

		if (is_wp_error($user)) {
			// invalid combination
			return false;
		} 

		return true;
	}

	/**
	 * Returns an array of posts with each item
	 * containing a label and value.
	 *
	 * @since  0.1
	 * @param object $request
	 * @return array
	 */
	static public function posts( $request ) {


		// get lists of posts
		$args = [
			'post_type' => 'fl-theme-layout',
			'fields' => 'ids',
			'post_status' => 'any',

		];

		$posts = \get_posts( $args );

		$data = [];

		if ( sizeof($posts)>0 ) {
			foreach ($posts as $post_id) {

				$slug = get_post_field( 'post_name' , $post_id );
				$remote_counterpart = get_post_meta( $post_id, 'toolboxsync_remote_post_id', true );

				$data[] = [ 
							'slug' => $slug,
							'local_ID' => $post_id,
							'remote_ID' => $remote_counterpart ? $remote_counterpart : false,
						];

			}
		}

		return rest_ensure_response( $data );

		return rest_ensure_response( [ 'success' ] );
		//$twig_files = ( ScanDir::scan( ScanDir::get_view_directories() , 'twig' , true ) );

		$files = [];

		foreach($twig_files as $file) {

			if ( $file['basename'] == 'fallback.twig' ) continue;

			$template_type = filter_input( INPUT_GET , 'templatetype' , FILTER_SANITIZE_STRING );

			// if a certain template type has been provided
			// skip files that have no TemplateType set
			// skip files that have no matching templatetype
			if ( $template_type ) {

				if ( !$file[ 'data' ] ) continue;
				if ( !in_array( $template_type , $file[ 'data' ][ 'template_type' ] ) ) continue;

			} 

			if ( $file[ 'data' ] ) {
				if ( $file[ 'data' ][ 'hidden' ] ) continue;
			}
			
			$files[] = [ 
					'value' => $file['rel_path'] . $file['basename'],
					'label' => $file['rel_path'] . $file['basename'],
					'data' => $file[ 'data' ], 
				];
		}


		return rest_ensure_response( $files );
	}

}
