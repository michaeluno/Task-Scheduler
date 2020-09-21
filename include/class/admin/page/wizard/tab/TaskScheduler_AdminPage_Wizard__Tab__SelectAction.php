<?php
/**
 * Task Scheduler
 * 
 * Provides an enhanced task management system for WordPress.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * Defines the 'Select Action' tab page.
 * 
 * One of the base classes of the plugin admin page class for the wizard pages.
 * @since   1.4.0
 */
class TaskScheduler_AdminPage_Wizard__Tab__SelectAction extends TaskScheduler_AdminPage_Tab_Base {

    /**
     * 
     * 
     * @since           1.4.0
     * @callback        action      load_{page slug}_{tab slug}
     */        
    public function replyToLoadTab( $oAdminPage ) {
        
        new TaskScheduler_AdminPage_Wizard__Section__SelectAction(
            $oAdminPage,
            $this->sPageSlug,
            array(
                'tab_slug'     => 'wizard_select_action',
                'section_id'   => 'wizard_select_action',
                'title'        => __( 'Select Action', 'task-scheduler' ),
            )        
        );        
        
    }    
    
}
