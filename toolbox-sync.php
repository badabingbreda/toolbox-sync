<?php
/**
 * Toolbox Sync
 *
 * @package     ToolboxSync
 * @author      Badabingbreda
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Toolbox Sync
 * Plugin URI:  https://www.badabing.nl
 * Description: Sync between 2 WP sites
 * Version:     1.0.2
 * Author:      Badabingbreda
 * Author URI:  https://www.badabing.nl
 * Text Domain: toolbox-sync
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

use ToolboxSync\Autoloader;
use ToolboxSync\Init;

if ( defined( 'ABSPATH' ) && ! defined( 'TOOLBOXSYNC_VERION' ) ) {
 register_activation_hook( __FILE__, 'TOOLBOXSYNC_check_php_version' );

 /**
  * Display notice for old PHP version.
  */
 function TOOLBOXSYNC_check_php_version() {
     if ( version_compare( phpversion(), '7.4', '<' ) ) {
        die( esc_html__( 'Toolbox Sync requires PHP version 7.4+. Please contact your host to upgrade.', 'toolbox-sync' ) );
    }
 }

  define( 'TOOLBOXSYNC_VERSION'   , '1.0.2' );
  define( 'TOOLBOXSYNC_DIR'     , plugin_dir_path( __FILE__ ) );
  define( 'TOOLBOXSYNC_FILE'    , __FILE__ );
  define( 'TOOLBOXSYNC_URL'     , plugins_url( '/', __FILE__ ) );

  define( 'CHECK_TOOLBOXSYNC_PLUGIN_FILE', __FILE__ );

}

if ( ! class_exists( 'ToolboxSync\Init' ) ) {

    require_once 'vendor/autoload.php';
    /**
     * The file where the Autoloader class is defined.
    */
    
    require_once 'inc/Autoloader.php';
    spl_autoload_register( array( new Autoloader(), 'autoload' ) );

    $toolboxsync = new Init();
    // looking for the init hooks? Find them in the Check_Plugin_Dependencies.php->run() callback

}
