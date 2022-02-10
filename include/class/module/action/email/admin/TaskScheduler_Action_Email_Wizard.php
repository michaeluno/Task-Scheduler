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
 */

/**
 * Creates wizard pages for the 'Email' action.
 * @since      1.0.0
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
                'field_id'           => 'user_roles',
                'title'              => __( 'User Roles', 'task-scheduler' ),
                'type'               => 'select',
                'is_multiple'        => true,
                'label'              => $this->___getUserRoleLabels(),
                'description'        => __( 'Select user roles to send Emails.', 'task-scheduler' )
                    . ' ' . __( 'To deselect items, press the <code>Control</code> key and click the item.', 'task-scheduler' ),
                'attributes'         => array(
                    'select' => array(
                        'style' => 'min-height: 100px;',
                    ),
                ),
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
                'field_id'           => 'from_full_name',
                'title'              => __( 'From: Full Name', 'task-scheduler' ),
                'type'               => 'text',
                'description'        => __( 'The name set to the From: part.', 'task-scheduler' ),
                'attributes'         => array(
                    'size'    => 60,
                ),
            ),
            array(
                'field_id'           => 'from_email',
                'title'              => __( 'From: Email Address', 'task-scheduler' ),
                'type'               => 'text',
                'description'        => __( 'The email address set to the From: part.', 'task-scheduler' ),
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
        /**
         * @return array
         * @since  1.6.0
         */
        private function ___getUserRoleLabels() {
            $_aRoleLabels = array();
            $_aRoles      = array_reverse( get_editable_roles() );
            foreach ( $_aRoles as $_sRole => $_aDetails ) {
                $_sNameTranslated = translate_user_role( $_aDetails[ 'name' ] );
                $_aRoleLabels[ $_sRole ] = $_sNameTranslated;
            }
            return $_aRoleLabels;
        }

    public function validateSettings( /* $aInputs, $aOldInput, $oAdminPage, $aSubmitInfo */ ) { 

        $_aParams    = func_get_args() + array(
            null, null, null, null
        );
        $aInputs     = $_aParams[ 0 ];
        $aOldInputs  = $_aParams[ 1 ];
        $oAdminPage  = $_aParams[ 2 ];
        $aSubmitInfo = $_aParams[ 3 ];

        $_bIsValid   = true;
        $_aErrors    = array();
                
        $aInputs[ 'email_addresses' ] = $this->___getEmailAddressesSanitized( $aInputs[ 'email_addresses' ] );
        
        if ( empty( $aInputs[ 'email_addresses' ] ) && empty( $aInputs[ 'user_roles' ] ) ) {
            
            // $aVariable[ 'section_id' ]['field_id']
            $_aErrors[ $this->_sSectionID ][ 'email_addresses' ] = __( 'At least one item needs to be set.', 'task-scheduler' );
            $_bIsValid = false;            
            
        }
                
        // Make it numeric array
        $aInputs[ 'email_addresses' ] = array_values( $aInputs[ 'email_addresses' ] );    // re-order numerically
        
        if ( ! $_bIsValid ) {

            // Set the error array for the input fields.
            $oAdminPage->setFieldErrors( $_aErrors );        
            $oAdminPage->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
            
        }    
    
        return $aInputs;         

    }
    
        /**
         * @since       1.2.0
         * @since       1.5.0   Uncommented the major part of the code.
         * @return      array
         */
        private function ___getEmailAddressesSanitized( array $_aEmailSets ) {

            // Drop non-email elements.
            foreach( array_filter( $_aEmailSets ) as $_iSetIndex => $_sEmailSet ) {
                $_aEmailAddresses = preg_split( "/([\n\r](\s+)?)+/", $_sEmailSet );
                foreach( $_aEmailAddresses as $_iIndex => $_sEmailAddress ) {
                    $_aEmailAddresses[ $_iIndex ] = sanitize_email( $_sEmailAddress );
                    if ( ! filter_var( $_sEmailAddress, FILTER_VALIDATE_EMAIL ) ) {
                        unset( $_aEmailAddresses[ $_iIndex ] );
                    }
                }
                $_aEmailSets[ $_iSetIndex ] = implode( PHP_EOL, $_aEmailAddresses );
            }
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

        $_aRoles     = get_editable_roles();
        $_aSelected  = TaskScheduler_Utility::getElementAsArray( $_aOptions, array( 'user_roles' ) );
        $_aOutputs[] = "<h4>" . __( 'User Roles', 'task-scheduler' ) . ":</h4>";
        $_aOutputs[] = "<ul class='task-scheduler-admin-list'>";
        foreach( $_aSelected as $_sUserRole ) {
            $_aOutputs[] = "<li>" . translate_user_role( $_aRoles[ $_sUserRole ][ 'name' ] ) . "</li>";
        }
        if ( empty( $_aSelected ) ) {
            $_aOutputs[] = "<li>" . __( 'Unselected', 'task-scheduler' ) . "</li>";
        }
        $_aOutputs[] = '</ul>';
        
        $_aOutputs[] = "<h4>" . __( 'Email Subject', 'task-scheduler' ) . ":</h4>";
        $_aOutputs[] = "<input type='text' readonly='readonly' value='" . esc_attr( $_aOptions[ 'email_title' ] ) . "' style='width:80%;' />";
        
        $_aOutputs[] = "<h4>" . __( 'Email Message', 'task-scheduler' ) . ":</h4>";
        $_aOutputs[] = "<textarea readonly='readonly' style='width:80%;'>" . esc_textarea( $_aOptions[ 'email_message' ] ) . "</textarea>";

        $_aOutputs[] = "<h4>" . __( 'From: Full Name', 'task-scheduler' ) . ":</h4>";
        $_aOutputs[] = "<input type='text' readonly='readonly' value='" . esc_attr( $_aOptions[ 'from_full_name' ] ) . "' style='width:80%;' />";
        $_aOutputs[] = "<h4>" . __( 'From: Email Address', 'task-scheduler' ) . ":</h4>";
        $_aOutputs[] = "<input type='text' readonly='readonly' value='" . esc_attr( $_aOptions[ 'from_email' ] ) . "' style='width:80%;' />";

        return implode( '', $_aOutputs );
                
    }    
    
}