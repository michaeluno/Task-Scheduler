<?php
/**
 * Task Scheduler
 * 
 * Provides an enhanced task management system for WordPress.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

 
/**
 * One of the abstract class of the plugin admin page class.
 * @extends     TaskScheduler_AdminPage_Setting_Form_Heartbeat
 */
final class TaskScheduler_AdminPage_Setting extends TaskScheduler_AdminPage_Setting_Form_Heartbeat {

    public function setUp() {
    
        $this->setRootMenuPageBySlug( TaskScheduler_Registry::$aAdminPages[ 'root' ] );
        $this->addSubMenuItems(
            array(
                'title'            => __( 'Settings', 'task-scheduler' ),    // page and menu title
                'page_slug'        => TaskScheduler_Registry::$aAdminPages[ 'setting' ],    // page slug
                'order'            => 40,  // sub-menu order
            )
        );        
                        
        add_action( "load_" . TaskScheduler_Registry::$aAdminPages[ 'setting' ], array( $this, '_replyToLoadSettingPage' ) );
        
    }
    
    /**
     * Gets triggered when one of the registered pages gets loaded. 
     */
    public function load_TaskScheduler_AdminPage_Setting( $oAdminPage ) {
    
        $this->setPageHeadingTabsVisibility( false );        // disables the page heading tabs by passing false.
        $this->setInPageTabsVisibility( true );        // disables the page heading tabs by passing false.
        $this->setPageTitleVisibility( false );
        $this->setInPageTabTag( 'h2' );                
        $this->enqueueStyle( TaskScheduler_Registry::getPluginURL( '/asset/css/admin_settings.css' ) );
        if ( version_compare( $GLOBALS[ 'wp_version' ], '5.3', '>=' ) ) {
            $this->enqueueStyle( TaskScheduler_Registry::getPluginURL( '/asset/css/wp53.css' ) );
        }
        $this->setDisallowedQueryKeys( 'settings-notice' );
        $this->setDisallowedQueryKeys( 'transient_key' );

    }
    
    /**
     * Gets triggered when the page gets loaded. 
     * 
     * Used to define form elements.
     */
    public function _replyToLoadSettingPage( $oAdminPage ) {
        $this->_defineInPageTabs();
        $this->_defineForm();
        $_idnsGMTOffset = get_option( 'gmt_offset' );
        if ( ! strlen( $_idnsGMTOffset ) ) {
            $_sMessage  = __( 'The time zone needs to be set for the plugin to run properly.', 'task-scheduler' );
            $_sMessage .= ' ' . sprintf(
                __( 'Go to the <a href="%1$s">General Setting</a> section to set a timezone.', 'task-scheduler' ),
                esc_url( admin_url( 'options-general.php' ) )
            );
            $oAdminPage->setAdminNotice( $_sMessage, 'error' );
        }
    }
    
}
