<?php
/**
 * A base class for meta box classes.
 * 
 * @since			1.0.0
 */
abstract class TaskScheduler_MetaBox_Base extends TaskScheduler_AdminPageFramework_MetaBox {
// abstract class TaskScheduler_MetaBox_Base extends AdminPageFramework_MetaBox {
		
	public function start() {
		
		// Register custom field types

		$_sClassName = get_class( $this );
		new TaskScheduler_DateTimeCustomFieldType( $_sClassName );		
		new TaskScheduler_TimeCustomFieldType( $_sClassName );		
		new TaskScheduler_DateCustomFieldType( $_sClassName );				
				
		add_action( 'admin_enqueue_scripts', array( $this, '_replyToAddCSS' ), 10, 1 );
		
	}
			
	public function _replyToAddCSS( $sHook ) {
		
	    global $post;

		if ( ! in_array( $sHook, array( 'post.php', 'post-new.php' ) ) ) { return; }		
		if ( ! in_array( $post->post_type, array( TaskScheduler_Registry::PostType_Task, TaskScheduler_Registry::PostType_Thread ) ) ) { return; }
		
		wp_enqueue_style( 'task_scheduler_meta_box_css', TaskScheduler_Registry::getPluginURL( 'asset/css/meta_box.css' ) );
		
	}
}
