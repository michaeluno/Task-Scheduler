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

final class TaskScheduler_Occurrence_FixedInterval_Wizard extends TaskScheduler_Wizard_Occurrence_Base {
	
	/**
	 * User constructor.
	 */
	public function construct() {}
	
	/**
	 * Returns the field definition arrays.
	 * 
	 * @remark		The field definition structure must follows the specification of Admin Page Framework v3.
	 */ 
	public function getFields() {
	
		return array(		
			array(	
				'field_id'			=>	'interval',
				'title'				=>	__( 'Interval', 'task-scheduler' ),
				'type'				=>	'number',
				array(
					'type'		=>	'select',
					'default'	=>	'minute',
					'label'		=>	array(
						'second'	=>	__( 'second(s)', 'task-scheduler' ),
						'minute'	=>	__( 'minute(s)', 'task-scheduler' ),
						'hour'		=>	__( 'hour(s)', 'task-scheduler' ),
						'day'		=>	__( 'day(s)', 'task-scheduler' ),
					),
				),
			),				
		);
		
	}	

	public function validateSettings( $aInput, $aOldInput, $oAdminPage ) { 
		
		$_bIsValid = true;
		$_aErrors = array();
		
		if ( ! isset( $aInput['interval'][ 0 ] ) || ! $aInput['interval'][ 0 ] ) {
			// $aVariable[ 'sectioni_id' ]['field_id']
			$_aErrors[ $this->_sSectionID ][ 'interval' ] = __( 'The interval must be greater than 0.', 'task-scheduler' );
			$_bIsValid = false;			
			
		}
		
		if ( ! $_bIsValid ) {

			// Set the error array for the input fields.
			$oAdminPage->setFieldErrors( $_aErrors );		
			$oAdminPage->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
			return array();
			
		}	
	
		return $aInput; 
		
	}
	
	
}