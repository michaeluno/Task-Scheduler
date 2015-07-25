<?php
/**
 * Provides utility methods which use the Task Scheduler plugin specific components.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2015, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

class TaskScheduler_PluginUtility extends TaskScheduler_WPUtility {
        
    /**
     * Checks if the same routine exists.
     */
    static protected function hasSameRoutine( $aTaskMeta, $hWPQueryCallback ) {
                        
        // The top level arguments
        $_aCheckingTopLevelArguments = array(
            'post_title',    //     => [ <string> ] // The title of your post.
        );
        // The following change dynamically so don't check
        $_aNotCheckingTopLevelArguments = array_merge( 
            array(
                'ID',               // [ <post id> ] // Are you updating an existing post?
                'tax_input',        // => [ array( <taxonomy> => <array | string> ) ] // For custom taxonomies. Default empty.
                'post_type',        //     => [ 'post' | 'page' | 'link' | 'nav_menu_item' | custom post type ] // Default 'post'.
                'post_author',      //    => [ <user ID> ] // The user ID number of the author. Default is the current user ID.
                'post_status',      //   => [ 'draft' | 'publish' | 'pending'| 'future' | 'private' | custom registered status ] // Default 'draft'.
                'post_content',     // [ <string> ] // The full text of the post.
                'post_name',        // [ <string> ] // The name (slug) for your post            
                'post_parent',      // [ <post ID> ] // Sets the parent of the new post, if any. Default 0.
                'menu_order',       // [ <order> ] // If new post is a page, sets the order in which it should appear in supported menus. Default 0.
                'ping_status',      //    => [ 'closed' | 'open' ] // Pingbacks or trackbacks allowed. Default is the option 'default_ping_status'.
                'to_ping',          // Space or carriage return-separated list of URLs to ping. Default empty string.
                'pinged',           // Space or carriage return-separated list of URLs that have been pinged. Default empty string.
                'post_password',    // [ <string> ] // Password for post, if any. Default empty string.
                'guid',             // Skip this and let Wordpress handle it, usually.
                'post_content_filtered', // // Skip this and let Wordpress handle it, usually.
                'post_excerpt',     // [ <string> ] // For all your post excerpt needs.
                'post_date',        // [ Y-m-d H:i:s ] // The time post was made.
                'post_date_gmt',    // [ Y-m-d H:i:s ] // The time post was made, in GMT.
                'comment_status',   // [ 'closed' | 'open' ] // Default is the option 'default_comment_status', or 'closed'.
                'post_category',    // [ array(<category id>, ...) ] // Default empty.
                'tags_input',       // [ '<tag>, <tag>, ...' | array ] // Default empty.            
                'page_template',    // [ <string> ] // Requires name of template file, eg template.php. Default empty.
            ), array(
                // These plugin specific meta keys have dynamic values so it should not be compared.
                '_next_run_time',
                '_last_run_time',
                '_count_call',
                '_count_exit',
                '_count_run',
                '_count_hung',
                'log_id',
                '_is_spawned',          
                '_spawned_time',          
                '_exit_code',
                '_routine_status',
            ), array(
                // These are the ones that WordPress inserts
                '_edit_lock',
                '_edit_last',  
            )
        );
        $_aQueryArguments = array( );
        // Extract the top level argument elements and remvoe them from the meta array.
        foreach ( $_aCheckingTopLevelArguments as $_sCheckingKey ) {
            if ( isset( $aTaskMeta[ $_sCheckingKey ] ) ) {
                $_aQueryArguments[ $_sCheckingKey ] = $aTaskMeta[ $_sCheckingKey ];
                unset( $aTaskMeta[ $_sCheckingKey ] );
            }
        }
        // Remove the unchecking items from the meta array. 
        foreach ( $_aNotCheckingTopLevelArguments as $_sCheckingKey ) {
            unset( $aTaskMeta[ $_sCheckingKey ] );
        }
        // Now $aTaskMeta contains the meta keys. Drop values that are not string, integer, nor boolean.
        foreach ( $aTaskMeta as $_sMetaKey => $_vValue ) {
            if ( is_string( $_vValue ) || is_numeric( $_vValue ) || is_bool( $_vValue ) ) {
                continue;
            }
            unset( $aTaskMeta[ $_sMetaKey ] );
        }
                
        // Compose the 'meta_query' argument element.
        $_aMetaQuery = array( 'relation' => 'AND' );
        foreach ( $aTaskMeta as $_sKey => $_vValue ) {
            $_aMetaQuery[] = array(
                'key'       => $_sKey,
                'value'     => $_vValue,
                'compare'   => 'IN',                
            );
        }
        $_aQueryArguments[ 'meta_query' ]     = $_aMetaQuery;
        $_aQueryArguments[ 'post_status ' ]   = 'any';
        $_aQueryArguments[ 'posts_per_page' ] = 1;

        $_aResults = call_user_func_array( $hWPQueryCallback, array( $_aQueryArguments ) );
        return $_aResults->found_posts ? true : false;
        
    }    
    
    /**
     * 
     * This is used to display stored options that are not editable in meta boxes.
     */
    static public function getListFromAssociativeArray( array $aArray ) {
        
        $_aList = array();
        foreach( $aArray as $_sKey => $_vValue ) {
            $_aList[] = "<li>" 
                    . "<span class='key_label'>" . $_sKey . "</span>" 
                    . "<span class='value_container'>" . ( is_array( $_vValue ) ? self::getListFromAssociativeArray( $_vValue ) : $_vValue ) . "</span>" 
                . "</li>";
        }
        return "<ul class='key-value-container'>"
                . implode( PHP_EOL, $_aList )
            . "</ul>";

    }
    
    /**
     * Redirects the user to the Add New wizard page.
     */
    public static function goToAddNewPage() {
        wp_safe_redirect( 
            add_query_arg( 
                array( 
                    'page'        =>    TaskScheduler_Registry::$aAdminPages[ 'add_new' ],
                ), 
                admin_url( 'admin.php' ) 
            )
        );        
    }
    
    /**
     * Redirects the user to the Edit Module wizard page.
     */ 
    public static function goToEditTaskPage() {        
        wp_safe_redirect( self::getEditTaskPageURL() );
    }    
    
    /**
     * Returns the module editing page url.
     */
    public static function getEditTaskPageURL() {
        return add_query_arg(
            array( 
                'post'   => isset( $_GET['post'] ) 
                    ? $_GET['post'] 
                    : '',
                'action' => 'edit',
            ),
            admin_url( "post.php" )
        );        
    }    
    
    /**
     * Redirects the user to the Edit Module wizard page.
     */ 
    public static function goToModuleEditPage()     {        
        wp_safe_redirect( self::getModuleEditPageURL() );
    }    
    
    /**
     * Returns the module editing page url.
     */
    public static function getModuleEditPageURL( $_aGetQuery=array() ) {
        return add_query_arg( 
            $_aGetQuery
            + array( 
                'page' => TaskScheduler_Registry::$aAdminPages[ 'edit_module' ],
            ), 
            admin_url( 'admin.php' ) 
        );        
    }
            
    /**
     * Redirects the user to the task listing page.
     */
    public static function goToTaskListingPage() {
        wp_safe_redirect( self::getTaskListingPageURL() );
    }
    
    /**
     * Returns the url of the task listing page.
     */
    static public function getTaskListingPageURL( array $aQueryArgs=array() ) {
        return add_query_arg( 
            $aQueryArgs + array( 
                'page' => TaskScheduler_Registry::$aAdminPages[ 'task_list' ],
            ), 
            admin_url( 'admin.php' ) 
        );
    }    

}