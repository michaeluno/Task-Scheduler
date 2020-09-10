<?php
/**
 * Task Scheduler
 * 
 * Provides an enhanced task management system for WordPress.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * Defines the 'Add New Task' admin page.
 * 
 * @since   1.4.0
 */
class TaskScheduler_AdminPage_Wizard__Page__AddNewTask extends TaskScheduler_AdminPage_Page_Base {

    /**
     * @callback    action      load_{page slug}
     * @since       1.4.0
     * @return      void
     */
    public function replyToLoadPage( $oFactory ) {
                        
        // the wizard starting page.       
        new TaskScheduler_AdminPage_Wizard__Tab__AddNewTask(
            $oFactory,
            $this->sPageSlug,
            array(    
                'tab_slug'            => 'wizard',    
                'title'               => '',   // __( 'Wizard', 'task-scheduler' ),
                'order'               => 1,    // this must be the 'default' tab
                'show_in_page_tab'    => false,
            )            
        );
        
        // the hidden page that let the user select an action.
        new TaskScheduler_AdminPage_Wizard__Tab__SelectAction(
            $oFactory,
            $this->sPageSlug,        
            array(    
                'tab_slug'            => 'wizard_select_action',    
                'title'               => '', // __( 'Select Action', 'task-scheduler' ),
                'show_in_page_tab'    => false,
            )                        
        );
        
        // the hidden page that deals with creating a redirected task by inserting the transient options as the meta data of a newly creating a custom post type post.
        new TaskScheduler_AdminPage_Wizard__Tab__CreateTask(
            $oFactory,
            $this->sPageSlug,
            array(    
                'tab_slug'            => 'wizard_create_task',    
                'title'               => '', // __( 'Create Task', 'task-scheduler' ),
                'show_in_page_tab'    => false,
            )            
        );
        
    }                
            
    /**
     * @since       1.4.0
     * @return      void
     */
    public function replyToDoPage( $oFactory ) {
        
        if ( ! $oFactory->oUtil->isDebugMode() ) {
            return;
        }
        
        echo "<h3>" . __( 'Debug Info', 'task-scheduler' ) . "</h3>";
        echo "<h4>" . __( 'Saved Wizard Options', 'task-scheduler' ) . "</h4>";
        echo "<pre>" . TaskScheduler_Debug::getDetails( $oFactory->oProp->aOptions ) . "</pre>";
        
    }    

}
