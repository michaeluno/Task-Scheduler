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
                'type'               => 'textarea',
                'repeatable'         => true,
                'description'        => __( 'Set an address per line.', 'task-scheduler' ),
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
                
        $aInput[ 'email_addresses' ] = $this->_getEmailAddressesSanitized( $aInput[ 'email_addresses' ] );             
        
        if ( empty( $aInput['email_addresses'] ) ) {
            
            // $aVariable[ 'sectioni_id' ]['field_id']
            $_aErrors[ $this->_sSectionID ][ 'email_addresses' ] = __( 'At least one item needs to be set.', 'task-scheduler' );
            $_bIsValid = false;            
            
        }
                
        // Make it numeric array
        $aInput[ 'email_addresses' ] = array_values( $aInput[ 'email_addresses' ] );    // re-order numerically
        
        if ( ! $_bIsValid ) {

            // Set the error array for the input fields.
            $oAdminPage->setFieldErrors( $_aErrors );        
            $oAdminPage->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
            
        }    
    
        return $aInput;         

    }
    
        /**
         * @since       1.2.0
         * @return      array
         */
        private function _getEmailAddressesSanitized( array $_aEmailSets ) {           
        
            $_aEmailSets = array_filter( $_aEmailSets );
            
            // Drop non-email elements.
            /* foreach( $_aEmailSets as $_iSetIndex => $_sEmailSet ) {
                $_aEmailAddresses = preg_split( "/([\n\r](\s+)?)+/", $_sEmailSet );
                foreach( $_aEmailAddresses as $_iIndex => $_sEmailAdderss ) {
                    if ( ! filter_var( $_sEmailAdderss, FILTER_VALIDATE_EMAIL ) ) {
                        unset( $_aEmailAddresses[ $_iIndex ] );
                    }
                }
                $_aEmailSets[ $_iSetIndex ] = implode( PHP_EOL, $_aEmailAddresses );
            } */
            
            return $_aEmailSets;
            
        }    
    
    /**
     * @since       1.2.0
     * @return      string
     */
    public function getMetaBoxOutput( /* $sOutput, $oTask */ ) {

        $_aParams    = func_get_args() + array(
            null, null
        );
        $sOutput   = $_aParams[ 0 ];
        $oTask     = $_aParams[ 1 ];      
        $_sSlug    = $oTask->routine_action;
        $_aOptions = ( array ) $oTask->{$_sSlug};
        $_aOutputs   = array();
        
        $_aOutputs[] = "<h4>" . __( 'Action', 'task-scheduler' ) . ":</h4>";
        $_aOutputs[] = "<p>" . apply_filters( "task_scheduler_filter_label_action_" . $_sSlug, '' ) . "</p>";
        
        $_aOutputs[] = "<h4>" . __( 'Email Addresses', 'task-scheduler' ) . ":</h4>";
        foreach( $_aOptions[ 'email_addresses' ] as $_aEmailSet ) {
            $_aOutputs[] = "<textarea readonly='readonly' style='width:80%;'>" . esc_textarea( $_aEmailSet ) . "</textarea>";
        }
        
        $_aOutputs[] = "<h4>" . __( 'Email Subject', 'task-scheduler' ) . ":</h4>";
        $_aOutputs[] = "<input type='text' readonly='readonly' value='" . esc_attr( $_aOptions[ 'email_title' ] ) . "' style='width:80%;' />";
        
        $_aOutputs[] = "<h4>" . __( 'Email Message', 'task-scheduler' ) . ":</h4>";
        $_aOutputs[] = "<textarea readonly='readonly' style='width:80%;'>" . esc_textarea( $_aOptions[ 'email_message' ] ) . "</textarea>";        
        return implode( '', $_aOutputs );
                
    }    
    
}
