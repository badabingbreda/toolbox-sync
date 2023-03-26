<?php
namespace ToolboxSync\Helpers;

use ToolboxSync\Helpers\Sync\Remote;
use ToolboxSync\Helpers\Sync\Local;
use ToolboxSync\Helpers\Sync\Diff;

class Ajax {

    public function __construct() {

        add_action( 'wp_ajax_tsync_push_prepare' , __CLASS__ . '::push_prepare' );
        add_action( 'wp_ajax_tsync_pull_prepare' , __CLASS__ . '::pull_prepare' );
        add_action( 'wp_ajax_tsync_push_item' , __CLASS__ . '::push_item' );
    }
    
    /**
     * push_prepare
     *
     * @return void
     */
    public static function push_prepare() {

        if ( !defined( 'DOING_AJAX' ) )  DEFINE( 'DOING_AJAX' , true );
        if ( !current_user_can('administrator')) return false;

        if (!Remote::connect()) wp_send_json_error( 'Could not connect', 403 );

        $post_type = filter_input( INPUT_GET , 'posttype' );
        if ( !$post_type ) $post_type = false;

        // connect to remote and get the posts
        $remote = Remote::get_all( $post_type );
        // get the local posts
        $local = Local::get_all( $post_type );

        // compare both lists, but make local leading
        $suggest = Diff::suggest( $local , $remote , 'push' );

        // add test if is member at some time

        wp_send_json_success( [ 'suggest'=>$suggest, 'remote'=> $remote , 'local'=>$local], 200 );
       
    }
    
    
    /**
     * pull_prepare
     *
     * @return void
     */
    public static function pull_prepare() {

        if ( !defined( 'DOING_AJAX' ) )  DEFINE( 'DOING_AJAX' , true );
        if ( !current_user_can('administrator')) return false;

        if (!Remote::connect()) wp_send_json_error( 'Could not connect', 403 );

        $post_type = filter_input( INPUT_GET , 'posttype' );
        if ( !$post_type ) $post_type = false;

        // connect to remote and get the posts
        $remote = Remote::get_all( $post_type );
        // get the local posts
        $local = Local::get_all( $post_type );

        // compare both lists, but make local leading
        $suggest = Diff::suggest( $local , $remote , 'pull' );

        // add test if is member at some time

        wp_send_json_success( [ 'suggest'=>$suggest, 'remote'=> $remote , 'local'=>$local], 200 );
       
    }    
    /**
     * push_item
     *
     * @return void
     */
    public static function push_item() {

        if ( !defined( 'DOING_AJAX' ) )  DEFINE( 'DOING_AJAX' , true );
        if ( !current_user_can('administrator')) return false;

        $local_id = filter_input( INPUT_POST , 'local' , FILTER_SANITIZE_NUMBER_INT );
        if ( !$local_id ) wp_send_json_error( false, 403 );
        $remote_id = filter_input( INPUT_POST , 'remote' );
        // get local info
        $local = Local::get_single( $local_id );
        //wp_send_json_success( $local, 200 );
        if ( $remote_id == 'new' ) {
            $success = Remote::update( $local , 'new' );
            // wp_send_json_error( false, 404 );
        } else {
            $success = Remote::update( $local , $remote_id );
        }
        if ($success ) \update_post_meta( $local_id, 'tsync_remote_id', (integer)$success );
        wp_send_json_success( $success , 200 );
        
    }





}