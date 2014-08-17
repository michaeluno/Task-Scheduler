<?php
/**
 * Creates a meta box for occurrence options.
 * 
 * @since            1.0.0
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
                'field_id'        =>    'occurrence',
                'title'            =>    __( 'Occurrence', 'task-scheduler' ),
                'type'            =>    'text',
                'attributes'    =>    array(
                    'ReadOnly'    =>    'ReadOnly',
                    'name'        =>    '',
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
        $aField['value']    = apply_filters( "task_scheduler_filter_label_occurrence_{$this->_oTask->occurrence}", $this->_oTask->occurrence );
        return $aField;        
        
    }
    
    /**
     * Redefines the form fields.
     */
    public function field_definition_TaskScheduler_MetaBox_Occurrence( $aAllFields ) {    // field_definition_{class name}

        if ( ! $this->_oTask ) { return $aAllFields; }    
        if ( ! isset( $aAllFields['_default'] ) || ! is_array( $aAllFields['_default'] ) ) { return $aAllFields; }
        
        $aAllFields['_default'] = $aAllFields['_default'] 
            + $this->_getModuleFields( $this->_oTask->occurrence, ( array ) $this->_oTask->{$this->_oTask->occurrence} )
            + array( 'wizard_redirect_button_occurrence' => $this->_getModuleEditButtonField( 'wizard_redirect_button_occurrence', 'edit_occurrence' ) );
        
        return $aAllFields;        
        
    }    
        
    /*
     * Validation methods
     */
    public function validation_TaskScheduler_MetaBox_Occurrence( $aInput, $aOldInput ) {    // validation_ + extended class name
                    
        return $aInput;
        
    }
    
}
