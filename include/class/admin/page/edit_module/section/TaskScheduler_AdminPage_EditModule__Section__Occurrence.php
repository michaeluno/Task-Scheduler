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
 * Defines the section.
 * 
 * @since   1.4.0
 */
class TaskScheduler_AdminPage_EditModule__Section__Occurrence extends TaskScheduler_AdminPage_Section_Base {

    /**
     * 
     * 
     * @since           1.4.0
     */ 
    public function addFields( $oFactory, $sSectionID ) {
            
        $oFactory->addSettingFields(
            $sSectionID,    // the target section ID
            array(
                'field_id'          => 'transient_key',
                'type'              => 'text',                
                'hidden'            => true,
                'value'             => $oFactory->sTransientKey,
            ),            
            array(    
                'field_id'          => 'occurrence',
                'title'             => __( 'Occurrence', 'task-scheduler' ),
                'type'              => 'radio',
                'label_min_width'   => '100%',                
                'label'             => array(),
            ),            
            array(    
                'field_id'          => 'prevnext',
                'type'              => 'submit',
                'label'             => __( 'Next', 'task-scheduler' ),
                'label_min_width'   => '0px',
                'attributes'        => array(
                    'field'    =>    array(
                        'style'    => 'float:right; clear:none; display: inline;',
                    ),
                ),
                array(
                    'value'          => __( 'Back', 'task-scheduler' ),
                    'href'           => TaskScheduler_PluginUtility::getEditTaskPageURL(),
                    'attributes'     => array(
                        'class'    => 'button secondary ',
                    ),                        
                ),                 
            )    
        );        
        
        $_aFieldIDsToRedefine = array(
            'occurrence',
        );
        foreach( $_aFieldIDsToRedefine as $_sFieldID ) {            
            add_filter( 
                'field_definition_' .  $oFactory->oProp->sClassName . '_' . $sSectionID . '_' . $_sFieldID,
                array( $this, '_defineField_' . $_sFieldID )
            );
        }

    }
        
    /**
     * Defines the 'occurrence' field of the 'edit_occurrence' section.
     */
    public function _defineField_occurrence( $aField ) {
    
        $aField['label']   = apply_filters( 'task_scheduler_admin_filter_field_labels_wizard_occurrence', $aField['label'] );    
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
     * @since       1.0.0
     * @callback    filter      validation_{instantiated class name}_{section ID}
     */
    public function validate( $aInput, $aOldInput, $oAdminPage, $aSubmitInfo ) {

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
        $aInput['previous_urls'] = $oAdminPage->getWizardOptions( 'previous_urls' );
        $aInput['previous_urls'] = is_array( $aInput['previous_urls'] ) ? $aInput['previous_urls'] : array();
        $aInput['previous_urls'][ $_sPreviousURLKey ] = add_query_arg( array( 'transient_key'    =>    $aInput['transient_key'], ) );
        
        $aInput['occurrence_label'] = apply_filters( "task_scheduler_filter_label_occurrence_" . $aInput['occurrence'], $aInput['occurrence'] );

        // This will be checked when the meta data gets updated in the destination tab.
        $aInput['_update_next_schedule'] = true;
        
        $oAdminPage->saveWizardOptions( $aInput['transient_key'], $aInput );
    
        // Go to the next page.
        exit( wp_safe_redirect( $_sRedirectURL ) );
        
    }

    
}
