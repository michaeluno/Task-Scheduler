<?php
/**
 * The class that checks necessary requirements.
 *
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014-2020, Michael Uno
 * @author      Michael Uno
 * @authorurl   http://michaeluno.jp
 * @since       1.0.0
*/
abstract class TaskScheduler_PostType_Task_Base extends TaskScheduler_AdminPageFramework_PostType {

    /**
     * User constructor.
     */
    public function start() {

        // Create the task post type
        $this->setPostTypeArgs(
            array(            // argument - for the array structure, refer to http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
                'labels'                => $this->_getPostTypeLabels(),
                'public'                => true,
                'show_ui'               => true,                
                'show_in_menu'          => false, // Whether to show post type in the admin menu. 'show_ui' must be true for this to work. bool (defaults to 'show_ui')
                'menu_position'         => 110,
                'can_export'            => true,
                'supports'              => array( 'title', ),
                'taxonomies'            => array( TaskScheduler_Registry::$aTaxonomies[ 'system' ], ),
                'menu_icon'             => is_admin() ? TaskScheduler_Registry::getPluginURL( '/asset/image/menu_icon_16x16.png' ) : '',
                'has_archive'           => false,
                'hierarchical'          => true,
                'show_admin_column'     => true,
                'screen_icon'           => is_admin() ? TaskScheduler_Registry::getPluginURL( "/asset/image/screen_icon_32x32.png" ) : '',
                'exclude_from_search'   => true,        
            )        
        );
        
        // Create the internal label taxonomy 
        $this->addTaxonomy( 
            TaskScheduler_Registry::$aTaxonomies[ 'system' ], // taxonomy slug
            array(            // argument - for the argument array keys, refer to : http://codex.wordpress.org/Function_Reference/register_taxonomy#Arguments
                'labels'                => array(
                    'name'            => __( 'Task Scheduler System Label', 'task-scheduler' ),
                    'add_new_item'    => __( 'Add New Label', 'task-scheduler' ),
                    'new_item_name'   => __( 'New Label', 'task-scheduler' ),
                ),
                'show_ui'               => false,
                'show_tagcloud'         => false,
                'hierarchical'          => false,
                'show_admin_column'     => true,
                'show_in_nav_menus'     => false,
                'show_table_filter'     => true,    // framework specific key
                'show_in_sidebar_menus' => false,    // framework specific key
            ),
            array( TaskScheduler_Registry::$aPostTypes[ 'thread' ] )    // additional object types
        );        
            
        add_filter( 'the_content', array( $this, '_replyToShowTaskDetails' ) );
                
    }
        private function _getPostTypeLabels() {
            
            if ( ! $this->oProp->bIsAdmin ) {
                return array();
            }
            
            return array(
                'name'                  => __( 'Task Scheduler', 'task-scheduler' ),
                'all_items'             => __( 'Tasks', 'task-scheduler' ),    // sub menu label
                'singular_name'         => __( 'Task', 'task-scheduler' ),
                'menu_name'             => __( 'Task Scheduler', 'task-scheduler' ),    // this changes the root menu name 
                'add_new'               => __( 'Add New Task', 'task-scheduler' ),
                'add_new_item'          => __( 'Add New Task', 'task-scheduler' ),
                'edit'                  => __( 'Edit', 'task-scheduler' ),
                'edit_item'             => __( 'Edit Task', 'task-scheduler' ),
                'new_item'              => __( 'New Task', 'task-scheduler' ),
                'view'                  => __( 'View', 'task-scheduler' ),
                'view_item'             => __( 'View Tasks', 'task-scheduler' ),
                'search_items'          => __( 'Search Tasks', 'task-scheduler' ),
                'not_found'             => __( 'No Task found for Task Scheduler', 'task-scheduler' ),
                'not_found_in_trash'    => __( 'No Task Found for Task Scheduler in Trash', 'task-scheduler' ),
                'parent'                => __( 'Parent Task', 'task-scheduler' ),
                'plugin_action_link'    => '',    // framework specific key. [APF 3.7.3+] - passing an empty will disable the automatic link insertion to the plugin listing table.
            );
            
        }
        public function _replyToShowTaskDetails( $sContent ) {
            
            global $post;
            if ( $post->post_type != $this->oProp->sPostType ) { 
                return $sContent; 
            }
            if( ! is_singular() || ! is_main_query() ) {
                return $sContent; 
            }

            $_aPageListArgs = array(
                'authors'       => '',
                // 'parent'         =>    '',
                'child_of'      => $post->ID,
                'date_format'   => 'Y/m/d G:i:s', // get_option('date_format'),
                'depth'         => 0,
                'echo'          => false,
                'exclude'       => '',
                'include'       => '',
                'link_after'    => '',
                'link_before'   => '',
                'post_type'     => TaskScheduler_Registry::$aPostTypes[ 'log' ],
                'post_status'   => array( 'private', 'publish' ),
                'show_date'     => true,
                'sort_column'   => 'post_date',    // , menu_order, post_title
                'sort_order'    => 'DESC',    // ASC or DESC
                'title_li'      => '',
                'walker'        => new TaskScheduler_Walker_Log,
                'hierarchical'  => true,
            );                    
            return $sContent
                . "<h3>" .  __( 'Log', 'task-scheduler' ) . "</h3>"
                . "<ul>" . wp_list_pages( $_aPageListArgs ) . "</ul>";
            
        }



    /*
     * Extensible methods
     */
    // public function columns_task_scheduler( $aHeaderColumns ) {    // columns_{post type slug}
        // return $aHeaderColumns;
    // }    

    // public function sortable_task_scheduler( $aSortableHeaderColumns ) {    // sortable_columns_{post type slug}
        // return $aSortableHeaderColumns;
    // }        

}