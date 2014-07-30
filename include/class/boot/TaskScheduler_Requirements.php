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
final class TaskScheduler_Requirements {

	/**
	 * Stores the admin notice messages.
	 */
	private $_aWarnings = array();	
	
	/**
	 * Indicates what to do when the requirement check fails.
	 * 
	 * 0 : do nothing
	 * 1 : deactivate the plugin
	 * 2 : end the script
	 */
	private $_iExitCode = 1;	
	
	/**
	 * Stores the criteria of the requirements.
	 */
	private $_aParams = array();
	
	/**
	 * The default criteria and their error messages.
	 */
	private $_aDefaultParams = array(
		'php' => array(
			'version' => '5.2.4',
			'error' => 'The plugin requires the PHP version %1$s or higher.',
		),
		'wordpress' => array(
			'version' => '3.3',
			'error' => 'The plugin requires the WordPress version %1$s or higher.',
		),
		'functions' => array(
			// e.g. 'mblang' => 'The plugin requires the mbstring extension.',
		),
		'classes' => array(
			// e.g. 'DOMDocument' => 'The plugin requires the DOMXML extension.',
		),
		'constants'	=> array(
			// e.g. 'THEADDONFILE' => 'The plugin requires the ... addon to be installed.',
			// e.g. 'APSPATH' => 'The script cannot be loaded directly.',
		),
		'files'	=>	array(
			// e.g. 'home/my_user_name/my_dir/scripts/my_scripts.php' => 'The required script could not be found.',
			
		),
	);
	private $_sPluginFilePath;
	
	private $_sTextDomain = '';
	
	function __construct( $sPluginFilePath, $aParams=array(), $iExitCode=1, $sHook='', $_sTextDomain='' ) {
		
		// avoid undefined index warnings.
		$this->_aParams = $aParams + $this->_aDefaultParams;	
		$this->_aParams['php'] = $this->_aParams['php'] + $this->_aDefaultParams['php'];
		$this->_aParams['wordpress'] = $this->_aParams['wordpress'] + $this->_aDefaultParams['wordpress'];

		$this->_sPluginFilePath = $sPluginFilePath;
		$this->_iExitCode = $iExitCode;
		
		if ( ! empty( $sHook ) ) 
			add_action( $sHook, array( $this, 'check' ) );
		else if ( $sHook === '' )		
			$this->check();
		else if ( is_null( $sHook ) )
			return $this;	// do nothing if it's null
			
	}
	
	/**
	 * Returns whether or not the it passed the check.
	 */
	public function isSufficient() {
		
		return empty( $this->_aWarnings );
		
	}
	
	/**
	 * Sets a warning message.
	 */
	public function setWarning( $sWarningMessage ) {
		
		add_action( 'admin_notices', array( $this, '_replyToPrintAdminNotice' ) );	
		$this->_aWarnings[] = $sWarningMessage;
		
	}
	
