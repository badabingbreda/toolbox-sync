<?php
namespace ToolboxSync\Helpers;

use ToolboxSync\Helpers\Sync\Remote;
use ToolboxSync\Helpers\Sync\Local;
use ToolboxSync\Helpers\Sync\Diff;

class Ajax {

    public function __construct() {

        add_action( 'wp_ajax_tsync_push_prepare' , __CLASS__ . '::push_prepare' );
        add_action( 'wp_ajax_tsync_push_item' , __CLASS__ . '::push_item' );
    }

    public static function push_prepare() {

        if ( !defined( 'DOING_AJAX' ) )  DEFINE( 'DOING_AJAX' , true );

        if (!Remote::connect()) wp_send_json_error( 'Could not connect', 403 );

        $remote = Remote::get_all();
        $local = Local::get_all();

        $suggest = Diff::suggest( $local , $remote );

        // add test if is member at some time

        wp_send_json_success( [ 'suggest'=>$suggest, 'remote'=> $remote , 'local'=>$local], 200 );

       
    }

    public static function push_item() {

        if ( !defined( 'DOING_AJAX' ) )  DEFINE( 'DOING_AJAX' , true );

        //if (!Remote::connect()) wp_send_json_error( 'Could not connect', 403 );

        $local_id = filter_input( INPUT_POST , 'local' , FILTER_SANITIZE_NUMBER_INT );
        
        if ( !$local_id ) wp_send_json_error( false, 403 );

        $remote_id = filter_input( INPUT_POST , 'remote' );

        // get local info
        $local = Local::get_single( $local_id );

        if ( $remote_id == 'new' ) {
            wp_send_json_error( false, 404 );
        } else {
            $success = Remote::update( $local , $remote_id );
            \update_post_meta( $local_id, 'tsync_remote_id', $success );
        }

        wp_send_json_success( $success , 200 );
    }

}