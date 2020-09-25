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
 * Defines the 'Edit Module Options' wizard admin page.
 * 
 * @since   1.4.0
 */
class TaskScheduler_AdminPage_EditModule__Page__EditOptions extends TaskScheduler_AdminPage_Page_Base {

    /**
     * @return array
     * @since 1.5.3
     */
    protected function _getArguments() {
         return array(
            'title'         => __( 'Edit Module Options', 'task-scheduler' ),    // page and menu title
            'page_slug'     => TaskScheduler_Registry::$aAdminPages[ 'edit_module' ],    // page slug
            'show_in_menu'  => false,        // do not add in the sub menu
        );
    }

    /**
     * @callback    action      load_{page slug}
     * @since       1.4.0
     * @return      void
     */
    public function replyToLoadPage( $oFactory ) {
                        
        new TaskScheduler_AdminPage_EditModule__Tab__Wizard(
            $oFactory,
            $this->sPageSlug,
            array(    // this is needed to be the parent tab of module tabs. (the wizard base class assumes the parent tab exists with this slug.)
                'tab_slug'          => 'wizard',    
                'title'             => __( 'Edit Module Options', 'task-scheduler' ),
                'order'             => 1,    // this must be the 'default' tab
                'show_in_page_tab'  => false,    
            )
        );
        
        // the landing page of the editing page of action module options.
        new TaskScheduler_AdminPage_EditModule__Tab__Action(
            $oFactory,
            $this->sPageSlug,
            array(    
                'tab_slug'            => 'edit_action',    
                'title'               => '', // __( 'Edit Action', 'task-scheduler' ),
                'show_in_page_tab'    => false,
            )            
        );        

        // the landing page of the editing page of occurrence module options.
        new TaskScheduler_AdminPage_EditModule__Tab__Occurrence(
            $oFactory,
            $this->sPageSlug,
            array(    
                'tab_slug'            => 'edit_occurrence',    
                'title'               => __( 'Edit Occurrence', 'task-scheduler' ),
                'order'               => 1,    // this must be the 'default' tab <-- @todo ? duplicates with the wizard tab.
                'show_in_page_tab'    => false,
            )            
        );        
        
        // the options will be redirected to this page and saved and redirected to post.php 
        new TaskScheduler_AdminPage_EditModule__Tab__UpdateOptions(
            $oFactory,
            $this->sPageSlug,
            array(    
                'tab_slug'            => 'update_module',    
                'title'               => __( 'Update Module Options', 'task-scheduler' ),
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
