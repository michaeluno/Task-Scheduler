<?php
/**
 * PHP Class Files Minifier
 * 
 * Provides plugin and theme developers with simpler means of creating option pages, custom post types, ant meta boxes. 
 * 
 * @author				Michael Uno <michael@michaeluno.jp>
 * @copyright			2013-2014 (c) Michael Uno
 * @license				MIT	<http://opensource.org/licenses/MIT>
 */
 
/**
 * Creates a minified version of PHP scripts from the given PHP class directory.
 * 
 * It collects PHP class files and make them into one and removes PHP comments except the specified class docBlock.
 * 
 * @remark	The parsed class file must have a name of the class defined in the file.
 */
class PHP_Class_Files_Minifier {
	
	static protected $_aStructure_Options = array(
	
		'header_class'		=>	'',
		'output_buffer'		=>	true,
		'header_type'		=>	'DOCBLOCK',	
		'exclude_classes'	=>	array(),
		
		// Search options
		'search'	=>	array(
			'allowed_extensions'	=>	array( 'php' ),	// e.g. array( 'php', 'inc' )
			'exclude_dir_paths'		=>	array(),
			'exclude_dir_names'		=>	array(),
			'is_recursive'			=>	true,
		),		
		
	);
	
	/**
	 * 
	 * @param		string	$sSourceDirPath		The target directory path.
	 * @param		string	$sOutputFilePath	The destination file path.
	 * @param		array	$aOptions			The options array. It takes the following arguments.
	 *  - 'header_class'	: string	the class name that provides the information for the heading comment of the result output of the minified script.
	 *  - 'output_buffer'	: boolean	whether or not output buffer should be printed.
	 *  - 'header_type'		: string	whether or not to use the docBlock of the header class; otherwise, it will parse the constants of the class. 
	 *  - 'exclude_classes'	: array		an array holding class names to exclude.
	 *  - 'search'			: array		the arguments for the directory search options.
	 * 	The accepted values are 'CONSTANTS' or 'DOCBLOCK'.
	 * <h3>Example</h3>
	 * <code>array(
	 *		'header_class'	=>	'HeaderClassForMinifiedVerions',
	 *		'file_pettern'	=>	'/.+\.(php|inc)/i',
	 *		'output_buffer'	=>	false,
	 *		'header_type'	=>	'CONSTANTS',
	 * 
	 * )</code>
	 * 
	 * When false is passed to the 'use_docblock' argument, the constants of the header class must include 'Version', 'Name', 'Description', 'URI', 'Author', 'CopyRight', 'License'. 
	 * <h3>Example</h3>
	 * <code>class TaskScheduler_Registry_Base {
	 * 		const Version		= '1.0.0b08';
	 * 		const Name			= 'Task Scheduler';
	 * 		const Description	= 'Provides an enhanced task management system for WordPress.';
	 * 		const URI			= 'http://en.michaeluno.jp/';
	 * 		const Author		= 'miunosoft (Michael Uno)';
	 * 		const AuthorURI		= 'http://en.michaeluno.jp/';
	 * 		const CopyRight		= 'Copyright (c) 2014, <Michael Uno>';
	 * 		const License		= 'GPL v2 or later';
	 * 		const Contributors	= '';
	 * }</code>
	 */
	public function __construct( $asScanDirPaths, $sOutputFilePath, array $aOptions=array() ) {

		$aOptions			= $aOptions + self::$_aStructure_Options;
		$aOptions['search']	= $aOptions['search'] + self::$_aStructure_Options['search'];
		
		$_sCarriageReturn	= php_sapi_name() == 'cli' ? PHP_EOL : '<br />';
		$_aScanDirPaths		= ( array ) $asScanDirPaths;
		if ( $aOptions['output_buffer'] ) {
			echo 'Searching files under the directory: ' . implode( ', ', $_aScanDirPaths ) . $_sCarriageReturn;
		}
		
		/* Store the file contents into an array. */
		$_aFilePaths	= $this->_getFileLists( $_aScanDirPaths, $aOptions['search'] );	
		$_aFiles		= $this->_formatFileArray( $_aFilePaths, $aOptions['exclude_classes'] );
		unset( $_aFiles[ pathinfo( $sOutputFilePath, PATHINFO_FILENAME ) ] );	// it's possible that the minified file also gets loaded but we don't want it.

		if ( $aOptions['output_buffer'] ) {
			
			echo sprintf( 'Found %1$s file(s)', count( $_aFiles ) ) . $_sCarriageReturn;
			foreach ( $_aFiles as $_aFile ) {
				echo $_aFile['path'] . $_sCarriageReturn;
			}
			
		}			
		
		// Generate the heading comment
		$_sHeaderClass		= $aOptions['header_class'];
		$_sHeaderComment	= $this->_getHeaderComment( 
			isset( $_aFiles[ $_sHeaderClass ] ) ? $_aFiles[ $_sHeaderClass ][ 'path' ] : '',
			$_sHeaderClass,
			$aOptions['header_type']
		);
		if ( $aOptions['output_buffer'] ) {
			echo( $_sHeaderComment ) . $_sCarriageReturn;
		}
		
		/* Sort the classes - in some PHP versions, parent classes must be defined before extended classes. */
		$_aFiles = $this->sort( $_aFiles );
		
		/* Write to a file */
		$this->write( $_aFiles, $sOutputFilePath, $_sHeaderComment );
		
	}
				
