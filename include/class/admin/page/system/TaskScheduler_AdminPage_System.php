<?php
/**
 * One of the abstract class of the plugin admin page class.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

final class TaskScheduler_AdminPage_System extends TaskScheduler_AdminPage_System_Form {
    
    
    public function setUp() {
        
        $this->setRootMenuPageBySlug( TaskScheduler_Registry::$aAdminPages['root'] );
        $this->addSubMenuItems(
            array(
                'title'            => __( 'System', 'task-scheduler' ),    // page and menu title
                'page_slug'        => TaskScheduler_Registry::$aAdminPages[ 'system' ]    // page slug
            )
        );        
        
        $this->_setSystemForm();
        
        $this->setPluginSettingsLinkLabel( '' );    // pass an empty string.        
        
    }
    
}