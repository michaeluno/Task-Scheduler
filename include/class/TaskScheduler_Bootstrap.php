<?php
/**
 * Handles the initial set-up for the plugin.
 *    
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 * 
 */

/**
 * 
 * @action        schedule    task_scheduler_action_check_heartbeat_hourly      Scheduled via the activation hook.
 * @action        do          task_scheduler_action_after_loading_plugin        Triggered when all the plugin components are loaded. Extension plugins should use this hook to add modules.
 */
final class TaskScheduler_Bootstrap {
    
    public function __construct( $sPluginFilePath ) {
        
        // 0. The class properties.
        $this->_sFilePath = $sPluginFilePath;
        $this->_bIsAdmin = is_admin();
        
        // 1. Define constants.
        // $this->_defineConstants();
        
        // 2. Set global variables.
        // $this->_setGlobalVariables();
            
        // 3. Set up auto-load classes.
        $this->_loadClasses( $this->_sFilePath );

        // 4. Set up activation hook.
        register_activation_hook( $this->_sFilePath, array( $this, '_replyToDoWhenPluginActivates' ) );
        
        // 5. Set up deactivation hook.
        register_deactivation_hook( $this->_sFilePath, array( $this, '_replyToDoWhenPluginDeactivates' ) );
        
        // 6. Set up localization.
        $this->_localize();
        
        // 7. Check requirements.
        register_activation_hook( $this->_sFilePath, array( $this, '_replyToCheckRequirements' ) );
        
        // 8. Schedule to load plugin specific components.
        add_action( 'after_setup_theme', array( $this, '_replyToLoadPluginComponents' ) );
                        
    }    
    
    // private function _defineConstants() {}
    
    /**
     * Sets up global variables.
     */
    // private function _setGlobalVariables() {}
    
    /**
     * Register classes to be auto-loaded.
     * 
     */
    private function _loadClasses( $sFilePath ) {

        $_aClassFiles        = array();
        include( dirname( $sFilePath ) . '/include/class-list.php' );
        new TaskScheduler_AdminPageFramework_RegisterClasses( 
            array(), 
            array(), 
            $_aClassFiles 
        );
                
    }

    /**
     * 
     * @since            2.1
     */
    public function _replyToCheckRequirements() {

        $_oRequirements = new TaskScheduler_Requirements( 
            TaskScheduler_Registry::$aRequirements,
            TaskScheduler_Registry::NAME
        );    
        $_oRequirements->check();
        if ( $_oRequirements->check() ) {            
            $_oRequirements->deactivatePlugin( 
                $this->_sFilePath, 
                __( 'Deactivating the plugin', 'task-scheduler' ),  // additional message
                true    // is in the activation hook. This will exit the script.
            );
        }                
        
    }

    /**
     * The plugin activation callback method.
     */    
    public function _replyToDoWhenPluginActivates() {
        
        // In case the server disables wp-cron, start the heartbeat manually.
        add_action( 'shutdown', array( $this, '_replyToStartServerHeartbeat' ) );
        
        // Schedule a heartbeat resuming event.
        if ( ! wp_next_scheduled( 'task_scheduler_action_check_heartbeat_hourly' ) ) {
            
            // Run the server heartbeat with the stored options. (if options are not set, it will use  the default settings.)
            wp_schedule_single_event( time(), 'task_scheduler_action_check_heartbeat_hourly' );                    
            
            // Schedule hourly heartbeat checking event
            wp_schedule_event( time(), 'hourly', 'task_scheduler_action_check_heartbeat_hourly' );
            
        }        
        
    }
        public function _replyToStartServerHeartbeat() {

            $this->_replyToLoadPluginComponents();
            TaskScheduler_Event_ServerHeartbeat_Resumer::resume();
            
        }
    /**
     * The plugin deactivation callback method.
     */
    public function _replyToDoWhenPluginDeactivates() {
                            
        // Delete transients
        TaskScheduler_WPUtility::clearTransients( TaskScheduler_Registry::TRANSIENT_PREFIX );

        // Remove the server heartbeat resume WP Cron event.
        TaskScheduler_WPUtility::unscheduleWPCronEventsByName( 'task_scheduler_action_check_heartbeat_hourly' );
        
        // Delete options
        if ( TaskScheduler_Option::get( array( 'reset', 'reset_upon_deactivation' ) ) ) {
            delete_option( TaskScheduler_Registry::$aOptionKeys['main'] );
        }
    }    
    
