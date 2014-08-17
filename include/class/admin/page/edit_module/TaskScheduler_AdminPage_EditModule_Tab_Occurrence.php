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

abstract class TaskScheduler_AdminPage_EditModule_Tab_Occurrence extends TaskScheduler_AdminPage_EditModule_Tab_Action {

    protected function _defineInPageTabs() {
                    
        $this->addInPageTabs(
            TaskScheduler_Registry::AdminPage_EditModule,    // the target page slug                    
            array(    // the landing page of the editing page of occurrence module options.
                'tab_slug'            =>    'edit_occurrence',    
                'title'                =>    __( 'Edit Occurrence', 'task-scheduler' ),
                'order'                =>    1,    // this must be the 'default' tab
                'show_in_page_tab'    =>    false,
            )
        );
        
        parent::_defineInPageTabs();
        
    }
    
    protected function _defineForm() {
    
        $this->addSettingSections(
            TaskScheduler_Registry::AdminPage_EditModule,    // the target page slug
            array(
                'section_id'    =>    'edit_occurrence',
                'tab_slug'        =>    'edit_occurrence',
                'title'            =>    __( 'Occurrence', 'task-scheduler' ),
            )            
        );        
            
        $this->addSettingFields(
            'edit_occurrence',    // the target section ID        
            array(
                'field_id'            =>    'transient_key',
                'type'                =>    'text',                
                'hidden'            =>    true,
                'value'                =>    $this->_sTransientKey,
            ),            
            array(    
                'field_id'                =>    'occurrence',
                'title'                    =>    __( 'Occurrence', 'task-scheduler' ),
                'type'                    =>    'radio',
                'label_min_width'        =>    '100%',                
                'label'                    =>    array(),
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
     * Defines the 'occurrence' field of the 'edit_occurrence' section.
     */
    public function field_definition_TaskScheduler_AdminPage_EditModule_edit_occurrence_occurrence( $aField ) {
    
        $aField['label']    = apply_filters( 'task_scheduler_admin_filter_field_labels_wizard_occurrence', $aField['label'] );    
        foreach( $aField['label'] as $_sSlug => $_sLabel ) {
            $_sDescription = apply_filters( "task_scheduler_filter_description_occurrence_{$_sSlug}", '' );
            if ( $_sDescription ) {
                $aField['label'][ $_sSlug ] = $_sLabel . ' - ' . "<span class='description'>" . $_sDescription . "</span>";
            }            
        }        
        return $aField;
        
    }    
    
    /**
     * The validation callback method for the 'edit_occurrence' form section.
     * 
     * @since    1.0.0
     */
    public function validation_TaskScheduler_AdminPage_EditModule_edit_occurrence( $aInput, $aOldInput ) {    // validation_{instantiated class name}_{section ID}
                
        // The transient key must be embedded in the url.
        $_sRedirectURL = add_query_arg( array( 'transient_key'    =>    $aInput['transient_key'], ));    
        $_sRedirectURL = add_query_arg(
            array(
                'transient_key'    =>    $aInput['transient_key'],
            ),
            apply_filters( 'task_scheduler_admin_filter_wizard_occurrence_redirect_url_' . $aInput['occurrence'], $_sRedirectURL, $aInput )
        );
        
        // Save the wizard options.
        $_sPreviousURLKey = remove_query_arg( array( 'transient_key', 'settings-notice', 'settings-updated' ), $_sRedirectURL );
        $aInput['previous_urls'] = $this->_getWizardOptions( 'previous_urls' );
        $aInput['previous_urls'] = is_array( $aInput['previous_urls'] ) ? $aInput['previous_urls'] : array();
        $aInput['previous_urls'][ $_sPreviousURLKey ] = add_query_arg( array( 'transient_key'    =>    $aInput['transient_key'], ) );
        
        $aInput['occurrence_label'] = apply_filters( "task_scheduler_filter_label_occurrence_" . $aInput['occurrence'], $aInput['occurrence'] );

        // This will be checked when the meta data gets updated in the destination tab.
        $aInput['_update_next_schedule'] = true;
        
        $this->_saveWizardOptions( $aInput['transient_key'], $aInput );
    
        // Go to the next page.
        exit( wp_safe_redirect( $_sRedirectURL ) );
        
    }
    
}