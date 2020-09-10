<?php
/**
 *    
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014-2016, Michael Uno
 * @author      Michel Uno
 * @authorurl   http://michaeluno.jp
 * @since       1.0.0 
*/

/**
 * Handles the list table of Task Scheduler tasks.
 */
class TaskScheduler_ListTable extends TaskScheduler_ListTable_Views {

    /**
     * Sets up properties and hooks.
     *
     * @param array $aData
     * @param TaskScheduler_AdminPageFramework $oAdminPage
     */
    public function __construct( array $aData, $oAdminPage ){

        $this->aData = $aData;
        $this->oAdminPage = $oAdminPage;
        $this->setNonce();
        
        // Set parent defaults
        $this->aArgs = array(
            'singular'  => 'task_scheduler_task',        // singular name of the listed items
            'plural'    => 'task_scheduler_tasks',       // plural name of the listed items
            'ajax'      => false,       // does this table support ajax?
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
    

    function prepare_items() {
            
        /**
         * Set how many records per page to show
         */
// @todo: let the user set this.        
        $_iItemsPerPage = 20;
        
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
        $_iCurrentPageNumber = $this->get_pagenum();
        $_iTotalItems = count( $_aData );
        $this->set_pagination_args( 
            array(
                'total_items' => $_iTotalItems,                      // calculate the total number of items
                'per_page'    => $_iItemsPerPage,                     // determine how many items to show on a page
                'total_pages' => ceil( $_iTotalItems / $_iItemsPerPage )   // calculate the total number of pages
            )
        );
        $_aData = array_slice( $_aData, ( ( $_iCurrentPageNumber - 1 ) * $_iItemsPerPage ), $_iItemsPerPage );
        
        /*
         * Set data
         * */
        // Convert the array of IDs to task objects.
        $this->items = array();
        foreach( $_aData as $_iPostID ) {
            $_oRoutine = TaskScheduler_Routine::getInstance( $_iPostID );
            if ( false === $_oRoutine ) {
                continue;
            }
            $this->items[] = $_oRoutine;
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
            
            $_sOrderBy = ! empty( $_REQUEST[ 'orderby' ] ) 
                ? $_REQUEST[ 'orderby' ] 
                : 'post_date'; 
            $_sOrder   = ! empty( $_REQUEST[ 'order' ] )
                ? $_REQUEST[ 'order' ]   
                : 'desc'; // desc: largest to smaller            
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