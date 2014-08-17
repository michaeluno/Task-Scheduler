<?php
/**
 * One of the abstract class of the plugin admin page class.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_AdminPage_Form extends TaskScheduler_AdminPage_Start {
    
    /**
     * The callback function triggered when the page loads.
     */
    public function load_ts_task_list() {    // load_{page slug}

        // Define the form.
        $this->_setTaskListingTableForm();
    
        $this->_oTaskListTable = new TaskScheduler_ListTable;
        $this->_oTaskListTable->process_bulk_action();            // do this before fetching posts

        // the 'status' key can be either 'enabled', 'disabled', or 'thread'.
        $_sStatus    = isset( $_GET['status'] ) ? $_GET['status'] : 'enabled';    
        $_oEnabled   = $this->_getRoutines( 'enabled' );
        $_oDisabled  = $this->_getRoutines( 'disabled' );
        $_oSystem    = $this->_getRoutines( 'system' );
        $_oRoutines  = $this->_getRoutines( 'routine' );
        $_oThreads   = $this->_getRoutines( 'thread' );
        switch( strtolower( $_sStatus ) ) {
            default:
            case 'enabled':            
                $_aTasks = $_oEnabled->posts;
                break;
            case 'disabled':
                $_aTasks = $_oDisabled->posts;
                break;
            case 'system':
                $_aTasks = $_oSystem->posts;
                break;
            case 'routine':
                $_aTasks = $_oRoutines->posts;
                break;                
            case 'thread':
                $_aTasks = $_oThreads->posts;
                break;
        }
        $this->_oTaskListTable->aData = $_aTasks;
        
        // Set the count for the view links.
        $this->_oTaskListTable->_iEnabledTasks      = $_oEnabled->found_posts;
        $this->_oTaskListTable->_iDisabledTasks     = $_oDisabled->found_posts;
        $this->_oTaskListTable->_iSystemRoutines    = $_oSystem->found_posts;
        $this->_oTaskListTable->_iRoutines          = $_oRoutines->found_posts;
        $this->_oTaskListTable->_iThreads           = $_oThreads->found_posts;
        
    }    
    
        /**
         * Retrieves the tasks(custom posts) of the given label.
         * 
         * The label can be either, 'enabled', 'disabled', 'volatile'
         */
        private function _getRoutines( $sLabel ) {
            
            $_aQueryArgs = array();
            if ( isset( $_GET['orderby'], $_GET['order'] ) ) {
                $_aQueryArgs['meta_key'] = $_GET['orderby'];
                $_aQueryArgs['order']     = strtoupper( $_GET['order'] );
            }                
            
            switch( strtolower( $sLabel ) ) {
                default:
                case 'enabled':
                    $_aQueryArgs = array(
                        'post_status'    =>    array( 'private', ),
                        'tax_query' => array(
                            'relation'    =>    'AND',
                            array(
                                'taxonomy'  => TaskScheduler_Registry::Taxonomy_SystemLabel,
                                'field'     => 'slug',
                                'terms'     => array( 'system' ),
                                'operator'  => 'NOT IN'
                            ),            
                        ),                            
                    ) + $_aQueryArgs;
                    break;
                case 'disabled':
                    $_aQueryArgs = array(
                        'post_status'   => array( 'pending', ),
                        'tax_query'     => array(
                            'relation'  => 'AND',
                            array(
                                'taxonomy'  =>  TaskScheduler_Registry::Taxonomy_SystemLabel,
                                'field'     =>  'slug',
                                'terms'     =>  array( 'system' ),
                                'operator'  =>  'NOT IN'
                            ),            
                        ),                            
                    ) + $_aQueryArgs;                    
                    break;
                case 'system':
                    $_aQueryArgs = array(
                        'post_type'     => TaskScheduler_Registry::PostType_Task,
                        'post_status'   => array( 'pending', 'private', 'publish' ),    
                        'tax_query'     => array(
                            array(
                                'taxonomy'  => TaskScheduler_Registry::Taxonomy_SystemLabel,
                                'field'     => 'slug',
                                'terms'     => 'system',
                            ),
                        ),                                
                    ) + $_aQueryArgs;                                    
                    break;
                case 'routine':
                    return TaskScheduler_RoutineUtility::find( 
                        array( 
                            'post_type' =>  TaskScheduler_Registry::PostType_Routine,
                        ) 
                        + $_aQueryArgs 
                    );
                case 'thread':
                    return TaskScheduler_ThreadUtility::find( $_aQueryArgs );
            
            }
            return TaskScheduler_TaskUtility::find( $_aQueryArgs );        

        }
    
    /**
     * Creates a form so that the task list form will be embedded.
     */
    protected function _setTaskListingTableForm() {
                    
        $this->addSettingSections(
            TaskScheduler_Registry::AdminPage_TaskList,    // the target page slug
            array(
                'section_id'    => 'task_listing_table',
                // 'title'            =>    __( 'Tasks', 'task-scheduler' ),
            )            
        );        
        $this->addSettingFields(
            'task_listing_table',    // the target section ID            
            array(
                'field_id'      => 'check_actions_now',
                'type'          => 'submit',
                'value'         => __( 'Check Actions Now', 'task-scheduler' ),
                'attributes'    => array(
                    'field'     => array(
                        'style'    => 'float:right; clear:none; display: inline;',
                    ),                
                    'class'     => 'button button-secondary',
                ),
            )
        );    
                    
    }
    /**
     * Triggered when the 'check_actions_now' submit button is pressed.
     */
    public function submit_TaskScheduler_AdminPage_task_listing_table_check_actions_now() {    // submit_{instantiated class name}_{section id}_{field id}
        
        do_action( 'task_scheduler_action_check_shceduled_actions' );
        $this->setSettingNotice( __( 'Checking actions now.', 'task-scheduler' ), 'updated' );
        
    }
    
    /**
     * Inserts the table at the top of the 'task_listing_table' section output.
     */
    public function content_ts_task_list( $sHTML ) {    // content_{page slug}

        return $this->_getHeartbeatStatus()
            . $this->_getTableOutput()
            . $sHTML;    // $sHTML this includes the output of framework form fields.
        
    }    

        /**
         * Returns the heartbeat status.
         */
        private function _getHeartbeatStatus() {
            
            $_fIsAlive = TaskScheduler_Option::get( array( 'server_heartbeat', 'power' ) ) && TaskScheduler_ServerHeartbeat::isAlive(); 
            $_sStatus = $_fIsAlive
                ? "<span class='running'>" . __( 'Running', 'task-scheduler' ) . "</span>"
                : "<span class='not-running'>" . __( 'Not Running', 'task-scheduler' ) . "</span>";
            $_sLastCheckedTime = $_fIsAlive
                ? ' ' . __( 'The last checked time', 'task-scheduler' ) . ': ' . TaskScheduler_WPUtility::getSiteReadableDate( floor( TaskScheduler_ServerHeartbeat::getLastBeatTime() ), 'Y/m/d G:i:s', true )
                : '';
            $_sCurrentServerTime = __( 'The current server set time', 'task-scheduler' ) . ': ' . TaskScheduler_WPUtility::getSiteReadableDate( time(), 'Y/m/d G:i:s', true );
            return "<p class='section-description'>"
                    . sprintf( __( 'The background task monitoring routine is <strong>%1$s</strong>.', 'task-scheduler' ), $_sStatus )
                    . $_sLastCheckedTime
                    . ' ' . $_sCurrentServerTime
                . "</p>";
                
        }
        
        /**
         * Returns the output buffer of the task listing table.
         */
        private function _getTableOutput() {
                
            $this->_oTaskListTable->prepare_items();
                
            $_sNonce = $this->_oTaskListTable->setNonce();
            
            ob_start(); // Start buffer.
            $this->_oTaskListTable->views();
            $this->_oTaskListTable->display();
            echo "<input type='hidden' name='task_scheduler_task_table' value='1' />";
            echo "<input type='hidden' name='task_scheduler_nonce' value='{$_sNonce}' />";
            $_sContent = ob_get_contents(); // Assign the content buffer to a variable.
            ob_end_clean(); // End buffer and remove the buffer.
            return $_sContent;            
                
        }
    
}