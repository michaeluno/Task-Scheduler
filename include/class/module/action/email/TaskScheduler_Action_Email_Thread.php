<?php
/**
 * The class that defines the Email action for the Task Scheduler plugin.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

 /**
  * 
  * @filter        add|apply        task_scheduler_filter_task_email_subject
  * @filter        add|apply        task_scheduler_filter_task_email_body
  */
class TaskScheduler_Action_Email_Thread extends TaskScheduler_Action_Base {

    protected $sSlug = 'task_scheduler_action_send_individual_email';

    /**
     * @var string
     * @since   1.5.0
     */
    private $___sError = '';

    /**
     * The user constructor.
     * 
     * This method is automatically called at the end of the class constructor.
     */
    public function construct() {
                                
        add_filter( 'task_scheduler_filter_task_email_subject', array( $this, '_replyToFormatEmailText' ), 10, 2 );
        add_filter( 'task_scheduler_filter_task_email_body', array( $this, '_replyToFormatEmailText' ), 10, 2 );
            
    }
    
    /**
     * Returns the readable label of this action.
     * 
     * This will be called when displaying the action in an pull-down select option, task listing table, or notification email message.
     */    
    public function getLabel( $sLabel ) {
        return __( 'Send Each Email', 'task-scheduler' );
    }

    /**
     * Defines the behavior of the task action.
     *
     * Volatile child tasks to send only one email.
     *
     * @param integer|string $isExitCode
     * @param TaskScheduler_Routine_Thread $oThread
     *
     * @return bool|int|string
     */
    public function doAction( $isExitCode, $oThread ) {

        $_aThreadMeta = $oThread->getMeta();
        if ( 
            ! isset( 
                $_aThreadMeta[ 'email_address' ],
                $_aThreadMeta[ 'email_title' ],
                $_aThreadMeta[ 'email_message' ]
            ) 
        ) {
            return 0;    // failed
        }

        $this->___sError = '';
        add_action( 'wp_mail_failed', array( $this, 'replyToCaptureMailError' ), 10 );
        add_filter( 'wp_mail_from', array( $this, 'replyToGetMailFrom' ), 10 );

        $_bResult = wp_mail(
            $_aThreadMeta[ 'email_address' ],    // email address
            apply_filters( 'task_scheduler_filter_task_email_subject', $_aThreadMeta[ 'email_title' ], $oThread ),    // subject
            apply_filters( 'task_scheduler_filter_task_email_body', $_aThreadMeta[ 'email_message' ], $oThread ),        // message
            $this->___getHeaders( $oThread )
        );
        if ( $this->___sError ) {
            $_oRoutine = $oThread->getOwner();
            $_oRoutine->log( $this->___sError, $oThread->parent_routine_log_id );
        }
        return $_bResult;

    }
        /**
         * @param TaskScheduler_Routine_Thread $oThread
         * @return array
         * @since   1.5.0
         */
        private function ___getHeaders( $oThread ) {
            $_sFromFullName = $oThread->getMeta( 'from_full_name' );
            $_sFromAddress  = $oThread->getMeta( 'from_email' );
            if ( ! $_sFromFullName || ! $_sFromAddress ) {
                $_oOwnerUser    = get_user_by( 'id', $oThread->post_author );
                if ( $_oOwnerUser ) {
                    $_sFromFullName = $_sFromFullName
                        ? $_sFromFullName
                        : $_oOwnerUser->first_name . ' ' . $_oOwnerUser->last_name;
                    $_sFromAddress  = $_sFromAddress
                        ? $_sFromAddress
                        : $_oOwnerUser->user_email;
                }
            }
            if ( ! $_sFromFullName || ! $_sFromAddress ) {
                $_sFromFullName = $_sFromFullName
                    ? $_sFromFullName
                    : get_option( 'blogname' );
                $_sFromAddress  = $_sFromAddress
                    ? $_sFromAddress
                    : get_option( 'admin_email' );
            }
            return array(
                "From: {$_sFromFullName} <{$_sFromAddress}>",
            );
        }


        /**
         *
         * @callback action wp_mail_failed
         * @since   1.5.0
         * @return string
         */
        public function replyToCaptureMailError( WP_Error $oWPError ) {
            remove_filter( 'wp_mail_failed', array( $this, 'replyToCaptureMailError' ), 10 );
            $this->___sError = $oWPError->get_error_code() . ': ' . $oWPError->get_error_message() . PHP_EOL
                . TaskScheduler_Debug::get( $oWPError->get_error_data(), null, false );
        }

        /**
         * Fixes an issue that sending mail fails due to an invalid mail address for the from field.
         * @param string $sFrom
         * @return mixed
         * @see https://wordpress.org/support/topic/phpmailervalidateaddress-not-working/
         * @since   1.5.0
         */
        public function replyToGetMailFrom( $sFrom ) {
            remove_filter( 'wp_mail_from', array( $this, 'replyToGetMailFrom' ), 10 );
            if ( class_exists( 'PHPMailer' ) ) {
                PHPMailer::$validator = 'noregex'; // or 'html5'
            }
            return $sFrom;
        }

        /**
         * Returns the text formatted for emails.
         * 
         * @since        1.0.0
         */
        public function _replyToFormatEmailText( $sText, $oThread ) {
            
            $_oRoutine       = $oThread->getOwner();
            $_oOwner         = $_oRoutine->getOwner();
            $_sOccurrence    = $_oOwner->occurrence ? $_oOwner->occurrence : $oThread->occurrence;
            $_sAction        = $_oOwner->routine_action ? $_oOwner->routine_action : $oThread->routine_action;
            $_aFind          = array(
                0 => '%task_name%', 
                1 => '%task_description%', 
                2 => '%occurrence%', 
                3 => '%action%', 
                4 => '%site_url%',
                5 => '%site_name%',
                6 => '%admin_email%',
            );
            $_aChange        = array(
                0 => $_oOwner->post_title ? $_oOwner->post_title : $oThread->post_title,
                1 => $_oOwner->post_excerpt ? $_oOwner->post_excerpt : $oThread->post_excerpt,
                2 => trim( apply_filters( "task_scheduler_filter_label_occurrence_" . $_sOccurrence, $_sOccurrence ) ),
                3 => trim( apply_filters( "task_scheduler_filter_label_action_" . $_sAction, $_sAction ) ),
                4 => get_option( 'siteurl' ),
                5 => get_option( 'blogname' ),
                6 => get_option( 'admin_email' ),
            );
            return str_replace( $_aFind , $_aChange, $sText );
            
        }
            
}