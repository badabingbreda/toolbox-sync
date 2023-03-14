<?php
namespace ToolboxSync\Helpers\Sync;

class Meta extends \ToolboxSync\Helpers\Sync {

	private static $exclude_meta_keys = [
		'tsync_remote_id',
		'tsync_original_post_url',
		'tsync_original_blog_id',
		'_edit_lock',
		'_edit_last',
		'_wp_old_slug',
		'_wp_old_date',
	];    

    public function __construct() { }
    
    /**
     * prepare_meta
     *
     * @param  mixed $post_id
     * @return void
     */
    public static function prepare_meta( $post_id ) {
        $meta          = get_post_meta( $post_id );
        $prepared_meta = array();
        $excluded_meta = self::excluded_meta( $post_id );
    
        // Transfer all meta
    
        foreach ( $meta as $meta_key => $meta_array ) {
            foreach ( $meta_array as $meta_value ) {
                if ( ! in_array( $meta_key, $excluded_meta, true ) ) {
                    $meta_value = \maybe_unserialize( $meta_value );
                    /**
                     * Filter whether to sync meta.
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
                    if ( false === apply_filters( 'toolboxsync/post_meta/sync', true, $meta_key, $meta_value, $post_id ) ) {
                        continue;
                    }
                    $prepared_meta[ $meta_key ][] = $meta_value;
                }
            }
        }
        return $prepared_meta;
    } 
        
    /**
     * exclude_meta
     *
     * @return void
     */
    private static function excluded_meta( $post_id ) {

        // get the post_type for this id
		$post_type = get_post_field( 'post_type' , $post_id , 'raw' );

		// generic filter
        $exclude_meta_keys = apply_filters( "toolboxsync/post_meta/export/exclude" , self::$exclude_meta_keys );
        // filter specifically for this post_type
		$exclude_meta_keys = apply_filters( "toolboxsync/post_meta/export/exclude/{$post_type}" , $exclude_meta_keys );
        return $exclude_meta_keys;
    }

}