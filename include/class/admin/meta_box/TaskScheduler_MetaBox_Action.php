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
        
        $this->oTask = isset( $_GET['post'] )
            ? TaskScheduler_Routine::getInstance( $_GET['post'] )
            : null;        

        $_sModuleEditPageURL = TaskScheduler_PluginUtility::getModuleEditPageURL(
            array(
                'transient_key'  => TaskScheduler_Registry::TRANSIENT_PREFIX . uniqid(),
                'tab'            => 'edit_action',
                'post'           => isset( $_GET['post'] )
                    ? $_GET['post'] 
                    : 0,
            )
        );
            
        $this->addSettingFields(
            array(
                'field_id'        => 'routine_action_label',
                'title'           => __( 'Action', 'task-scheduler' ),
                'type'            => 'text',
                'attributes'      => array(
                    'readonly'    => 'readonly',
                    'name'        => '',    // not saving the data
                ),
            ),
            array(
                'field_id'        => 'routine_action',
                'title'           => __( 'Action Slug', 'task-scheduler' ),
                'type'            => 'text',
                'attributes'      => array(
                    'readonly'    => 'readonly',
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
     * Redefines the 'routine_action_label' field.
     */
    public function field_definition_TaskScheduler_MetaBox_Action_routine_action_label( $aField ) {
        
        if ( ! $this->oTask ) { 
            return $aField; 
        }
        $aField['value']    = apply_filters( "task_scheduler_filter_label_action_{$this->oTask->routine_action}", $this->oTask->routine_action );
        return $aField;
        
    }
    /**
     * Redefines the 'routine_action' field.
     */
    public function field_definition_TaskScheduler_MetaBox_Action_routine_action( $aField ) {
        
        if ( ! $this->oTask ) { 
            return $aField; 
        }
        $aField['value']    = $this->oTask->routine_action;
        return $aField;
        
    }    
    /**
     * Redefines the form fields.
     * 
     * @callback        filter       field_definition_{class name}
     */
    public function field_definition_TaskScheduler_MetaBox_Action( $aAllFields ) {   

        if ( ! $this->oTask ) { 
            return $aAllFields; 
        }
        if ( ! isset( $aAllFields[ '_default' ] ) || ! is_array( $aAllFields[ '_default' ] ) ) { 
            return $aAllFields; 
        }
        
        $aAllFields[ '_default' ] = $aAllFields[ '_default' ] 
            + $this->_getModuleFields( 
                $this->oTask->routine_action, 
                ( array ) $this->oTask->{$this->oTask->routine_action} 
            );
        
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
 
    public function content( $sOutput ) {
        $sOutput = isset( $this->oTask->routine_action )
            ? apply_filters(
                'task_scheduler_admin_filter_meta_box_content_' . $this->oTask->routine_action,
                $sOutput,
                $this->oTask
            )
            : $sOutput;
        return $sOutput 
             . $this->_getChangeButton( 'edit_action' )
        ;            
    }
 
}
