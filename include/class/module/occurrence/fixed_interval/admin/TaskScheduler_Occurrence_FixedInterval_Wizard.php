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

final class TaskScheduler_Occurrence_FixedInterval_Wizard extends TaskScheduler_Wizard_Occurrence_Base {
    
    /**
     * User constructor.
     */
    public function construct() {}
    
    /**
     * Returns the field definition arrays.
     * 
     * @remark        The field definition structure must follows the specification of Admin Page Framework v3.
     */ 
    public function getFields() {
    
        return array(        
            array(    
                'field_id'            => 'interval',
                'title'               => __( 'Interval', 'task-scheduler' ),
                'type'                => 'number',
                array(
                    'type'       => 'select',
                    'default'    => 'minute',
                    'label'      => $this->_getUnits(),
                ),
            ),                
        );
        
    }   
        /**
         * 
         * @since       1.0.1
         * @return      array       The unit array.
         */
        private function _getUnits() {
            return array(
                'second' => __( 'second(s)', 'task-scheduler' ),
                'minute' => __( 'minute(s)', 'task-scheduler' ),
                'hour'   => __( 'hour(s)', 'task-scheduler' ),
                'day'    => __( 'day(s)', 'task-scheduler' ),            
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
        
        if ( ! isset( $aInput['interval'][ 0 ] ) || ! $aInput['interval'][ 0 ] ) {
            // $aVariable[ 'sectioni_id' ]['field_id']
            $_aErrors[ $this->_sSectionID ][ 'interval' ] = __( 'The interval must be greater than 0.', 'task-scheduler' );
            $_bIsValid = false;            
            
        }
        
        if ( ! $_bIsValid ) {

            // Set the error array for the input fields.
            $oAdminPage->setFieldErrors( $_aErrors );        
            $oAdminPage->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
            return array();
            
        }    
    
        return $aInput; 
        
    }   
    
    /**
     * Defines the output in the meta box.
     * @since       1.0.1
     * @return      string
     */
    public function getMetaBoxOutput( /* $sOutput, $oTask */ ) {
        
        $_aParams    = func_get_args() + array(
            null, null
        );
        $sOutput   = $_aParams[ 0 ];
        $oTask     = $_aParams[ 1 ];
        $_sSlug    = $oTask->occurrence;
        $_aOptions = ( array ) $oTask->{$_sSlug};
        $_sLabel   = apply_filters( 
            "task_scheduler_filter_label_occurrence_{$_sSlug}", 
            $_sSlug
        );
        
        return "<h4>" . __( 'Type', 'task-scheduler' ) . ":</h4>"
            . "<p>" . $_sLabel . "</p>"
            . "<h4>" . __( 'Interval', 'task-scheduler' ) . ":</h4>"
            . "<p>" 
                . $this->_getReadableInterval( $_aOptions[ 'interval' ] )
            . "</p>";
              
    }    
        /**
         * 
         * @since       1.0.1
         * @return      string      The readable interval value.
         */
        private function _getReadableInterval( array $aInterval ) {
            $aInterval = $aInterval + array( null, null );
            $_aUnits   = $this->_getUnits();
            $_sUnit    = isset( $_aUnits[ $aInterval[ 1 ] ] )
                ? $_aUnits[ $aInterval[ 1 ] ]
                : '';
            return $aInterval[ 0 ]
                . ' ' .  $_sUnit
            ;
        }
    
    
}