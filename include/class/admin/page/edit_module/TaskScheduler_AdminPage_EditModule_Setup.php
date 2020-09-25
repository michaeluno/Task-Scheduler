<?php
/**
 * One of the base classes of the editing module options pages.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_AdminPage_EditModule_Setup extends TaskScheduler_AdminPage_EditModule_Validation {
    
    /**
     * Overrides the parent method as it does parent-specific routines.
     */
    public function start() {}
    
    /**
     * Defines the admin pages of the plugin.
     * 
     * @since    1.0.0
     */         
    public function setUp() {
            
        $this->setRootMenuPageBySlug( TaskScheduler_Registry::$aAdminPages[ 'root' ] );
        
        new TaskScheduler_AdminPage_EditModule__Page__EditOptions( $this );

        // add_action( 
            // "load_" . TaskScheduler_Registry::$aAdminPages[ 'edit_module' ], 
            // array( $this, '_replyToLoadPage' ) 
        // );
        
        $this->setPluginSettingsLinkLabel( '' );    // pass an empty string.
        
    }
    
    /**
     * Called when the framework page loads.
     * 
     * @deprecated
     */
/*     public function _replyToLoadPage( $oAdminPage ) {

        $this->addInPageTabs(
            TaskScheduler_Registry::$aAdminPages[ 'edit_module' ],    // the target page slug
            array(    // this is needed to be the parent tab of module tabs. (the wizard base class assumes the parent tab exists with this slug.)
                'tab_slug'          => 'wizard',    
                'title'             => __( 'Edit Module Options', 'task-scheduler' ),
                'order'             => 1,    // this must be the 'default' tab
                'show_in_page_tab'  => false,    
            )
        );        
   
        $this->_defineInPageTabs();
        $this->_defineForm();
        
    }
 */
}