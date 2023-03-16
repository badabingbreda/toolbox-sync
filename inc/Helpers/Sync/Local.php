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
			'post_status' => 'any',
			'numberposts' => -1,

		];

		$posts = \get_posts( $args );

		$data = [];

		if ( sizeof($posts)>0 ) {

			foreach ($posts as $post_id) {

				$remote_id = get_post_meta( $post_id, 'tsync_remote_id', true );

				$data[] = [ 
							'slug' => get_post_field( 'post_name' , $post_id, 'raw' ),
							'title' => get_post_field( 'post_title' , $post_id, 'raw' ),
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

		$post_fields = PostField::prepare_post_fields( $post_id );
		$post_meta = Meta::prepare_meta( $post_id );
		$remote_id = get_post_meta( $post_id, 'tsync_remote_id', true );

		return [ 
			'local_id' => $post_id,								// the original local id
			'remote_id' => $remote_id ? $remote_id : false,
			'fields' => $post_fields, 
			'meta' => $post_meta,
		];
		
	}
}