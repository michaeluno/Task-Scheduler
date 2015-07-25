<?php
/**
 * An abstract class of a custom post type for task logs.
 *
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2015, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
*/
abstract class TaskScheduler_PostType_Log_Base extends TaskScheduler_AdminPageFramework_PostType {


    public function start() {
        // Create the task post type
        $this->setPostTypeArgs(
            array(            // argument - for the array structure, refer to http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
                'labels'                =>    array(
                    'name'                    => __( 'Task Scheduler Log', 'task-scheduler' ),
                    'all_items'               => __( 'Logs', 'task-scheduler' ),    // sub menu label
                    'singular_name'           => __( 'Log', 'task-scheduler' ),
                    'menu_name'               => __( 'Logs', 'task-scheduler' ),    // this changes the root menu name 
                    'add_new'                 => __( 'Add New Log', 'task-scheduler' ),
                    'add_new_item'            => __( 'Add New Log', 'task-scheduler' ),
                    'edit'                    => __( 'Edit', 'task-scheduler' ),
                    'edit_item'               => __( 'Edit Log', 'task-scheduler' ),
                    'new_item'                => __( 'New Log', 'task-scheduler' ),
                    'view'                    => __( 'View', 'task-scheduler' ),
                    'view_item'               => __( 'View Log', 'task-scheduler' ),
                    'search_items'            => __( 'Search Logs', 'task-scheduler' ),
                    'not_found'               => __( 'No Log found for Task Scheduler', 'task-scheduler' ),
                    'not_found_in_trash'      => __( 'No Log Found for Task Scheduler in Trash', 'task-scheduler' ),
                    'parent'                  => __( 'Parent Log', 'task-scheduler' ),
                    // 'publish'                =>    __( 'Run', 'task-scheduler' ),
                    'plugin_listing_table_title_cell_link'    =>    '',    // framework specific key. [3.0.6+] - passing an empty will disable the automatic link insertion to the plugin listing table.
                ),
                'public'                  => true,
                'show_ui'                 => true,
                // 'show_in_menu'         => false, // Whether to show post type in the admin menu. 'show_ui' must be true for this to work. bool (defaults to 'show_ui')
                'show_in_menu'            => 'TaskScheduler_AdminPage',    // the plugin root admin page
                'menu_position'           => 999,
                'can_export'              => true,
                // 'supports' => array( 'title', 'editor', 'comments', 'thumbnail' ),    // 'custom-fields'
                'supports'                => array( 'title', 'editor', 'excerpt' ),
                'taxonomies'              => array( '' ),
                'menu_icon'               => TaskScheduler_Registry::getPluginURL( '/asset/image/menu_icon_16x16.png' ),
                'has_archive'             => false,
                'hierarchical'            => true,
                'show_admin_column'       => true,
                'screen_icon'             => TaskScheduler_Registry::getPluginURL( "/asset/image/screen_icon_32x32.png" ),
                'exclude_from_search'     => true,
                // 'show_table_filter'    => false,    // not working.
                // 'capabilities' => array(
                    // 'create_posts' => false,
                // ),        
                'capabilities' => array(
                    // 'read_private_posts'    =>    'none',
                    // 'delete_private_posts'    =>    'none',
                    // 'edit_private_posts'    =>    'none',
        
                    // 'publish_posts' => 'publish_movies',
                    // 'edit_posts' => 'edit_movies',
                    // 'edit_others_posts' => 'edit_others_movies',
                    // 'delete_posts' => 'delete_movies',
                    // 'delete_others_posts' => 'delete_others_movies',
                    // 'read_private_posts' => 'read_private_movies',
                    // 'edit_post' => 'edit_movie',
                    // 'delete_post' => 'delete_movie',
                    // 'read_post' => 'read_movie',
                ),                
            )        
        );
    
        // For admin
        if ( $this->oProp->bIsAdmin && $this->oUtil->getCurrentPostType() == $this->oProp->sPostType ) {
                        
            // Task listing table
            add_action( 'admin_head-edit.php', array( $this, '_replyToAddCustomCSS' ) );
            
            // The default sort order 
            add_action( 'pre_get_posts', array( $this, '_replyToSetDefautSortOrder' ) );
            
        }
        
    }
                
        /**
          * Sets the default sort order of the log listing table.
         */ 
        public function _replyToSetDefautSortOrder( $oWPQuery ) {

            if ( $oWPQuery->query['post_type'] != $this->oProp->sPostType ) { 
                return; 
            }
            
            // 'orderby' value can be any column name
            $oWPQuery->set( 'orderby', 'date' );

            // 'order' value can be ASC or DESC
            $oWPQuery->set( 'order', 'DESC' );
            
        }        
        /**
         * Adds custom CSS rules to the listing table page.
         */
        public function _replyToAddCustomCSS() {
            ?>
            <style type="text/css">
                .add-new-h2 { display: none; }
                .widefat .column-time { width: 14%; }
            </style>
            <?php
        }    

     public function columns_ts_log( $aHeaderColumns ) {    // columns_{post type slug}
        
        return array(
            'cb'                   => '<input type="checkbox" />',    // Checkbox for bulk actions. 
            'title'                => __( 'title', 'task-scheduler' ),    
            // 'date'              => __( 'Date', 'task-scheduler' ),    
            'time'                 => __( 'Time', 'task-scheduler' ),    
        ) ;
            
    }
    
    public function sortable_columns_ts_log( $aSortableHeaderColumns ) {    // sortable_columns_{post type slug}
        return $aSortableHeaderColumns + array(
            'title'                => 'title',
            'time'                 => 'time',
        );
    }    

    /**
     * Defines the output of the date column cells.
     * 
     */
    public function cell_ts_log_time( $sCell, $iPostID ) {    // cell_{post type slug}_{cell key}
        
        $_oLog = TaskScheduler_Log::getInstance( $iPostID );
        return $_oLog->getReadableTime( 
            mysql2date( 'U' , $_oLog->post_date ),
            'Y/m/d G:i:s', 
            true 
        );
        // TaskScheduler_WPUtility::getRedableMySQLDate( 
            // 'modified' == $aArgs['show_date'] ? $oLog->post_modified  : $oLog->post_date,
            // $aArgs['date_format'], 
            // true 
        // );    
    
        return 'hello!';
                
    }
    
}

