<?php
/**
 * Provides the task management system.
 *
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, <Michael Uno>
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
     * Stores action hook names for multiple actions.
     * @var array
     * @since   4.3.0
     */
    protected $_aActionHookNames    = array();

    /**
     * TaskScheduler_Event_Action_DeleteThreads constructor.
     */
    public function __construct() {

        if ( $this->_sActionHookName ) {
            add_action(
                $this->_sActionHookName,
                array( $this, 'replyToDoAction' ),
                $this->_iPriority,
                $this->_iNumberOfParams
            );
        }

        foreach( $this->_aActionHookNames as $_sActionHookName ) {
            add_action(
                $_sActionHookName,
                array( $this, 'replyToDoAction' ),
                $this->_iPriority,
                $this->_iNumberOfParams
            );
        }
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
     * @return bool
     * @since 1.5.2
     */
    protected function _shouldProceed( /* $aArguments */ ) {
        return true;
    }

    /**
     * @callback action $this->_sActionHookName
     */
    public function replyToDoAction() {
        $_aParameters = func_get_args();
        if ( ! call_user_func_array( array( $this, '_shouldProceed' ), $_aParameters ) ) {
            return;
        }
        call_user_func_array( array( $this, '_doAction' ), $_aParameters );
    }

}