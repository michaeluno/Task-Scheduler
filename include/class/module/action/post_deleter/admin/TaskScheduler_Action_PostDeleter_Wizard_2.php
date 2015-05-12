<?php
/**
 * Creates wizard pages for the 'Delete Posts' action.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2015, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

final class TaskScheduler_Action_PostDeleter_Wizard_2 extends TaskScheduler_Wizard_Action_Base {

    /**
     * Returns the field definition arrays.
     * 
     * @remark        The field definition structure must follows the specification of Admin Page Framework v3.
     */ 
    public function getFields() {

        $_aWizardOptions = apply_filters( 'task_scheduler_admin_filter_get_wizard_options', array(), $this->sSlug );
        return array(
            array(    
                'field_id'          => 'post_type_label_of_deleting_posts',
                'title'             => __( 'Post Type', 'task-scheduler' ),
                'type'              => 'text',
                'attributes'        => array(
                    'readonly' => 'readonly',
                    'name'     => '',    // dummy
                ),
                'value'             => TaskScheduler_WPUtility::getPostTypeLabel( 
                    isset( $_aWizardOptions['post_type_of_deleting_posts'] ) 
                        ? $_aWizardOptions['post_type_of_deleting_posts'] 
                        : null 
                ),
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
                'field_id'            => 'taxonomy_of_deleting_posts',
                'title'               => __( 'Taxonomy', 'task-scheduler' ),
                'type'                => 'select',
                'label'               => array(
                        -1    => __( 'All Posts', 'task-scheduler' ),
                    )
                    + TaskScheduler_WPUtility::getTaxonomiesByPostTypeSlug( 
                        isset( $_aWizardOptions['post_type_of_deleting_posts'] )
                            ? $_aWizardOptions['post_type_of_deleting_posts'] 
                            : null 
                    ),
                'description'   => __( 'Select a taxonomy that deleting posts are associated with.', 'task-scheduler' ),
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
    
        // The Admin Page Framework inserts some keys into the $aInput array that it thinks 
        // the keys may be of higher capability users. So here ensure these keys won't be sent.
        unset( 
            $aInput['post_statuses_of_deleting_posts'],
            $aInput['post_type_label_of_deleting_posts']
        );
        
        return $aInput;         

    }
    
}