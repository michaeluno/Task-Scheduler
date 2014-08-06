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
		
		$this->_iRoutineID = isset( $_GET['post'] ) ? $_GET['post'] : 0;
		
		$this->addSettingFields(
			array(
				'field_id'		=>	'label_last_run_time',
				'type'			=>	'text',			
				'title'			=>	__( 'Last Run Time', 'task-scheduler' ),
				'attributes'	=>	array(
					'readonly'	=>	'ReadOnly',
					'name'		=>	'',	// dummy
					'size'		=>	16,
				),
			),
			array(
				'field_id'		=>	'label_next_run_time',
				'type'			=>	'text',			
				'title'			=>	__( 'Next Run Time', 'task-scheduler' ),
				'attributes'	=>	array(
					'readonly'	=>	'ReadOnly',
					'name'		=>	'',	// dummy
					'size'		=>	16,
				),
			),	
			array(
				'field_id'		=>	'_is_enabled',
				'type'			=>	'radio',		
				'title'			=>	__( 'Switch', 'task-scheduler' ),
				'label'			=>	array(
					1	=>	__( 'Enabled', 'task-scheduler' ),
					0	=>	__( 'Disabled', 'task-scheduler' ),				
				),
				// 'value'			=>	1,
			),			
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
	
	/**
	 * Redefines fields.
	 */
	public function field_definition_TaskScheduler_MetaBox_Submit_label_last_run_time( $aField ) {
		
		if ( ! $this->_iRoutineID ) { return $aField; }
		$this->_oRoutine	= isset( $this->_oRoutine ) ? $this->_oRoutine : TaskScheduler_Routine::getInstance( $this->_iRoutineID );
		$aField['value']	= $this->_oRoutine->getReadableTime( $this->_oRoutine->_last_run_time, 'Y/m/d G:i:s', true );
		return $aField;
		
	}
	public function field_definition_TaskScheduler_MetaBox_Submit_label_next_run_time( $aField ) {
		
		if ( ! $this->_iRoutineID ) { return $aField; }
		$this->_oRoutine	= isset( $this->_oRoutine ) ? $this->_oRoutine : TaskScheduler_Routine::getInstance( $this->_iRoutineID );
		$aField['value']	= $this->_oRoutine->getReadableTime( $this->_oRoutine->_next_run_time, 'Y/m/d G:i:s', true );
		return $aField;
		
	}
	public function field_definition_TaskScheduler_MetaBox_Submit__is_enabled( $aField ) {
		
		if ( ! $this->_iRoutineID ) { return $aField; }
		$this->_oRoutine	= isset( $this->_oRoutine ) ? $this->_oRoutine : TaskScheduler_Routine::getInstance( $this->_iRoutineID );
		$aField['value']	= $this->_oRoutine->isEnabled() ? 1 : 0;
		// $aField['value']	= 1;
		return $aField;
		
	}
	
	/*
	 * Validation methods
	 */
	public function validation_TaskScheduler_MetaBox_Submit( $aInput, $aOldInput ) {	// validation_ + extended class name
		
		$_bShouldBeEnabled = $aInput['_is_enabled'] ? true : false;
		$_sEnableOrDisable = $aInput['_is_enabled'] ? 'enable' : 'disable';
		unset( $aInput[ '_is_enabled' ], $aInput['task_submit'] );

		$_iRoutineID	= isset( $_POST['post_ID'] ) ? $_POST['post_ID'] : ( isset( $_POST['ID'] ) ? $_POST['ID'] : 0 );
		$_oRoutine		= TaskScheduler_Routine::getInstance( $_iRoutineID );
		$_bisEnabled	= $_oRoutine->isEnabled() ? true : false;
		if ( $_bisEnabled !== $_bShouldBeEnabled ) {
			$_oRoutine->{$_sEnableOrDisable}();
		}

		return $aInput;
		
	}
	
}