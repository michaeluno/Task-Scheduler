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

final class TaskScheduler_Action_PostDeleter_Wizard extends TaskScheduler_Wizard_Action_Base {

    /**
     * User constructor.
     */
    public function construct() {
        
        add_filter( 
            'field_' . 'TaskScheduler_MetaBox_Action_' . 'term_ids_of_deleting_posts', 
            array( $this, '_replyToModifyFieldOutput_term_ids_of_deleting_posts' ),
            10,
            2
        );
        add_filter( 
            'field_' . 'TaskScheduler_MetaBox_Action_' . 'taxonomy_of_deleting_posts', 
            array( $this, '_replyToModifyFieldOutput_taxonomy_of_deleting_posts' ),
            10,
            2
        );        
        add_filter( 
            'field_' . 'TaskScheduler_MetaBox_Action_' . 'post_statuses_of_deleting_posts', 
            array( $this, '_replyToModifyFieldOutput_post_statuses_of_deleting_posts' ),
            10,
            2
        );       
        
        add_filter(
            'field_definition_TaskScheduler_MetaBox_Action',
            array( $this, '_replyToModifyFieldOrder' ),
            30 // low priority to let the `TaskScheduler_MetaBox_Action` class process the filter.
        );

    }

    /**
     * Returns the field definition arrays.
     * 
     * @remark        The field definition structure must follows the specification of Admin Page Framework v3.
     */ 
    public function getFields() {

        return array(
            array(    
                'field_id'      => 'post_type_of_deleting_posts',
                'title'         => __( 'Post Type', 'task-scheduler' ),
                'type'          => 'select',
                'label'         => TaskScheduler_WPUtility::getRegisteredPostTypeLabels(),
                'description'   => __( 'Select which post type of posts should be deleted.', 'task-scheduler' ),
            ),                 
            array(             
                'field_id'      => 'post_statuses_of_deleting_posts',
                'title'         => __( 'Post Statuses', 'task-scheduler' ),
                'type'          => 'checkbox',
                'label'         => TaskScheduler_WPUtility::getRegisteredPostStatusLabels(),
                'select_all_button'  => true,
                'select_none_button' => true,
                'default'       => array( 'trash' => 1 ),                
                'description'   => __( 'Select post statuses with witch the posts get deleted.', 'task-scheduler' ),
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
    
        $_bIsValid = true;
        $_aErrors = array();    

        $_aCheckedPostStatuses = isset( $aInput[ 'post_statuses_of_deleting_posts' ] ) 
            ? $aInput[ 'post_statuses_of_deleting_posts' ] 
            : array();
            
        $_aCheckedPostStatuses = array_filter( $_aCheckedPostStatuses );    // drop unchecked items.
        if ( empty( $_aCheckedPostStatuses ) ) {

            // $aVariable[ 'sectioni_id' ]['field_id']
            $_aErrors[ $this->_sSectionID ][ 'post_statuses_of_deleting_posts' ] = __( 'At least one item needs to be checked.', 'task-scheduler' );
            $_bIsValid = false;
        
        }
        
        if ( ! $_bIsValid ) {

            // Set the error array for the input fields.
            $oAdminPage->setFieldErrors( $_aErrors );        
            $oAdminPage->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
            
        }            

        return $aInput;         

    }
           
    /**
     * Modifies the `term_ids_of_deleting_posts` field output.
     * @since       1.0.1
     * @return      string
     */
    public function _replyToModifyFieldOutput_term_ids_of_deleting_posts( $sOutput, $aOptions ) {
        $_aActionArguments = $aOptions[ '_caller_object' ]->oTask->task_scheduler_action_delete_post;
        return $this->_getSelectedTermList( 
            array_keys( array_filter( $_aActionArguments[ 'term_ids_of_deleting_posts' ] ) ),
            $_aActionArguments[ 'taxonomy_of_deleting_posts' ]
        );       
    }
    /**
     * Modifies the `taxonomy_of_deleting_posts` field output.
     * @since       1.0.1
     * @return      string
     */
    public function _replyToModifyFieldOutput_taxonomy_of_deleting_posts( $sOutput, $aOptions ) {
        $_aActionArguments = $aOptions[ '_caller_object' ]->oTask->task_scheduler_action_delete_post;
        return TaskScheduler_WPUtility::getTaxonomyNameBySlug( 
            $_aActionArguments[ 'taxonomy_of_deleting_posts' ] 
        );        
    }    
    /**
     * Modifies the `post_statuses_of_deleting_posts` field output.
     * @since       1.0.1
     * @return      string
     */
    public function _replyToModifyFieldOutput_post_statuses_of_deleting_posts( $sOutput, $aOptions ) {
        $_aActionArguments = $aOptions[ '_caller_object' ]->oTask->task_scheduler_action_delete_post;
        return $this->_getPostStatusLabelsList( 
            array_keys( 
                array_filter( 
                    $_aActionArguments[ 'post_statuses_of_deleting_posts' ] 
                ) 
            ) 
        );
    }
        /**
         * 
         * @since       1.0.1
         * @return      string  
         */
        private function _getPostStatusLabelsList( array $aPostStatusSlugs ) {
            $_aPostStatusList = array();
            foreach ( $aPostStatusSlugs as $_sPostStatusSlug ) {
                $_aPostStatusList[] = "<li>"
                        . TaskScheduler_WPUtility::getPostStatusLabelBySlug( $_sPostStatusSlug )
                    . "</li>"
                    ;
            }
            return "<ul class='task-scheduler-post_deleter-module-list'>"
                . implode( '', $_aPostStatusList )
                . "</ul>"
                ;
        }
        /**
         * 
         * @since       1.0.1
         * @return      string      The list of readable term labels.
         */
        private function _getSelectedTermList( array $aTermIDs, $sTaxonomySlug ) {
            $_aTermLabelList = array();
            foreach ( $aTermIDs as $_iTermID ) {
                $_aTermLabelList[] = "<li>"
                        . TaskScheduler_WPUtility::getTermName( $_iTermID, $sTaxonomySlug )
                    . "</li>";                        
            }
            return "<ul class='task-scheduler-post_deleter-module-list'>"
                    . implode( '', $_aTermLabelList )
                . "</ul>";
        }        
    
    /**
     * 
     * @callback    filter      field_definition_TaskScheduler_MetaBox_Action
     * @return  array
     */
    public function _replyToModifyFieldOrder( $aFields ) {

        if ( ! isset( $aFields[ '_default' ] ) || ! is_array( $aFields[ '_default' ] ) ) {
            return $aFields;
        }
   
        $_aFields = $aFields[ '_default' ];
        $_aFields = $this->_resetElement( $_aFields, 'post_type_of_deleting_posts' );
        $_aFields = $this->_resetElement( $_aFields, 'post_statuses_of_deleting_posts' );
        $_aFields = $this->_resetElement( $_aFields, 'taxonomy_of_deleting_posts' );
        $_aFields = $this->_resetElement( $_aFields, 'term_ids_of_deleting_posts' );
        $_aFields = $this->_resetElement( $_aFields, 'number_of_posts_to_delete_per_routine' );
        $aFields[ '_default' ] = $_aFields;
        return $aFields;
        
    }
        /**
         * Put the specified element into the last of the array.
         * @return  array
         */
        private function _resetElement( array $aArray, $sKey ) {
            
            if ( ! isset( $aArray[ $sKey ] ) ) {
                return $aArray;
            }
            
            $_mTemp = $aArray[ $sKey ];
            unset( $aArray[ $sKey ] );
            $aArray[ $sKey ] = $_mTemp;
            return $aArray;
            
        }
    
}