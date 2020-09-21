<?php
/**
 * The class that checks necessary requirements.
 *
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
*/
final class TaskScheduler_PostType_Task extends TaskScheduler_PostType_Task_Base {
    
    public function setUp() {

        // For admin
        if ( $this->oProp->bIsAdmin && $this->oUtil->getCurrentPostType() == $this->oProp->sPostType ) {
                        
            // Task listing table
            add_filter( 'enter_title_here', array( $this, '_replyToChangeTitleMetaBoxFieldLabel' ) );    // add_filter( 'gettext', array( $this, 'changeTitleMetaBoxFieldLabel' ) );
            add_action( 'edit_form_after_title', array( $this, '_replyToAddTextAfterTitle' ) );    
            add_action( 'admin_head-edit.php', array( $this, '_replyToAddCustomColumnCSS' ) );
            
            // Listing table default sort order
            add_action( 'pre_get_posts', array( $this, '_replyToSetDefaultSortOrder' ) );

            // When the post is hierarchical use the 'page_row_actions' hook; otherwise, 'post_row_actions'.
            add_filter( 'page_row_actions', array( $this, '_repyToRemoveQuickEdit' ), 10, 2 );        
                    
            // Post definition page (meta box page)
            $this->setAutoSave( false );
            $this->setAuthorTableFilter( true );            
            if ( in_array( $this->oUtil->getPageNow(), array( 'post.php', 'post-new.php' ) ) ) {
                add_filter( 'gettext', array( $this, '_replyToChangePublishButtonLabel' ), 10, 2 );
                add_filter( 'post_updated_messages', array( $this, '_replyToChangeUpdatedMessage' ) );
            }
                        
        }    
    
    }

        /**
         * Sets the default sort order of the task listing table, which is by ID and descending.
         */
        public function _replyToSetDefaultSortOrder( $oQuery ) {

            if ( $oQuery->get( 'post_type' ) != $this->oProp->sPostType ) {
                return;
            }
            
            if ( ! isset( $_GET['orderby'], $_GET['order'] ) ) {
                $oQuery->set( 'orderby', 'ID' );
                $oQuery->set( 'order', 'DESC' );
            }
            
        }        
    
        /**
         * Adds custom CSS rules to the listing table page.
         */
        public function _replyToAddCustomColumnCSS() {
            return;
        }        
        
        public function _repyToRemoveQuickEdit( $aActions, $oPost ) {
            
            if ( $this->oUtil->getCurrentPostType() != $this->oProp->sPostType ) {
                return $aActions;
            }

            unset( $aActions['inline hide-if-no-js'] );        

            $aActions['run_now'] = "<a href=''>" . __( 'Run Now', 'task-scheduler' ) . "</a>";
            return $aActions;
            
        }
        
        public function _replyToChangeTitleMetaBoxFieldLabel( $sText ) {
            return __( 'Set the task name here.', 'task-scheduler' );        
        }    
        public function _replyToAddTextAfterTitle() {
                    
            // Insert feed information text here.
            // echo 'fetched item';
            
        }
        /**
         * Changes the 'Publish' button label in the default meta box.
         */
        public function _replyToChangePublishButtonLabel( $_sTranslation, $sText ) {

            if ( 'Publish' == $sText ) {
                return __( 'Create', 'task-scheduler' );
            }
            return $_sTranslation;
        }    
        /**
         * Changes the admin notice in the beat(custom post) definition page.
         */
        public function _replyToChangeUpdatedMessage( $aMessages ) {
            /**
             * the structure of $aMessages looks like this
             *     array (size=3)
                  'post' => 
                    array (size=11)
                      0 => string '' (length=0)
                      1 => string 'Post updated. <a href="http://localhost/wp39x/?task_scheduler=test-beat">View post</a>' (length=83)
                      2 => string 'Custom field updated.' (length=21)
                      3 => string 'Custom field deleted.' (length=21)
                      4 => string 'Post updated.' (length=13)
                      5 => boolean false
                      6 => string 'Post published. <a href="http://localhost/wp39x/?task_scheduler=test-beat">View post</a>' (length=85)
                      7 => string 'Post saved.' (length=11)
                      8 => string 'Post submitted. <a target="_blank" href="http://localhost/wp39x/?task_scheduler=test-beat&#038;preview=true">Preview post</a>' (length=122)
                      9 => string 'Post scheduled for: <strong>Jun 14, 2014 @ 10:23</strong>. <a target="_blank" href="http://localhost/wp39x/?task_scheduler=test-beat">Preview post</a>' (length=147)
                      10 => string 'Post draft updated. <a target="_blank" href="http://localhost/wp39x/?task_scheduler=test-beat&#038;preview=true">Preview post</a>' (length=126)
                 'pages'    =>    array(...)
                 'attachments'    =>    array(...)
             */
            $aMessages['post'][ 1 ] = __( 'Task updated', 'task-scheduler' );
            $aMessages['post'][ 4 ] = __( 'Task updated', 'task-scheduler' );
            $aMessages['post'][ 6 ] = __( 'Task created', 'task-scheduler' );
            $aMessages['post'][ 7 ] = __( 'Task saved', 'task-scheduler' );
            $aMessages['post'][ 8 ] = __( 'Task submitted', 'task-scheduler' );
            return $aMessages;
            
        }
    
}
