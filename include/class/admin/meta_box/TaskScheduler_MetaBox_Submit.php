<?php
/**
 * Creates a meta box for the basic options.
 * 
 * @since			1.0.0
 */
class TaskScheduler_MetaBox_Submit extends TaskScheduler_MetaBox_Base {
	
	public function start() { 
	
		parent::start();
		
		add_action( 'admin_menu', array( $this, '_replyToRemoveDefaultMetaBoxes' ) );
		
	}
		/**
		 * Removes the default submit meta box that has the Update/Create button.
		 */
		public function _replyToRemoveDefaultMetaBoxes() {
			remove_meta_box( 'submitdiv', TaskScheduler_Registry::PostType_Task, 'side' );
		}	
	
	
	/**
	 * Adds form fields for the basic options.
	 * 
	 */ 
	public function setUp() {
		
		$this->addSettingFields(
			array(
				'field_id'		=>	'task_submit',
				'type'			=>	'submit',
				'value'			=>	__( 'Update', 'task-scheduler' ),
				'attributes'	=>	array(
					'style'	=>	'float:right;',					
				),
			),
			array()
		);	
	
	}

	/*
	 * Validation methods
	 */
	public function validation_TaskScheduler_MetaBox_Submit( $aInput, $aOldInput ) {	// validation_ + extended class name
					
		return $aInput;
		
	}
	
}