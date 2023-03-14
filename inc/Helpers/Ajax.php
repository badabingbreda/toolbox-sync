<?php
namespace ToolboxSync\Helpers;

use Curl\Curl;

class Ajax {

    public function __construct() {

        add_action( 'wp_ajax_toolboxsync_get_posts' , __CLASS__ . '::get_posts' );
    }

    public static function get_posts() {

        if ( !defined( 'DOING_AJAX' ) )  DEFINE( 'DOING_AJAX' , true );


        $curl = new Curl();
        $curl->setBasicAuthentication( 'didou', 'MslX+irsi+D458+kwaf+xcvq+WD8T');
        $curl->setUserAgent('');
        $curl->setHeader('X-Requested-With', 'XMLHttpRequest');      
        $curl->get(get_option( 'toolboxsync_remotesite' ) . '/wp-json/toolboxsync/v1/posts' );
        
        if ($curl->error) {
            $data =  $curl->response;
        } else {
            $data = $curl->response;
        }

        // add test if is member at some time

        wp_send_json_success( $data, 200 );

       
    }


}