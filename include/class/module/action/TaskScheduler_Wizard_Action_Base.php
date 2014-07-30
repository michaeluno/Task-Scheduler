<?php
/**
 * An abstract class of the 'Action' wizard hidden tabbed pages.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

 /**
  * 
  * @filter		add		task_scheduler_admin_filter_field_labels_wizard_action				Receives an array of labels used for a selector field. Each extended action class will insert own label with the key of the action slug so that their action will be listed in the selector.
  * @filter		add		task_scheduler_filter_label_action_{action slug}				Receives a string of a label usually an empty one and the callback should return the human readable label of the slug.
  * @filter		add		task_scheduler_admin_filter_wizard_action_redirect_url_{action slug}
  */
abstract class TaskScheduler_Wizard_Action_Base extends TaskScheduler_Wizard_Base {

	/**
	 * The next tab slug.
	 * 
	 * @remark	The scope must be public as the module factory class modifies it when multiple wizard screens are added.
	 */
	public $sNextTabSlug = 'wizard_create_task';
	
	/**
	 * The wizard type.
	 * 
	 * This is used for filter names,
	 */
	protected $_sModuleType = 'action';		
	
}