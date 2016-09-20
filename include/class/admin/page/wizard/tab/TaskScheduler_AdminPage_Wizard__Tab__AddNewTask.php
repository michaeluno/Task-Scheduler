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
 * Defines the 'Add New Task' tab page.
 * 
 * One of the base classes of the plugin admin page class for the wizard pages.
 * @since   1.4.0
 */
class TaskScheduler_AdminPage_Wizard__Tab__AddNewTask extends TaskScheduler_AdminPage_Tab_Base {

    /**
     * 
     * 
     * @since           1.4.0
     * @callback        action      load_{page slug}_{tab slug}
     */ 
    public function replyToLoadTab( $oAdminPage ) {
        
        new TaskScheduler_AdminPage_Wizard__Section__Wizard(
            $oAdminPage,
            $this->sPageSlug,
            array(
                'tab_slug'     => 'wizard',
                'section_id'   => 'wizard',
                'title'        => __( 'Task Creation Wizard', 'task-scheduler' ),
            )        
        );
        
    }    
    
}
