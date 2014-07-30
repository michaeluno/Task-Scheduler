<?php
/**
 * Creates a meta box for occurrence options.
 * 
 * @since			1.0.0
 */
class TaskScheduler_MetaBox_Occurrence extends TaskScheduler_MetaBox_Base {
				
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
				'field_id'		=>	'occurrence',
				'title'			=>	__( 'Occurrence', 'task-scheduler' ),
				'type'			=>	'text',
				'attributes'	=>	array(
					'ReadOnly'	=>	'ReadOnly',
					'name'		=>	'',
				),
			),		
			array()
		);	
	
	}

	/**
	 * Redefines the 'occurrence' field.
	 */
	public function field_definition_TaskScheduler_MetaBox_Occurrence_occurrence( $aField ) {
		
		if ( ! $this->_oTask ) { return $aField; }
		$aField['value']	= apply_filters( "task_scheduler_filter_label_occurrence_{$this->_oTask->occurrence}", $this->_oTask->occurrence );
		return $aField;		
		
	}
	
	/**
	 * Redefines the form fields.
	 */
	public function field_definition_TaskScheduler_MetaBox_Occurrence( $aAllFields ) {	// field_definition_{class name}

		if ( ! $this->_oTask ) { return $aAllFields; }
		if ( ! isset( $aAllFields['_default'] ) ) { return $aAllFields; }
		
		// Retrieve modular fields for this routine.
		$_aModularFields = apply_filters( "task_scheduler_filter_fields_{$this->_oTask->occurrence}", array() );
		$_aModularOptions = $this->_oTask->{$this->_oTask->occurrence};
		if ( empty( $_aModularOptions ) ) {
			return  $aAllFields;
		}		
		
		$_aModularFields = isset( $_aModularFields[ $this->_oTask->occurrence ] ) ? $_aModularFields[ $this->_oTask->occurrence ] : array();
		foreach( $_aModularFields  as $_aModularField ) {

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
				'tab'			=>	'edit_occurrence',
				'post'			=>	isset( $_GET['post'] ) ? $_GET['post'] : 0,
			)
		);		
		$aAllFields['_default'][ 'wizard_redirect_button_occurrence' ] = array(
			'field_id'		=>	'wizard_redirect_button_occurrence',
			'type'			=>	'hidden',
			// 'hidden'		=>	true,
			'value'			=>	'',
			'before_fieldset'=>	"<div style='float:right;'><a class='button button-secondary button-large' href='{$_sModuleEditPageURL}'>" . __( 'Change', 'task-scheduler' ) .  "</a></div>",
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
	
	/**
	 * Redefines the 'occurrence_options' field.
	 */
	public function field_definition_TaskScheduler_MetaBox_Occurrence_occurrence_options( $aField ) {

		if ( ! $this->_oTask ) { return $aField; }
		$_aWizardFields = apply_filters( "task_scheduler_filter_fields_{$this->_oTask->occurrence}", array() );
		if ( ! isset( $_aWizardFields[ $this->_oTask->occurrence ] ) ) {
			 return $aField;
		}
		$_aOccurrenceOptions = $this->_oTask->{$this->_oTask->occurrence};
		if ( empty( $_aOccurrenceOptions ) ) {
			return $aField;
		}
		
		$_aList = array();
		foreach( $_aWizardFields[ $this->_oTask->occurrence ] as $_aWizardField ) {
			
			if ( ! isset( $_aWizardField['title'] ) ) { continue; }
			$_aisValue = $_aOccurrenceOptions[ $_aWizardField['field_id'] ];
			
			$_aSubField = array(
				// 'title'				=>	$_aWizardField['title'],
				'show_title_column'	=>  true,
				'type'				=>	'hidden',
				'attributes'	=>	array(
					'ReadOnly'	=>	'ReadOnly',
					'name'		=>	'',
				),
			);
			if ( is_array( $_aisValue ) ) {
				$_aSubField['after_fieldset'] = "<div class='task-scheduler-module-options-title-container'><span class='field-title'>{$_aWizardField['title']}</span></div>"
					. "<div class='task-scheduler-module-options-value-container'>"
						. TaskScheduler_PluginUtility::getListFromAssociativeArray( array( __( 'Key', 'task-scheduler' ) => __( 'Value', 'task-scheduler' ) ) + $_aisValue )
					. "</div>";
				$_aSubField['hidden']	= true;
			} else {
				$_aSubField['type']		= 'text';
				$_aSubField['value']	= $_aisValue;
				
			}
			
			$aField[] = $_aSubField;
		}
		
		return $aField;		
		
	}
	
	/*
	 * Validation methods
	 */
	public function validation_TaskScheduler_MetaBox_Occurrence( $aInput, $aOldInput ) {	// validation_ + extended class name
					
		return $aInput;
		
	}
	
}
