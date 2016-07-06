<?php
/**
 * One of the abstract class of the plugin admin page class.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_AdminPage_Setting_Form_Reset extends TaskScheduler_AdminPage_Setting_Start {

    public function _defineInPageTabs() {
        
        $this->addInPageTabs(
            TaskScheduler_Registry::$aAdminPages[ 'setting' ],    // the target page slug 
            array(
                'tab_slug'    => 'reset',    // avoid hyphen(dash), dots, and white spaces
                'title'       => __( 'Reset', 'task-scheduler' ),
            )
        );
        parent::_defineInPageTabs();
        
    }
    
    /**
     * Defines the settings form.
     */
    protected function _defineForm() {
    
        $this->addSettingSections(
            TaskScheduler_Registry::$aAdminPages[ 'setting' ],    // the target page slug    
            array(
                'section_id'    => 'reset',
                'tab_slug'      => 'reset',
                'title'         => __( 'Reset', 'task-scheduler' ),
            )
        );        
        
        $this->addSettingFields(
            'reset',    // the target section ID
            array(    
                'field_id'        => 'reset_on_uninstall',
                'title'           => __( 'Delete Options upon Uninstall', 'task-scheduler' ),
                'type'            => 'checkbox',
                'label'           => __( 'Delete the settings when the plugin gets uninstalled.', 'task-scheduler' ),
            ),            
            array(    
                'field_id'        => 'submit',
                'type'            => 'submit',
                'label'           => __( 'Save', 'task-scheduler' ),
                'label_min_width' => 0,
                'attributes'      => array(
                    'field'    => array(
                        'style'    => 'float:right; clear:none; display: inline;',
                    ),
                ),                    
            ),
            array(    
                'field_id'        => 'reset',
                'type'            => 'submit',
                'title'           => __( 'Reset Options', 'task-scheduler' ),
                'label'           => __( 'Reset', 'task-scheduler' ),
                'label_min_width' => 0,
                'reset'           => true,
                'attributes'      => array(
                    'field'    => array(
                        // 'style'    => 'float:right; clear:none; display: inline;',
                    ),
                    'class' => 'button button-secondary',
                ),                    
            )    
        );        
        
        parent::_defineForm();
    }
    
}