<?php
namespace ToolboxSync\Integration;

class Toolbox {

    public function __construct() {

		// when a twig templates cpt has been updated, trigger a save of the cpt data to a file (needed by Timber)
		add_action( 'toolboxsync/update/after'	, __CLASS__ . '::save_twig_templates_data' , 10 ,  1 );

    }
	
	/**
	 * save_twig_templates_data
     * 
     * if we've just updated a twig_templates post, make sure to transfer that to a physical file as well
	 *
	 * @param  mixed $post_id
	 * @return void
	 */
	public static function save_twig_templates_data( $post_id ) {

        // if post_id we updated isn't twig_templates do nothing
        if ( get_post_type($post_id) !== 'twig_templates' ) return;
		
		if (class_exists( 'toolboxTwigTemplates' )) {
			// toolbox v1
			\toolboxTwigTemplates::monitor_save_twigs( $post_id );
		} elseif (class_exists( 'Toolbox\Integration\TwigTemplates' )) {
			// toolbox v2
			\Toolbox\Integration\TwigTemplates::monitor_save_twigs( $post_id );
		} else {
			// bail
			return;
		}
		
	}



}