    /**
     * Load localization files.
     */
    private function _localize() {
        
        load_plugin_textdomain( 
            TaskScheduler_Registry::TEXT_DOMAIN, 
            false, 
            dirname( plugin_basename( $this->_sFilePath ) ) . TaskScheduler_Registry::TEXT_DOMAIN_PATH
        );
                
    }        
    
    /**
     * Loads the plugin specific components. 
     * 
     * @remark      All the necessary classes should have been already loaded.
     * @callback    action      after_setup_theme
     */
    public function _replyToLoadPluginComponents() {

        // 1. Events - handles background processes and hooks. This should be loaded earlier than the admin classes as some callbacks use the hooks of the admin page framework.
        new TaskScheduler_Event;    

        // 2. Post types - we have four custom post types. One is for tasks, another is for routines, another is for threads, and the last is for logs.
        new TaskScheduler_PostType_Task( TaskScheduler_Registry::$aPostTypes[ 'task' ], null, $this->_sFilePath );
        new TaskScheduler_PostType_Thread( TaskScheduler_Registry::$aPostTypes[ 'thread' ], null, $this->_sFilePath );
        new TaskScheduler_PostType_Routine( TaskScheduler_Registry::$aPostTypes[ 'routine' ], null, $this->_sFilePath );
        new TaskScheduler_PostType_Log( TaskScheduler_Registry::$aPostTypes[ 'log' ], null, $this->_sFilePath );
        
        // 3. Admin pages
        if ( $this->_bIsAdmin ) {

            // 3.1. Root
            $this->oAdminPage = new TaskScheduler_AdminPage( '', $this->_sFilePath );

            // 3.2. Add New
            new TaskScheduler_AdminPage_Wizard( 
                '', // disable storing options
                $this->_sFilePath 
            );        
            
            // 3.3. Edit Module Options
            new TaskScheduler_AdminPage_EditModule(
                '', // disable storing options
                $this->_sFilePath 
            );    
            
            // 3.4. Settings
            new TaskScheduler_AdminPage_Setting( TaskScheduler_Registry::$aOptionKeys['main'], $this->_sFilePath );
            
            // 3.5. System - will be implemented at some point in the future.
            // new TaskScheduler_AdminPage_System( TaskScheduler_Registry::$aOptionKeys['main'], $this->_sFilePath );    
            
            // 3.6. Meta Boxes for task editing page (post.php).
            $this->_registerMetaBoxes();
        
        }            
        
        // Modules should use this hook.
        do_action( 'task_scheduler_action_after_loading_plugin' );
        
    }

        /**
         * Registers meta boxes.
         */
        protected function _registerMetaBoxes() {
            
            if ( ! isset( $GLOBALS[ 'pagenow' ] ) ) {
                return;
            }
            if ( 'post.php' !== $GLOBALS[ 'pagenow' ] ) {
                return;
            }
                        
            new TaskScheduler_MetaBox_Main(
                'task_scheduler_meta_box_main',
                __( 'Main', 'task-scheduler' ),
                array( TaskScheduler_Registry::$aPostTypes[ 'task' ], TaskScheduler_Registry::$aPostTypes[ 'thread' ] ),
                'normal',    // context
                'high'
            );        
            new TaskScheduler_MetaBox_Occurrence(
                'task_scheduler_meta_box_occurrence',
                __( 'Occurrence', 'task-scheduler' ),
                array( TaskScheduler_Registry::$aPostTypes[ 'task' ], TaskScheduler_Registry::$aPostTypes[ 'thread' ] ),
                'normal',    // context
                'default'        // priority
            );
            new TaskScheduler_MetaBox_Action(
                'task_scheduler_meta_box_action',
                __( 'Action', 'task-scheduler' ),
                array( TaskScheduler_Registry::$aPostTypes[ 'task' ], TaskScheduler_Registry::$aPostTypes[ 'thread' ] ),
                'normal',    // context
                'low'        // priority
            );            
            new TaskScheduler_MetaBox_Advanced(
                'task_scheduler_meta_box_advanced',
                __( 'Advanced', 'task-scheduler' ),
                array( TaskScheduler_Registry::$aPostTypes[ 'task' ], TaskScheduler_Registry::$aPostTypes[ 'thread' ] ),
                'advanced',    // context
                'default'
            );
            new TaskScheduler_MetaBox_Submit(
                'task_scheduler_meta_box_submit',
                __( 'Update', 'task-scheduler' ),
                array( TaskScheduler_Registry::$aPostTypes[ 'task' ], TaskScheduler_Registry::$aPostTypes[ 'thread' ] ),
                'side',
                'high'
            );
        
        } 
        
}