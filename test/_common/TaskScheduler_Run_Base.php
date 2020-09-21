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
 * A unit test base class.
 *  
 * @package     Task Scheduler
 * @since       1.5.2
*/
class TaskScheduler_Run_Base extends TaskScheduler_PluginUtility {

    /**
     * @param $mValue
     * @return string
     */
    protected function _getDetails( $mValue ) {
        return TaskScheduler_Debug::getDetails( $mValue );
    }

    /**
     * @param $sErrorMessage
     * @throws Exception
     */
    protected function _throwError( $sErrorMessage, $isCode=0 ) {
        throw new Exception( $sErrorMessage, $isCode );
    }

}