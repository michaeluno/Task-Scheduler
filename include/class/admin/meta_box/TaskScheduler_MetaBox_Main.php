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
                'field_id'      => 'excerpt',
                'title'         => __( 'Description', 'task-scheduler' ),
                'type'          => 'textarea',
                'attributes'    => array(
                    'style'   => 'width:100%',
                ),
            ),
            array()
        );    
    
    }
    
    /**
     * Redefines the 'excerpt' field.
     */
    public function field_definition_TaskScheduler_MetaBox_Main_excerpt( $aField ) {
        if ( isset( $_GET[ 'post' ] ) ) {   // sanitization unnecessary
            $_oPost = get_post( absint( $_GET[ 'post' ] ) );    // sanitization done
            $aField[ 'value' ] = $_oPost->post_excerpt;
        }
        return $aField;
    }
        
    /**
     * A validation callback.
     * 
     * @callback        filter      validation_ + extended class name
     */
    public function validation_TaskScheduler_MetaBox_Main( /* $aInput, $aOldInput, $oAdminpage, $aSubmitInfo */ ) {
           
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