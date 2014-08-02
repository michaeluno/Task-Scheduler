<?php
/**
 * The server heart beat class.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

 /**
  * Creates a periodical background page loads.
  * 
  * @action		schedule		task_scheduler_action_check_server_heartbeat		This action will check if the heartbeat is alive and if not, it resumes it.
  * @filter		apply			task_scheduler_filter_serverheartbeat_interval
  * @filter		apply			task_scheduler_filter_serverheartbeat_target_url
  * @filter		apply			task_scheduler_filter_serverheartbeat_cookies
  * 
  */
final class TaskScheduler_ServerHeartbeat {
			
	/**
	 * The transient key that stores the server heartbeat status.
	 */
	static public $sTransientKey = 'TS_server_heartbeat';	

	/**
	 * The transient key that stores whether the server heartbeat is sleeping.
	 */
	static public $sTransientKey_Sleep = 'TS_server_heartbeat_sleep';	
	
	/**
	 * The action name used for WP Cron.
	 */
	static public $sServerHeartbeatActionHook = 'task_scheduler_action_check_server_heartbeat';
	
	/**
	 * Indicates whether the heartbeat should stop.
	 */
	static private $_bStop = false;
	
	/**
	 * Returns the heartbeat ID.
	 */
	static public function getID() {		
		return isset( $_COOKIE{'server_heartbeat_id'} ) ? $_COOKIE{'server_heartbeat_id'} : '';
	}			

	/**
	 * Checks if the page load is in the background.
	 * 
	 * @remark	If the ID is an empty string, this yields false.
	 * @return	boolean	
	 */
	static public function isBackground() {
		return self::getID() ? true : false;
	}	

	/**
	 * Checks if the server heartbeat is sleeping.
	 */
	static public function isSleeping() {
		return ( false !== get_transient( self::$sTransientKey_Sleep ) );
	}	
	
	/**
	 * Tells whether the beat is alive.
	 */
	static public function isAlive() {

		if ( wp_next_scheduled( self::$sServerHeartbeatActionHook ) ) {
			return true;
		}
		
		if ( self::isSleeping() ) {
			return true;
		}
		
		$_iLastBeatTime	= self::getLastBeatTime();
		$_iInterval		= self::getInterval();
		$_bIsAlive = ( $_iLastBeatTime + $_iInterval > microtime( true ) );
		return $_bIsAlive;		
		
	}	
	
	/**
	 * Returns the next beat checking time.
	 */
	static public function getNextCheckTime() {
		return wp_next_scheduled( self::$sServerHeartbeatActionHook );
	}
	/**
	 * Returns the last beat time.
	 */
	static public function getLastBeatTime() {
		return self::_getInfo( 'last_beat_time' );		
	}	
	/**
	 * Retrieves the interval.
	 */
	static public function getInterval() {
			
		static $_iCached;
		$_iCached = isset( $_iCached ) ? $_iCached : ( int ) self::_getInfo( 'interval' );
		return ( int ) apply_filters( 'task_scheduler_filter_serverheartbeat_interval', $_iCached );
		
	}
		/**
		 * Returns the settings of the heart beat.
		 */
		static private function _getInfo( $sKey='' ) {
			
			$_aInfo = get_transient( self::$sTransientKey );
			if ( false === $_aInfo ) { return false; }
			
			$_aInfo = is_array( $_aInfo ) ? $_aInfo : array();
			if ( ! $sKey ) {
				return $_aInfo;
			}
			return isset( $_aInfo[ $sKey ] ) ? $_aInfo[ $sKey ] : null;
				
		}	
		/**
		 * Saves the heartbeat settings into a transient.
		 * 
		 * @remark	This is called in the both background and foreground.
		 */
		static private function _saveInfo( array $aSettings=array() ) {
			
			$_aInfo = array(
				'interval'			=>	self::getInterval(),
			) + ( array ) self::_getInfo();
			
			if ( self::isBackground() ) {
				$_aInfo['second_last_beat_time']	=	isset( $_aInfo['last_beat_time'] ) ? $_aInfo['last_beat_time'] : null;
				$_aInfo['last_beat_time']			=	microtime( true );
				$_aInfo['id']						=	self::getID();
			}
			$_aInfo = $aSettings + array_filter( $_aInfo ); // drop non-true values.
			set_transient( self::$sTransientKey, $_aInfo );	// made it not vanish by itself
			
		}	
	
