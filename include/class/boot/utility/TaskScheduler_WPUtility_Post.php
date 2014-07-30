<?php
/**
 * One of the abstract class providing utility methods which use WordPress built-in functions.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

abstract class TaskScheduler_WPUtility_Post extends TaskScheduler_Utility {
	
	/**
	 * Checks if the post exists by the given post ID.
	 */	
	static public function doesPostExist( $iID ) {
		return is_string( get_post_status( $iID ) );
	}	
	
	/**
	 * Returns an array holding the registered post types with the key of the slugs and the value of the label.
	 */
	static public function getRegisteredPostTypeLabels( $aArgs=array() ) {
		
		$_aPostTypes = array();
		$_aArgs = array(
		   // 'public'   => true,
		   // '_builtin' => false
		);
		foreach( get_post_types( $aArgs, 'objects', 'and' ) as $_oPostType ) {
			
			// Check necessary keys.
			if (  ! isset( $_oPostType->name, $_oPostType->label ) ) continue;			
						
			$_aPostTypes[ $_oPostType->name ] = $_oPostType->label;

		}
		return $_aPostTypes;
		// return array_diff_key( $_aPostTypes, array_flip( $_aPostTypes ) );			
		
	}
	/**
	 * Returns an array holding the registered post statuses with the keys of post status keys and the values of labels.
	 * 
	 * This is used for field definitions to display labels associated with the key of the slug.
	 * 
	 * @return	array	An array holding the registered post statuses with the keys of post status keys and the values of labels.
	 */
	static public function getRegisteredPostStatusLabels() {
		
		static $_aPostStatusLabels;	// cache
		if ( isset( $_aPostStatusLabels ) ) {
			return $_aPostStatusLabels;
		}
		
		$_aPostStatusLabels = array();
		$_aRegisteredPostStatuses = get_post_stati( array(), '' );
		foreach( $_aRegisteredPostStatuses as $_sSlug => $_oPostStatus )  {
			$_aPostStatusLabels[ $_sSlug ] = $_oPostStatus->label;
		}
		return $_aPostStatusLabels;
		
	}
	
	/**
	 * Returns the excerpt of the given post.
	 */
	public static function getExcerpt( $iPostID ) {

		return get_post( $iPostID )->post_excerpt;	
		
	}
	
	/**
	 * Checks if the given post has a child post.
	 * 
	 * @deprecated		Not used at this moment.
	 */
	public static function getNumberOfChildren( $iPostID, $sPostType='any' ) {
		
		$_aArgs = array(
			'numberposts'		=>	-1,
			// 'order'				=>	'ASC',
			// 'post_mime_type'	=> 'image',
			'post_parent'		=>	$iPostID,
			// 'post_status'		=>	null,
			'post_type'			=>	$sPostType,
		);

		return ( count( get_children( $_aArgs ) ) > 0 );
		
	}
	
	public static function getParentPost( $iPostID ) {
		
		return get_post( $iPostID )->post_parent;
		
	}
	
	/**
	 * Returns an associative array holding all the meta key-value pairs by the given post ID.
	 * 
	 * @return		array		The array holding the post meta data of the given post ID.
	 */
	public static function getPostMetas( $iPostID ) {
		
		// This way, array will be unserialized; easier to view.
		$_aPostData = array();
		foreach( ( array ) get_post_custom_keys( $iPostID ) as $_sKey ) {
			$_aPostData[ $_sKey ] = get_post_meta( $iPostID, $_sKey, true );
		}	
		return $_aPostData;
		
	}

	/**
	 * Creates a post of a specified custom post type with unit option meta fields.
	 * 
	 */
	public static function insertPost( array $aPostMeta, $sPostTypeSlug ) {
		
		// If the database objects were not ready. Do nothing.
		if ( ! is_object( $GLOBALS['wpdb'] ) || ! is_object( $GLOBALS['wp_rewrite'] ) ) return;
		
		static $_iUserID;
		$_iUserID = isset( $_iUserID ) ? $_iUserID : get_current_user_id();
		
		$_aDefaults = array(
			// Plugin specific default values.
			'post_type'				=>	$sPostTypeSlug,
			'post_date'				=>	date( 'Y-m-d H:i:s' ),
			'comment_status'		=>	'closed',
			'ping_status'			=>	'closed',
			'post_status'			=>	'publish',
		) + array( 
			// WordPress built-in wp_insert_post() function's default values.
			'post_author'			=>	$_iUserID,
			'post_parent'			=>	0,
			'menu_order'			=>	0,
			'to_ping'				=>  '',
			'pinged'				=> '',
			'post_password'			=>	'',
			'guid'					=>	'',
			'post_content_filtered'	=>	'',
			'post_excerpt'			=>	'',
			'import_id'				=>	0,
			'post_content'			=>	'',
			'post_title'			=>	'',
			'tax_input'    			=>	null,	// should be an array
		);
			
		// Construct the post arguments array.
		$_aPostArguments = array();
		foreach( $_aDefaults as $_sKey => $_sValue ) {
			$_aPostArguments[ $_sKey ] = isset( $aPostMeta[ $_sKey ] )
				?	$aPostMeta[ $_sKey ]
				:	$_sValue;
		}
		
		// Create a custom post if it's a new unit.		
		$_iPostID = wp_insert_post( $_aPostArguments );
					
		// Remove the default post arguments. See the definition of wp_insert_post() in post.php	
		foreach( $_aDefaults as $_sKey => $_sFieldKey ) {
			unset( $aPostMeta[ $_sKey ] );
		}
		
		// Custom meta data needs to be updated as the wp_isnert_post() cannot handle them.
		self::updatePostMeta( $_iPostID, $aPostMeta );
						
		return $_iPostID;
		
	}	
	
	/**
	 * Updates post meta by the given ID and the array holding the meta data.
	 */
	static public function updatePostMeta( $iPostID, $aPostMeta ) {
		
		foreach( $aPostMeta as $_sFieldID => $_vValue ) {
			update_post_meta( $iPostID, $_sFieldID, $_vValue );
		}
		
	}	
	
}