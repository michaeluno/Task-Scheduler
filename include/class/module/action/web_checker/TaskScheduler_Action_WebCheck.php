<?php
/**
 * The class that defines the Email action for the Task Scheduler plugin.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 */

 /**
  * 
  * @since        1.3.0
  */
class TaskScheduler_Action_WebCheck extends TaskScheduler_Action_Base {

    /**
     * The user constructor.
     * 
     * This method is automatically called at the end of the class constructor.
     */
    public function construct() {}
    
    /**
     * Returns the readable label of this action.
     * 
     * This will be called when displaying the action in an pull-down select option, task listing table, or notification email message.
     */
    public function getLabel( $sLabel ) {
        return __( 'Check Web Site', 'task-scheduler' );
    }
    
    /**
     * Returns the description of the module.
     */
    public function getDescription( $sDescription ) {
        return __( 'Accesses a specified web site and checks if the site is up.', 'task-scheduler' )
            . ' ' . __( 'This can be used for triggering WordPress Cron jobs from a separate remote site.', 'task-scheduler' );
    }    
        
    /**
     * Defines the behavior of the task action.
     */
    public function doAction( $isExitCode, $oRoutine ) {
        
        $_aTaskMeta = $oRoutine->getMeta();
        if ( 
            ! isset( 
                $_aTaskMeta[ $this->sSlug ],
                $_aTaskMeta[ $this->sSlug ][ 'url' ],
                $_aTaskMeta[ $this->sSlug ][ 'sslverify'],
                $_aTaskMeta[ $this->sSlug ][ 'timeout' ],
                $_aTaskMeta[ $this->sSlug ][ 'http_method' ],
                $_aTaskMeta[ $this->sSlug ][ 'queries' ],
                $_aTaskMeta[ $this->sSlug ][ 'search_keywords' ],
                $_aTaskMeta[ $this->sSlug ][ 'must_have_all_keywords' ],
                $_aTaskMeta[ $this->sSlug ][ 'search_in_the_source' ]
            ) 
        ) {
            $oRoutine->log( __( 'A required argument is missing', 'task-scheduler' ) );            
            return 0;    // failed
        }
        $_aActionMetas = $_aTaskMeta[ $this->sSlug ];
        
        // Perform an HTTP request
        $_aoResponse = call_user_func_array( 
            'wp_remote_' . $_aActionMetas[ 'http_method' ],
            $this->_getHTTPRequstArguments( $_aActionMetas )
        );

        // Handle the HTTP response
        if ( is_wp_error( $_aoResponse ) ) {
            $oRoutine->log( __( 'Connection Failed', 'task-scheduler' ) . ': ' . $_aoResponse->get_error_message() );
            return 0;        // connection failed
        }
        
        $_sResponseCode = ( string ) wp_remote_retrieve_response_code( $_aoResponse );
        
        // If HTTP response status code is 2xx, it means success
        if ( '2' === $_sResponseCode[ 0 ] && 'head' === $_aActionMetas[ 'http_method' ] ) {
            $oRoutine->log( 
                __( 'Status Code', 'task-scheduler' ) . ': ' . $_sResponseCode
                . ' ' . wp_remote_retrieve_response_message( $_aoResponse )
            );
            return 1;
        }
        
        // If the response status indicates a problem, return the response code.
        if ( '2' !== $_sResponseCode[ 0 ] ) {
            $oRoutine->log( 
                __( 'Status Code', 'task-scheduler' ) . ': ' . $_sResponseCode
                . ' ' . wp_remote_retrieve_response_message( $_aoResponse )
            );
            return $_sResponseCode;
        }
        
        // At this point, the web page returned a result. And the HTTP method is either GET or POST. HEAD is excluded in the above checks.
        
        // If nothing to check, leave a log and indicate success.
        $_aActionMetas[ 'search_keywords' ] = array_filter( $_aActionMetas[ 'search_keywords' ] );
        if ( empty( $_aActionMetas[ 'search_keywords' ] ) ) {
            $oRoutine->log( 
                __( 'Status Code', 'task-scheduler' ) . ': ' . $_sResponseCode
                . ' ' . wp_remote_retrieve_response_message( $_aoResponse )
            );
            return 1;
        }
        
        $_sBody = wp_remote_retrieve_body( $_aoResponse );        
        
        if ( $_aActionMetas[ 'search_in_the_source' ] ) {
            $_bFound = $this->_isKeywordFound( $_sBody, $_aActionMetas[ 'search_keywords' ], $_aActionMetas[ 'must_have_all_keywords' ] );            
            $oRoutine->log( 
                $_bFound
                    ? __( 'Keyword(s) found in the source code.', 'task-scheduler' ) 
                    : __( 'Keyword(s) not found in the source code.', 'task-scheduler' ) 
            );                     
            return ( integer ) $_bFound;
        }
        
        if ( ! class_exists( 'DOMDocument', false ) ) {
            $oRoutine->log( __( 'Error: The PHP DOMDocument extension must be installed.', 'task-scheduler' ) );
            return 0;        // connection failed         
         
        }
        
        $_bFound = $this->_isInTheWebPage( $_sBody, $_aActionMetas[ 'search_keywords' ], $_aActionMetas[ 'must_have_all_keywords' ] );
        $oRoutine->log( 
            $_bFound
                ? __( 'Keyword(s) found.', 'task-scheduler' ) 
                : __( 'Keyword(s) not found.', 'task-scheduler' ) 
        );                     
        return ( integer ) $_bFound;
                 
    }
        /**
         * @since       1.3.0
         * @return      boolean
         */        
        private function _isInTheWebPage( $sHTML, array $aKeywords, $bFindAll=false ) {
            libxml_use_internal_errors(true);
            $_oDOM = new DOMDocument;
            $_oDOM->loadHTML( $sHTML );
            libxml_clear_errors();
            return $this->_isKeywordFound(
                $_oDOM->getElementsByTagName( 'body' )->item( 0 )->textContent,
                $aKeywords,
                $bFindAll
            );
            
        }
    
