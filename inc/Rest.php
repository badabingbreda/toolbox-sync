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

	
		add_action( 'rest_api_init' 			, __CLASS__ . '::register_routes' );

		//add_filter( 'toolboxsync/update/data' , __CLASS__ . '::update_to_local_url' , 10, 2 );

	}

	/**
	 * Register routes.
	 *
	 * @since  0.1
	 * @return void
	 */
	public static function register_routes() {

		register_rest_route(
			self::$namespace, '/posts(?:\/(?P<postype>))?', array(
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'permission_callback' => 'is_user_logged_in', //'__return_true',
					'callback' => __CLASS__ . '::posts',
					'args' => [ 
						'posttype' => [ 
							'type' => 'string',
							'required' => true,
						],
					]
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
					'permission_callback' => 'is_user_logged_in',//'is_user_logged_in', //'__return_true',
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
					'permission_callback' => 'is_user_logged_in',
					'callback' => __CLASS__ . '::update',
				),
			)
		);		

		\register_rest_route(
			self::$namespace, '/insert', array(
				array(
					'methods'  => \WP_REST_Server::EDITABLE,
					'permission_callback' => 'is_user_logged_in',
					'callback' => __CLASS__ . '::insert',
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
	public static function posts( $request ) {

		$posts = Local::get_all( $request[ 'posttype' ] );

		return rest_ensure_response( $posts );

	}

	public static function post( $request ) {

		return rest_ensure_response( [ 
			'id' => $request['id'],
			'data' => Local::get_single( $request[ 'id' ] ),
			 ] );
		
	}
	
	/**
	 * update
	 *
	 * @param  mixed $request
	 * @return void
	 */
	public static function update( $request ) {

		// data
		$data = $_POST['data'];

		$update_data = $data['fields'];
		// set the ID so that we control what post id is going to be updated
		$update_data[ 'ID' ] = $data['remote'];

		if ( isset( $data[ 'meta' ] ) ) $update_data[ 'meta_input' ] = $data[ 'meta' ];
		if ( isset( $data[ 'tax' ] ) ) $update_data[ 'tax_input' ] = $data[ 'tax' ];

		// add the tsync_remote_id key
		$update_data[ 'tsync_remote_id' ] = $data[ 'local_id' ];

		// allow update data to be filtered on the receiving site prior to update
		$update_data = apply_filters( 'toolboxsync/update/data' , $update_data , $data );

		$post_id = \wp_update_post( $update_data );
		
		// do actions after the update
		// for instance, perform raw update of meta values
		do_action( 'toolboxsync/update/after' , $post_id , $update_data , $data );
		
		// return the post_id
		return rest_ensure_response( $post_id );
		
	}
	
	/**
	 * insert
	 *
	 * @param  mixed $request
	 * @return void
	 */
	public static function insert( $request ) {

		// data
		$data = $_POST['data'];
		
		$update_data = $data['fields'];

		if ( isset( $data[ 'meta' ] ) ) $update_data[ 'meta_input' ] = $data[ 'meta' ];
		if ( isset( $data[ 'tax' ] ) ) $update_data[ 'tax_input' ] = $data[ 'tax' ];

		// add the tsync_remote_id key
		$update_data[ 'tsync_remote_id' ] = $data[ 'local_id' ];

		// allow update data to be filtered on the receiving site prior to update
		$updata_data = apply_filters( 'toolboxsync/update/data' , $update_data , $data );

		$post_id = \wp_insert_post( $update_data );
		
		// do actions after the update
		// for instance, perform raw update of meta values
		do_action( 'toolboxsync/update/after' , $post_id , $update_data , $data );
		
		// return the post_id
		return rest_ensure_response( $post_id );
		
	}
	
	/**
	 * update_to_local_url
	 *
	 * @param  mixed $update_data
	 * @param  mixed $data
	 * @return void
	 */
	public static function update_to_local_url( $update_data , $data ) {

		foreach( $update_data[ 'meta_input' ] as $key => $value ) {
			if ( !is_array( $value )) {

				$update_data[ 'meta_input' ][$key] = str_replace( 
					[ stripslashes($data[ 'requesting_siteurl' ]) ] , 
					[ get_option( 'siteurl' , true ) ] , 
					$update_data[ 'meta_input' ][$key] );
			}
		}

		return $update_data;
	}


}
