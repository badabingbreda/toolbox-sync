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
			self::$namespace, '/posts', array(
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'permission_callback' => 'is_user_logged_in', //'__return_true',
					'callback' => __CLASS__ . '::posts',
					'args' => array(),
				),
			)
		);

		register_rest_route(
			self::$namespace, '/connect', array(
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'permission_callback' => 'is_user_logged_in', //'__return_true',
					'callback' => '__return_true',
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


		\register_rest_route(
			self::$namespace, '/update', array(
				array(
					'methods'  => \WP_REST_Server::EDITABLE,
					'permission_callback' => '__return_true',
					'callback' => __CLASS__ . '::update',
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

		$posts = Local::get_all( );

		return rest_ensure_response( $posts );

	}

	static public function post( $request ) {

		return rest_ensure_response( [ 
			'id' => $request['id'],
			'data' => Local::get_single( $request[ 'id' ] ),
			 ] );
		
	}

	static public function update( $request ) {

		// data
		$data = $_POST['data'];
		$remote_id = $_POST['remote_id'];
		
		$update_data = $data['fields'];
		$update_data[ 'ID' ] = $remote_id;

		$data[ 'meta' ][ 'tsync_remote_id' ] = $data[ 'local_id' ];


		$update_data[ 'meta_input' ] = $data[ 'meta' ];

		$post_id = \wp_update_post( $update_data );

		return rest_ensure_response( $post_id );

	}


}
