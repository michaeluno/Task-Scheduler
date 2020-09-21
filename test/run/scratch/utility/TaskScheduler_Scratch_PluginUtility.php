<?php


class TaskScheduler_Scratch_PluginUtility extends TaskScheduler_Scratch_Base {

    /**
     * @purpose Check what returns.
     * @return string
     */
    public function scratch_getCurrentAdminURL() {
        return $this->_getDetails( TaskScheduler_PluginUtility::getCurrentAdminURL() );
    }

    /**
     * @purpose Check what returns.
     * @return string
     */
    public function scratch_getCurrentURL() {
         return $this->_getDetails( TaskScheduler_PluginUtility::getCurrentURL() );
    }

}