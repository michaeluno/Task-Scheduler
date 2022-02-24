<?php
/**
 * Handles plugin options.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

 /**
  * 
  */
final class TaskScheduler_Option {
        
    /**
     * Stores the option key.
     */
    public $sOptionKey;

    /**
     * @var array
     */
    public $aOptions = array();

    /**
     * Represents the option structure and the default values.
     */
    static public $aDefaults = array(
        'server_heartbeat'    => array(
            'power'        => true,
            'interval'     => 24,
            'query_string' => array(
                0 => true,
                1 => 'doing_server_heartbeat',
            ),
        ),
        'email'    => array(
            'message_body'    => null,
        ),
        'task_default'        => array(
            'max_root_log_count'    => 0,
            'max_execution_time'    => 30,
        ),
        'routine'    => array(
            'max_background_routine_count' => 12,
        ),        
        'reset'    => array(
            // 'reset_upon_deactivation'    => false, // @deprecated 1.3.1
            'reset_on_uninstall'         => true,
        ),
    );
    
    /**
     * Stores the self-instance.
     */
    static public $oInstance;
    
    public function __construct( $sOptionKey ) {
        $this->sOptionKey = $sOptionKey;
        $this->aOptions   = TaskScheduler_Utility::uniteArrays(
            get_option( $this->sOptionKey, array() ),
            $this->___getDefaultsFormatted( self::$aDefaults )
        );
    }
        
        /**
         * Formats the default option array.
         * 
         * Some options are dynamic and those dynamic assignments cannot achieve in a property declaration. 
         * So this method takes care of it. e.g. PHP max execution time as a default option value.
         * @return array
         */
        private function ___getDefaultsFormatted( array $aDefaults ) {
            $_iServerAllowedMaxExecutionTime = TaskScheduler_Utility::getServerAllowedMaxExecutionTime( 30 );
            $_iDefaultInterval               = $_iServerAllowedMaxExecutionTime > 60
                ? 60
                : (
                    $_iServerAllowedMaxExecutionTime
                        ? round( $_iServerAllowedMaxExecutionTime * 8 / 10 )
                        : 24
                );
            $aDefaults[ 'server_heartbeat' ][ 'interval' ] = $_iDefaultInterval;
            $aDefaults[ 'task_default' ][ 'max_execution_time' ] = 0 === $_iServerAllowedMaxExecutionTime
                ? $aDefaults[ 'task_default' ][ 'max_execution_time' ]    // 30 ( 0 is not recommended )
                : $_iServerAllowedMaxExecutionTime;
            return $aDefaults;
        }
    
    /**
     * Returns the instance of the class.
     * 
     * This is to ensure only one instance exists.
     */
    static public function getInstance() {
        self::$oInstance = self::$oInstance ? self::$oInstance : new TaskScheduler_Option( TaskScheduler_Registry::$aOptionKeys['main'] );
        return self::$oInstance;
    }
    
    /**
     * Resets the cached options.
     * 
     * It will re-retrieve the options.
     */
    static public function refresh() {
        self::$oInstance = null;
        self::getInstance();
    }
    
    /**
     * Returns the specified option value.
     */
    static public function get( $asKey=null, $vDefault=null ) {
                
        $_oOption = self::getInstance();
        
        // If the key is not set or false, return the entire option array.
        if ( ! $asKey ) {
            return empty( $_oOption->aOptions )
                ? $vDefault
                : $_oOption->aOptions + self::$aDefaults;
        }

        // Now either the section ID or field ID is given. 
        return TaskScheduler_AdminPageFramework_WPUtility::getArrayValueByArrayKeys( 
            $_oOption->aOptions + self::$aDefaults,
            array_values( TaskScheduler_AdminPageFramework_WPUtility::getAsArray( $asKey ) ), 
            $vDefault 
        );
        
    }
    
}