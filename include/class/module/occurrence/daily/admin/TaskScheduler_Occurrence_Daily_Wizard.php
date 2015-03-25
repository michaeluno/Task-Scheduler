<?php
/**
 * Creates wizard pages for the 'Occurrence' option.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2015, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

final class TaskScheduler_Occurrence_Daily_Wizard extends TaskScheduler_Wizard_Occurrence_Base {
    
    /**
     * Returns the field definition arrays.
     * 
     * @remark        The field definition structure must follows the specification of Admin Page Framework v3.
     */ 
    public function getFields() {
    
        return array(
            array(    
                'field_id'          => 'times',
                'title'             => __( 'Time', 'task-scheduler' ),
                'type'              => 'time',
                'time_format'       => 'H:mm',    // H:mm is the default format.
                'repeatable'        => true,
                'attributes'        => array(
                    'size' => 20,
                ),
                // 'options'    => "{
                    // numberOfMonths: 2,
                    // minDate: 0
                // }"
            ),  
            array(
                'field_id'              => 'days',
                'title'                 => __( 'Days', 'task-scheduler' ),
                'type'                  => 'checkbox',
                'select_all_button'     => true,
                'select_none_button'    => true,
                'label'                 => array(
                    7   => __( 'Sunday', 'task-scheduler' ),
                    1   => __( 'Monday', 'task-scheduler' ),
                    2   => __( 'Tuesday', 'task-scheduler' ),
                    3   => __( 'Wednesday', 'task-scheduler' ),
                    4   => __( 'Thursday', 'task-scheduler' ),
                    5   => __( 'Friday', 'task-scheduler' ),
                    6   => __( 'Saturday', 'task-scheduler' ),
                ),
                'default'               => array(
                    7   => true,
                    1   => true,
                    2   => true,
                    3   => true,
                    4   => true,
                    5   => true,
                    6   => true,
                ),
            ),
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
        
        // Drop non-true values.
        $aInput[ 'times' ] = array_filter( $aInput[ 'times' ] );    
        if ( empty( $aInput[ 'times' ] ) ) {
            
            // $aVariable[ 'sectioni_id' ]['field_id']
            $_aErrors[ $this->_sSectionID ][ 'times' ] = __( 'At least one item needs to be set.', 'task-scheduler' );
            $_bIsValid = false;            
            
        }
        $aInput[ 'days' ] = array_filter( $aInput[ 'days' ] );
        if ( empty( $aInput[ 'days' ] ) ) {
            $_aErrors[ $this->_sSectionID ][ 'days' ] = __( 'At least one day needs to be selected.', 'task-scheduler' );
            $_bIsValid = false;                        
        }
        
        // Reorder the array to be numerically indexed
        natsort( $aInput[ 'times' ] );
        $aInput[ 'times' ] = array_values( $aInput[ 'times' ] );
        
        if ( ! $_bIsValid ) {

            // Set the error array for the input fields.
            $oAdminPage->setFieldErrors( $_aErrors );        
            $oAdminPage->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
    
        } 
    
        return $aInput;         

    }
    
    
}