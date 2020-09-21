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
 * Defines the 'Edit Action' tab page.
 * 
 * One of the base classes of the plugin admin page class for the Edit Module Options pages.
 * @since   1.4.0
 */
class TaskScheduler_AdminPage_EditModule__Tab__Action extends TaskScheduler_AdminPage_Tab_Base {

    /**
     * 
     * 
     * @since           1.4.0
     * @callback        action      load_{page slug}_{tab slug}
     */ 
    public function replyToLoadTab( $oAdminPage ) {
        
        new TaskScheduler_AdminPage_EditModule__Section__Action(
            $oAdminPage,
            $this->sPageSlug,
            array(
                'section_id'    => $this->sTabSlug,
                'tab_slug'      => $this->sTabSlug,
                'title'         => __( 'Action', 'task-scheduler' ),
            )       
        );
        
    }    
    
}
