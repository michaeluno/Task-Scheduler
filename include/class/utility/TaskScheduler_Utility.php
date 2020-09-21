<?php
/**
 * Task Scheduler
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 */

/**
 * The class that provides utility methods without WordPress built-in functions.
 * 
 * @since        1.0.0
 */
abstract class TaskScheduler_Utility extends TaskScheduler_AdminPageFramework_FrameworkUtility {

    /**
     * Converts the given string with delimiters to a multi-dimensional array.
     *
     * Parameters:
     * 1: haystack string
     * 2, 3, 4...: delimiter
     * e.g. $arr = getStringIntoArray( 'a-1,b-2,c,d|e,f,g', "|", ',', '-' );
     * @since   1.5.2
     */
    static public function getStringIntoArray() {

        $intArgs      = func_num_args();
        $arrArgs      = func_get_args();
        $strInput     = $arrArgs[ 0 ];
        $strDelimiter = $arrArgs[ 1 ];

        if ( ! is_string( $strDelimiter ) || $strDelimiter == '' ) {
            return $strInput;
        }
        if ( is_array( $strInput ) ) {
            return $strInput;    // note that is_string( 1 ) yields false.
        }

        $arrElems = preg_split( "/[{$strDelimiter}]\s*/", trim( $strInput ), 0, PREG_SPLIT_NO_EMPTY );
        if ( ! is_array( $arrElems ) ) {
            return array();
        }

        foreach( $arrElems as &$strElem ) {

            $arrParams = $arrArgs;
            $arrParams[0] = $strElem;
            unset( $arrParams[ 1 ] );    // remove the used delimiter.
            // now `$strElem` becomes an array.
            // if the delimiters are gone,
            if ( count( $arrParams ) > 1 ) {
                $strElem = call_user_func_array(
                    array( __CLASS__, 'getStringIntoArray' ),
                    $arrParams
                );
            }

            // Added this because the function was not trimming the elements sometimes... not fully tested with multi-dimensional arrays.
            if ( is_string( $strElem ) ) {
                $strElem = trim( $strElem );
            }

        }
        return $arrElems;

    }

    /**
     * Checks if the given value is empty or not.
     *
     * @remark      This is useful when PHP throws an error ' Fatal error: Can't use method return value in write context.'.
     * @since       1.5.2
     * @retuen      boolean
     */
    static public function isEmpty( $mValue ) {
        return ( boolean ) empty( $mValue );
    }

    /**
     * Overrides the ancestor method.
     * @remark Fixes the bug of the parent method that extra port is added.
     * @return string
     * @since 1.5.2 
     */
    static public function getCurrentURL() {
        $_bSSL = self::isSSL();
        $_sServerProtocol = strtolower($_SERVER['SERVER_PROTOCOL']);
        $_aProtocolSuffix = array(0 => '', 1 => 's',);
        $_sProtocol = substr($_sServerProtocol, 0, strpos($_sServerProtocol, '/'))
            . $_aProtocolSuffix[( int )$_bSSL];
        $_sPort = self::___getURLPortSuffix($_bSSL);
        $_sHost = isset($_SERVER['HTTP_X_FORWARDED_HOST'])
            ? $_SERVER['HTTP_X_FORWARDED_HOST']
            : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
        $_sHost = preg_replace( '/:.+/', '', $_sHost ); // remove the port part in case it is added.
        return $_sProtocol . '://' . $_sHost . $_sPort . $_SERVER['REQUEST_URI'];
    }
    static private function ___getURLPortSuffix($_bSSL) {
        $_sPort = isset( $_SERVER['SERVER_PORT'] ) ? ( string )$_SERVER['SERVER_PORT'] : '';
        $_aPort = array(0 => ':' . $_sPort, 1 => '',);
        $_bPortSet = (!$_bSSL && '80' === $_sPort) || ($_bSSL && '443' === $_sPort);
        return $_aPort[( int )$_bPortSet];
    }
    
    
    /**
     * Tell WordPress this is a background routine by setting the Cron flag.
     * @return void
     * @since 1.5.0
     */
    static public function setCronFlag() {

        if ( ! defined( 'DOING_CRON' ) ) {
            define( 'DOING_CRON', true );
        }
        if ( ! defined( 'WP_USE_THEMES' ) ) {
            define( 'WP_USE_THEMES', false );
        }
        ignore_user_abort( true );

    }

    /**
     * Checks if a string is JSON encoded.
     */
    static public function isJSON( $str ) {
        
        if ( ! is_string( $str ) ) {
            return false;
        }
        
        $_v = json_decode( $str );
        return is_object( $_v ) || is_array( $_v );
            
        // json_decode( $str );
        // return ( json_last_error() == JSON_ERROR_NONE );    // not available in 5.2.x or below
        
    }
    
