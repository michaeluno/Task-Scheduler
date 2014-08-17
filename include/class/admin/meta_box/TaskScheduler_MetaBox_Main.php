<?php
/**
 * Creates a meta box for the basic options.
 * 
 * @since            1.0.0
 */
class TaskScheduler_MetaBox_Main extends TaskScheduler_MetaBox_Base {
                
    /**
     * Adds form fields for the basic options.
     * 
     */ 
    public function setUp() {
        
        $this->addSettingFields(
            array(
                'field_id'        =>    'excerpt',
                'title'            =>    __( 'Description', 'task-scheduler' ),
                'type'            =>    'textarea',
            ),
            array()
        );    
    
    }
    
    /**
     * Redefines the 'excerpt' field.
     */
    public function field_definition_TaskScheduler_MetaBox_Main_excerpt( $aField ) {
        if ( isset( $_GET['post'] ) ) {
            $_oPost = get_post( $_GET['post'] );
            $aField['value'] = $_oPost->post_excerpt;
        }
        return $aField;
    }
        
    /*
     * Validation methods
     */
    public function validation_TaskScheduler_MetaBox_Main( $aInput, $aOldInput ) {    // validation_ + extended class name
                    
        return $aInput;
        
    }
    
}