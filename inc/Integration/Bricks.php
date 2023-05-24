<?php
namespace ToolboxSync\Integration;

use ToolboxSync\Helpers\Sync\Meta;

class Bricks {

    public function __construct() {

        		// update certain metadata as raw
		add_action( 'toolboxsync/update/after' 	, __CLASS__ . '::beaverbuilder_rawmeta_update' , 10 , 2 );

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
            // check if these strings are part of the metakey. Check for exact match with === because 0 (position) is also possible
			if ( strpos( $meta_key , '_bricks_' ) === false ) continue;

			Meta::raw_meta_update( $post_id , $meta_key , $meta_value );
		}

	} 

}