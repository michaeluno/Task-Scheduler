<?php
/**
 * Amazon Auto Links
 *
 * Generates links of Amazon products just coming out today. You just pick categories and they appear even in JavaScript disabled browsers.
 *
 * http://en.michaeluno.jp/amazon-auto-links/
 * Copyright (c) 2013-2020 Michael Uno
 */

/**
 * Adds an in-page tab to an admin page.
 * 
 * @since       1.5.2
 * @extends     TaskScheduler_AdminPage_Tab_Base
 */
class TaskScheduler_AdminPage_Tab_Scratch extends TaskScheduler_AdminPage_Tab_Unit {

    /**
     * @return  array
     * @since   1.5.2
     */
    protected function _getArguments() {
        return array(
            'tab_slug'  => 'scratches',
            'title'     => 'Scratches',
        );
    }

    /**
     * Triggered when the tab is loaded.
     * 
     * @callback        action      load_{page slug}_{tab slug}
     */
    protected function _loadTab( $oAdminPage ) {
        parent::_loadTab( $oAdminPage );
    }

    /**
     * @return array
     */
    protected function _getTagLabelsForCheckBox() {
        return $this->_getTagLabels( TaskScheduler_Registry::$sDirPath . '/test/run/scratch', array( 'TaskScheduler_Scratch_Base' ) );
    }


    /**
     * Write scratches here to test something.
     * @callback        action      do_{page slug}_{tab slug}
     */
    protected function _doTab( $oFactory ) {
        parent::_doTab( $oFactory );

    }
        protected function _printFiles() {
            echo "<div class='files-container'>";
            echo "<h4>Files</h4>";
            $_oFinder = new TaskScheduler_ClassFinder( TaskScheduler_Registry::$sDirPath . '/test/run/scratch', array( 'TaskScheduler_Scratch_Base' ) );
            TaskScheduler_Debug::dump( $_oFinder->getFiles() );
            echo "</div>";
        }
            
}