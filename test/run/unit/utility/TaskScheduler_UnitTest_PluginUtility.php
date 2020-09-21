<?php


class TaskScheduler_UnitTest_PluginUtility extends TaskScheduler_UnitTest_Base {

    /**
     * @purpose check if getCurrentAdminURL() and getCurrentURL() returns the same value.
     * @tags url
     * @return bool
     */
    public function test_getCurrentURLAndAdminURL() {
        return TaskScheduler_PluginUtility::getCurrentAdminURL() === TaskScheduler_PluginUtility::getCurrentURL();
    }

}