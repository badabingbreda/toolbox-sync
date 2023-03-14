<?php
namespace ToolboxSync\Helpers\Sync;

class PostField extends \ToolboxSync\Helpers\Sync {

	private static $post_fields = [ 
		//'post_author', 
		'post_date', 
		'post_date_gmt', 
		'post_content', 
		'post_title', 
		'post_excerpt',
		'post_status',
		//'ping_status',
		'post_password',
		'post_name',
		//'to_ping',
		//'pinged',
		'post_modified',
		'post_modified_gmt',
		'post_content_filtered',
		'post_parent',
		//'guid',
		'menu_order',
		// 'post_type',
		//'post_mime_type',
		//'comment_count',
		//'filter',
	];   

    public function __construct() { }
    
    /**
     * prepare_post_fields
     *
     * @param  mixed $post_id
     * @return void
     */
    public static function prepare_post_fields( $post_id ) {

		// get the post_type for this id
		$post_type = get_post_field( 'post_type' , $post_id , 'raw' );

        $post_fields = apply_filters( "toolboxsync/post_fields/export" , self::$post_fields , $post_type , $post_id );
        $post_fields = apply_filters( "toolboxsync/post_fields/export/{$post_type}" , $post_fields, $post_type , $post_id );

        $prepared_post_fields = array();
    
        // Transfer all meta
    
        foreach ( $post_fields as $field_key ) {
                    $field_value = \get_post_field( $field_key , $post_id , 'raw' );
                    $post_field_value = \maybe_unserialize( $field_value );
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
                    if ( false === apply_filters( 'toolboxsync/post_field/sync', true, $field_key, $post_field_value, $post_id ) ) {
                        continue;
                    }
                    $prepared_post_fields[ $field_key ][] = $post_field_value;

        }
        return $prepared_post_fields;
    } 
        
}