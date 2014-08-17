<?php
/**
 * One of the base classes of the editing module options pages.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_AdminPage_EditModule_Tab_Action extends TaskScheduler_AdminPage_EditModule_Tab_UpdateModuleOptions {

    protected function _defineInPageTabs() {
                    
        $this->addInPageTabs(
            TaskScheduler_Registry::AdminPage_EditModule,    // the target page slug                    
            array(    // the landing page of the editing page of action module options.
                'tab_slug'            =>    'edit_action',    
                'title'                =>    __( 'Edit Action', 'task-scheduler' ),
                'show_in_page_tab'    =>    false,
            )    
        );
                
        parent::_defineInPageTabs();
        
    }
    
    protected function _defineForm() {

        $this->addSettingSections(
            TaskScheduler_Registry::AdminPage_EditModule,    // the target page slug
            array(
                'section_id'    =>    'edit_action',
                'tab_slug'        =>    'edit_action',
                'title'            =>    __( 'Action', 'task-scheduler' ),
            )            
        );        
            
        // Here the 'argument' field is not set here because it is editable in the post edition page(post.php).
        $this->addSettingFields(
            'edit_action',    // the target section ID        
            array(
                'field_id'            =>    'transient_key',
                'type'                =>    'text',                
                'hidden'            =>    true,
                'value'                =>    $this->_sTransientKey,
            ),            
            array(
                'field_id'            =>    'routine_action',
                'title'                =>    __( 'Action', 'task-scheduler' ),
                'type'                =>    'revealer',
                'label'                =>    array(),    // will be redefined in the 'field_definition_{...}' callback.
                // 'description'        =>    __( 'Select the one to perform when the time comes', 'task-scheduler' ),
            ),
            array(
                'field_id'            =>    'custom_action',
                'title'                =>    __( 'Custom Action', 'task-scheduler' ),
                'type'                =>    'text',
                'description'        =>    __( 'If none of the action you want to execute is listed above, specify the action name here.', 'task-scheduler' ),
            ),                
            array(    
                'field_id'            =>    'submit',
                'type'                =>    'submit',
                'label'                =>    __( 'Next', 'task-scheduler' ),
                'label_min_width'    =>    0,
                'attributes'        =>    array(
                    'field'    =>    array(
                        'style'    =>    'float:right; clear:none; display: inline;',
                    ),
                ),    
                 array(
                    'value'            =>    __( 'Back', 'task-scheduler' ),
                    'href'            =>    TaskScheduler_PluginUtility::getEditTaskPageURL(),
                    'attributes'    =>    array(
                        'class'    =>    'button secondary ',
                    ),                        
                ),                 
            )    
        );    
        
        parent::_defineForm();
        
    }
    
    /**
     * Redefines the 'routine_action' field of the 'edit_action' section.
     * 
     * If the saved action slug is not listed in the label array, it forces to select -1 to let it set a custom action slug.
     */     
    public function field_definition_TaskScheduler_AdminPage_EditModule_edit_action_routine_action( $aField ) {
        
        return $this->_getRoutineActionField( $aField );
        
        $_sRoutineActionSlug    = $this->_getWizardOptions( 'routine_action' );
        $aField['label']        = apply_filters( 
            'task_scheduler_admin_filter_field_labels_wizard_action', 
            array(
                -1    =>    '--- ' . __( 'Select Action', 'task-scheduler' ) . ' ---',
            ) 
        );
        if ( ! array_key_exists ( $_sRoutineActionSlug, $aField['label'] ) ) {
            $aField['value']    = -1;
        }
        return $aField;
        
    }    
    
    /**
     * Redefines the 'custom_action' field of the 'edit_action' section.
     */     
    public function field_definition_TaskScheduler_AdminPage_EditModule_edit_action_custom_action( $aField ) {
        
        $_sRoutineActionSlug    = $this->_getWizardOptions( 'routine_action' );
        if ( ! array_key_exists ( $_sRoutineActionSlug, apply_filters( 'task_scheduler_admin_filter_field_labels_wizard_action', array( -1 => '_dummy_value' ) ) ) ) {
            $aField['value']    = $_sRoutineActionSlug;
        }        
        return $aField;
        
    }    
    
    /**
     * The validation callback method for the 'edit_action' form section.
     * 
     * @since    1.0.0
     */
    public function validation_TaskScheduler_AdminPage_EditModule_edit_action( $aInput, $aOldInput ) {    // validation_{instantiated class name}_{section ID}

        $_bIsValid = true;
        $_aErrors = array();
        
        // Do validation checks.
        if ( '-1' === ( string ) $aInput['routine_action'] && '' == trim( $aInput['custom_action'] ) ) {
            
            // $aVariable[ 'sectioni_id' ]['field_id']
            $_aErrors['edit_action'] = __( 'At least an action must be set.', 'task-scheduler' );            
            $_bIsValid = false;
            
        }
        // Only one action is allowed because the next page is redirected based on the action and the page is only capable of displaying options for one action.
        if ( '-1' !== ( string ) $aInput['routine_action'] && trim( $aInput['custom_action'] ) ) {
            
            // $aVariable[ 'sectioni_id' ]['field_id']
            $_aErrors['edit_action'] = __( 'Only one action can be set.', 'task-scheduler' );            
            $_bIsValid = false;
            unset( $aInput['custom_action'] );    // remove one of these and leave the other.
            
        }                
        
        if ( ! $_bIsValid ) {

            // Set the error array for the input fields.
            $this->setFieldErrors( $_aErrors );        
            $this->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
            $this->_saveWizardOptions( $aInput['transient_key'], $aInput );
            return array();
            
        }
        
        // Drop the 'custom_action' key and unity the values into the 'routine_action' key.
        $aInput['routine_action'] = '-1' !== ( string ) $aInput['routine_action'] ? $aInput['routine_action'] : $aInput['custom_action'];

        // Remove the prefix of the selector of the action slug.
        $aInput['routine_action'] = preg_replace( '/^#description-/', '', $aInput['routine_action'] );        
        
        // Modify the wizard options.
        $_aWizardOptions = $aInput;            
        $_aWizardOptions['action_label'] = apply_filters( "task_scheduler_filter_label_action_" . $aInput['routine_action'], $aInput['routine_action'] );
            
        // Get the next page url
        $_sNextPageURL = $this->_getNextPageURL( $_aWizardOptions );
        $_sNextURLURLKey = remove_query_arg( array( 'transient_key', 'settings-notice', 'settings-updated' ), $_sNextPageURL );
        $_aWizardOptions['previous_urls'] = $this->_getWizardOptions( 'previous_urls' );
        $_aWizardOptions['previous_urls'] = is_array( $_aWizardOptions['previous_urls'] ) ? $_aWizardOptions['previous_urls'] : array();
        $_aWizardOptions['previous_urls'][ $_sNextURLURLKey ] = add_query_arg( array( 'transient_key'    =>    $aInput['transient_key'], ) );
        
        $_aSavedValue = $this->_saveWizardOptions( $_aWizardOptions['transient_key'], $_aWizardOptions );
        
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
                    'transient_key'    =>    $aWizardOptions['transient_key'],
                    'tab'            =>    'update_module',    // if the applying filters below does not take effect, it goes to the create task page.
                )
            );    
            // The transient key must be embedded in the url.
            $_sRedirectURL = add_query_arg( 
                array( 'transient_key'    =>    $aWizardOptions['transient_key'], ),
                apply_filters( "task_scheduler_admin_filter_wizard_action_redirect_url_{$_sActionHook}", $_sRedirectURL, $aWizardOptions )
            );
            return $_sRedirectURL;            
            
        }

}