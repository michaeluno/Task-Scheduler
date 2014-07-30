<?php
/**
 * The walker class to display task logs.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */
if ( ! class_exists( 'Walker_Page' ) ) { include_once( ABSPATH . WPINC . '/post-template.php' ); }

/**
 * Create HTML list of task logs.
 *
 * @since 1.0.0
 * @uses Walker_Page
 */
class TaskScheduler_Walker_Log extends Walker_Page {

	/**
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $sOutput Passed by reference. Used to append additional content.
	 * @param object $oLog Page data object.
	 * @param int $iDepth Depth of page. Used for padding.
	 * @param int $iCurrentPage Page ID.
	 * @param array $aArgs
	 */
	function start_el( &$sOutput, $oLog, $iDepth=0, $aArgs=array(), $iCurrentPage=0 ) {
		
		$_aOutput = array( $sOutput );
		$_aOutput[] = ( $iDepth ? str_repeat( "\t", $iDepth ) : '' )
			. '<li class="' . $this->_getCSSClass( $oLog, $iDepth, $aArgs, $iCurrentPage  ) . '">'
				. '<a href="' . get_permalink( $oLog->ID ) . '">' 
					. $aArgs['link_before'] 
					. sprintf( __( '#%d' ), $oLog->ID )
					// . apply_filters( 'the_title', $oLog->post_title, $oLog->ID )  // Don't display the title
					. $aArgs['link_after'] 
				. '</a>';
		// $_sCreatedDate = 'modified' == $aArgs['show_date'] ? $oLog->post_modified  : $oLog->post_date;
		// $_iCreatedTimeStamp = mysql2date( 'U' , $_sCreatedDate ) + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
		// $_aOutput[] = date_i18n( $aArgs['date_format'], $_iCreatedTimeStamp );
		$_aOutput[] = TaskScheduler_WPUtility::getRedableMySQLDate( 
			'modified' == $aArgs['show_date'] ? $oLog->post_modified  : $oLog->post_date,
			$aArgs['date_format'], 
			false 
		);
		$_aOutput[] = $oLog->post_excerpt ? $oLog->post_excerpt : $this->_getExcerpt( $oLog->post_content );
		
		// Update the output variable
		$sOutput = implode( " ", $_aOutput );
		
	}
		private function _getExcerpt( $sText, $iMaxChars=250 ) {
			
			$_sSubstr = function_exists( 'mb_substr' ) ? 'mb_substr' : 'substr';
			$_sStrlen = function_exists( 'mb_strlen' ) ? 'mb_strlen' : 'strlen';
			
			return $_sSubstr( $sText, 0, $iMaxChars ) . ( $_sStrlen( $sText ) > $iMaxChars ? '...' : '' );			
			
		}
		private function _getCSSClass( $oLog, $iDepth, $aArgs, $iCurrentPage ) {
			
			$_aCSSClass = array( 'page_item', 'page-item-' . $oLog->ID );

			if( isset( $aArgs['pages_with_children'][ $oLog->ID ] ) ) {
				$_aCSSClass[] = 'page_item_has_children';
			}

			if ( ! empty( $iCurrentPage ) ) {
				$_oCurrentLog = get_post( $iCurrentPage );
				if ( in_array( $oLog->ID, $_oCurrentLog->ancestors ) )
					$_aCSSClass[] = 'current_page_ancestor';
				if ( $oLog->ID == $iCurrentPage )
					$_aCSSClass[] = 'current_page_item';
				elseif ( $_oCurrentLog && $oLog->ID == $_oCurrentLog->post_parent )
					$_aCSSClass[] = 'current_page_parent';
			} elseif ( $oLog->ID == get_option('page_for_posts') ) {
				$_aCSSClass[] = 'current_page_parent';
			}
			
			/** This filter is documented in wp-includes/post-template.php */
			/**
			 * Filter the list of CSS classes to include with each page item in the list.
			 *
			 * @since 2.8.0
			 *
			 * @see wp_list_pages()
			 *
			 * @param array   $_aCSSClass    An array of CSS classes to be applied
			 *                             to each list item.
			 * @param WP_Post $oLog         Page data object.
			 * @param int     $iDepth        Depth of page, used for padding.
			 * @param array   $aArgs         An array of arguments.
			 * @param int     $iCurrentPage ID of the current page.
			 */
			return implode( ' ', apply_filters( 'page_css_class', $_aCSSClass, $oLog, $iDepth, $aArgs, $iCurrentPage ) );
			
		}

}