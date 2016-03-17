<?php
/**
 * The class that creates a custom post type for routines.
 *
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
*/
final class TaskScheduler_PostType_Routine extends TaskScheduler_AdminPageFramework_PostType {
    
    public function start() {

        $this->setPostTypeArgs(
            array(            // post type argument - for the array structure, refer to http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
                'labels'                => array(
                    'name'                                 => __( 'Routines', 'task-scheduler' ),
                    'plugin_listing_table_title_cell_link' => '',    // framework specific key. [3.0.6+] - passing an empty will disable the automatic link insertion to the plugin listing table.
                ),
                'public'                => true,
                'show_ui'               => false,                
                'show_in_menu'          => false, // Whether to show post type in the admin menu. 'show_ui' must be true for this to work. bool (defaults to 'show_ui')
                'menu_position'         => 110,
                'can_export'            => true,
                'supports'              => array( 'title' ),
                'taxonomies'            => array( TaskScheduler_Registry::$aTaxonomies[ 'system' ], ),
                'menu_icon'             => is_admin() ? TaskScheduler_Registry::getPluginURL( '/asset/image/menu_icon_16x16.png' ) : '',
                'has_archive'           => false,
                'hierarchical'          => true,
                'show_admin_column'     => true,
                'exclude_from_search'   => true,    
            )        
        );
                
    }    
    
}