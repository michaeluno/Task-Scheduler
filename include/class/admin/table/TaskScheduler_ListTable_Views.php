<?php
/**
 * An abstract class that defines the views links of the Task Scheduler task listing table.
 *
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, Michael Uno
 * @author       Michel Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0 
*/

abstract class TaskScheduler_ListTable_Views extends TaskScheduler_ListTable_Column {
    
    /**
     * Stores the number indicated in the view link above the listing table.
     * @scope       public      Accessed publicly.
     */
    public $_iEnabledTasks   = 0;
    public $_iDisabledTasks  = 0;
    public $_iSystemRoutines = 0;
    public $_iRoutines       = 0;
    public $_iThreads        = 0;
    
    public function get_views() {
                
        $_aBaseKeys = array(
            'enabled'    => __( 'Enabled', 'task-scheduler' ) 
                . " <span class='count'>(" . $this->_iEnabledTasks . ")</span>",
            'disabled'    => __( 'Disabled', 'task-scheduler' ) 
                . " <span class='count'>(" . $this->_iDisabledTasks . ")</span>",
            'system'    => __( 'System', 'task-scheduler' ) 
                . " <span class='count'>(" . $this->_iSystemRoutines . ")</span>",                
            'routine'    => __( 'Routine', 'task-scheduler' ) 
                . " <span class='count'>(" . $this->_iRoutines . ")</span>",                
            'thread'    => __( 'Thread', 'task-scheduler' ) 
                . " <span class='count'>(" . $this->_iThreads . ")</span>",
        );
        if ( ! $this->_iSystemRoutines ) {
            unset( $_aBaseKeys[ 'system' ] );
        }        
        if ( ! $this->_iRoutines ) {
            unset( $_aBaseKeys[ 'routine' ] );
        }
        if ( ! $this->_iThreads ) {
            unset( $_aBaseKeys[ 'thread' ] );
        }

        $_aViews = array();        
        foreach ( $_aBaseKeys as $_sKey => $_sLabel ) {
            
            $_sSelfURL_StatusQuery = esc_url( $this->getQueryURL( array( 'status' => $_sKey ) ) );
            $_sCurrent = ( ! isset( $_GET['status'] ) && 'enabled' == $_sKey ) || ( isset( $_GET['status'] ) && $_sKey == $_GET['status'] ) 
                ? 'current' 
                : '';
            $_aViews[ $_sKey ] = "<a href='{$_sSelfURL_StatusQuery}' class='{$_sCurrent}'>"
                    . $_sLabel
                . "</a>";            
        }
        
        return $_aViews;
        
    }    
    
}