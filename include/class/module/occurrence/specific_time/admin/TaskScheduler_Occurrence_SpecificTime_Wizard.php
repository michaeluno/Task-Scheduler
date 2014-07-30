<?php
/**
 * Creates wizard pages for the 'Occurrence' option.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

final class TaskScheduler_Occurrence_SpecificTime_Wizard extends TaskScheduler_Wizard_Occurrence_Base {
	
	/**
	 * Returns the field definition arrays.
	 * 
	 * @remark		The field definition structure must follows the specification of Admin Page Framework v3.
	 */ 
	public function getFields() {
	
		return array(
			array(	
				'field_id'			=>	'when',
				'title'				=>	__( 'When', 'task-scheduler' ),
				'type'				=>	'date_time',
				'time_format'		=>	'hh:mm',	// H:mm is the default format.
				'repeatable'		=>	true,
				'attributes'		=>	array(
					'size'		=>	20,
				),
				// 'options'			=>	array(	// somehow passing as an array did not set the time correctly.
					// 'minDate'			=>	0,
					// 'numberOfMonths'	=>	2,
				// ),
				'options'	=>	"{
					numberOfMonths: 2,
					minDate: 0
				}"
			),						
		);
		
	}	

	public function validateSettings( $aInput, $aOldInput, $oAdminPage ) { 
	
		$_bIsValid = true;
		$_aErrors = array();
		
		$aInput['when'] = array_filter( $aInput['when'] );	// drop non-true values.
		if ( empty( $aInput['when'] ) ) {
			
			// $aVariable[ 'sectioni_id' ]['field_id']
			$_aErrors[ $this->_sSectionID ][ 'when' ] = __( 'At least one item needs to be set.', 'task-scheduler' );
			$_bIsValid = false;			
			
		}
		$_bUnset = false;
		foreach( $aInput['when'] as $_iIndex => $_sDateTime ) {
			$_iSetTimeStamp = strtotime( $_sDateTime );
			$_iCurrentTimeStamp = current_time( 'timestamp', false );	// somehow the second parameter is okay with false.
// TaskScheduler_Debug::log( 
	// array( 
		// 'set timestamp' 			=>	$_iSetTimeStamp, 
		// 'WP current timestamp'		=>	$_iCurrentTimeStamp, 
		// 'PHP current timestamp'		=>	time(),
		// 'readable set time'			=>	TaskScheduler_WPUtility::getSiteReadableDate( $_iSetTimeStamp ),
		// 'readable WP current time'	=>	TaskScheduler_WPUtility::getSiteReadableDate( $_iCurrentTimeStamp ),
		// 'readable PHP current time'	=>	TaskScheduler_WPUtility::getSiteReadableDate( time() ),
	// )
// );
			if ( $_iSetTimeStamp < $_iCurrentTimeStamp ) {
				$_bUnset = true;			
				unset( $aInput['when'][ $_iIndex ] );
			}
		}
		if ( $_bUnset ) {
			$_bIsValid = false;	
			$_sMessage = __( 'A future time needs to be set, not past.', 'task-scheduler' );
			$_aErrors[ $this->_sSectionID ][ 'when' ] = isset( $_aErrors[ $this->_sSectionID ][ 'when' ] )
				? $_aErrors[ $this->_sSectionID ][ 'when' ] . '<br />' . $_sMessage
				: $_sMessage;
		}
		// reorder the array to be numerically indexed
		$aInput['when'] = array_values( $aInput['when'] );
		
		if ( ! $_bIsValid ) {
// TaskScheduler_Debug::log( 'errors' );
// TaskScheduler_Debug::log( $_aErrors );
			// Set the error array for the input fields.
			$oAdminPage->setFieldErrors( $_aErrors );		
			$oAdminPage->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
	
		} else {
// TaskScheduler_Debug::log( 'no error' );			
// TaskScheduler_Debug::log( $aInput['when'] );			
		}
	
		return $aInput; 		

	}
	
	
}