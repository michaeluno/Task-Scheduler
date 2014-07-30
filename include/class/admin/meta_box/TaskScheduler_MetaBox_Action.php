<?php
/**
 * Creates a meta box for occurrence options.
 * 
 * @since			1.0.0
 */
class TaskScheduler_MetaBox_Action extends TaskScheduler_MetaBox_Base {
				
	/**
	 * Adds form fields for the basic options.
	 * 
	 */ 
	public function setUp() {
		
		$this->_oTask = isset( $_GET['post'] )
			? TaskScheduler_Routine::getInstance( $_GET['post'] )
			: null;		
			
		$this->addSettingFields(
			array(
				'field_id'		=>	'routine_action',
				'title'			=>	__( 'Action', 'task-scheduler' ),
				'type'			=>	'text',
				'attributes'	=>	array(
					'ReadOnly'	=>	'ReadOnly',
					'name'		=>	'',	// not saving the data
				),
			),
			array(
				'field_id'		=>	'argument',
				'title'			=>	__( 'Arguments', 'task-scheduler' ),
				'type'			=>	'text',
				'repeatable'	=>	true,
			),
			array()
		);	
	
	}
		
	/**
	 * Redefines the 'routine_action' field.
	 */
	public function field_definition_TaskScheduler_MetaBox_Action_routine_action( $aField ) {
		
		if ( ! $this->_oTask ) { return $aField; }
		$aField['value']	= apply_filters( "task_scheduler_filter_label_action_{$this->_oTask->routine_action}", $this->_oTask->routine_action );
		return $aField;
		
	}
	
	/**
	 * Redefines the form fields.
	 */
	public function field_definition_TaskScheduler_MetaBox_Action( $aAllFields ) {	// field_definition_{class name}

		if ( ! $this->_oTask ) { return $aAllFields; }
		if ( ! isset( $aAllFields['_default'] ) ) { return $aAllFields; }
		
		// Retrieve modular fields for this routine.
		$_aModularFields = apply_filters( "task_scheduler_filter_fields_{$this->_oTask->routine_action}", array() );
		$_aModularOptions = $this->_oTask->{$this->_oTask->routine_action};
		if ( empty( $_aModularOptions ) ) {
			return  $aAllFields;
		}		
		$_aModularFields = isset( $_aModularFields[ $this->_oTask->routine_action ] ) ? $_aModularFields[ $this->_oTask->routine_action ] : array();
		foreach( $_aModularFields as $_aModularField ) {
			
			if ( ! isset( $_aModularField['title'], $_aModularField['field_id'], $_aModularOptions[ $_aModularField['field_id'] ] ) || ! $_aModularField['title'] ) { continue; }
			unset( $_aModularField['section_id'], $_aModularField['repeatable'] );
			
			$_aisValue = $_aModularOptions[ $_aModularField['field_id'] ];
			$_aisValue = TaskScheduler_Utility::isJSON( $_aisValue ) ? json_decode( $_aisValue, true ) : $_aisValue;
			$_aisValue = maybe_unserialize( $_aisValue );
			
			$_aModularField = array(
				'attributes'	=>	array(
					'ReadOnly'	=>	'ReadOnly',
					'name'		=>	'',
					'class'		=>	'read-only',
				),
				'field_id'	=>	$_aModularField['field_id'],
				// 'type'		=>	is_array( $_aisValue ) ? 'hidden' : $_aModularField['type'],
				'type'		=>	$this->_getFieldTypeForDisplay( $_aisValue, $_aModularField['type'] ),
				'value'		=>	$_aisValue,
				'title'		=>	$_aModularField['title'],
				'show_title_column'	=>  true,
			);	
			$_aModularField['attributes']['cols'] = 'textarea' == $_aModularField['type'] ? 42 : null;
			if ( is_array( $_aisValue ) ) {
				$_aModularField['before_fieldset'] = "<div class='task-scheduler-module-options-value-container'>"
						. TaskScheduler_PluginUtility::getListFromAssociativeArray( array( __( 'Key', 'task-scheduler' ) => __( 'Value', 'task-scheduler' ) ) + $_aisValue )
					. "</div>";
			}			
			
			$aAllFields['_default'][ $_aModularField['field_id'] ] = $_aModularField;
			
		}
		
		$_sModuleEditPageURL = TaskScheduler_PluginUtility::getModuleEditPageURL(
			array(
				'transient_key'	=>	TaskScheduler_Registry::TransientPrefix . uniqid(),
				'tab'			=>	'edit_action',
				'post'			=>	isset( $_GET['post'] ) ? $_GET['post'] : 0,
			)
		);
		$aAllFields['_default'][ 'wizard_redirect_button_action' ] = array(
			'field_id'		=>	'wizard_redirect_button_action',
			'type'			=>	'hidden',
			'value'			=>	'',
			'before_field'	=>	"<div style='float:right;'><a class='button button-secondary button-large' href='{$_sModuleEditPageURL}'>" . __( 'Change', 'task-scheduler' ) .  "</a></div>",
			'attributes'	=>	array(
				'name'		=>	'',
			),				
		);
		return $aAllFields;
		
	}
		/**
		 * Returns the field type for displaying field values.
		 */
		private function _getFieldTypeForDisplay( $aisValue, $sSetFieldType ) {
			
			if ( is_array( $aisValue ) ) {
				return 'hidden';
			}
			
			if ( 'textarea' === $sSetFieldType ) {
				return $sSetFieldType;
			}
			
			return 'text';
			
		}		
	/*
	 * Validation methods
	 */
	public function validation_TaskScheduler_MetaBox_Action( $aInput, $aOldInput ) {	// validation_ + extended class name
					
		return $aInput;
		
	}
	
}
