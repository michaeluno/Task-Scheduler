<?php
/**
 * Provides the task management system.
 *
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 *
 * @since   1.5.0
 */
abstract class TaskScheduler_Event_Action_Base extends TaskScheduler_PluginUtility {

    protected $_sActionHookName = '';
    protected $_iNumberOfParams = 1;
    protected $_iPriority       = 10;

    /**
     * TaskScheduler_Event_Action_DeleteThreads constructor.
     */
    public function __construct() {
        add_action( $this->_sActionHookName, array( $this, 'replyToDoAction' ), $this->_iPriority, $this->_iNumberOfParams );
        $this->_construct();
    }

    /**
     * Override in an extended class.
     */
    protected function _construct() {}

    /**
     * Override in an extended class.
     */
    protected function _doAction() {}

    /**
     * @callback action $this->_sActionHookName
     */
    public function replyToDoAction() {
        call_user_func_array( array( $this, '_doAction' ), func_get_args() );
    }

}