<?php
namespace ToolboxSync;

use ToolboxSync\Rest;
use ToolboxSync\Helpers\Ajax;
use ToolboxSync\Dashboard\Dashboard;
use ToolboxSync\Helpers\Sync;

class Init {

    private static $default_cpt = array(
        [ 'value' => 'fl-theme-layout' , 'label' => 'Themer Layouts' ],
        [ 'value' => 'fl-builder-template' , 'label' => 'Builder Templates' ],
        [ 'value' => 'page' , 'label' => 'Pages' ],
    );

    public function __construct() {

        new Rest();
        new Ajax();
        new Dashboard();
        new Sync();

        add_filter( 'toolboxsync/push_post_types' , __CLASS__ . '::add_default_cpts' , 10 , 1 );

    }
    
    /**
     * add_default_cpts
     *
     * @param  mixed $post_types
     * @return void
     */
    public static function add_default_cpts( $post_types ) {

        return array_merge( $post_types, self::$default_cpt );
    }
}