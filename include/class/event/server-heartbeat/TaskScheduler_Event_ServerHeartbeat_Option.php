<?php
/**
 * Deals with the server heartbeat hooks.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

 /**
  * 
  */
class TaskScheduler_Event_ServerHeartbeat_Option {
		
	public function __construct() {

		add_filter( 'task_scheduler_filter_serverheartbeat_interval', array( $this, '_replyToModifyServerHeartbeatInterval' ) );
		add_filter( 'task_scheduler_filter_serverheartbeat_target_url', array( $this, '_replyToModifyServerHeartbeatTargetURL' ), 10, 2 );
		
	}
	
	/**
	 * Returns the saved interval option value.
	 */
	public function _replyToModifyServerHeartbeatInterval( $iInterval ) {

		$_bPowered = TaskScheduler_Option::get( array( 'server_heartbeat', 'power' ) );
		return $_bPowered
			? TaskScheduler_Option::get( array( 'server_heartbeat', 'interval' ) )
			: 0;
	}
	/**
	 * Returns the target url based on the saved query string.
	 */
	public function _replyToModifyServerHeartbeatTargetURL( $sURL, $sContext ) {
		
		if ( ! in_array( $sContext, array( 'start', 'pulsate', 'beat', 'spawn_routine' ) )  ) {
			return $sURL;
		}
		$_bEmbedQueryKey	= TaskScheduler_Option::get( array( 'server_heartbeat', 'query_string', 0 ) );
		$_sQueryKey 		= TaskScheduler_Option::get( array( 'server_heartbeat', 'query_string', 1 ) );
		$_iCurrentMicrotime	= microtime( true );
		$_aEmbedQueryKeys	= $_bEmbedQueryKey && $_sQueryKey 
			? array( 
				$_sQueryKey	=>	$_iCurrentMicrotime,
				'context'			=>	$sContext,
				'last_beat_time'	=>	TaskScheduler_ServerHeartbeat::getLastBeatTime(),				
			) 
			: array();
		return add_query_arg(
			$_aEmbedQueryKeys,
			$_bEmbedQueryKey ? $sURL : site_url()
		);
		 
	}
	
}
