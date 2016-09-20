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
        
        $this->oTask = isset( $_GET['post'] )
            ? TaskScheduler_Routine::getInstance( $_GET['post'] )
            : null;
        
        $this->addSettingFields(
            array(
                'field_id'        => 'occurrence',
                'title'           => __( 'Occurrence', 'task-scheduler' ),
                'type'            => 'text',
                'attributes'      => array(
                    'readonly'    => 'readonly',
                    'name'        => '',
                ),
            )
        );    
    
    }

    /**
     * Redefines the 'occurrence' field.
     * @callback    filter      field_definition_{class name}_{field id}
     * @return      array   
     */
    public function field_definition_TaskScheduler_MetaBox_Occurrence_occurrence( $aField ) {

        if ( ! $this->oTask ) { 
            return $aField; 
        }
        $aField[ 'value' ] = apply_filters( 
            "task_scheduler_filter_label_occurrence_{$this->oTask->occurrence}", 
            $this->oTask->occurrence 
        );
        return $aField;      
        
    }
    
    /**
     * Redefines the form fields.
     * @callback    filter      field_definition_{class name}
     * @return      array
     */
    public function field_definition_TaskScheduler_MetaBox_Occurrence( $aAllFields ) {    

        if ( ! $this->oTask ) { 
            return $aAllFields; 
        }    
        if ( ! isset( $aAllFields[ '_default' ] ) || ! is_array( $aAllFields[ '_default' ] ) ) { 
            return $aAllFields; 
        }
        
        $aAllFields[ '_default' ] = $aAllFields[ '_default' ] 
            + $this->_getModuleFields( 
                $this->oTask->occurrence, 
                ( array ) $this->oTask->{$this->oTask->occurrence}
            )
        ;
        
        return $aAllFields;        
        
    }    
        
    /**
     * A validation callback method.
     * 
     * @callback        filter      validation_ + extended class name
     */
    public function validation_TaskScheduler_MetaBox_Occurrence( /* $aInput, $aOldInput, $oAdminPage, $aSubmitInfo */ ) {
                  
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
        $sOutput = isset( $this->oTask->occurrence )
            ? apply_filters(
                'task_scheduler_admin_filter_meta_box_content_' . $this->oTask->occurrence,
                $sOutput,
                $this->oTask
            )
            : $sOutput;
        return $sOutput 
             . $this->_getChangeButton( 'edit_occurrence' )
            ;
    }    
    
}