    /**
     * Returns a joined string line of array contents.
     * 
     * This is used for logging lines.
     * @deprecated
     */
    static public function implodeByKeyValues( array $aSubject ) {
        return http_build_query( $aSubject, '', ', ' );
    }
    
    /**
     * Swap the given two array elements.
     */
    static public function swapElements( array &$aSubject, $isSourceKey, $isDestinationKey ) {
        $_vTemp                            = $aSubject[ $isDestinationKey ];  
        $aSubject[ $isDestinationKey ]    = $aSubject[ $isSourceKey ];
        $aSubject[ $isSourceKey ]        = $_vTemp;
        return $aSubject;
    }

    
    /**
     * Merges multiple multi-dimensional array recursively.
     * 
     * The advantage of using this method over the array unite operator or array_merge() is that it merges recursively and the null values of the preceding array will be overridden.
     *
     * @static
     * @access          public
     * @remark          The parameters are variadic and can add arrays as many as necessary.
     * @return          array            the united array.
     * @deprecated      1.4.1   Defined in a parent class.
     */
/*     static public function uniteArrays( $arrPrecedence, $arrDefault1 ) {
                
        $arrArgs = array_reverse( func_get_args() );
        $arrArray = array();
        foreach( $arrArgs as $arrArg ) 
            $arrArray = self::uniteArraysRecursive( $arrArg, $arrArray );
            
        return $arrArray;
        
    } */
    /**
     * Merges two multi-dimensional arrays recursively.
     * 
     * The first parameter array takes its precedence. This is useful to merge default option values. 
     * An alternative to <em>array_replace_recursive()</em>; it is not supported PHP 5.2.x or below.
     * 
     * @static
     * @access            public
     * @remark            null values will be overwritten.     
     * @param            array            $arrPrecedence            the array that overrides the same keys.
     * @param            array            $arrDefault                the array that is going to be overridden.
     * @return            array            the united array.
     * @deprecated      1.4.1   Defined in a parent class.
     */ 
/*     static public function uniteArraysRecursive( $arrPrecedence, $arrDefault ) {
                
        if ( is_null( $arrPrecedence ) ) $arrPrecedence = array();
        
        if ( ! is_array( $arrDefault ) || ! is_array( $arrPrecedence ) ) return $arrPrecedence;
            
        foreach( $arrDefault as $strKey => $v ) {
            
            // If the precedence does not have the key, assign the default's value.
            if ( ! array_key_exists( $strKey, $arrPrecedence ) || is_null( $arrPrecedence[ $strKey ] ) )
                $arrPrecedence[ $strKey ] = $v;
            else {
                
                // if the both are arrays, do the recursive process.
                if ( is_array( $arrPrecedence[ $strKey ] ) && is_array( $v ) ) 
                    $arrPrecedence[ $strKey ] = self::uniteArraysRecursive( $arrPrecedence[ $strKey ], $v );
            
            }
        }
        return $arrPrecedence;        
    } */
    
    /**
     * Determines whether or not the ini_set() function can be used on the server.
     */
    static public function canUseIniSet() {
        
        static $_bCanUse;
        if ( isset( $_bCanUse ) ) {
            return $_bCanUse;
        }        
        $_bCanUse = ! function_exists( 'ini_set' ) ? false : ( false === @ini_set( get_class(), get_class() ) );        
        return $_bCanUse;
        
    }
    
    /**
     * Returns the server set max execution time in second.
     */
    static public function getServerAllowedMaxExecutionTime( $iDefault=30 ) {
        
        if ( function_exists( 'ini_get' ) ) {
            $_iMaxExecutionTime = @ini_get( 'max_execution_time' );    // returns false on failure
            return ! $_iMaxExecutionTime && 0 !== $_iMaxExecutionTime
                ? $iDefault
                : $_iMaxExecutionTime;
        }
        return $iDefault;
        
    }    
    
    /**
     * Converts characters not supported to be used in the URL query key to underscore.
     * 
     * @see            http://stackoverflow.com/questions/68651/can-i-get-php-to-stop-replacing-characters-in-get-or-post-arrays
     */
    static public function sanitizeCharsForURLQueryKey( $sString ) {

        $sString = trim( $sString );
        $_sSearch = array( chr( 32 ), chr( 46 ), chr( 91 ) );
        for ( $i=128; $i <= 159; $i++ ) {
            array_push( $_sSearch, chr( $i ) );
        }
        return str_replace( $_sSearch, '_', $sString );
        
    }    
    
}