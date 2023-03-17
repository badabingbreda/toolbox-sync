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

		// update certain metadata as raw
		add_action( 'toolboxsync/update/after' 	, __CLASS__ . '::beaverbuilder_rawmeta_update' , 10 , 2 );

		// when a twig templates cpt has been updated, trigger a save of the cpt data to a file (needed by Timber)
		add_action( 'toolboxsync/update/after'	, __CLASS__ . '::save_twig_templates_data' , 10 , 1 );
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

	public static function update( $request ) {

		// data
		$data = $_POST['data'];
		
		$update_data = $data['fields'];
		// set the ID so that we control what post id is going to be updated
		$update_data[ 'ID' ] = $data['remote'];

		$update_data[ 'meta_input' ] = $data[ 'meta' ];
		$update_data[ 'tax_input' ] = $data[ 'tax' ];

		// add the tsync_remote_id key
		$update_data[ 'tsync_remote_id' ] = $data[ 'local_id' ];

		// allow update data to be filtered on the receiving site prior to update
		$updata_data = apply_filters( 'toolboxsync/update/data' , $update_data );

		$post_id = \wp_update_post( $update_data );
		
		// do actions after the update
		// for instance, perform raw update of meta values
		do_action( 'toolboxsync/update/after' , $post_id , $update_data );
		
		// return the post_id
		return rest_ensure_response( $post_id );
		
	}

	public static function insert( $request ) {

		// data
		$data = $_POST['data'];
		
		$update_data = $data['fields'];

		$update_data[ 'meta_input' ] = $data[ 'meta' ];
		$update_data[ 'tax_input' ] = $data[ 'tax' ];

		// add the tsync_remote_id key
		$update_data[ 'tsync_remote_id' ] = $data[ 'local_id' ];

		// allow update data to be filtered on the receiving site prior to update
		$updata_data = apply_filters( 'toolboxsync/update/data' , $update_data );

		$post_id = \wp_insert_post( $update_data );
		
		// do actions after the update
		// for instance, perform raw update of meta values
		do_action( 'toolboxsync/update/after' , $post_id , $update_data );
		
		// return the post_id
		return rest_ensure_response( $post_id );
		
	}

	
	/**
	 * raw_meta_update
	 * 
	 * helper to update raw meta in the database
	 *
	 * @param  mixed $post_id
	 * @param  mixed $meta_key
	 * @param  mixed $meta_value
	 * @return void
	 */
	private static function raw_meta_update( $post_id , $meta_key , $meta_value ) {

		global $wpdb;
		
		$wpdb->update( 
			$wpdb->prefix . 'postmeta', 
			[ 'meta_value' => str_replace( [ '\"' , "\'" ] , [ '"' , "'" ] , $meta_value) ] ,
			[ 'meta_key' => $meta_key , 'post_id' => $post_id ]
		 );

		 return null;
	}
		
	/**
	 * beaverbuilder_rawmeta_update
	 * 
	 * Check the meta_input values for _fl_theme_* and _fl_builder_* keys. If found import as raw meta
	 * Also make sure to clear builder draft because otherwise we will end up with old layout on next edit
	 *
	 * @param  mixed $post_id
	 * @param  mixed $update_data
	 * @return void
	 */
	public static function beaverbuilder_rawmeta_update( $post_id , $update_data ) {

		foreach ($update_data[ 'meta_input' ] as $meta_key => $meta_value ) {
			if ( strpos( $meta_key , '_fl_theme' ) === false && strpos( $meta_key , '_fl_builder' ) === false ) continue;

			self::raw_meta_update( $post_id , $meta_key , $meta_value );
		}

		\delete_post_meta( $post_id , '_fl_builder_draft' );

	}

	public static function save_twig_templates_data( $post_id ) {
		
		if (class_exists( 'toolboxTwigTemplates' )) {
			// toolbox v1
			\toolboxTwigTemplates::monitor_save_twigs( $post_id );
		} elseif (class_exists( 'Toolbox\Integration\TwigTemplates' )) {
			// toolbox v2
			\Toolbox\Integration\TwigTemplates::monitor_save_twigs( $post_id );
		} else {
			// bail
			return;
		}
		
	}


}