		/**
		 * Sets up the array consisting of class paths with the key of file name w/o extension.
		 */
		private function _formatFileArray( array $_aFilePaths, array $aExcludingClassNames ) {
						
			/*
			 * Now the structure of $_aFilePaths looks like:
				array
				  0 => string '.../class/MyClass.php'
				  1 => string '.../class/MyClass2.php'
				  2 => string '.../class/MyClass3.php'
				  ...
			 * 
			 */		 
			$_aFiles = array();
			foreach( $_aFilePaths as $_sFilePath ) {
				
				$_sClassName	= pathinfo( $_sFilePath, PATHINFO_FILENAME );
				if ( in_array( $_sClassName, $aExcludingClassNames ) ) {
					continue;
				}
				$_sPHPCode		= $this->getPHPCode( $_sFilePath );
				$_aFiles[ $_sClassName ] = array(	// the file name without extension will be assigned to the key
					'path'			=>	$_sFilePath,	
					'code'			=>	$_sPHPCode ? trim( $_sPHPCode ) : '',
					'dependency'	=>	$this->_getDependentClass( $_sPHPCode ),
				); 

			}
			return $_aFiles;
				
		}
			private function getPHPCode( $sFilePath ) {
				$sCode = php_strip_whitespace ( $sFilePath );
				$sCode = preg_replace( '/^<\?php/', '', $sCode );
				$sCode = preg_replace( '/\?>\s+?$/', '', $sCode );
				return $sCode;
			}
			