	/**
	 * Starts the background periodical calls.
	 * 
	 * @remark	This method can be called both in the background and the foreground.
	 * @remark	array	$aSettings	The settings array. Currently accepts only one argument.
	 * 	Example:
	 *  array(
	 * 		'interval'	=>	30
	 *  )
	 * @return	boolean	true if it started; otherwise, false.
	 */
	static public function run( $aSettings=array() ) {
		
		$_bIsAlive = self::isAlive();
		
		self::_saveInfo( $aSettings );	// pass overriding settings(info).
		
		self::_scheduleToCheckBeat();
		
		// If it is alive, do nothing.
		if ( $_bIsAlive ) { return false; } 		
		
		// Start the beat.	
		add_action( 'shutdown', array( get_class(), '_replyToStart' ) );
		return true;
		
	}
		/**
		 * Starts the server heartbeat.
		 */
		static public function _replyToStart() {

			// Ensures it is done only once in a page load.
			static $_bIsCalled;
			if ( $_bIsCalled ) { return; }
			$_bIsCalled = true;	
								
			// Load the page in the background
			self::loadPage( '', array(), 'start' );

		}

	/**
	 * Stop the server heartbeat.
	 */
	static public function stop() {
		
		self::$_bStop = true;
		
		delete_transient( self::$sTransientKey );
		delete_transient( self::$sTransientKey_Sleep );
		$_iTimestamp = wp_next_scheduled( self::$sServerHeartbeatActionHook );
		if ( $_iTimestamp ) {
			wp_unschedule_event( $_iTimestamp, self::$sServerHeartbeatActionHook );
		}
	
	}	
	
	/**
	 * Creates a single page load.
	 * 
	 * Use this to create a background page load at a desired moment.
	 */
	static public function beat() {
		add_action( 'shutdown', array( get_class(), '_replyToBeat' ) );
	}
		static public function _replyToBeat() {
			
			static $_bIsLoaded = false;
			if ( $_bIsLoaded ) { return; }
			$_bIsLoaded = true;
			
			// Set an empty value to the ID so that the page load will be regarded as a background call.
			self::loadPage( '', array(), 'beat' );
							
		}
		
	
	/**
	 * Keeps beating.
	 *  
	 * It is assumed that every page load calls this method so that the heartbeat keeps going.
	 * 
	 * @remark	If this is called not in the background (the user opened a page), this does nothing. 
	 * However, if this is called in the background, meaning the class triggered the page load, it will re-trigger the background page load.
	 */
	static public function pulsate() {
		
		$_sClassName = get_class();	
		
		// If the page load is not in the background, just check to resume the heartbeat.
		if ( ! self::isBackground() ) {
			add_action( self::$sServerHeartbeatActionHook, array( $_sClassName, '_replyToCheck' ) );
			return;
		}		
		
		// At this point, the page is loaded in the background. Tell WordPress this is a background task by setting the Cron flag.
		if ( ! defined( 'DOING_CRON' ) ) { define( 'DOING_CRON', true ); }				
		ignore_user_abort( true );
		
		// Another heartbeat is already running. (this sometimes occurs but not sure why )
		if ( self::isSleeping() ) {
			return;
		}
				
		// If the transient does not exists, it means the user has stopped the beat.
		if ( false === self::_getInfo() ) {
			self::stop();
			
			// Do not call exit() here because when the server heartbeat is disabled but the user wants to check actions manually, 
			// the action checker class needs to be loaded.
			return;	
		}
		
		// Save the last beat time, the interval, and heartbeat ID etc.
		self::_saveInfo();			
		
		// Set the sleep transient and go sleep.
		add_action( 'wp_loaded', array( $_sClassName, '_replyToSleepAndExit' ), 20 );
				
	}
		
		/**
		 * Checks the heart beat.
		 */
		static public function _replyToCheck() {
			self::run();
		}		
		
