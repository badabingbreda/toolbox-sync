<?php
namespace ToolboxSync\Helpers\Sync;

use ToolboxSync\Helpers\Sync\Meta;
use ToolboxSync\Helpers\Sync\PostField;

class Local extends \ToolboxSync\Helpers\Sync {


	public function __construct() {

		//add_filter( 'toolboxsync/post_meta/filter' , function( $value ) { return json_encode( $value ); } );
		
	}
    
    /**
     * get
	 * 
	 * return the local posts and relevant data
     *
     * @param  mixed $post_type
     * @return void
     */
    public static function get_all( $post_type = 'fl-theme-layout' ) {

		// get lists of posts
		$args = [
			'post_type' => $post_type,
			'fields' => 'ids',
			'post_status' => ['publish' , 'draft' ],
			'numberposts' => -1,

		];

		$posts = \get_posts( $args );

		$data = [];

		if ( sizeof($posts)>0 ) {

			foreach ($posts as $post_id) {

				$remote_id = (integer)get_post_meta( $post_id, 'tsync_remote_id', true );

				$data[] = [ 
							'slug' => get_post_field( 'post_name' , $post_id, 'raw' ),
							'title' => get_post_field( 'post_title' , $post_id, 'raw' ),
							'extra' => apply_filters( 'toolboxsync/get/extra' , "" , $post_id ),
							'modified' => get_post_field( 'post_modified' , $post_id, 'raw' ),
							'local_id' => $post_id,
							'remote_id' => $remote_id ? $remote_id : false,
						];
			}
		}

        return $data;

    }

	
	/**
	 * get_single
	 *
	 * @param  mixed $post_id
	 * @return void
	 */
	public static function get_single( $post_id ) {

		// get post fields for post id
		$post_fields = PostField::prepare_post_fields( $post_id );

		// get postmeta for post id
		$post_meta = Meta::prepare_meta( $post_id );

		// get taxonomies and terms for post id
		$post_tax = Tax::prepare_tax( $post_id );

		// get remote id it is connected to if available
		$remote_id = get_post_meta( $post_id, 'tsync_remote_id', true );


		return [ 
			'local_id' => $post_id,								// the original local id
			'remote_id' => $remote_id ? $remote_id : false,
			'fields' => $post_fields, 
			'tax' => $post_tax,
			'meta' => $post_meta,
			'requesting_siteurl' => \get_option( 'siteurl' ),
		];
		
	}
}