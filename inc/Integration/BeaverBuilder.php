<?php
namespace ToolboxSync\Integration;

use ToolboxSync\Helpers\Sync\Meta;

class BeaverBuilder {

    public function __construct() {

        		// update certain metadata as raw
		add_action( 'toolboxsync/update/after' 	, __CLASS__ . '::beaverbuilder_rawmeta_update' , 10 , 2 );

		add_filter( 'toolboxsync/get/extra' , __CLASS__ . '::get_builder_template_extra' , 10, 2 );
		add_filter( 'toolboxsync/get/extra' , __CLASS__ . '::get_theme_layout_extra' , 10, 2 );

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
			if ( strpos( $meta_key , '_fl_theme' ) === false && strpos( $meta_key , '_fl_builder' ) === false ) continue;

			Meta::raw_meta_update( $post_id , $meta_key , $meta_value );
		}

        // make sure to remove 
		\delete_post_meta( $post_id , '_fl_builder_draft' );

	} 
		
	/**
	 * get_builder_template_extra
	 *
	 * @param  mixed $extra
	 * @param  mixed $post_id
	 * @return void
	 */
	public static function get_builder_template_extra( $extra , $post_id ) {
		if( get_post_type($post_id) !== 'fl-builder-template' ) return $extra;

		$type = get_the_terms( $post_id , 'fl-builder-template-type' );
		if ( sizeof($type) !== 0 ) $type = $type[0]->slug;
		$extra .= "[{$type}]";

		return $extra;
	}

	/**
	 * get_builder_template_extra
	 *
	 * @param  mixed $extra
	 * @param  mixed $post_id
	 * @return void
	 */
	public static function get_theme_layout_extra( $extra , $post_id ) {
		if( get_post_type($post_id) !== 'fl-theme-layout' ) return $extra;

		$extra .= "[".get_post_meta( $post_id , '_fl_theme_layout_type' , true )."]";

		return $extra;
	}

}