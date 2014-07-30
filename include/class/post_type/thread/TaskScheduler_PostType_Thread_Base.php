<?php
/**
 * An abstract class of a custom post type for task logs.
 *
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
*/
abstract class TaskScheduler_PostType_Thread_Base extends TaskScheduler_AdminPageFramework_PostType {


	public function start() {
		// Create the task post type
		$this->setPostTypeArgs(
			array(			// argument - for the array structure, refer to http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
				'labels'				=>	array(
					'name'					=>	__( 'Task Threads ', 'task-scheduler' ),
					'all_items'				=>	__( 'Manage Threads', 'task-scheduler' ),	// sub menu label
					'singular_name'			=>	__( 'Task Thread', 'task-scheduler' ),
					'menu_name'				=>	__( 'Task Thread', 'task-scheduler' ),	// this changes the root menu name 
					'add_new'				=>	__( 'Add New Thread', 'task-scheduler' ),
					'add_new_item'			=>	__( 'Add New Thread', 'task-scheduler' ),
					'edit'					=>	__( 'Edit', 'task-scheduler' ),
					'edit_item'				=>	__( 'Edit Thread', 'task-scheduler' ),
					'new_item'				=>	__( 'New Thread', 'task-scheduler' ),
					'view'					=>	__( 'View', 'task-scheduler' ),
					'view_item'				=>	__( 'View Threads', 'task-scheduler' ),
					'search_items'			=>	__( 'Search Threads', 'task-scheduler' ),
					'not_found'				=>	__( 'No Thread found for Task Scheduler', 'task-scheduler' ),
					'not_found_in_trash'	=>	__( 'No Thread Found for Task Scheduler in Trash', 'task-scheduler' ),
					'parent'				=>	__( 'Parent Thread', 'task-scheduler' ),
					// 'publish'				=>	__( 'Run', 'task-scheduler' ),
					'plugin_listing_table_title_cell_link'	=>	'',	// framework specific key. [3.0.6+] - passing an empty will disable the automatic link insertion to the plugin listing table.
				),
				'public'				=>	true,
				'show_ui' 				=>	false,				
'show_in_menu'     		=>	false, // Whether to show post type in the admin menu. 'show_ui' must be true for this to work. bool (defaults to 'show_ui')
				'menu_position'			=>	110,
				'can_export'  			=>	true,
				// 'supports' => array( 'title', 'editor', 'comments', 'thumbnail' ),	// 'custom-fields'
				'supports'				=>	array( 'title' ),
				// 'taxonomies'			=>	array( '' ),
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
				
	}
		

}

