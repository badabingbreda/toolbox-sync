<?php
namespace ToolboxSync\Helpers\Sync;

use \Curl\Curl;

class Remote extends \ToolboxSync\Helpers\Sync {

    private static $user_login = false;
    private static $password = false;
    private static $remotesite = false;

    public function __construct() {

        self::$user_login = get_option( 'tsync_user_login' );
        self::$password = get_option( 'tsync_password' );
        self::$remotesite = get_option( 'tsync_remotesite' );

    }
    
    /**
     * test_connection
     *
     * @return void
     */
    public static function connect() {

        $curl = new Curl();
        $curl->setBasicAuthentication( self::$user_login, self::$password );
        $curl->setUserAgent('');
        $curl->setHeader('X-Requested-With', 'XMLHttpRequest');      
        $curl->get(self::$remotesite . '/wp-json/toolboxsync/v1/connect' );
        

        if ($curl->error) {
            return false;
        } else {
            return true;
        }


    }
    
    /**
     * get
     *
     * @return void
     */
    public static function get_all() {

        $curl = new Curl();
        $curl->setBasicAuthentication( self::$user_login, self::$password );
        $curl->setUserAgent('');
        $curl->setHeader('X-Requested-With', 'XMLHttpRequest');      
        $curl->get(self::$remotesite . '/wp-json/toolboxsync/v1/posts' );
        

        if ($curl->error) {
            $data =  $curl->response;
        } else {
            $data = $curl->response;
            $data = json_decode( $data , true );
        }

        return $data;

    }

    public static function update( $data , $remote_id = false ) {

        $curl = new Curl();
        $curl->setBasicAuthentication( self::$user_login, self::$password );
        $curl->setUserAgent('');
        $curl->setHeader('X-Requested-With', 'XMLHttpRequest'); 
        
        $data = array_merge( $data , array( 'remote' => $remote_id ) );
        $curl->post(self::$remotesite . '/wp-json/toolboxsync/v1/update' , array( 'data' => $data ) );

        if ($curl->error) {
            return false;
        } else {
            return $curl->response;
        }


    }


}