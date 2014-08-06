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

final class TaskScheduler_Action_PostDeleter_Wizard_3 extends TaskScheduler_Wizard_Action_Base {

	/**
	 * Returns the field definition arrays.
	 * 
	 * @remark		The field definition structure must follows the specification of Admin Page Framework v3.
	 */ 
	public function getFields() {

		$_aWizardOptions = apply_filters( 'task_scheduler_admin_filter_get_wizard_options', array(), $this->sSlug );
		$_bIsTaxonomySet = isset( $_aWizardOptions['taxonomy_of_deleting_posts'] ) && -1 !== $_aWizardOptions['taxonomy_of_deleting_posts'] && '-1' !== $_aWizardOptions['taxonomy_of_deleting_posts'];
		return array(
			array(	
				'field_id'			=>	'post_type_label_of_deleting_posts',
				'title'				=>	__( 'Post Type', 'task-scheduler' ),
				'type'				=>	'text',
				'attributes'		=>	array(
					'readonly'	=>	'ReadOnly',
					'name'		=>	'',	// dummy
				),
				'value'				=>	TaskScheduler_WPUtility::getPostTypeLabel( isset( $_aWizardOptions['post_type_of_deleting_posts'] ) ? $_aWizardOptions['post_type_of_deleting_posts'] : null ),
			),			
			array(	
				'field_id'			=>	'post_statuses_of_deleting_posts',
				'title'				=>	__( 'Post Statuses', 'task-scheduler' ),
				'type'				=>	'checkbox',
				'label'				=>	TaskScheduler_WPUtility::getRegisteredPostStatusLabels(),
				'attributes'		=>	array(
					'disabled'	=>	'Disabled',
					'name'		=>	'',	// dummy
				),
			),	
			array(
				'field_id'			=>	'taxonomy_label_of_deleting_posts',
				'title'				=>	__( 'Taxonomy', 'task-scheduler' ),
				'type'				=>	'text',
				'attributes'		=>	array(
					'readonly'	=>	'ReadOnly',
					'name'		=>	'',	// dummy
				),
				'value'				=>	TaskScheduler_WPUtility::getTaxonomiyLabelBySlug( isset( $_aWizardOptions['taxonomy_of_deleting_posts'] ) ? $_aWizardOptions['taxonomy_of_deleting_posts'] : null ),
				'if'				=>	$_bIsTaxonomySet,
			),	
			array(
				'field_id'			=>	'term_ids_of_deleting_posts',
				'title'				=>	__( 'Terms', 'task-scheduler' ),
				'type'				=>	'taxonomy',
				'taxonomy_slugs'	=>	isset( $_aWizardOptions['taxonomy_of_deleting_posts'] ) ? $_aWizardOptions['taxonomy_of_deleting_posts'] : array(),
				'if'				=>	$_bIsTaxonomySet,
							 
			),	
		);
		
	}	
		
	public function validateSettings( $aInput, $aOldInput, $oAdminPage ) { 

		$_bIsValid = true;
		$_aErrors = array();	
	
		// Ensure to remove unnecessary elements.
		unset( 
			$aInput['post_type_label_of_deleting_posts'],
			$aInput['post_statuses_of_deleting_posts'],
			$aInput['taxonomy_label_of_deleting_posts']
		);
		
		$_aCheckedPostStatuses = isset( $aInput['term_ids_of_deleting_posts'] ) ? $aInput['term_ids_of_deleting_posts'] : array();
		$_aCheckedPostStatuses = array_filter( $_aCheckedPostStatuses );	// drop unchecked items.
		if ( isset( $aInput['term_ids_of_deleting_posts'] ) && empty( $_aCheckedPostStatuses ) ) {

			// $aVariable[ 'sectioni_id' ]['field_id']
			$_aErrors[ $this->_sSectionID ][ 'term_ids_of_deleting_posts' ] = __( 'At least one item needs to be checked.', 'task-scheduler' );
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