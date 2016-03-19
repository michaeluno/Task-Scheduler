<?php
/**
 * Creates wizard pages for the 'Email' action.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 */

 /**
  * @since      1.3.0
  */
final class TaskScheduler_Action_WebCheck_Wizard extends TaskScheduler_Wizard_Action_Base {

    /**
     * Returns the field definition arrays.
     * 
     * @remark        The field definition structure must follows the specification of Admin Page Framework v3.
     */ 
    public function getFields() {

        return array(
            array(    
                'field_id'           => 'url',
                'title'              => __( 'Web Page URL', 'task-scheduler' ),
                'type'               => 'text',
                'description'        => __( 'Set a url to check.', 'task-scheduler' ),
                'attributes'         => array(
                    'style' => 'min-width: 640px; max-width: 100%;',
                ),
            ),         
            array(    
                'field_id'           => 'sslverify',
                'title'              => __( 'SSL Verify', 'task-scheduler' ),
                'type'               => 'checkbox',
                'label'              => __( 'Whether to verify SSL. Uncheck this if the check keeps failing.', 'task-scheduler' ),
                'default'            => true,
            ),        
            array(    
                'field_id'           => 'timeout',
                'title'              => __( 'Timeout', 'task-scheduler' ),
                'type'               => 'number',
                'default'            => 20,
            ),            
            array(    
                'field_id'            => 'http_method',
                'title'               => __( 'HTTP Request Method', 'task-scheduler' ),
                'type'                => 'radio',
                'label'               => array(
                    'head'   => 'HEAD',
                    'get'    => 'GET',
                    'post'   => 'POST',
                ),
                'default'             => 'get',
            ),
            array(    
                'field_id'            => 'queries',
                'title'               => __( 'Query Arguments', 'task-scheduler' ),
                'type'                => 'text',
                'label'               => array(
                    'key'     => __( 'Key', 'task-scheduler' ),
                    'value'   => __( 'Value', 'task-scheduler' ),
                ),
                'description'         => __( 'Set parameters of key-value pairs to be sent to the server with the url.', 'task-scheduler' ),
                'repeatable'          => true,
            ),
            array(    
                'field_id'            => 'search_keywords',
                'title'               => __( 'Keywords to Search', 'task-scheduler' ),
                'type'                => 'text',
                'description'         => __( 'Set keywords to find in the loaded page. Leave it blank to just confirm it does not return HTTP response error.', 'task-scheduler' ),
                'repeatable'          => true,
            ),
            array(    
                'field_id'           => 'must_have_all_keywords',
                'title'              => __( 'Find All', 'task-scheduler' ),
                'type'               => 'checkbox',
                'label'              => __( 'Whether all the above keywords must be present.', 'task-scheduler' )
                    . ' ' . __( 'Uncheck this if at least one of them should be included.', 'task-scheduler' ),
                'default'            => false,
            ),             
            array(    
                'field_id'           => 'search_in_the_source',
                'title'              => __( 'Search in HTML Source Code', 'task-scheduler' ),
                'type'               => 'checkbox',
                'label'              => __( 'Whether to search the above keywords in the HTML source code.', 'task-scheduler' )
                    . ' ' . __( 'Uncheck this if you want to check particular keywords in the displayed text on the page.', 'task-scheduler' ),
                'default'            => false,
            ),                  
        );
        
    }    

    public function validateSettings( /* $aInputs, $aOldInputs, $oAdminPage, $aSubmitInfo */ ) { 

        $_aParams    = func_get_args() + array(
            null, null, null, null
        );
        $aInputs      = $_aParams[ 0 ];
        $aOldInputs   = $_aParams[ 1 ];
        $oAdminPage  = $_aParams[ 2 ];
        $aSubmitInfo = $_aParams[ 3 ];      
    
        $_bIsValid   = true;
        $_aErrors    = array();

        // Make it numeric array
        if ( false === filter_var( $aInputs[ 'url' ], FILTER_VALIDATE_URL ) ) {
            $_bIsValid = false;
            $_aErrors[ $this->_sSectionID ][ 'url' ] = __( 'The specified URL is not valid.', 'task-scheduler' );            
        } 
        
        if ( ! $_bIsValid ) {

            // Set the error array for the input fields.
            $oAdminPage->setFieldErrors( $_aErrors );        
            $oAdminPage->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
            
        }    
    
        return $aInputs;         

    }
    
    
    /**
     * @since       1.2.0
     * @return      string
     */
    public function getMetaBoxOutput( /* $sOutput, $oTask */ ) {

        $_aParams    = func_get_args() + array(
            null, null
        );
        $sOutput   = $_aParams[ 0 ];
        $oTask     = $_aParams[ 1 ];      
        $_sSlug    = $oTask->routine_action;
        $_aOptions = ( array ) $oTask->{$_sSlug};
        $_aOutputs   = array();
return $sOutput;
/*         
        $_aOutputs[] = "<h4>" . __( 'Action', 'task-scheduler' ) . ":</h4>";
        $_aOutputs[] = "<p>" . apply_filters( "task_scheduler_filter_label_action_" . $_sSlug, '' ) . "</p>";
        
        $_aOutputs[] = "<h4>" . __( 'Email Addresses', 'task-scheduler' ) . ":</h4>";
        foreach( $_aOptions[ 'email_addresses' ] as $_aEmailSet ) {
            $_aOutputs[] = "<textarea readonly='readonly' style='width:80%;'>" . esc_textarea( $_aEmailSet ) . "</textarea>";
        }
        
        $_aOutputs[] = "<h4>" . __( 'Email Subject', 'task-scheduler' ) . ":</h4>";
        $_aOutputs[] = "<input type='text' readonly='readonly' value='" . esc_attr( $_aOptions[ 'email_title' ] ) . "' style='width:80%;' />";
        
        $_aOutputs[] = "<h4>" . __( 'Email Message', 'task-scheduler' ) . ":</h4>";
        $_aOutputs[] = "<textarea readonly='readonly' style='width:80%;'>" . esc_textarea( $_aOptions[ 'email_message' ] ) . "</textarea>";        
        return implode( '', $_aOutputs ); */
                
    }    
    
}
