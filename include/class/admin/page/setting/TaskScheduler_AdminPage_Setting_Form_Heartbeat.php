<?php
/**
 * One of the abstract class of the plugin admin page class.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

abstract class TaskScheduler_AdminPage_Setting_Form_Heartbeat extends TaskScheduler_AdminPage_Setting_Form_Task {

	/**
	 * Defines the settings form.
	 */
	protected function _defineForm() {
		
		$this->addInPageTabs(
			TaskScheduler_Registry::AdminPage_Setting,	// the target page slug 
			array(
				'tab_slug'	=>	'server_heartbeat',	// avoid hyphen(dash), dots, and white spaces
				'title'		=>	__( 'Server Heartbeat', 'task-scheduler' ),
			)
		);
	
		$this->addSettingSections(
			TaskScheduler_Registry::AdminPage_Setting,	// the target page slug
			array(
				'section_id'	=>	'server_heartbeat',
				'tab_slug'		=>	'server_heartbeat',
				'title'			=>	__( 'Heartbeat', 'task-scheduler' ),
			)			
		);		
		
		$this->addSettingFields(
			'server_heartbeat',	// the target section ID
			array(	
				'field_id'			=>	'power',
				'title'				=>	__( 'Heartbeat', 'task-scheduler' ),
				'type'				=>	'radio',
				'default'			=>	1,
				'label'				=>	array(
					1	=>	__( 'On', 'task-scheduler' ),
					0	=>	__( 'Off', 'task-scheduler' ),
				),
				'description'		=>	__( 'Decide whether the server checks the tasks in the background.', 'task-scheduler' ),
			),			
			array(
				'field_id'			=>	'instruction',
				'type'				=>	'text',
				'if'				=>	! TaskScheduler_Option::get( array( 'server_heartbeat', 'power' ) ),
				'attributes'		=>	array(
					'readonly'	=>	'ReadOnly',
					'name'		=>	'',	// dummy
					'size'		=>	60,
				),
				'value'				=>	site_url( '/?task_scheduler_checking_actions=1' ),
				'before_fieldset'	=>	'<p class="warning">' . '* ' . __( 'Set up a Cron job that accesses the following url to check scheduled actions.', 'task-scheduler' ) . '</p>',	
			),
			array(	
				'field_id'			=>	'status',
				'title'				=>	__( 'Status', 'task-scheduler' ),
				'type'				=>	'text',
				'attributes'		=>	array(
					'readonly'	=>	'ReadOnly',
					'name'		=>	'',	// dummy
				),
				array(),	// sub-field to show last checked time
			),			
			array(	
				'field_id'			=>	'interval',
				'title'				=>	__( 'Interval', 'task-scheduler' ),
				'type'				=>	'number',
				'after_label'		=>	' ' . __( 'second(s)', 'task-scheduler' ),
				'description'		=>	__( 'Set the interval in seconds that the plugin checks the tasks in the background.', 'task-scheduler' )
					. ' ' . __( 'This may not take effect if the server has a restriction on PHP\'s maximum execution time and <code>ini_set()</code> function.' ),	//'
				'attributes'		=>	array(
					'min'	=>	0,
					'step'	=>	1,
					'max'	=>	TaskScheduler_WPUtility::canUseIniSet() 
						? null
						: TaskScheduler_WPUtility::getServerAllowedMaxExecutionTime( 24 ),
				),
			),
			array(	
				'field_id'			=>	'query_string',
				'title'				=>	__( 'URL', 'task-scheduler' ),
				'type'				=>	'checkbox',
				'default'			=>	true,
				'label'				=>	__( 'Show the query string in the request URL of the background page-load that to indicate the server heartbeat.', 'task-scheduler' ),
				array(
					'type'		=>	'text',
					'label'		=>	__( 'Key', 'task-scheduler' ),
					'default'	=>	'doing_server_heartbeat',
				),
			),			
			array(	
				'field_id'			=>	'submit',
				'type'				=>	'submit',
				'label'				=>	__( 'Save', 'task-scheduler' ),
				'label_min_width'	=>	0,
				'attributes'		=>	array(
					'field'	=>	array(
						'style'	=>	'float:right; clear:none; display: inline;',
					),
				),					
			)	
		);
		
		parent::_defineForm();
		
	}
	
	/**
	 * Redefines the field.
	 */
	public function field_definition_TaskScheduler_AdminPage_Setting_server_heartbeat_status( $aField ) {	// field_definition_{instantiated class name}_{section id}_{field id}
			
		$aField['before_label'] = "<span class='description-label'>" . __( 'Last Beat Time', 'task-scheduler' ) . ":</span>";
		$aField[ 0 ]['before_label'] = "<span class='description-label'>" . __( 'Next Check', 'task-scheduler' ) . ":</span>";
								
		$_sLastBeatTime	= TaskScheduler_WPUtility::getSiteReadableDate( 
			TaskScheduler_ServerHeartbeat::getLastBeatTime(), 
			get_option( 'date_format' ) . ' G:i:s',
			true 
		);
		$aField['value']	= $_sLastBeatTime;
		$aField[ 0 ]['value']	= TaskScheduler_WPUtility::getSiteReadableDate( 
			TaskScheduler_ServerHeartbeat::getNextCheckTime(), 
			get_option( 'date_format' ) . ' G:i:s',
			true 
		);
		return $aField;
	}
	
	/**
	 * Validates the submitted form data.
	 */
	public function validation_TaskScheduler_AdminPage_Setting_server_heartbeat( $aInput, $aOldInput, $oAdminPage ) {

		// Sanitize the query key
		if ( isset( $aInput['query_string'][ 1 ] ) ) {
			$aInput['query_string'][ 1 ] = TaskScheduler_WPUtility::sanitizeCharsForURLQueryKey( $aInput['query_string'][ 1 ] );
		}	
	
		// Stop / Start the server heartbeat.
		if ( isset( $aInput['power'], $aInput['interval'] ) 
			&& $aInput['power'] 
			&& $aInput['interval']
		) {
			
			$_sTargetURL = $aInput['query_string'][ 0 ] && $aInput['query_string'][ 1 ]
				? add_query_arg(
					array( $aInput['query_string'][ 1 ]	=>	microtime( true ), ),
					site_url()
				)
				: site_url();
			TaskScheduler_ServerHeartbeat::run( array( 'target_url' => $_sTargetURL, 'interval' => $aInput['interval'] ) );
			
		}
		
		if ( isset( $aInput['power'], $aInput['interval'] ) 
			&& ( ! $aInput['power'] || ! $aInput['interval'] )
		) {
			TaskScheduler_ServerHeartbeat::stop();			
		}
						
		return $aInput;
		
	}

}