<?php
/**
 * One of the abstract class of the plugin admin page class that adds setting pages.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

abstract class TaskScheduler_AdminPage_Setting_Form_Task extends TaskScheduler_AdminPage_Setting_Form_Email {

	public function setUp() {
		
		$this->addInPageTabs(
			TaskScheduler_Registry::AdminPage_Setting,	// the target page slug 
			array(
				'tab_slug'	=>	'task',	// avoid hyphen(dash), dots, and white spaces
				'title'		=>	__( 'Task', 'task-scheduler' ),
			)
		);	
		parent::setUp();
		
	}

	/**
	 * Defines the settings form.
	 */
	protected function _defineForm() {
	
		$this->addSettingSections(
			TaskScheduler_Registry::AdminPage_Setting,	// the target page slug
			array(
				'section_id'	=>	'routine',
				'tab_slug'		=>	'task',
				'title'			=>	__( 'Task', 'task-scheduler' ),
				'description'	=>	__( 'The general settings regarding task routines.', 'task-scheduler' ),
			),
			array(
				'section_id'	=>	'task_default',
				'tab_slug'		=>	'task',
				'title'			=>	__( 'Default', 'task-scheduler' ),
				'description'	=>	__( 'The settings of individual tasks take precedence.', 'task-scheduler' ),
			)				
		);		
		
		$this->addSettingFields(
			'routine',	// the target section ID
			array(	
				'field_id'			=>	'max_background_routine_count',
				'title'				=>	__( 'Max Number of Background Routines', 'task-scheduler' ),
				'type'				=>	'number',
				'description'		=>	__( 'The larger the number, the faster the tasks get completed but consume server resources.', 'task-scheduler' ),
			)
		);		
		
		$this->addSettingFields(
			'task_default',	// the target section ID
			array(	
				'field_id'			=>	'max_root_log_count',
				'title'				=>	__( 'Max Count of Log Entries', 'task-scheduler' ),
				'type'				=>	'number',
				'description'		=>	__( 'The default number of allowed max count of log items.', 'task-scheduler' ),
				'attributes'		=>	array(
					'min'	=>	0,
					'step'	=>	1,
				),
			),			
			array(	
				'field_id'			=>	'max_execution_time',
				'title'				=>	__( 'Max Task Execution Time', 'task-scheduler' ),
				'type'				=>	'number',
				'after_label'		=>	' ' .__( 'second(s)', 'task-scheduler' ),
				'description'		=>	__( 'The default duration of the max task execution.', 'task-scheduler' ),
				'attributes'		=>	array(
					'min'	=>	0,
					'step'	=>	1,				
					'max'	=>	TaskScheduler_WPUtility::canUseIniSet() 
						? null
						: TaskScheduler_WPUtility::getServerAllowedMaxExecutionTime( 30 ),
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
	
}