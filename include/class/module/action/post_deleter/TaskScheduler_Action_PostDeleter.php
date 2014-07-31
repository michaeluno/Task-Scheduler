<?php
/**
 * The class that defines the action of Delete Posts for the Task Scheduler plugin.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

 /**
  * Creates a thread with the constant occurrence type and deletes posts that matches the user set argument.
  * 
  */
class TaskScheduler_Action_PostDeleter extends TaskScheduler_Action_Base {
		
	/**
	 * The user constructor.
	 * 
	 * This method is automatically called at the end of the class constructor.
	 */
	public function construct() {
									
		new TaskScheduler_Action_PostDeleter_Thread( 'task_scheduler_action_post_deleter_thread' );

	}

	/**
	 * Returns the readable label of this action.
	 * 
	 * This will be called when displaying the action in an pull-down select option, task listing table, or notification email message.
	 */
	public function getLabel( $sLabel ) {
		return __( 'Delete Posts', 'task-scheduler' );
	}

	/**
	 * Defines the behavior of the task action.
	 * 
	 * Required arguments: 
	 * - post_type_of_deleting_posts		string
	 * - post_statuses_of_deleting_posts	array
	 * - taxonomy_of_deleting_posts			string
	 * - term_ids_of_deleting_posts		array
	 * 
	 */
	public function doAction( $sExitCode, $oRoutine ) {
		
		$_aRoutineMeta		= $oRoutine->getMeta();	// the task meta data
		$_aRoutineArguments	= isset( $_aRoutineMeta[ $this->sSlug ] ) ? $_aRoutineMeta[ $this->sSlug ] : array();	// the task specific options(arguments)
		if ( 
			! isset(	
				$_aRoutineMeta[ $this->sSlug ],
				$_aRoutineArguments['post_type_of_deleting_posts'],
				$_aRoutineArguments['post_statuses_of_deleting_posts'],
				$_aRoutineArguments['taxonomy_of_deleting_posts']
				// $_aRoutineArguments['term_ids_of_deleting_posts']
			) 
		) {
			return 0;	// failed
		}
		
		if ( $oRoutine->hasThreads() ) {
			$oRoutine->log( 'There is a thread already.' );
			return 0;
		}

		// Create a thread with the constant occurrence type that handle the job.		
		$_iThreadTaskID	= TaskScheduler_ThreadUtility::create( 
			$oRoutine->ID, 
			array(
				// plugin specific arguments
				'_next_run_time'		=>	microtime( true ),
				'routine_action'		=>	'task_scheduler_action_post_deleter_thread',
				'post_title'			=>	sprintf( __( 'Thread %1$s of %2$s', 'task-scheduler' ), 1, $oRoutine->post_title ),
				'post_excerpt'			=>	sprintf( __( 'Deletes posts defined in %1$s', 'task-scheduler' ), $oRoutine->post_title ),
				// 'occurrence'			=>	'constant',			// must be 'constant' so that the thread will stay forever until the job gets done.
				'parent_routine_log_id'	=>	$oRoutine->log_id,
				// module specific arguments
				'post_type_of_deleting_posts'		=>	$_aRoutineArguments['post_type_of_deleting_posts'],
				'post_statuses_of_deleting_posts'	=>	$_aRoutineArguments['post_statuses_of_deleting_posts'],
				'taxonomy_of_deleting_posts'		=>	$_aRoutineArguments['taxonomy_of_deleting_posts'],
				'term_ids_of_deleting_posts'		=>	isset( $_aRoutineArguments['term_ids_of_deleting_posts'] ) ? $_aRoutineArguments['term_ids_of_deleting_posts'] : null,
			)			
		);

		// Check actions in the background.
		if ( $_iThreadTaskID ) {			
			TaskScheduler_ServerHeartbeat::beat();
		}
		
		return null;	// exit code: do not log; it will be, when the threads finish.
		
	}
			
}