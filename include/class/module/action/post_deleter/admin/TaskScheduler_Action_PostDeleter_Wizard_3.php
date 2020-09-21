<?php
/**
 * Creates wizard pages for the 'Delete Posts' action.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

final class TaskScheduler_Action_PostDeleter_Wizard_3 extends TaskScheduler_Wizard_Action_Base {

    /**
     * Returns the field definition arrays.
     * 
     * @remark        The field definition structure must follows the specification of Admin Page Framework v3.
     */ 
    public function getFields() {

        $_aWizardOptions = apply_filters( 'task_scheduler_admin_filter_get_wizard_options', array(), $this->sSlug );
        $_bIsTaxonomySet = isset( $_aWizardOptions['taxonomy_of_deleting_posts'] ) && -1 !== $_aWizardOptions['taxonomy_of_deleting_posts'] && '-1' !== $_aWizardOptions['taxonomy_of_deleting_posts'];
        return array(
            array(    
                'field_id'          => 'post_type_label_of_deleting_posts',
                'title'             => __( 'Post Type', 'task-scheduler' ),
                'type'              => 'text',
                'attributes'        => array(
                    'readonly' => 'readonly',
                    'name'     => '',    // dummy
                ),
                'value'             => TaskScheduler_WPUtility::getPostTypeLabel( isset( $_aWizardOptions['post_type_of_deleting_posts'] ) ? $_aWizardOptions['post_type_of_deleting_posts'] : null ),
            ),            
            array(    
                'field_id'          => 'post_statuses_of_deleting_posts',
                'title'             => __( 'Post Statuses', 'task-scheduler' ),
                'type'              => 'checkbox',
                'label'             => TaskScheduler_WPUtility::getRegisteredPostStatusLabels(),
                'attributes'        => array(
                    'disabled' => 'disabled',
                    'name'     => '',    // dummy
                ),
            ),    
            array(
                'field_id'          => 'taxonomy_label_of_deleting_posts',
                'title'             => __( 'Taxonomy', 'task-scheduler' ),
                'type'              => 'text',
                'attributes'        => array(
                    'readonly' => 'readonly',
                    'name'     => '',    // dummy
                ),
                'value'             => TaskScheduler_WPUtility::getTaxonomiyLabelBySlug( isset( $_aWizardOptions['taxonomy_of_deleting_posts'] ) ? $_aWizardOptions['taxonomy_of_deleting_posts'] : null ),
                'if'                => $_bIsTaxonomySet,
            ),    
            array(
                'field_id'          => 'term_ids_of_deleting_posts',
                'title'             => __( 'Terms', 'task-scheduler' ),
                'type'              => 'taxonomy',
                'width'             => '400px',
                'taxonomy_slugs'    => isset( $_aWizardOptions[ 'taxonomy_of_deleting_posts' ] ) 
                    ? $_aWizardOptions['taxonomy_of_deleting_posts']
                    : array(),
                'if'                => $_bIsTaxonomySet,
            ),    
            array(    
                'field_id'          => 'number_of_posts_to_delete_per_routine',
                'title'             => __( 'Number of Posts to Process per Routine', 'task-scheduler' ),
                'type'              => 'number',
                'default'           => 20,
                'description'       => __( 'This determines how many posts will be processed per a page load. Set a smaller number if the task gets hung.', 'task-scheduler' ),
                'attributes'        => array(
                    'min'    => 1,
                    'step'   => 1,
                ),
            ),   
            array(    
                'field_id'          => 'elapsed_time',
                'title'             => __( 'Elapsed Time', 'task-scheduler' ),
                'type'              => 'size',
                'units'             => array( 
                    'hours'   => __( 'hour(s)', 'task-scheduler' ),
                    'days'    => __( 'day(s)', 'task-scheduler' ),         
                ),     
                'default' => array(  
                    'size'      => 0, 
                    'unit'      => 'day'  
                ),
                'description'       => __( 'Matched posts will be deleted only if they are older than the set time.', 'task-scheduler' )
                    . ' ' . __( 'Set <code>0</code> to simply delete matched posts with any date.', 'task-scheduler' ),
                'attributes'        => array(
                    'step'   => 1,
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
    
        // Ensure to remove unnecessary elements.
        unset( 
            $aInput['post_type_label_of_deleting_posts'],
            $aInput['post_statuses_of_deleting_posts'],
            $aInput['taxonomy_label_of_deleting_posts']
        );
        
        $_aCheckedTerms = isset( $aInput['term_ids_of_deleting_posts'] ) 
            ? $aInput['term_ids_of_deleting_posts'] 
            : array();
        $_aCheckedTerms = array_filter( $_aCheckedTerms );    // drop unchecked items.
        if ( isset( $aInput['term_ids_of_deleting_posts'] ) && empty( $_aCheckedTerms ) ) {

            // $aVariable[ 'sectioni_id' ]['field_id']
            $_aErrors[ $this->_sSectionID ][ 'term_ids_of_deleting_posts' ] = __( 'At least one item needs to be checked.', 'task-scheduler' );
            $_bIsValid = false;
        
        }
        
        if ( ! $_bIsValid ) {

            // Set the error array for the input fields.
            $oAdminPage->setFieldErrors( $_aErrors );        
            $oAdminPage->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
            
        }            
        
        // Fix a number 
        $aInput['number_of_posts_to_delete_per_routine']    = $oAdminPage->oUtil->fixNumber( $aInput['number_of_posts_to_delete_per_routine'], 20, 1 );
        
        // Reverse the element order
        return array_reverse( $aInput );         

    }
    
    
}