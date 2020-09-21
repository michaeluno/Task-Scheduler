<?php
class TaskScheduler_AdminPage_Test_Loader {

    public function __construct() {

        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return;
        }
        add_action( 'set_up_' .  'TaskScheduler_AdminPage', array( $this, 'replyToSetUpAdminPage' ) );

    }
        /**
         * @param TaskScheduler_AdminPageFramework $oFactory
         */
        public function replyToSetUpAdminPage( $oFactory ) {
            new TaskScheduler_AdminPage_Page_Unit( $oFactory );
        }

}