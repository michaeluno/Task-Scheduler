<?php
/**
 * Creates a meta box for occurrence options.
 * 
 * @since            1.0.0
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

        $_sModuleEditPageURL = TaskScheduler_PluginUtility::getModuleEditPageURL(
            array(
                'transient_key'  => TaskScheduler_Registry::TRANSIENT_PREFIX . uniqid(),
                'tab'            => 'edit_action',
                'post'           => isset( $_GET['post'] ) ? $_GET['post'] : 0,
            )
        );
            
        $this->addSettingFields(
            array(
                'field_id'        => 'routine_action',
                'title'           => __( 'Action', 'task-scheduler' ),
                'type'            => 'text',
                'attributes'      => array(
                    'ReadOnly'    => 'ReadOnly',
                    'name'        => '',    // not saving the data
                ),
            ),
            array(
                'field_id'        => 'argument',
                'title'           => __( 'Arguments', 'task-scheduler' ),
                'type'            => 'text',
                'repeatable'      => true,
            ),
            array()
        );    
    
    }
        
    /**
     * Redefines the 'routine_action' field.
     */
    public function field_definition_TaskScheduler_MetaBox_Action_routine_action( $aField ) {
        
        if ( ! $this->_oTask ) { return $aField; }
        $aField['value']    = apply_filters( "task_scheduler_filter_label_action_{$this->_oTask->routine_action}", $this->_oTask->routine_action );
        return $aField;
        
    }
    
    /**
     * Redefines the form fields.
     */
    public function field_definition_TaskScheduler_MetaBox_Action( $aAllFields ) {    // field_definition_{class name}

        if ( ! $this->_oTask ) { 
            return $aAllFields; 
        }
        if ( ! isset( $aAllFields['_default'] ) || ! is_array( $aAllFields['_default'] ) ) { 
            return $aAllFields; 
        }
        
        $aAllFields['_default'] = $aAllFields['_default'] 
            + $this->_getModuleFields( $this->_oTask->routine_action, ( array ) $this->_oTask->{$this->_oTask->routine_action} )
            + array( 'wizard_redirect_button_action' => $this->_getModuleEditButtonField( 'wizard_redirect_button_action', 'edit_action' ) );
        
        return $aAllFields;
        
    }
    
    /**
     * * A validation callback.
     * 
     * @callback    filter      validation_ + extended class name
     */
    public function validation_TaskScheduler_MetaBox_Action( /* $aInput, $aOldInput, $oAdminPage, $aSubmitInfo */ ) {    
                    
        $_aParams    = func_get_args() + array(
            null, null, null, null
        );
        $aInput      = $_aParams[ 0 ];
        $aOldInput   = $_aParams[ 1 ];
        $oAdminPage  = $_aParams[ 2 ];
        $aSubmitInfo = $_aParams[ 3 ];                      
                    
        return $aInput;
        
    }
    
}
