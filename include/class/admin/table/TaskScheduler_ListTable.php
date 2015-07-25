<?php
/**
 * Handles the list table of Task Scheduler tasks. 
 *    
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014-2015, Michael Uno
 * @author      Michel Uno
 * @authorurl   http://michaeluno.jp
 * @since       1.0.0 
*/


class TaskScheduler_ListTable extends TaskScheduler_ListTable_Views {
    
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
    public $sNonce;
    
    /**
     * Stores admin notification messages.
     */
    protected $_aAdminNotices;
     
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
     * Sets up properties and hooks.
     */
    public function __construct( array $aData=array() ){
              
        $this->aData = $aData;
        
        // Set parent defaults
        $this->aArgs = array(
            'singular'  => 'task_scheduler_task',        // singular name of the listed items
            'plural'    => 'task_scheduler_tasks',        // plural name of the listed items
            'ajax'      => false,        // does this table support ajax?
            'screen'    => null,        // not sure what this is for... 
        );
        if ( ! headers_sent() ) {
            add_action( 'admin_notices', array( $this, '_replyToDelayConstructor' ) );
        } else {
            parent::__construct( $this->aArgs );
        }
        
    }
        /**
         * Delays the parent construct to be loaded.
         */
        public function _replyToDelayConstructor() {
            parent::__construct( $this->aArgs );
        }    
    
    /**
     * Set a nonce transient.
     * 
     * This is used for actions to prevent multiple calls or unexpected calls from external sources.
     */
    public function setNonce() {
        
        $this->sNonce = uniqid();
        TaskScheduler_WPUtility::setTransient( TaskScheduler_Registry::TRANSIENT_PREFIX . $this->sNonce, $this->sNonce, 60*10 );
        return $this->sNonce;
        
    }
    /**
     * Returns the set nonce.
     */
    public function getNonce( $sNonce ) {
        
        return TaskScheduler_WPUtility::getTransient( TaskScheduler_Registry::TRANSIENT_PREFIX . $sNonce );
        
    }
    /**
     * Deletes the specified nonce.
     */
    public function deleteNonce( $sNonnce ) {
        TaskScheduler_WPUtility::deleteTransient( TaskScheduler_Registry::TRANSIENT_PREFIX . $sNonnce );
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
        
    /**
     * Sets the given admin notice.
     */
    public function setAdminNotice( $sMessage, $sType='error' )    {
        
        $this->_aAdminNotices[ md5( trim( $sMessage ) . $sType ) ] = array( 'message' => $sMessage, 'type' => $sType );
        
        static $_bLoaded;
        if ( $_bLoaded ) {
            return;
        }
        $_bLoaded = true;
        
        add_action( 'admin_notices', array( $this, '_replyToPrintAdminNotice' ) );
        
    }
        public function _replyToPrintAdminNotice() {
            foreach( $this->_aAdminNotices as $_aAdminNotice ) {
                echo '<div class="' . $_aAdminNotice['type'] . '">'
                        . '<p>' . $_aAdminNotice['message'] . '</p>'
                    . '</div>';                    
            }
        }

    function prepare_items() {
            
        /**
         * Set how many records per page to show
         */
// @todo: let the user set this.        
        $iItemsPerPage = 20;
        
        /**
         * Define our column headers. 
         */
        $this->_column_headers = array( 
            $this->get_columns(),     // $aColumns
            array(),    // $aHidden
            $this->get_sortable_columns()    // $aSortable
        );
     
        /**
         * Process bulk actions.
         */
        // $this->process_bulk_action(); // in our case, it is dealt before the header is sent. ( with the Admin page class )
              
        /**
         * Variables
         */
        $_aData = $this->aData;
                                
        /**
         * For pagination.
         */
        $iCurrentPageNumber = $this->get_pagenum();
        $iTotalItems = count( $_aData );
        $this->set_pagination_args( 
            array(
                'total_items' => $iTotalItems,                      // calculate the total number of items
                'per_page'    => $iItemsPerPage,                     // determine how many items to show on a page
                'total_pages' => ceil( $iTotalItems / $iItemsPerPage )   // calculate the total number of pages
            )
        );
        $_aData = array_slice( $_aData, ( ( $iCurrentPageNumber - 1 ) * $iItemsPerPage ), $iItemsPerPage );
        
        /*
         * Set data
         * */
        // Convert the array of IDs to task objects.
        $this->items = array();
        foreach( $_aData as $_iIndex => $_iPostID ) {
            $this->items[ $_iIndex ] = TaskScheduler_Routine::getInstance( $_iPostID );
        }
        
        /**
         * Sort the array.
         */
        usort( $this->items, array( $this, 'usort_reorder' ) );
                   
        
    }
        /**
         * Sorts the data array of the table.
         * 
         * The url contains a query keys and values like this: &orderby=_next_run_time&order=desc
         */
        public function usort_reorder( $a, $b ) {
            
            $_sOrderBy = ! empty( $_REQUEST['orderby'] ) 
                ? $_REQUEST['orderby'] 
                : 'post_date'; 
            $_sOrder   = ! empty( $_REQUEST['order'] )
                ? $_REQUEST['order']   
                : 'desc'; // desc: larget to smaller            
            $_iResult  = 1;
            if ( is_array( $a ) && is_array( $b ) ) {
                $_iResult = strcmp( $a[ $_sOrderBy ], $b[ $_sOrderBy ] ); 
            } else if ( is_object( $a ) && is_object( $b ) ){
                $_iResult = strcmp( $a->{$_sOrderBy} , $b->{$_sOrderBy} ); 
            }
            return ( 'desc' === $_sOrder ) 
                ? -$_iResult 
                : $_iResult; 
            
        }

}