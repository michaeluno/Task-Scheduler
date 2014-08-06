<?php
/**
 * Creates wizard pages for the 'Delete Posts' action.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

final class TaskScheduler_Action_PostDeleter_Wizard extends TaskScheduler_Wizard_Action_Base {

	/**
	 * User constructor.
	 */
	public function construct() {}

	/**
	 * Returns the field definition arrays.
	 * 
	 * @remark		The field definition structure must follows the specification of Admin Page Framework v3.
	 */ 
	public function getFields() {

		return array(
			array(	
				'field_id'			=>	'post_type_of_deleting_posts',
				'title'				=>	__( 'Post Type', 'task-scheduler' ),
				'type'				=>	'select',
				'label'				=>	TaskScheduler_WPUtility::getRegisteredPostTypeLabels(),
			),			
			array(	
				'field_id'			=>	'post_statuses_of_deleting_posts',
				'title'				=>	__( 'Post Statuses', 'task-scheduler' ),
				'type'				=>	'checkbox',
				'label'				=>	TaskScheduler_WPUtility::getRegisteredPostStatusLabels(),
				'default'			=>	array( 'trash' => 1 ),				
			),	
		);
		
	}	

	public function validateSettings( $aInput, $aOldInput, $oAdminPage ) { 

		$_bIsValid = true;
		$_aErrors = array();	

		$_aCheckedPostStatuses = isset( $aInput['post_statuses_of_deleting_posts'] ) ? $aInput['post_statuses_of_deleting_posts'] : array();
		$_aCheckedPostStatuses = array_filter( $_aCheckedPostStatuses );	// drop unchecked items.
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
	
	
}