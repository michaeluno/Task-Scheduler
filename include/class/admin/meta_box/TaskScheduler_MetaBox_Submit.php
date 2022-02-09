<?php
/**
 * Creates a meta box for the basic options.
 * 
 * @since            1.0.0
 */
class TaskScheduler_MetaBox_Submit extends TaskScheduler_MetaBox_Base {
    
    public function start() { 
    
        parent::start();
        
        add_action( 'admin_menu', array( $this, '_replyToRemoveDefaultMetaBoxes' ) );
        
        // Modifies the post status of the task
        add_filter( 
            'wp_insert_post_data', 
            array( $this, 'replyToModifyThePostStatus' ), 
            100,    // lower priority than Admin Page Framework (which also uses the same hook for metabox form field validation)
            2 
        );        
        
    }
        /**
         * Removes the default submit meta box that has the Update/Create button.
         */
        public function _replyToRemoveDefaultMetaBoxes() {
            remove_meta_box( 
                'submitdiv', 
                TaskScheduler_Registry::$aPostTypes[ 'task' ], 
                'side'
            );
        }    
    
    
    /**
     * Adds form fields for the basic options.
     * 
     */ 
    public function setUp() {
        
        $this->_iRoutineID = isset( $_GET['post'] ) 
            ? $_GET['post'] 
            : 0;
        
        $this->addSettingFields(
            array(
                'field_id'        => 'label_last_run_time',
                'type'            => 'text',            
                'title'           => __( 'Last Run Time', 'task-scheduler' ),
                'attributes'      => array(
                    'readonly'    => 'ReadOnly',
                    'name'        => '',    // dummy
                    'size'        => 16,
                ),
            ),
            array(
                'field_id'        => 'label_next_run_time',
                'type'            => 'text',            
                'title'           => __( 'Next Run Time', 'task-scheduler' ),
                'attributes'      => array(
                    'readonly'    => 'ReadOnly',
                    'name'        => '',    // dummy
                    'size'        => 16,
                ),
            ),    
            array(
                'field_id'        => '_is_enabled',
                'type'            => 'radio',        
                'title'           => __( 'Status', 'task-scheduler' ),
                'label'           => array(
                    1    =>    __( 'Enabled', 'task-scheduler' ),
                    0    =>    __( 'Disabled', 'task-scheduler' ),                
                ),
                'label_min_width' => 0,
                // 'value'            =>    1,
            ),            
            array(
                'field_id'        => 'task_submit',
                'type'            => 'submit',
                'value'           => __( 'Update', 'task-scheduler' ),
                'label_min_width' => '0px',
                'attributes'      => array(
                    'fields' => array(
                        'style'    => 'width: auto; float:right;',
                    ),
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
        $this->_oRoutine    = isset( $this->_oRoutine ) ? $this->_oRoutine : TaskScheduler_Routine::getInstance( $this->_iRoutineID );
        $aField['value']    = $this->_oRoutine->getReadableTime( $this->_oRoutine->_last_run_time, 'Y/m/d G:i:s', true );
        return $aField;
        
    }
    public function field_definition_TaskScheduler_MetaBox_Submit_label_next_run_time( $aField ) {
        
        if ( ! $this->_iRoutineID ) { return $aField; }
        $this->_oRoutine    = isset( $this->_oRoutine ) ? $this->_oRoutine : TaskScheduler_Routine::getInstance( $this->_iRoutineID );
        $aField['value']    = $this->_oRoutine->getReadableTime( $this->_oRoutine->_next_run_time, 'Y/m/d G:i:s', true );
        return $aField;
        
    }
    public function field_definition_TaskScheduler_MetaBox_Submit__is_enabled( $aField ) {
        
        if ( ! $this->_iRoutineID ) { return $aField; }
        $this->_oRoutine    = isset( $this->_oRoutine ) ? $this->_oRoutine : TaskScheduler_Routine::getInstance( $this->_iRoutineID );
        $aField['value']    = $this->_oRoutine->isEnabled() ? 1 : 0;
        // $aField['value']    = 1;
        return $aField;
        
    }
    
    /**
     * A validation callback.
     * 
     * @callback        filter      validation_ + extended class name
     */
    public function validation_TaskScheduler_MetaBox_Submit( /* $aInput, $aOldInput, $oAdminPage, $aSubmitInfo */ ) {
        
        $_aParams    = func_get_args() + array(
            null, null, null, null
        );
        $aInput      = $_aParams[ 0 ];
        $aOldInput   = $_aParams[ 1 ];
        $oAdminPage  = $_aParams[ 2 ];
        $aSubmitInfo = $_aParams[ 3 ];            
        
        $_bShouldBeEnabled  = $aInput['_is_enabled'] ? true : false;
        $_sEnableOrDisable  = $aInput['_is_enabled'] ? 'enable' : 'disable';
        unset( $aInput[ '_is_enabled' ], $aInput['task_submit'] );

        $_iRoutineID    = isset( $_POST[ 'post_ID' ] )
            ? absint( $_POST[ 'post_ID' ] )
            : ( isset( $_POST[ 'ID' ] )
                ? absint( $_POST[ 'ID' ] )
                : 0
            );
        $_oRoutine      = TaskScheduler_Routine::getInstance( $_iRoutineID );
        $_bisEnabled    = ( bool ) $_oRoutine->isEnabled();
        if ( $_bisEnabled !== $_bShouldBeEnabled ) {
                            
            // @deprecated  The below method uses wp_update_post() and it causes infinite recursive function calls in a recent framework or WordPress version.
            // $_oRoutine->{$_sEnableOrDisable}();
            
            $this->_sNewPostStatus = $_bShouldBeEnabled 
                ? 'private' 
                : 'pending';            
            update_post_meta( 
                $_iRoutineID, 
                '_routine_status', 
                'ready'
            );

            
        }

        return $aInput;
        
    }
        /**
         * Stores the new task (post) status.
         */
        private $_sNewPostStatus = '';
        /**
         * 
         */
        public function replyToModifyThePostStatus( $aPostData, $aUnmodified ) {
            
            if ( $this->_sNewPostStatus ) {
                $aPostData['post_status'] = $this->_sNewPostStatus;
            }
            return $aPostData;
            
        }
    
}