	/**
	 * Checks requirements.
	 * 
	 * @remark			Do not call this method with register_activation_hook(). For some reasons, it won't trigger the deactivate_plugins() function.
	 */
	public function check() {
				 	
		if ( ! $this->_isSufficientPHPVersion( $this->_aParams['php']['version'] ) ) {
			$this->_aWarnings[] = sprintf( $this->_aParams['php']['error'], $this->_aParams['php']['version'] );
		}

		if ( ! $this->_isSufficientWordPressVersion( $this->_aParams['wordpress']['version'] ) ) {
			$this->_aWarnings[] = sprintf( $this->_aParams['wordpress']['error'], $this->_aParams['wordpress']['version'] );
		}
		
		$_aNonFoundFuncs = $this->_checkFunctions( $this->_aParams['functions'] );
		if ( ! empty( $_aNonFoundFuncs ) ) {
			foreach ( $_aNonFoundFuncs as $i => $sError ) 
				$this->_aWarnings[] = $sError;
		}
		
		$_aNonFoundClasses = $this->_checkClasses( $this->_aParams['classes'] );
		if ( ! empty( $_aNonFoundClasses ) ) {
			foreach ( $_aNonFoundClasses as $i => $sError ) 
				$this->_aWarnings[] = $sError;
		}
		
		$_aNonFoundConstants = $this->_checkConstants( $this->_aParams['constants'] );
		if ( ! empty( $_aNonFoundConstants ) ) {
			foreach ( $_aNonFoundConstants as $i => $sError ) 
				$this->_aWarnings[] = $sError;
		}
		
		$_aNonFoundFiles = $this->_checkFiles( $this->_aParams['files'] );
		if ( ! empty( $_aNonFoundFiles ) ) {
			foreach ( $_aNonFoundFiles as $i => $sError ) 
				$this->_aWarnings[] = $sError;
		}		
	
		if ( ! empty( $this->_aWarnings ) ) {

			add_action( 'admin_notices', array( $this, '_replyToPrintAdminNotice' ) );	
			if ( $this->_iExitCode === 1 || $this->_iExitCode === true ) {
				$this->_aWarnings[] = '<strong>' . __( 'Deactivating the plugin.', $this->_sTextDomain ) . '</strong>';
				$this->includeOnce( ABSPATH . '/wp-admin/includes/plugin.php' );
				deactivate_plugins( $this->_sPluginFilePath );			
			}
			else if ( $this->_iExitCode === 2 ) {
				$this->_aWarnings[] = '<strong>' . __( 'Exiting the script.', $this->_sTextDomain ) . '</strong>';
				die( $this->_getWarnings( $this->_aWarnings, $this->_sPluginFilePath, $this->_iExitCode ) );
			}

		}
	}		
		private function includeOnce( $sPath ) {
			
			if ( ! file_exists( $sPath ) ) return false;
			include_once( $sPath );
			return true;
			
		}	
		public function _replyToPrintAdminNotice() {
			
			echo $this->_getWarnings( $this->_aWarnings, $this->_sPluginFilePath, $this->_iExitCode );
			
		}	
		private function _getWarnings( $aWarnings, $sScriptPath, $iExitCode ) {
			
			$aWarnings = array_unique( $aWarnings );
			$sPluginName = $this->_getScriptName( $sScriptPath );
			return "<div class='error'>"
					. "<p>"
						. ( $sPluginName ?  "<strong>" . $sPluginName . "</strong>:&nbsp;" : '' )
						. implode( '<br />', $aWarnings ) 
					. "</p>"
				. "</div>";		
			
		}
		private function _getScriptName( $sFilePath ) {
			
			$aPluginData = get_plugin_data( $sFilePath );
			return isset( $aPluginData['Name'] )
				? $aPluginData['Name']
				: '';
			
		}
		
		private function _isSufficientPHPVersion( $sPHPVersion ) {
			return version_compare( phpversion(), $sPHPVersion, ">=" );
		}
		private function _isSufficientWordPressVersion( $sWordPressVersion ) {
			return version_compare( $GLOBALS['wp_version'], $sWordPressVersion, ">=" );
		}
		

		private function _checkClasses( $aClasses ) {
			return $this->_getNonExistents( 'class_exists', $aClasses );
		}
		private function _checkFunctions( $aFuncs ) {
			return $this->_getNonExistents( 'function_exists', $aFuncs );
		}	
		private function _checkConstants( $aConstants ) {
			return $this->_getNonExistents( 'defined', $aConstants );
		}	
		private function _checkFiles( $aFilePaths ) {
			return $this->_getNonExistents( 'file_exists', $aFilePaths );
		}
			private function _getNonExistents( $sFuncName, $aSubjects ) {
				
				$aNonExistents = array();
				foreach( $aSubjects as $sSubject => $sError ) {
					if ( ! call_user_func_array( $sFuncName, array( $sSubject ) ) ) {
						$aNonExistents[] = sprintf( $sError, $sSubject );
					}
				}
				return $aNonExistents;
				
			}	
}