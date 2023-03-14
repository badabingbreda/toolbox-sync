<?php
namespace ToolboxSync\Helpers;

use ToolboxSync\Helpers\Sync\Remote;

class Ajax {

    public function __construct() {

        add_action( 'wp_ajax_tsync_push_prepare' , __CLASS__ . '::push_prepare' );
    }

    public static function push_prepare() {

        if ( !defined( 'DOING_AJAX' ) )  DEFINE( 'DOING_AJAX' , true );

        if (!Remote::connect()) wp_send_json_error( 'Could not connect', 403 );

        $data = Remote::get();

        // add test if is member at some time

        wp_send_json_success( $data, 200 );

       
    }


}