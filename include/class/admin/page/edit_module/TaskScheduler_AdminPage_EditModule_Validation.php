<?php
/**
 * One of the abstract classes of the editing module options pages.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_AdminPage_EditModule_Validation extends TaskScheduler_AdminPage_Wizard {
    
    /**
     * The validation handler of the wizard admin pages for the entire class.
     * 
     * @callback        filter      validation_{class name}
     */
    public function validation_TaskScheduler_AdminPage_EditModule( /* $aInput, $aOldInput, $oAdminPage, $aSubmitInfo */ ) {

        $_aParams    = func_get_args() + array(
            null, null, null, null
        );
        $aInput      = $_aParams[ 0 ];
        $aOldInput   = $_aParams[ 1 ];
        $oAdminPage  = $_aParams[ 2 ];
        $aSubmitInfo = $_aParams[ 3 ];     

        $_aWizardOptions = isset( $aInput[ '_wizard_options' ] ) 
            ? $aInput[ '_wizard_options' ] 
            : array();

        // The wizard options are stored in the '_wizard_options' element
        if ( ! empty( $_aWizardOptions ) ) {
            $_aSavedValues = $this->_saveValidatedWizardOptions( $_aWizardOptions );    
        }

        // Passing a dummy value will prevent the framework from displaying an admin notice.
        return array( 'dummy value' );    
        
    }
                
}