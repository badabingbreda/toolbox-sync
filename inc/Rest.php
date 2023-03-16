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

		add_action( 'toolboxsync/update/after' 	, __CLASS__ . '::rawmeta_update' , 10 , 2 );

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
		$remote = $data['remote'];
		
		$update_data = $data['fields'];
		$update_data[ 'ID' ] = $remote;

		$data[ 'meta' ][ 'tsync_remote_id' ] = $data[ 'local_id' ];

		$update_data[ 'meta_input' ] = $data[ 'meta' ];

		$updata_data = apply_filters( 'toolboxsync/update/data' , $update_data );

		$post_id = \wp_update_post( $update_data );

		do_action( 'toolboxsync/update/after' , $post_id , $update_data );

		
		return rest_ensure_response( $post_id );
		
	}
	
	static public function rawmeta_update( $post_id , $update_data ) {
		
		global $wpdb;
	
		$wpdb->update( $wpdb->prefix . 'postmeta' , 
		[ 'meta_value' => str_replace( [ '\"' ] , [ '"' ] , $update_data[ 'meta_input' ][ '_fl_builder_data' ]) ] ,
		[ 'meta_key' => '_fl_builder_data' , 'post_id' => $post_id ]
		 );
	
		 $wpdb->update( $wpdb->prefix . 'postmeta' , 
		 [ 'meta_value' => str_replace( [ '\"' ] , [ '"' ] , $update_data[ 'meta_input' ][ '_fl_builder_draft' ]) ] ,
		 [ 'meta_key' => '_fl_builder_draft' , 'post_id' => $post_id ]
		  );


	}


}
