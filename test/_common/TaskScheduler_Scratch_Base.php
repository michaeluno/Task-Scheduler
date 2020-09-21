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
 * A scratch base class.
 *  
 * @package     Task Scheduler
 * @since       1.5.2
*/
class TaskScheduler_Scratch_Base extends TaskScheduler_Run_Base {

    /**
     * Override this method.
     * @return mixed
     */
    public function scratch() {
        return true;
    }


}