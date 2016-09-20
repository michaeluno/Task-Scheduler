<?php
/**
 * Task Scheduler
 * 
 * Provides an enhanced task management system for WordPress.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * One of the base classes of the plugin admin page class for the wizard pages.
 * 
 * @extends     TaskScheduler_AdminPage_Wizard_Validation
 * @deprecated  1.4.0
 */
abstract class TaskScheduler_AdminPage_Wizard_Tab_Wizard extends TaskScheduler_AdminPage_Wizard_Validation {

    /**
     * Defines the add_new form.
     * 
     * @deprecated  1.4.0
     */
   /*  protected function _setWizardForm_AddNewTask( $sTransientKey ) {
        
        $this->addSettingSections(
            TaskScheduler_Registry::$aAdminPages[ 'add_new' ],    // the target page slug
            array(
                'tab_slug'     => 'wizard',
                'section_id'   => 'wizard',
                'title'        => __( 'Task Creation Wizard', 'task-scheduler' ),
            )            
        );        
        $this->addSettingFields(
            'wizard',    // the target section ID
            array(
                'field_id'            => 'transient_key',
                'type'                => 'text',                
                'hidden'              => true,
                'value'               => $sTransientKey,
            ),
            array(    
                'field_id'            => 'post_title',
                'title'               => __( 'Task Name', 'task-scheduler' ),
                'type'                => 'text',
            ),    
            array(    
                'field_id'            => 'post_excerpt',
                'title'               => __( 'Description', 'task-scheduler' ) . ' (' . __( 'optional', 'task-manager' ) . ')',
                'type'                => 'textarea',
            ),                
            array(    
                'field_id'            => 'occurrence',
                'title'               => __( 'Occurrence', 'task-scheduler' ),
                'type'                => 'radio',
                'label_min_width'     => '100%',
                'label'               => array(),    // redefined in the 'field_definition_{...}' callback.
            ),    
            array(    
                'field_id'            => 'submit',
                'type'                => 'submit',
                'label'               => __( 'Next', 'task-scheduler' ),
                'label_min_width'     => '0px',
                'attributes'          => array(
                    'field'    => array(
                        'style' => 'float:right; clear:none; display: inline;',
                    ),
                ),                        
            )    
        );
        
    } */
    
    /**
     * Defines the 'occurrence' field of the 'wizard' section.
     * 
     * @callback        filter          field_definition_{class name}_{section id}_{field_id}
     * @return          array
     * @deprecated      1.4.0
     */
/*     public function field_definition_TaskScheduler_AdminPage_Wizard_wizard_occurrence( $aField ) {
    
        // Set the first item as the default.
        $aField['label'] = apply_filters( 'task_scheduler_admin_filter_field_labels_wizard_occurrence', $aField['label'] );
        foreach( $aField['label'] as $_sSlug => $_sLabel ) {
            $_sDescription = apply_filters( "task_scheduler_filter_description_occurrence_{$_sSlug}", '' );
            if ( $_sDescription ) {
                $aField['label'][ $_sSlug ] = $_sLabel . ' - ' . "<span class='description'>" . $_sDescription . "</span>";
            }            
        }
        
        // Set the default value.
        $_aLabels = array_keys( $aField['label'] );    // Avoid the PHP strict standard warning            
        $aField['default'] = array_shift( $_aLabels );        
        return $aField;
        
    }     */
    
    /**
     * The validation callback method for the wizard form section.
     * 
     * @since       1.0.0
     * @callback    filter      validation_{instantiated class name}_{section ID}
     * 
     * @deprecated  1.4.0
     */
    public function _validation_TaskScheduler_AdminPage_Wizard_wizard( /* $aInput, $aOldInput, $oAdminPage, $aSubmitInfo */ ) { 

        $_aParams    = func_get_args() + array(
            null, null, null, null
        );
        $aInput      = $_aParams[ 0 ];
        $aOldInput   = $_aParams[ 1 ];
        $oAdminPage  = $_aParams[ 2 ];
        $aSubmitInfo = $_aParams[ 3 ];      
    
        $_bIsValid   = true;
        $_aErrors    = array();

        // Do validation checks.
        if ( ! $aInput['post_title'] ) {
            
            // $aVariable[ 'sectioni_id' ]['field_id']
            $_aErrors['wizard']['post_title'] = __( 'A task name must be set.', 'task-scheduler' );            
            $_bIsValid = false;
            
        }    
        if ( ! $aInput['occurrence'] ) {
            
            $_aErrors['wizard']['occurrence'] = __( 'At least one item must be selected.', 'task-scheduler' );            
            $_bIsValid = false;
        
        }

        if ( ! $_bIsValid ) {
        
            // Set the error array for the input fields.
            $this->setFieldErrors( $_aErrors );        
            $this->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
            return $aOldInput;
            
        }
                
        // The transient key must be embedded in the url.
        $_sRedirectURL = add_query_arg( array( 'transient_key'    =>    $aInput['transient_key'], ));    
        $_sRedirectURL = add_query_arg(
            array(
                'transient_key'    => $aInput['transient_key'],
            ),
            apply_filters( 'task_scheduler_admin_filter_wizard_occurrence_redirect_url_' . $aInput['occurrence'], $_sRedirectURL, $aInput )
        );
        
        // Save the wizard options.
        $_sPreviousURLKey = remove_query_arg( array( 'transient_key', 'settings-notice', 'settings-updated' ), $_sRedirectURL );
        $aInput['previous_urls'] = $this->getWizardOptions( 'previous_urls' );
        $aInput['previous_urls'] = is_array( $aInput['previous_urls'] ) ? $aInput['previous_urls'] : array();
        $aInput['previous_urls'][ $_sPreviousURLKey ] = add_query_arg( array( 'transient_key'    =>    $aInput['transient_key'], ) );
        
        $aInput['occurrence_label'] = apply_filters( "task_scheduler_filter_label_occurrence_" . $aInput['occurrence'], $aInput['occurrence'] );
        $this->saveWizardOptions( 
            $aInput['transient_key'], 
            $aInput
        );

        // Go to the next page.
        exit( wp_safe_redirect( $_sRedirectURL ) );
        
    }
    
}
