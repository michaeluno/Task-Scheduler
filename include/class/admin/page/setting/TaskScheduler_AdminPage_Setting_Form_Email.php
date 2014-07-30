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

abstract class TaskScheduler_AdminPage_Setting_Form_Email extends TaskScheduler_AdminPage_Setting_Form_Reset {

	/**
	 * Defines the settings form.
	 * 
	 * @deprecated	Will be implemented in the future.
	 */
	protected function __defineForm() {

		$this->addInPageTabs(
			TaskScheduler_Registry::AdminPage_Setting,	// the target page slug 
			array(
				'tab_slug'	=>	'email',	// avoid hyphen(dash), dots, and white spaces
				'title'		=>	__( 'Email', 'task-scheduler' ),
			)
		);
	
		$this->addSettingSections(
			TaskScheduler_Registry::AdminPage_Setting,	// the target page slug
			array(
				'section_id'	=>	'email',
				'tab_slug'		=>	'email',
				'title'			=>	__( 'Email', 'task-scheduler' ),
			)			
		);		
		
		$this->addSettingFields(
			'email',	// the target section ID
			array(	
				'field_id'			=>	'message_body',
				'title'				=>	__( 'Message Body', 'task-scheduler' ),
				'type'				=>	'textarea',
				'rich'				=>	true,
				'description'		=>	__( 'Define what message should be sent.', 'task-scheduler' ),
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