			private function _getFileLists( $asDirPaths, $aSearchOptions ) {
				$_aFiles = array();
				foreach( $asDirPaths as $_sDirPath ) {
					$_aFiles = array_merge( $this->_getFileList( $_sDirPath, $aSearchOptions ), $_aFiles );
				}
				return array_unique( $_aFiles );
			}			
			/**
			 * Returns an array of scanned file paths.
			 * 
			 * The returning array structure looks like this:
				array
				  0 => string '.../class/MyClass.php'
				  1 => string '.../class/MyClass2.php'
				  2 => string '.../class/MyClass3.php'
				  ...
			 * 
			 */
			protected function _getFileList( $sDirPath, array $aSearchOptions ) {
				
				$sDirPath	= rtrim( $sDirPath, '\\/' ) . DIRECTORY_SEPARATOR;	// ensures the trailing (back/)slash exists. 
				
				$_aExcludingDirPaths = $this->_formatPaths( $aSearchOptions['exclude_dir_paths'] );
				
				if ( defined( 'GLOB_BRACE' ) ) {	// in some OSes this flag constant is not available.
					$_sFileExtensionPattern = $this->_getGlobPatternExtensionPart( $aSearchOptions['allowed_extensions'] );
					$_aFilePaths = $aSearchOptions[ 'is_recursive' ]
						? $this->doRecursiveGlob( $sDirPath . '*.' . $_sFileExtensionPattern, GLOB_BRACE, $_aExcludingDirPaths, ( array ) $aSearchOptions['exclude_dir_names'] )
						: ( array ) glob( $sDirPath . '*.' . $_sFileExtensionPattern, GLOB_BRACE );
					return array_filter( $_aFilePaths );	// drop non-value elements.	
				} 
					
				// For the Solaris operation system.
				$_aFilePaths = array();
				foreach( $aSearchOptions['allowed_extensions'] as $__sAllowedExtension ) {
					$__aFilePaths = $aSearchOptions[ 'is_recursive' ]
						? $this->doRecursiveGlob( $sDirPath . '*.' . $__sAllowedExtension, 0, $_aExcludingDirPaths, ( array ) $aSearchOptions['exclude_dir_names'] )
						: ( array ) glob( $sDirPath . '*.' . $__sAllowedExtension );
					$_aFilePaths = array_merge( $__aFilePaths, $_aFilePaths );
				}
				return array_unique( array_filter( $_aFilePaths ) );
				
			}
				/**
				 * Formats the paths.
				 * 
				 * This is necessary to check excluding paths because the user may pass paths with a forward slash but the system may use backslashes.
				 */
				private function _formatPaths( $asDirPaths ) {
					
					$_aFormattedDirPaths = array();
					$_aDirPaths = is_array( $asDirPaths ) ? $asDirPaths : array( $asDirPaths );
					foreach( $_aDirPaths as $_sPath ) {
						$_aFormattedDirPaths[] = str_replace( '\\', '/', $_sPath );
					}
					return $_aFormattedDirPaths;
					
				}
				/**
				 * The recursive version of the glob() function.
				 */
				private function doRecursiveGlob( $sPathPatten, $nFlags=0, array $aExcludeDirPaths=array(), array $aExcludeDirNames=array() ) {

					$_aFiles	= glob( $sPathPatten, $nFlags );	
					$_aFiles	= is_array( $_aFiles ) ? $_aFiles : array();	// glob() can return false.
					$_aDirs		= glob( dirname( $sPathPatten ) . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR|GLOB_NOSORT );
					$_aDirs		= is_array( $_aDirs ) ? $_aDirs : array();
					foreach ( $_aDirs as $_sDirPath ) {
						$_sDirPath	= str_replace( '\\', '/', $_sDirPath );
						if ( in_array( $_sDirPath, $aExcludeDirPaths ) ) { continue; }
						if ( in_array( pathinfo( $_sDirPath, PATHINFO_DIRNAME ), $aExcludeDirNames ) ) { continue; }
						
						$_aFiles	= array_merge( $_aFiles, $this->doRecursiveGlob( $_sDirPath . DIRECTORY_SEPARATOR . basename( $sPathPatten ), $nFlags, $aExcludeDirPaths ) );
						
					}
					return $_aFiles;
					
				}				
				/**
				 * Constructs the file pattern of the file extension part used for the glob() function with the given file extensions.
				 */
				private function _getGlobPatternExtensionPart( array $aExtensions=array( 'php', 'inc' ) ) {
					return empty( $aExtensions ) 
						? '*'
						: '{' . implode( ',', $aExtensions ) . '}';
				}				
			
			/**
			 * Returns the depending class
			 */
			private function _getDependentClass( $sPHPCode ) {
				
				if ( ! preg_match( '/class\s+(.+?)\s+extends\s+(.+?)\s+{/i', $sPHPCode, $aMatch ) ) return null;
				
				return $aMatch[ 2 ];
				
			}
		
