<?php
class TaskScheduler_AdminPage_Page_Unit extends TaskScheduler_AdminPage_Page_Base {

    protected function _getArguments() {
        return array(
            'title'            => 'Tests',    // page and menu title
            'page_slug'        => TaskScheduler_Registry::$aAdminPages[ 'test' ],    // page slug
            'order'            => 9999,  // sub-menu order
            'style'            => array(
                TaskScheduler_Registry::$sDirPath . '/asset/css/ts_tests.css'
            ),
        );
    }

    protected function _construct( $oAdminPage ) {

        // Tabs
        new TaskScheduler_AdminPage_Tab_Unit( $oAdminPage, $this->sPageSlug );
        new TaskScheduler_AdminPage_Tab_Scratch( $oAdminPage, $this->sPageSlug );

    }

    protected function _loadPage( $oFactory ) {
        $oFactory->setPageTitleVisibility( false ); // disable the page title of a specific page.
        $oFactory->setInPageTabTag( 'h2' );
        $oFactory->setPluginSettingsLinkLabel( '' ); // pass an empty string to disable it.
    }

}