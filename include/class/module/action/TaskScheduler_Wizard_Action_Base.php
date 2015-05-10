<?php
/**
 * An abstract class of the 'Action' wizard hidden tabbed pages.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

 /**
  * 
  */
abstract class TaskScheduler_Wizard_Action_Base extends TaskScheduler_Wizard_Base {

    /**
     * The next tab slug.
     * 
     * @remark    The scope must be public as the module factory class modifies it when multiple wizard screens are added.
     */
    public $sNextTabSlug = 'wizard_create_task';
    
    /**
     * The wizard type.
     * 
     * This is used for filter names,
     */
    protected $_sModuleType = 'action';        

}