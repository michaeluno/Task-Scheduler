<?php
/**
 * Task Scheduler
 *
 * Provides an enhanced task management system for WordPress.
 *
 * http://en.michaeluno.jp/task-scheduler/
 * Copyright (c) 2013-2020 Michael Uno
 */

/**
 * Performs scratches.
 * @since   1.5.2
 *
 */
class TaskScheduler_Test_Event_Ajax_Scratches extends TaskScheduler_Test_Event_Ajax_Tests {

    protected $_sActionHookSuffix = 'task_scheduler_action_admin_do_scratches';
    protected $_bLoggedIn = true;
    protected $_bGuest    = false;

    /**
     * @since   1.5.2
     * @return  void
     */
    protected function _construct() {
        // load_{page slug}_{tab slug}
        add_action( 'load_ts_tests_scratches', array( $this, 'replyToEnqueueResources' ) );
    }
        /**
         * @since       1.5.2
         * @return      void
         */
        public function replyToEnqueueResources() {
            $this->_enqueueResources( TaskScheduler_Registry::$sDirPath . '/test/run/scratch', array( 'TaskScheduler_Scratch_Base' ), 'scratch' );
        }

    /**
     * @param string $sClassName The class name to test.
     * @param string $sFilePath The file path of the class.
     * @param array $aTags Tags set in the `@tags` annotation in test method doc-blocks.
     * @param string $sMethodPrefix The prefix of methods to test.
     * @return array
     * @throws ReflectionException
     * @since  1.5.2
     */
    protected function _getResults( $sClassName, $sFilePath, array $aTags=array(), $sMethodPrefix='scratch' ) {
        return parent::_getResults( $sClassName, $sFilePath, $aTags, 'scratch' );
    }

}