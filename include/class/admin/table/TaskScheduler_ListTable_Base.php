<?php
/**
 * Handles actions sent to the form of the list table of Task Scheduler tasks. 
 *    
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014-2016, Michael Uno
 * @author      Michel Uno
 * @authorurl   http://michaeluno.jp
 * @since       1.0.0 
*/

class TaskScheduler_ListTable_Base extends WP_List_Table {

    /**
     * Holds the parsing data to create the list.
     */
    public $aData = array();

    /**
     * The array stores the table settings.
     */
    public $aArgs = array();

    /**
     * Stores the nonce for the actions.
     */
    public $sNonce = '';

    /**
     * Stores admin notification messages.
     * @deprecated 1.5.0
     */
//    protected $_aAdminNotices;

    /**
     * Stores disallowed query keys embedded in links.
     */
    protected $_aDisallowedQueryKeys = array(
        'task_scheduler_nonce',
        'action',
        'task_scheduler_task',
        'orderby',
        'order',
        'paged',
    );

    /**
     * Set a nonce transient.
     *
     * This is used for actions to prevent multiple calls or unexpected calls from external sources.
     *
     */
    public function setNonce() {
        $this->sNonce = wp_create_nonce( 'task_scheduler_list_table_action' );
    }

    /**
     * Returns the set nonce.
     */
    public function getNonce() {
        return $this->sNonce;
    }

    /**
     * Sets the given admin notice.
     */
    public function setAdminNotice( $sMessage, $sType='error' )    {
        new TaskScheduler_AdminPageFramework_AdminNotice( $sMessage, array( 'class' => $sType ) );
    }

    /**
     * Returns the modified url with the query keys.
     */
    public function getQueryURL( array $aKeyValues, $sURL=null ) {

        $sURL                   = $sURL
            ? $sURL
            : $_SERVER['REQUEST_URI'];
        $_sModifiedURL          = add_query_arg( $aKeyValues, $sURL );
        $_aDisallowedQueryKeys  = array_diff( $this->_aDisallowedQueryKeys, array_keys( $aKeyValues ) );

        return remove_query_arg( $_aDisallowedQueryKeys, $_sModifiedURL );

    }

}