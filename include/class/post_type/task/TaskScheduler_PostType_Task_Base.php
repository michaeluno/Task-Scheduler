<?php
/**
 * The class that checks necessary requirements.
 *
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
*/
abstract class TaskScheduler_PostType_Task_Base extends TaskScheduler_AdminPageFramework_PostType {


	public function start() {

		// Create the task post type
		$this->setPostTypeArgs(
			array(			// argument - for the array structure, refer to http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
				'labels'				=>	array(
					'name'					=>	__( 'Task Scheduler', 'task-scheduler' ),
					'all_items'				=>	__( 'Tasks', 'task-scheduler' ),	// sub menu label
					'singular_name'			=>	__( 'Task', 'task-scheduler' ),
					'menu_name'				=>	__( 'Task Scheduler', 'task-scheduler' ),	// this changes the root menu name 
					'add_new'				=>	__( 'Add New Task', 'task-scheduler' ),
					'add_new_item'			=>	__( 'Add New Task', 'task-scheduler' ),
					'edit'					=>	__( 'Edit', 'task-scheduler' ),
					'edit_item'				=>	__( 'Edit Task', 'task-scheduler' ),
					'new_item'				=>	__( 'New Task', 'task-scheduler' ),
					'view'					=>	__( 'View', 'task-scheduler' ),
					'view_item'				=>	__( 'View Tasks', 'task-scheduler' ),
					'search_items'			=>	__( 'Search Tasks', 'task-scheduler' ),
					'not_found'				=>	__( 'No Task found for Task Scheduler', 'task-scheduler' ),
					'not_found_in_trash'	=>	__( 'No Task Found for Task Scheduler in Trash', 'task-scheduler' ),
					'parent'				=>	__( 'Parent Task', 'task-scheduler' ),
					// 'publish'				=>	__( 'Run', 'task-scheduler' ),
					'plugin_listing_table_title_cell_link'	=>	'',	// framework specific key. [3.0.6+] - passing an empty will disable the automatic link insertion to the plugin listing table.
				),
				'public'				=>	true,
				'show_ui' 				=>	true,				
				'show_in_menu'     		=>	false, // Whether to show post type in the admin menu. 'show_ui' must be true for this to work. bool (defaults to 'show_ui')
				'menu_position'			=>	110,
				'can_export'  			=>	true,
				// 'supports' => array( 'title', 'editor', 'comments', 'thumbnail' ),	// 'custom-fields'
				'supports'				=>	array( 'title', ),
				'taxonomies'			=>	array( TaskScheduler_Registry::Taxonomy_SystemLabel, ),
				'menu_icon'				=>	TaskScheduler_Registry::getPluginURL( '/asset/image/menu_icon_16x16.png' ),
				'has_archive'			=>	false,
				'hierarchical'			=>	true,
				'show_admin_column'		=>	true,
				'screen_icon'			=>	TaskScheduler_Registry::getPluginURL( "/asset/image/screen_icon_32x32.png" ),
				'exclude_from_search'	=>	true,
				// 'show_table_filter'		=>	false,	// not working.
				// 'capabilities' => array(
					// 'create_posts' => false,
				// ),		
				'capabilities' => array(
					// 'read_private_posts'	=>	'none',
					// 'delete_private_posts'	=>	'none',
					// 'edit_private_posts'	=>	'none',
        
					// 'publish_posts' => 'publish_movies',
					// 'edit_posts' => 'edit_movies',
					// 'edit_others_posts' => 'edit_others_movies',
					// 'delete_posts' => 'delete_movies',
					// 'delete_others_posts' => 'delete_others_movies',
					// 'read_private_posts' => 'read_private_movies',
					// 'edit_post' => 'edit_movie',
					// 'delete_post' => 'delete_movie',
					// 'read_post' => 'read_movie',
				),				
			)		
		);
		
		// Create the internal label taxonomy 
		$this->addTaxonomy( 
			TaskScheduler_Registry::Taxonomy_SystemLabel, // taxonomy slug
			array(			// argument - for the argument array keys, refer to : http://codex.wordpress.org/Function_Reference/register_taxonomy#Arguments
				'labels' => array(
					'name'			=> __( 'Task Scheduler System Label', 'task-scheduler' ),
					'add_new_item'	=> __( 'Add New Label', 'task-scheduler' ),
					'new_item_name'	=> __( 'New Label', 'task-scheduler' ),
				),
				'show_ui'				=> false,
				'show_tagcloud'			=> false,
				'hierarchical'			=> false,
				'show_admin_column'		=> true,
				'show_in_nav_menus'		=> false,
				'show_table_filter'		=> true,		// framework specific key
				'show_in_sidebar_menus'	=> false,	// framework specific key
			),
			array( TaskScheduler_Registry::PostType_Thread )	// additional object types
		);		
	
		// For admin
		if ( $this->oProp->bIsAdmin && $this->oUtil->getCurrentPostType() == $this->oProp->sPostType ) {
						
			// Task listing table
			add_filter( 'enter_title_here', array( $this, '_replyToChangeTitleMetaBoxFieldLabel' ) );	// add_filter( 'gettext', array( $this, 'changeTitleMetaBoxFieldLabel' ) );
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
		
		add_filter( 'the_content', array( $this, '_replyToShowTaskDetails' ) );
				
	}
			
		public function _replyToShowTaskDetails( $sContent ) {
			
			global $post;
			if ( $post->post_type != $this->oProp->sPostType ) { 
				return $sContent; 
			}
			if( ! is_singular() || ! is_main_query() ) {
				return $sContent; 
			}
							
			$_aPageListArgs = array(
				'authors'     	=>	'',
				// 'parent'			=>	'',
				'child_of'		=>	$post->ID,
				'date_format'	=>	'Y/m/d G:i:s', // get_option('date_format'),
				'depth'			=>	0,
				'echo'			=>	false,
				'exclude'		=>	'',
				'include'		=>	'',
				'link_after'	=>	'',
				'link_before'	=>	'',
				'post_type'		=>	TaskScheduler_Registry::PostType_Log,
				'post_status'	=>	array( 'private', 'publish' ),
				'show_date'		=>	true,
				'sort_column'	=>	'post_date',	// , menu_order, post_title
				'sort_order'	=>	'DESC',	// ASC or DESC
				'title_li'		=>	'',
				'walker'		=>	new TaskScheduler_Walker_Log,
				'hierarchical'	=>	true,
			);					
			return $sContent
				. "<h3>" .  __( 'Log', 'task-scheduler' ) . "</h3>"
				. "<ul>" . wp_list_pages( $_aPageListArgs ) . "</ul>";
			
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
// TaskScheduler_Debug::log( $aActions );
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
				 'pages'	=>	array(...)
				 'attachments'	=>	array(...)
			 */
			$aMessages['post'][ 1 ] = __( 'Task updated', 'task-scheduler' );
			$aMessages['post'][ 4 ] = __( 'Task updated', 'task-scheduler' );
			$aMessages['post'][ 6 ] = __( 'Task created', 'task-scheduler' );
			$aMessages['post'][ 7 ] = __( 'Task saved', 'task-scheduler' );
			$aMessages['post'][ 8 ] = __( 'Task submitted', 'task-scheduler' );
			return $aMessages;
			
		}

	/*
	 * Extensible methods
	 */
	// public function columns_task_scheduler( $aHeaderColumns ) {	// columns_{post type slug}
		// return $aHeaderColumns;
	// }	

	// public function sortable_task_scheduler( $aSortableHeaderColumns ) {	// sortable_columns_{post type slug}
		// return $aSortableHeaderColumns;
	// }		


		

}

