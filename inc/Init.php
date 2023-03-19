<?php
namespace ToolboxSync;

use ToolboxSync\Rest;
use ToolboxSync\Helpers\Ajax;
use ToolboxSync\Dashboard\Dashboard;
use ToolboxSync\Helpers\Sync;

use ToolboxSync\Integration\BeaverBuilder;
use ToolboxSync\Integration\Toolbox;

class Init {

    private static $default_cpt = array(
        'fl-theme-layout' => 'Themer Layouts',
        'fl-builder-template' => 'Builder Templates',
        'twig_templates' => 'Twig Templates',
        'page' => 'Pages',
    );

    public function __construct() {

        new Rest();
        new Ajax();
        new Dashboard();
        new Sync();

        new BeaverBuilder();
        new Toolbox();

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