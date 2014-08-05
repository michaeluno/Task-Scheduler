<?php
/**
 * The class that defines the Email action for the Task Scheduler plugin.
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
class TaskScheduler_Action_Email extends TaskScheduler_Action_Base {
		
	/**
	 * The user constructor.
	 * 
	 * This method is automatically called at the end of the class constructor.
	 */
	public function construct() {
									
		new TaskScheduler_Action_Email_Thread( 'task_scheduler_action_send_indiviual_email' );
		
	}
	
	/**
	 * Returns the readable label of this action.
	 * 
	 * This will be called when displaying the action in an pull-down select option, task listing table, or notification email message.
	 */
	public function getLabel( $sLabel ) {
		return __( 'Send Emails', 'task-scheduler' );
	}
	
	/**
	 * Returns the description of the module.
	 */
	public function getDescription( $sDescription ) {
		return __( 'Sends an email to specified email addresses.', 'task-scheduler' );
	}	
		
	/**
	 * Defines the behavior of the task action.
	 */
	public function doAction( $isExitCode, $oTask ) {
		
		$_aTaskMeta = $oTask->getMeta();
		if ( 
			! isset( 
				$_aTaskMeta[ $this->sSlug ],
				$_aTaskMeta[ $this->sSlug ]['email_addresses'],
				$_aTaskMeta[ $this->sSlug ]['email_title'],
				$_aTaskMeta[ $this->sSlug ]['email_message']
			) 
			|| ! is_array( $_aTaskMeta[ $this->sSlug ]['email_addresses']  ) 
		) {
			return 0;	// failed
		}
		
		// Handle each email per thread (spawn the subroutine in the background each)
		$_iThreads = 0;
		$_aEmailOptions = $_aTaskMeta[ $this->sSlug ];
		foreach( $_aEmailOptions['email_addresses'] as $_iIndex => $_sEmailAddress ) {
					
			$_aTaskOptions = array(
			
				// Required
				'routine_action'		=>	'task_scheduler_action_send_indiviual_email',
				'post_title'			=>	sprintf( __( 'Thread %1$s of %2$s', 'task-scheduler' ), $_iIndex + 1, $oTask->post_title ),
				'parent_routine_log_id'	=>	$oTask->log_id,		// the log_id key is set when a routine starts
				
				// internal options
				'_next_run_time'		=>	microtime( true ) + $_iIndex,	// add an offset so that they will be loaded with a delay of a second each.
				
				// Routine specific options
				'email_address'			=>	$_sEmailAddress,	// this action specific custom argument
				'email_title'			=>	$_aEmailOptions['email_title'],
				'email_message'			=>	$_aEmailOptions['email_message'],
				
			);
			
			$_iThreadTaskID	= TaskScheduler_ThreadUtility::add( $oTask->ID, $_aTaskOptions );
			$_iThreads		= $_iThreadTaskID ? ++$_iThreads : $_iThreads;

		}
		
		// Check actions in the background.
		if ( $_iThreads ) {
			do_action( 'task_scheduler_action_check_shceduled_actions' );
		}
		
		return null;	// exit code: do not log; it will be, when the threads finish.
		
	}
			
}
