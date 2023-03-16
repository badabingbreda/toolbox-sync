<?php
namespace ToolboxSync\Helpers\Sync;

class Tax extends \ToolboxSync\Helpers\Sync {

	private static $ignore_taxonomies = [ 
	];
    
    public function __construct() { }
    
    /**
     * prepare_post_fields
     *
     * @param  mixed $post_id
     * @return void
     */
    public static function prepare_tax( $post_id ) {

		// get the post_type for this id
		$post_type = get_post_field( 'post_type' , $post_id , 'raw' );

        // get list of taxonomies here
        $taxonomies = \get_post_taxonomies( $post_id );

        $taxonomies = apply_filters( "toolboxsync/taxonomy/export" , $taxonomies , $post_type , $post_id );
        $taxonomies = apply_filters( "toolboxsync/taxonomy/export/{$post_type}" , $taxonomies, $post_type , $post_id );

        $prepared_taxonomies = array();
    
        // Transfer all taxonomy and term slugs
    
        foreach ( $taxonomies as $taxonomy ) {

                    // get terms for this post id taxonomy as objects
                    $terms_o = get_the_terms( $post_id, $taxonomy );

                    if (!$terms_o) $terms_o = [];

                    /**
                     * Filter whether to sync taxonomy.
                     *
                     * @hook dt_sync_meta
                     *
                     * @param {bool}   $sync_meta  Whether to sync meta. Default `true`.
                     * @param {string} $meta_key   The meta key.
                     * @param {mixed}  $meta_value The meta value.
                     * @param {int}    $post_id    The post ID.
                     *
                     * @return {bool} Whether to sync meta.
                     */
                    if ( false === apply_filters( 'toolboxsync/taxonomy/sync', true, $taxonomy, $post_field_value, $post_id ) ) {
                        continue;
                    }

                    // return the ids only
                    $terms = array_map( function($v) { return $v->slug; } , $terms_o );

                    $prepared_taxonomies[ $taxonomy ] = $terms;

        }
        return $prepared_taxonomies;
    } 
        
}