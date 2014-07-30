<?php
/**
 * The class that provides utility methods without WordPress built-in functions.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

abstract class TaskScheduler_Utility {
	
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
		// return ( json_last_error() == JSON_ERROR_NONE );	// not available in 5.2.x or below
		
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
		$_vTemp							= $aSubject[ $isDestinationKey ];  
		$aSubject[ $isDestinationKey ]	= $aSubject[ $isSourceKey ];
		$aSubject[ $isSourceKey ]		= $_vTemp;
		return $aSubject;
	}

	
	/**
	 * Merges multiple multi-dimensional array recursively.
	 * 
	 * The advantage of using this method over the array unite operator or array_merge() is that it merges recursively and the null values of the preceding array will be overridden.
	 *
	 * @static
	 * @access			public
	 * @remark			The parameters are variadic and can add arrays as many as necessary.
	 * @return			array			the united array.
	 */
	static public function uniteArrays( $arrPrecedence, $arrDefault1 ) {
				
		$arrArgs = array_reverse( func_get_args() );
		$arrArray = array();
		foreach( $arrArgs as $arrArg ) 
			$arrArray = self::uniteArraysRecursive( $arrArg, $arrArray );
			
		return $arrArray;
		
	}
	/**
	 * Merges two multi-dimensional arrays recursively.
	 * 
	 * The first parameter array takes its precedence. This is useful to merge default option values. 
	 * An alternative to <em>array_replace_recursive()</em>; it is not supported PHP 5.2.x or below.
	 * 
	 * @static
	 * @access			public
	 * @remark			null values will be overwritten. 	
	 * @param			array			$arrPrecedence			the array that overrides the same keys.
	 * @param			array			$arrDefault				the array that is going to be overridden.
	 * @return			array			the united array.
	 */ 
	static public function uniteArraysRecursive( $arrPrecedence, $arrDefault ) {
				
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
	}
	
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
			$_iMaxExecutionTime = @ini_get( 'max_execution_time' );	// returns false on failure
			return ! $_iMaxExecutionTime && 0 !== $_iMaxExecutionTime
				? $iDefault
				: $_iMaxExecutionTime;
		}
		return $iDefault;
		
	}	
	
	/**
	 * Converts characters not supported to be used in the URL query key to underscore.
	 * 
	 * @see			http://stackoverflow.com/questions/68651/can-i-get-php-to-stop-replacing-characters-in-get-or-post-arrays
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