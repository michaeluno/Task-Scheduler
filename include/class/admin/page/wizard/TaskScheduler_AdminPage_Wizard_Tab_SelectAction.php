<?php
/**
 * One of the base classes of the plugin admin page class for the wizard pages.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2015, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

 /**
  * Defines the 'wizard_select_action' tab in the 'Add New' wizard admin page.
  * 
  * @filter        apply    task_scheduler_admin_filter_wizard_action_redirect_url_{action hook name}        Applies to the redirecting url after submitting the action selecting form.
  */
abstract class TaskScheduler_AdminPage_Wizard_Tab_SelectAction extends TaskScheduler_AdminPage_Wizard_Tab_Wizard {

    /**
     * Defines the add_new form.
     */
    protected function _setWizardForm_SelectAction( $sTransientKey ) {
        
        $this->addSettingSections(
            TaskScheduler_Registry::$aAdminPages[ 'add_new' ], // the target page slug
            array(
                'tab_slug'      => 'wizard_select_action',
                'section_id'    => 'wizard_select_action',
                'title'         => __( 'Select Action', 'task-scheduler' ),
            )            
        );        
        $this->addSettingFields(
            'wizard_select_action',    // the target section ID
            array(
                'field_id'          => 'transient_key',
                'type'              => 'hidden',                
                'hidden'            => true,
                'value'             => $sTransientKey,
            ),
            array(
                'field_id'          => 'routine_action',
                'title'             => __( 'Action', 'task-scheduler' ),
                'type'              => 'revealer',
                'label'             => array(),    // will be redefined in the field_definition_{...} callback
            ),
            array(
                'field_id'          => 'custom_action',
                'title'             => __( 'Custom Action', 'task-scheduler' ),
                'type'              => 'text',
                'description'       => __( 'If none of the action you want to execute is listed above, specify the action name here.', 'task-scheduler' ),
            ),            
            array(
                'field_id'          => 'argument',
                'title'             => __( 'Argument', 'task-scheduler' ) . ' (' . __( 'optional', 'task-scheduler' ) . ')',
                'type'              => 'text',
                'repeatable'        => true,
                'description'       => __( 'Set the arguments passed to the action.', 'task-scheduler' ),
            ),                        
            array(    
                'field_id'          => 'submit',
                'type'              => 'submit',
                'label'             => __( 'Next', 'task-scheduler' ),
                'value'             => __( 'Next', 'task-scheduler' ),
                'label_min_width'   => 0,
                'attributes'        => array(
                    'field'    => array(
                        'style'    => 'float:right; clear:none; display: inline;',
                    ),
                ),    
                // the sub-field, the Back button is defined below in the callback
            )    
        );
        
    }
    
    /**
     * Redefines the 'routine_action' field of the 'wizard_select_action' section.
     * 
     * If the saved action slug is not listed in the label array, it forces to select -1 to let it set a custom action slug.
     */     
    public function field_definition_TaskScheduler_AdminPage_Wizard_wizard_select_action_routine_action( $aField ) {
    
        return $this->_getRoutineActionField( $aField );
        
    }    
        /**
         * Get the redefined routine action field definition array.
         * 
         * @remark    The scope is protected because the extending Edit Module class also uses it.
         */
        protected function _getRoutineActionField( array $aField ) {
            
            $_sRoutineActionSlug = $this->_getWizardOptions( 'routine_action' );
            $aField[ 'label' ]   = apply_filters( 
                'task_scheduler_admin_filter_field_labels_wizard_action',
                array()
            );

            // Set the default value.
            $aField[ 'value' ] = array_key_exists ( $_sRoutineActionSlug, $aField['label'] )
                ? "#description-{$_sRoutineActionSlug}"
                : -1;

            // Convert the keys to the 'revealer' field type specification.
            $_aLabels = array(
                -1    =>    '--- ' . __( 'Select Action', 'task-scheduler' ) . ' ---',
            );
            $_aDescriptions = array();
            foreach( $aField['label'] as $_sSlug => $_sLabel ) {
                
                $_aLabels[ "#description-{$_sSlug}" ] = $_sLabel;
                
                // Create action description hidden elements.
                $_sDescription    = apply_filters( "task_scheduler_filter_description_action_{$_sSlug}", '' );
                if ( ! $_sDescription ) { continue; }
                $_sDisplay        = $_sSlug === $_sRoutineActionSlug
                    ? '' 
                    : 'display:none;';
                $_aDescriptions[] = "<p id='description-{$_sSlug}' style='{$_sDisplay}'>"
                    . $_sDescription
                 . "</p>";            
                 
            }
            
            $aField['label'] = $_aLabels;
            $aField['after_fieldset'] = implode( PHP_EOL, $_aDescriptions );
            return $aField;
            
        }
    
    /**
     * Redefines the 'custom_action' field of the 'wizard_select_action' section.
     */     
    public function field_definition_TaskScheduler_AdminPage_Wizard_wizard_select_action_custom_action( $aField ) {
        
        $_sRoutineActionSlug    = $this->_getWizardOptions( 'routine_action' );
        if ( ! array_key_exists ( $_sRoutineActionSlug, apply_filters( 'task_scheduler_admin_filter_field_labels_wizard_action', array( -1 => '_dummy_value' ) ) ) ) {
            $aField['value']    = $_sRoutineActionSlug;
        }        
        return $aField;
        
    }    
    
