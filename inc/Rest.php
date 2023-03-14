<?php
namespace ToolboxSync;

//use ToolboxHelper\ScanDir;
//use ToolboxHelper\Helpers\AcfHelper;
//use ToolboxHelper\Helpers\MBHelper;

use ToolboxSync\Helpers\Sync\Local;


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
					'args' => array(),
				),
			)
		);

		\register_rest_route(
			self::$namespace, '/post(?:\/(?P<id>))?', array(
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'permission_callback' => '__return_true',//'is_user_logged_in', //'__return_true',
					'callback' => __CLASS__ . '::post',
					'args' => [ 
						'id' => [ 
							'type' => 'numeric',
							'required' => true,
						],
					]
				),
			)
		);		


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

		$data = Local::get( 'fl-builder-template' );

		return rest_ensure_response( [ 'posts' => $data ] );

	}

	static public function post( $request ) {



		return rest_ensure_response( [ 
			'id' => $request['id'],
			
			'data' => Local::get_single( $request[ 'id' ] ),
			 ] );
		

		//$data = Local::post( );

	}


}