		/**
		 * Generates the script heading comment.
		 */
		private function _getHeaderComment( $sFilePath, $sClassName, $sHeaderType='DOCKBLOCK' ) {

			if ( ! file_exists( $sFilePath ) ) { return ''; }
			if ( ! $sClassName ) { return ''; }

			include_once( $sFilePath );
			$_aDeclaredClasses = ( array ) get_declared_classes();
			foreach( $_aDeclaredClasses as $_sClassName ) {
				if ( $sClassName !== $_sClassName ) { continue; }
				return 'DOCBLOCK' === $sHeaderType
					? $this->_getClassDocBlock( $_sClassName )
					: $this->_generateHeaderComment( $_sClassName );
			}
			return '';
		
		}
			/**
			 * Generates the heading comments from the class constants.
			 */
			private function _generateHeaderComment( $sClassName ) {
				
				$_oRC			= new ReflectionClass( $sClassName );
				$_aConstants	= $_oRC->getConstants() + array(
					'Name'			=>	'', 'Version'		=>	'',
					'Description'	=>	'', 'URI'			=>	'',
					'Author'		=>	'', 'AuthorURI'		=>	'',
					'Copyright'		=>	'', 'License'		=>	'',
					'Contributors'	=>	'',
				);
				$_aOutputs		= array();
				$_aOutputs[]	= '/' . '**' . PHP_EOL;
				$_aOutputs[]	= "\t" . $_aConstants['Name'] . ' '
					. ( $_aConstants['Version']	? 'v' . $_aConstants['Version'] . ' '  : '' ) 
					. ( $_aConstants['Author']	? 'by ' . $_aConstants['Author'] . ' ' : ''  )
					. PHP_EOL;
				$_aOutputs[]	= $_aConstants['Description']	? "\t". $_aConstants['Description'] . PHP_EOL : '';
				$_aOutputs[]	= $_aConstants['URI'] 			? "\t". '<' . $_aConstants['URI'] . '>' . PHP_EOL : '';
				$_aOutputs[]	= "\t" . $_aConstants['Copyright']
					. ( $_aConstants['License']	? '; Licensed under ' . $_aConstants['License'] : '' );
				$_aOutputs[]	= ' */' . PHP_EOL;
				return implode( '', array_filter( $_aOutputs ) );
			}	
			/**
			 * Returns the docblock of the specified class
			 */
			private function _getClassDocBlock( $sClassName ) {
				$_oRC = new ReflectionClass( $sClassName );
				return trim( $_oRC->getDocComment() );
			}
	
	public function sort( array $aFiles ) {
		return $this->_resolveDependent( $aFiles );
	}
		/**
		 * This sorts the parsed PHP classes by making parent classes come earlier.
		 * 
		 * In some PHP versions, extended class must be declared after the parent class. 
		 */
		private function _resolveDependent( array $aFiles ) {
		
			/* Append the dependent code to the dependent one and remove the dependent. */
			$aFiles = $this->_moveDependant( $aFiles );
			
			/* Unset the defendant element */
			foreach ( $aFiles as $sClassName => $aFile ) {
				if ( $aFile['code'] ) { continue; }
				unset( $aFiles[ $sClassName ] );
			}
			
			/* Make sure dependant elements no longer exist.*/
			$_iDependency = 0;
			foreach ( $aFiles as $sClassName => $aFile ) {
				if ( $aFile['dependency'] && isset( $aFiles[ $aFile['dependency'] ] ) ) {
					$_iDependency++;
				}
			}
			if ( $_iDependency ) {
				return $this->_resolveDependent( $aFiles );
			}
			return $aFiles;
			
		}
		private function _moveDependant( $aFiles ) {
			
			$iMoved = 0;
			foreach( $aFiles as $sClassName => &$aFile ) {
			
				if ( ! $aFile['dependency'] ) continue;
				if ( ! isset( $aFiles[ $aFile['dependency'] ] ) ) continue;	// it can be an external components.
				if ( ! $aFile['code'] ) continue;
				$aFiles[ $aFile['dependency'] ]['code'] .= $aFile['code'];
				$aFile['code'] = '';
				$iMoved++;
				
			}
			if ( $iMoved ) {
				$aFiles = $this->_moveDependant( $aFiles );
			}
			return $aFiles;
			
		}
		
	public function write( array $aFiles, $sOutputFilePath, $sHeadingComment ) {
			
		$_aData = array();
		
		// Create a heading.
		$_aData[] = mb_convert_encoding( '<?php ' . PHP_EOL . $sHeadingComment . ' ', 'UTF-8', 'auto' );		
		foreach( $aFiles as $_aFile ) {
			$_aData[] = mb_convert_encoding( $_aFile['code'], 'UTF-8', 'auto' );
		}
		
		// Remove the existing file.
		if ( file_exists( $sOutputFilePath ) ) {
			unlink( $sOutputFilePath );
		}
		
		// Write to a file.
		file_put_contents( $sOutputFilePath, implode( '', $_aData ), FILE_APPEND | LOCK_EX );
		
	}

}