    /**
     * Redefines the 'submit' field of the 'wizard_select_action' section.
     */     
    public function field_definition_TaskScheduler_AdminPage_Wizard_wizard_select_action_submit( $aField ) {
        
        $_aPreviousUrls  = $this->_getWizardOptions( 'previous_urls' );
        $_sCurrentURLKey = remove_query_arg( array( 'transient_key', 'settings-notice', 'settings-updated' ) );
        $aField[ 0 ] = array(
            'value'         => __( 'Back', 'task-scheduler' ),
            'href'          => isset( $_aPreviousUrls[ $_sCurrentURLKey ] ) ? $_aPreviousUrls[ $_sCurrentURLKey ] : '',
            'attributes'    => array(
                'class'     => 'button secondary ',
            ),            
        );
        return $aField;
    }

    /**
     * The validation callback method for the wizard form section.
     * 
     * @since       1.0.0
     * @callback    filter      validation_{instantiated class name}_{section ID}
     */
    public function validation_TaskScheduler_AdminPage_Wizard_wizard_select_action( /* $aInput, $aOldInput, $oAdminPage, $aSubmitInfo */ ) { 

        $_aParams    = func_get_args() + array(
            null, null, null, null
        );
        $aInput      = $_aParams[ 0 ];
        $aOldInput   = $_aParams[ 1 ];
        $oAdminPage  = $_aParams[ 2 ];
        $aSubmitInfo = $_aParams[ 3 ];      
 
        $_bIsValid = true;
        $_aErrors  = array();
        
        // Do validation checks.
        if ( '-1' === ( string ) $aInput['routine_action'] && '' == trim( $aInput['custom_action'] ) ) {
            
            // $aVariable[ 'sectioni_id' ]['field_id']
            $_aErrors['wizard_select_action'] = __( 'At least an action must be set.', 'task-scheduler' );            
            $_bIsValid = false;
            
        }
        // Only one action is allowed because the next page is redirected based on the action and the page is only capable of displaying options for one action.
        if ( '-1' !== ( string ) $aInput['routine_action'] && trim( $aInput['custom_action'] ) ) {
            
            // $aVariable[ 'sectioni_id' ]['field_id']
            $_aErrors['wizard_select_action'] = __( 'Only one action can be set.', 'task-scheduler' );            
            $_bIsValid = false;
            unset( $aInput['custom_action'] );    // remove one of these and leave the other.
            
        }                

        $aInput['argument'] = array_values( $aInput['argument'] );
        
        if ( ! $_bIsValid ) {

            // Set the error array for the input fields.
            $this->setFieldErrors( $_aErrors );        
            $this->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
            $this->_saveWizardOptions( $aInput['transient_key'], $aInput );
            return array();
            
        }    
        
        if ( ! $this->_getWizardOptions( 'post_title' )  ) {
            $this->setSettingNotice( __( 'The wizard session has been expired. Please start from the beginning.', 'task-scheduler' ) );         
            exit( TaskScheduler_PluginUtility::goToAddNewPage() );
        }        
        
        // Drop the 'custom_action' key and unity the values into the 'routine_action' key.
        $aInput['routine_action'] = '-1' !== ( string ) $aInput['routine_action'] 
            ? $aInput['routine_action'] 
            : $aInput['custom_action'];
        
        // Remove the prefix of the selector of the action slug.
        $aInput['routine_action'] = preg_replace( '/^#description-/', '', $aInput['routine_action'] );
        
        // Modify the wizard options.
        $_aWizardOptions = $aInput;            
        $_aWizardOptions['action_label'] = apply_filters( 
            "task_scheduler_filter_label_action_" . $aInput['routine_action'], 
            $aInput['routine_action'] 
        );
            
        // Get the next page url
        $_sNextPageURL   = $this->_getNextPageURL( $_aWizardOptions );
        $_sNextURLURLKey = remove_query_arg( array( 'transient_key', 'settings-notice', 'settings-updated' ), $_sNextPageURL );
        $_aWizardOptions['previous_urls'] = $this->_getWizardOptions( 'previous_urls' );
        $_aWizardOptions['previous_urls'] = is_array( $_aWizardOptions['previous_urls'] ) ? $_aWizardOptions['previous_urls'] : array();
        $_aWizardOptions['previous_urls'][ $_sNextURLURLKey ] = add_query_arg( 
            array( 
                'transient_key'    => $aInput['transient_key'], 
            ) 
        );
        
        $this->_saveWizardOptions( 
            $_aWizardOptions['transient_key'], 
            $_aWizardOptions 
        );
        
        // Go to the next page
        exit( wp_safe_redirect( $_sNextPageURL ) );
        
    }
        /**
         * Retrieves the next wizard page.
         */
        private function _getNextPageURL( array $aWizardOptions ) {
            
            $_sActionHook = $aWizardOptions['routine_action'];
            $_sRedirectURL = add_query_arg( 
                array( 
                    'transient_key'  => $aWizardOptions['transient_key'],
                    'tab'            => 'wizard_create_task',    // if the applying filters below does not take effect, it goes to the create task page.
                )
            );    
            // The transient key must be embedded in the url.
            $_sRedirectURL = add_query_arg( 
                array( 'transient_key'    => $aWizardOptions['transient_key'], ),
                apply_filters( "task_scheduler_admin_filter_wizard_action_redirect_url_{$_sActionHook}", $_sRedirectURL, $aWizardOptions )
            );
            return $_sRedirectURL;            
            
        }

}