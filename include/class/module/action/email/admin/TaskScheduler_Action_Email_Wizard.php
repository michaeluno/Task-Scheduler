<?php
/**
 * Creates wizard pages for the 'Email' action.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

final class TaskScheduler_Action_Email_Wizard extends TaskScheduler_Wizard_Action_Base {

    /**
     * Returns the field definition arrays.
     * 
     * @remark        The field definition structure must follows the specification of Admin Page Framework v3.
     */ 
    public function getFields() {

        return array(
            array(    
                'field_id'           => 'email_addresses',
                'title'              => __( 'Email Addresses', 'task-scheduler' ),
                'type'               => 'text',
                'repeatable'         => true,
            ),            
            array(    
                'field_id'           => 'email_title',
                'title'              => __( 'Email Title', 'task-scheduler' ),
                'type'               => 'text',
                'default'            => __( 'Automated Email Notice of %task_name% - %site_name%', 'task-scheduler' ),
                'attributes'         => array(
                    'size'    => 60,
                ),
            ),            
            array(    
                'field_id'            => 'email_message',
                'title'               => __( 'Email Message', 'task-scheduler' ),
                'type'                => 'textarea',
                'rich'                => true,
                'default'             => sprintf( 
                        __( 'This is an automated email message from %1$s (%2$s), sent by the task, %3$s, with the %4$s occurrence type.', 'task-scheduler' ),    
                        '%site_name%',
                        '%site_url%',
                        '%task_name%',
                        '%occurrence%'
                    ) . PHP_EOL . PHP_EOL 
                    . '%task_description%',
                'after_fields'        => '<div class="action-email-field-description"><h5>' . __( 'Available variables', 'task-scheduler'    ) . '</h5>'
                    . '<ul>' 
                        . '<li><code>%task_name%</code> - ' . __( 'the task name', 'task-scheduler' ) . '</li>'
                        . '<li><code>%task_description%</code> - ' . __( 'the task description', 'task-scheduler' ) . '</li>'
                        . '<li><code>%occurrence%</code> - ' . __( 'the occurrence type', 'task-scheduler' ) . '</li>'
                        . '<li><code>%action%</code> - ' . __( 'the action name', 'task-scheduler' ) . '</li>'
                        . '<li><code>%site_url%</code> - ' . __( 'the site url', 'task-scheduler' ) . '</li>'
                        . '<li><code>%site_name%</code> - ' . __( 'the site name', 'task-scheduler' ) . '</li>'
                        . '<li><code>%admin_email%</code> - ' . __( 'the admin email address', 'task-scheduler' ) . '</li>'
                    . '</ul>'
                    . '</div>',
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
        
        $aInput['email_addresses'] = array_filter( $aInput['email_addresses'] );    // drop non-true values.
        $aInput['email_addresses'] = array_unique( $aInput['email_addresses'] );
        if ( empty( $aInput['email_addresses'] ) ) {
            
            // $aVariable[ 'sectioni_id' ]['field_id']
            $_aErrors[ $this->_sSectionID ][ 'email_addresses' ] = __( 'At least one item needs to be set.', 'task-scheduler' );
            $_bIsValid = false;            
            
        }
        foreach ( $aInput['email_addresses'] as $_iIndex => $_sEmail ) {
            
            if ( ! filter_var( $_sEmail, FILTER_VALIDATE_EMAIL ) ) {
                unset( $aInput['email_addresses'][ $_iIndex ] );
                $_bIsValid = false;            
                $_aErrors[ $this->_sSectionID ][ 'email_addresses' ] = __( 'There was an invalid e-mail address.', 'task-scheduler' );
            }
            
        }
        
        // Re-order the array.
        $aInput['email_addresses'] = array_values( $aInput['email_addresses'] );    // re-order numerically
        
        if ( ! $_bIsValid ) {

            // Set the error array for the input fields.
            $oAdminPage->setFieldErrors( $_aErrors );        
            $oAdminPage->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
            
        }    
    
        return $aInput;         

    }
    
}