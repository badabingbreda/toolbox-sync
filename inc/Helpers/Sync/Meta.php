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
        '_fl_builder_draft',
	];    

    public function __construct() { 

        // add_filter( 'toolboxsync/post_meta/filter/_fl_builder_data' , __CLASS__ . '::rawmeta' , 10 , 3 );
        // add_filter( 'toolboxsync/post_meta/filter/_fl_builder_draft' , __CLASS__ . '::rawmeta' , 10 , 3 );

        add_filter( 'toolboxsync/post_meta/filter' , __CLASS__ . '::beaverbuilder_rawmeta_filters' , 10 , 3 );
        add_filter( 'toolboxsync/post_meta/sync' , __CLASS__ . '::beaverbuilder_no_history_state' , 10 , 2 );
    }

    public static function rawmeta( $meta_value , $post_id , $meta_key ) {
        global $wpdb;
        $meta_value = $wpdb->get_var( $wpdb->prepare("SELECT meta_value from {$wpdb->prefix}postmeta WHERE meta_key = %s AND post_id = %d" , $meta_key , $post_id ) );
        //$meta_value = \get_metadata_raw( 'post', $post_id, $meta_key, true );
        return $meta_value;
    }
    
    /**
     * beaverbuilder_no_history_state
     * 
     * do not export history states
     *
     * @param  mixed $sync
     * @param  mixed $meta_key
     * @return void
     */
    public static function beaverbuilder_no_history_state( $sync , $meta_key ) {
        if ( strpos( $meta_key , '_fl_builder_history_state' ) !== false ) return false;
        return true;
    }
    
    /**
     * beaverbuilder_rawmeta_filters
     * 
     * export the _fl_theme_* and _fl_builder_* meta-keys as raw meta
     *
     * @param  mixed $meta_value
     * @param  mixed $post_id
     * @param  mixed $meta_key
     * @return void
     */
    public static function beaverbuilder_rawmeta_filters( $meta_value , $post_id , $meta_key ) {
        if ( strpos( $meta_key , '_fl_theme' ) === false && !strpos( $meta_key , '_fl_builder' ) === false ) return $meta_value;

        return self::raw_meta_value( $post_id , $meta_key );
    }
        
    /**
     * get_meta_value
     * 
     * return raw meta value
     *
     * @param  mixed $post_id
     * @param  mixed $meta_key
     * @return void
     */
    private static function raw_meta_value( $post_id , $meta_key ) {
        global $wpdb;
        $meta_value = $wpdb->get_var( $wpdb->prepare("SELECT meta_value from {$wpdb->prefix}postmeta WHERE meta_key = %s AND post_id = %d" , $meta_key , $post_id ) );
        return $meta_value;
        
    }
    
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
                    if ( false == apply_filters( 'toolboxsync/post_meta/sync', true, $meta_key, $meta_value, $post_id ) ) {
                        continue;
                    }

                    $meta_value = apply_filters("toolboxsync/post_meta/filter" , $meta_value , $post_id , $meta_key );
                    $meta_value = apply_filters("toolboxsync/post_meta/filter/{$meta_key}" , $meta_value , $post_id , $meta_key  );

                    $prepared_meta[ $meta_key ] = $meta_value;
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