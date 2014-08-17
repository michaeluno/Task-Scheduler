<?php
/**
 * One of the abstract class of the plugin admin page class.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_AdminPage_System_Form extends TaskScheduler_AdminPage_System_Start {
    
    /**
     * Defines the add_new form.
     */
    protected function _setSystemForm() {
        
        $this->addSettingSections(
            TaskScheduler_Registry::AdminPage_System,    // the target page slug
            array(
                'section_id'    =>    'system',
                'title'            =>    __( 'System', 'task-scheduler' ),
            )            
        );        
        $this->addSettingFields(
            'system',    // the target section ID
            array(    
                'field_id'            =>    'site_system_information',
                'title'                =>    __( 'Site System Information', 'task-scheduler' ),
                'type'                =>    'system',
                'default'            =>    25,
            ),            
            array(    
                'field_id'            =>    'submit',
                'type'                =>    'submit',
                'label'                =>    __( 'Save', 'task-scheduler' ),
                'label_min_width'    =>    0,
                'attributes'        =>    array(
                    'field'    =>    array(
                        'style'    =>    'float:right; clear:none; display: inline;',
                    ),
                ),                    
            )    
        );        
        
    }
    
}