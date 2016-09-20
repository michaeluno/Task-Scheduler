<?php
/**
 * Task Scheduler
 * 
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 */

/** 
 * Creates wizard pages for the 'Transient Cleaner' action.
 * 
 * @since      1.1.0
 */
final class TaskScheduler_Action_TransientCleaner_Wizard extends TaskScheduler_Wizard_Action_Base {

    /**
     * Returns the field definition arrays.
     * 
     * @remark        The field definition structure must follows the specification of Admin Page Framework v3.
     */ 
    public function getFields() {

        return array(
            array(    
                'field_id'           => 'transient_prefix',
                'title'              => __( 'Transient Prefix', 'task-scheduler' ),
                'type'               => 'text',
                'description'        => __( 'Set the transient prefix. Set empty if you want to delete all the expired transients.', 'task-scheduler' ),
            ),            
            array(    
                'field_id'           => 'transient_type',
                'title'              => __( 'Network Transients', 'task-scheduler' ),
                'type'               => 'radio',
                'hidden'             => ! is_multisite(),
                'if'                 => is_multisite(),
                'label'              => array(
                    0   => __( 'Both', 'task-scheduler' ),
                    1   => __( 'Site transients', 'task-scheduler' ),
                    2   => __( 'Network transients', 'task-scheduler' ),
                 ),
                'default'            => 0,
            )
        );
        
    }    

    public function validateSettings( /* $aInput, $aOldInput, $oAdminPage, $aSubmitInfo */ ) { 

        $_aParams    = func_get_args() + array(
            null, null, null, null
        );
        $aInput      = $_aParams[ 0 ];
        $aOldInput   = $_aParams[ 1 ];
        $oAdminPage  = $_aParams[ 2 ];
        $aSubmitInfo = $_aParams[ 3 ];      
    
        $_bIsValid   = true;
        $_aErrors    = array();
        
        $aInput[ 'transient_prefix' ] = trim( $aInput[ 'transient_prefix' ] );
        $aInput[ 'transient_type' ]   = isset( $aInput[ 'transient_type' ] )
            ? $aInput[ 'transient_type' ]
            : 0;
        

        if ( ! $_bIsValid ) {

            // Set the error array for the input fields.
            $oAdminPage->setFieldErrors( $_aErrors );        
            $oAdminPage->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
            
        }    
    
        return $aInput;         

    }
    
}