        /**
         * @since       1.3.0
         * @return      boolean
         */
        private function _isKeywordFound( $sText, array $aKeywords, $bFindAll=false ) {
            
            // All keywords must be found.
            if ( $bFindAll ) {                
                foreach( $aKeywords as $_sKeyword ) {
                    if ( false === strpos( $sText, $_sKeyword ) ) {
                        return false;
                    }
                }
                return true;
            }

            // At least one item should be found.
            foreach( $aKeywords as $_sKeyword ) {
                if ( false !== strpos( $sText, $_sKeyword ) ) {
                    return true;
                }
            }
            return false;            
            
        }

        /**
         * @since       1.3.0
         * @return      array
         */
        private function _getHTTPRequstArguments( $aActionMetas ) {
            
            $_aRequest = array(
                'timeout'     => $aActionMetas[ 'timeout' ],
                'redirection' => 5,
                'httpversion' => '1.0',
                'user-agent'  => TaskScheduler_Registry::NAME . ' for WordPress/' . TaskScheduler_Registry::VERSION . '; ' . get_bloginfo( 'url' ),
                'compress'    => false,
                'decompress'  => true,
                'sslverify'   => $aActionMetas[ 'sslverify' ],              
            );
            if ( 'post' === $aActionMetas[ 'http_method' ] ) {
                $_aRequest[ 'body' ] = $aActionMetas[ 'queries' ];
            }
            if ( 'get' === $aActionMetas[ 'http_method' ] ) {
                $aActionMetas[ 'url' ] = add_query_arg(
                    $aActionMetas[ 'queries' ],
                    $aActionMetas[ 'url' ] 
                );
            }
            return array(
                $aActionMetas[ 'url' ],
                $_aRequest  // second parameter
            );
        }    
         
}