<?php
/**
 * Task Scheduler
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * Creates wizard pages for the 'Email' action.
 * 
 * @since       1.4.0
 */
final class TaskScheduler_Action_PHPScript_Wizard extends TaskScheduler_Wizard_Action_Base {

    /**
     * Returns the field definition arrays.
     * 
     * @remark        The field definition structure must follows the specification of Admin Page Framework v3.
     */ 
    public function getFields() {

        return array(
            array(    
                'field_id'           => 'php_script_paths',
                'title'              => __( 'PHP Script Paths', 'task-scheduler' ),
                'type'               => 'path',
                'repeatable'         => true,
                'options'            => array(
                    'fileExtensions' => 'php',
                ),
                'description'        => __( 'Select PHP scripts to run.', 'task-scheduler' ),
            ),            
        );
        
    }    

    public function validateSettings( /* $aInputs, $aOldInputs, $oAdminPage, $aSubmitInfo */ ) { 

        $_aParams    = func_get_args() + array(
            null, null, null, null
        );
        $aInputs     = $_aParams[ 0 ];
        $aOldInputs  = $_aParams[ 1 ];
        $oAdminPage  = $_aParams[ 2 ];
        $aSubmitInfo = $_aParams[ 3 ];      
    
        $_bIsValid   = true;
        $_aErrors    = array();
                
        

        if ( ! $_bIsValid ) {

            // Set the error array for the input fields.
            $oAdminPage->setFieldErrors( $_aErrors );        
            $oAdminPage->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
            
        }    
    
        return $aInputs;         

    }

}