		/**
		 * Exits the script after sleeping the given interval.
		 * 
		 * @remark		This method is only called from the beat() method so it is in the background.
		 */
		static public function _replyToSleepAndExit() {
						
			$_iInterval					= self::getInterval();
			$_iReservedSeconds			= 3;	// wp_remove_get() sometimes stalls
			$_nElapsedTime				= timer_stop( 0, 6 );
			$_nSleepDuration			= ( $_iInterval - $_nElapsedTime ) < 0 ? 0 : $_iInterval - $_nElapsedTime;	// to not allow a negative value.
			$_iMaxExecutionTime 		= function_exists( 'ini_get' ) ? ini_get( 'max_execution_time' ) : 25;
			$_iSecondsToLimit			= $_iMaxExecutionTime - ceil( $_nElapsedTime );
			$_nEstimatedRequiredTime	= $_nSleepDuration + $_iReservedSeconds;
			
			// If the estimated required time for the rest of the script execution is longer then the PHP max-execution time, 
			// attempt to override it.
			if ( $_iSecondsToLimit < $_nEstimatedRequiredTime ) {
				
				if ( function_exists( 'ini_set' ) ) {	// Some servers disable this function.
					// Make the execution time longer.
					@ini_set( 'max_execution_time', ceil( $_nEstimatedRequiredTime + $_nElapsedTime ) + 10 );
				} else {
					// Shorten the sleep time.
					$_nSleepDuration = $_iSecondsToLimit - $_iReservedSeconds;	
				}		
			} 							
			
			self::_sleep( $_nSleepDuration );
			self::_pulsate();
			exit();			
			
		} 
			/**
			 * Sleeps.
			 */
			static private function _sleep( $nSleepDuration ) {
				
				// Give the interval - for example, to wait for 2 seconds, pass 2000000. 
				if ( $nSleepDuration <= 0 ) { return; }

				// Be careful not to set 0 for the cache duration.
				$_iTransientDuration = ( int ) floor( $nSleepDuration );
				if ( $_iTransientDuration ) {					
					set_transient( self::$sTransientKey_Sleep, self::getID(), $_iTransientDuration );						
				}
				usleep( $nSleepDuration * 1000000 ); 
				if ( $_iTransientDuration )	 {
					self::_deleteSleepTransient();
				}
			
			}
			/**
			 * Deletes the sleep lock transient.
			 */
			static private function _deleteSleepTransient() {				
			
				// If the transient ID is different, it means another different heartbeat is pulsating.
				// $_sSleepID = get_transient( self::$sTransientKey_Sleep );
				$_sSleepID = self::_getSleepLock();
				if ( false !== $_sSleepID && self::getID() !== $_sSleepID ) {
					self::$_bStop = true;
					return;
				}			
				delete_transient( self::$sTransientKey_Sleep );				
				
			}
			/**
			 * Retrieve the sleep transient value directly from the database.
			 */
			static private function _getSleepLock() {
			
				if ( wp_using_ext_object_cache() ) {
					// Skip local cache and force re-fetch of doing_cron transient in case
					// another processes updated the cache
					return wp_cache_get( self::$sTransientKey_Sleep, 'transient', true );
				} 			
			
				global $wpdb;			
				$_oRow = $wpdb->get_row( 
					$wpdb->prepare( 
						"SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1",
						'_transient_' . self::$sTransientKey_Sleep
					) 
				);
				return is_object( $_oRow ) ? $_oRow->option_value: false;
				
			}
			
			
		/**
		 * Pulsates.
		 */
		static private function _pulsate() {
							
			if ( self::$_bStop ) { return; }	
					
			// Ensures it is done only once in a page load.
			static $_bIsCalled;
			if ( $_bIsCalled ) { return; }
			$_bIsCalled = true;	
					
			// For a safety net
			self::_scheduleToCheckBeat();
						
			// Load the page in the background
			self::loadPage();

		}
		
		/**
		 * Schedules the action to run in the background with WP Cron.	
		 */
		static private function _scheduleToCheckBeat() {
			
			if ( wp_next_scheduled( self::$sServerHeartbeatActionHook ) ) { return; }
			wp_schedule_single_event( time() + self::getInterval() + 1, self::$sServerHeartbeatActionHook );
			
		}				
	
	/**
	 * Loads the page in the background.
	 * 
	 * @param	string	$sTargetURL	The target url to load. It must be within the WordPress installed site.
	 * @param	array	$aCookies	The cookie array to pass to the next page load.
	 * @param	string	$sContext	Identifies why this method is called. If it is the server heartbeat automatically call, it will be 'pulsate'.
	 * This gives a hint to the callback functions that they should modify the given url or not.
	 * Currently the following three slugs are used as the context.
	 *  - start		: the heartbeat is starting
	 * 	- pulsate	: the recurrent heartbeat page load
	 * 	- beat		: an irregular background page load
	 */
	static public function loadPage( $sTargetURL='', $aCookies=array(), $sContext='pulsate' ) {
		
		$_sID		= self::getID();
		$_sID		= $_sID ? $_sID : uniqid();
		$sTargetURL = apply_filters(
			'task_scheduler_filter_serverheartbeat_target_url', 
			$sTargetURL ? $sTargetURL : add_query_arg( array( 'doing_server_heartbeat' => microtime( true ), 'id' => $_sID, 'context' => $sContext ), trailingslashit( site_url() ) ), // the Apache log indicates that if a trailing slash misses, it redirects to the url WITH it.
			$sContext 
		);		

		$aCookies	= $aCookies + array( 
			'server_heartbeat_id'		=>	$_sID, 
			'server_heartbeat_context'	=>	$sContext,
		);
		$aCookies	= apply_filters( 'task_scheduler_filter_serverheartbeat_cookies', $aCookies, $sContext );
		wp_remote_get(
			$sTargetURL,	// the target URL
			array( 	// HTTP Request Argument
				'timeout'	=>	0.01, 
				'sslverify'	=>	false, 
				'cookies'	=>	$aCookies,
			) 
		);			

	}

}