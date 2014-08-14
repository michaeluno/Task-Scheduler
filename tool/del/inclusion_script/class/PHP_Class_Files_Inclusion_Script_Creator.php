<?php
/**
 * PHP Class Files Inclusion Script Creator
 * 
 * @author				Michael Uno <michael@michaeluno.jp>
 * @copyright			2013-2014 (c) Michael Uno
 * @license				MIT	<http://opensource.org/licenses/MIT>
 */
 
/**
 * Creates a PHP script that includes files that are in a specified directory.
 *  
 * @remark	The parsed class file must have a name of the class defined in the file.
 */
class PHP_Class_Files_Inclusion_Script_Creator extends PHP_Class_Files_Script_Creator_Base {
	
	static protected $_aStructure_Options = array(
	
		'header_class_name'		=>	'',
		'header_class_path'		=>	'',
		'output_buffer'			=>	true,
		'header_type'			=>	'DOCBLOCK',	
		'exclude_classes'		=>	array(),
		'include_function'		=>	'include',		// require, require_once, include_once
		'base_dir_var'			=>	'',
		
		// Search options
		'search'	=>	array(
			'allowed_extensions'	=>	array( 'php' ),	// e.g. array( 'php', 'inc' )
			'exclude_dir_paths'		=>	array(),
			'exclude_dir_names'		=>	array(),
			'is_recursive'			=>	true,
		),
		
	);
	
	/**
	 * @param		string			$sBaseDirPath			The base directory path that the inclusion path is relative to.
	 * @param		string|array	$asScanDirPaths			The target directory path(s).
	 * @param		string			$sOutputFilePath		The destination file path.
	 * @param		array			$aOptions				The options array. It takes the following arguments.
	 *  - 'header_class_name'	: string	the class name that provides the information for the heading comment of the result output of the minified script.
	 *  - 'header_class_path'	: string	(optional) the path to the header class file.
	 *  - 'output_buffer'		: boolean	whether or not output buffer should be printed.
	 *  - 'header_type'			: string	whether or not to use the docBlock of the header class; otherwise, it will parse the constants of the class. 
	 *  - 'exclude_classes' 	: array		an array holding class names to exclude.
	 *  - 'include_function'	: string	the function name without parentheses to include the path such as require, require_once, include. Default: include.
	 *  - 'base_dir_var'		: string	the variable or constant name that is prefixed before the inclusion path.
	 *  - 'search'				: array		the arguments for the directory search options.
	 * 	The accepted values are 'CONSTANTS' or 'DOCBLOCK'.
	 * <h3>Example</h3>
	 * <code>array(
	 *		'header_class_name'	=>	'HeaderClassForMinifiedVerions',
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
	public function __construct( $sBaseDirPath, $asScanDirPaths, $sOutputFilePath, array $aOptions=array() ) {

		$aOptions			= $aOptions + self::$_aStructure_Options;
		$aOptions['search']	= $aOptions['search'] + self::$_aStructure_Options['search'];
		
		$_sCarriageReturn	= php_sapi_name() == 'cli' ? PHP_EOL : '<br />';
		$_aScanDirPaths		= ( array ) $asScanDirPaths;
		if ( $aOptions['output_buffer'] ) {
			echo 'Searching files under the directories: ' . implode( ', ', $_aScanDirPaths ) . $_sCarriageReturn;
		}
		
		/* Store the file contents into an array. */
		$_aFilePaths	= $this->_getFileLists( $_aScanDirPaths, $aOptions['search'] );	
		$_aFiles		= $this->_formatFileArray( $_aFilePaths );
		unset( $_aFiles[ pathinfo( $sOutputFilePath, PATHINFO_FILENAME ) ] );	// it's possible that the minified file also gets loaded but we don't want it.

		if ( $aOptions['output_buffer'] ) {
			
			echo sprintf( 'Found %1$s file(s)', count( $_aFiles ) ) . $_sCarriageReturn;
			foreach ( $_aFiles as $_aFile ) {
				echo $_aFile['path'] . $_sCarriageReturn;
				// echo implode( ', ', $_aFile['defined_classes'] ) . $_sCarriageReturn;
			}
			
		}			
	
		/* Generate the output script header comment */
		$_sHeaderComment = $this->_getHeaderComment( $_aFiles, $aOptions );
		if ( $aOptions['output_buffer'] ) {
			echo( $_sHeaderComment ) . $_sCarriageReturn;
		}
	
		/* Sort the classes - in some PHP versions, parent classes must be defined before extended classes. */
		$_aFiles = $this->sort( $_aFiles, $aOptions['exclude_classes'] );
		
		if ( $aOptions['output_buffer'] ) {
			echo sprintf( 'Sorted %1$s file(s)', count( $_aFiles ) ) . $_sCarriageReturn;			
		}				
		
		/* Write to a file */
		$this->write( $_aFiles, $sBaseDirPath, $sOutputFilePath, $_sHeaderComment, $aOptions['include_function'], $aOptions['base_dir_var'] );
		
	}
							
	public function sort( array $aFiles, array $aExcludingClassNames ) {
		
		foreach( $aFiles as $_sClassName => $_aFile ) {
			if ( in_array( $_sClassName, $aExcludingClassNames ) ) {
				unset( $aFiles[ $_sClassName ] );
			}
		}
		return $this->_resolveDependent( array(), $aFiles );
	
	}
		
		private function _resolveDependent( array $aNewFileContainer, array $aFiles ) {
					
			$_iNotMoved	= 0;
			foreach( $aFiles as $_sClassName => $_aFile ) {
				
				$_sParentClassName	= $_aFile['dependency'];
				$_aDefinedClasses	= $_aFile['defined_classes'];
				
				// If no dependency, just move.
				if ( ! $_sParentClassName ) {
					$aNewFileContainer[ $_sClassName ] = $_aFile;
					unset( $aFiles[ $_sClassName ] );
					continue;
				}
				
				// At this point, there is a dependency.
				
				// If it is stored in the moving array, go on.
				if ( isset( $aNewFileContainer[ $_sParentClassName ] ) ) {
					$aNewFileContainer[ $_sClassName ] = $_aFile;
					unset( $aFiles[ $_sClassName ] );
					continue;				
				}
				
				// If the parent class is already defined inside the moved files, go on.
				if ( $this->_isClassDefined( $_sParentClassName, $aNewFileContainer ) ) {
					$aNewFileContainer[ $_sClassName ] = $_aFile;
					unset( $aFiles[ $_sClassName ] );
					continue;					
				}
				
				// If the parent class is defined inside the remaining files (the ones not moved yet), do not move yet.
				$_aFilesCopy		= $aFiles;
				unset( $_aFilesCopy[ $_sClassName ] );
				if ( $this->_isClassDefined( $_sParentClassName, $_aFilesCopy ) ) {
					$_iNotMoved++;
					continue;					
				}
				
				// It can be an external component. In that case, it is not stored in the parsing array.
				if ( ! isset( $aFiles[ $_sParentClassName ] ) ) {					
					$aNewFileContainer[ $_sClassName ] = $_aFile;
					unset( $aFiles[ $_sClassName ] );					
					continue;
				}
				
				// Okay, here the parent class is not stored yet. So do not move.
				$_iNotMoved++;
				
			}
		
			if ( $_iNotMoved ) {
				$aNewFileContainer = array_merge( $this->_resolveDependent( $aNewFileContainer, $aFiles ), $aNewFileContainer );
			}
			
			return $aNewFileContainer;
			
		}
			/**
			 * Checks if the given class name is defined in the given files array.
			 */
			private function _isClassDefined( $sSubjectClassName, $aFiles ) {
				
				foreach( $aFiles as $_sClassName => $_aFile ) {
					if ( in_array( $sSubjectClassName, $_aFile['defined_classes'] ) ) {
						return true;
					}
				}
				return false;
				
			}
			
	public function write( array $aFiles, $sBaseDirPath, $sOutputFilePath, $sHeadingComment, $sIncludeFunc, $sBaseDirVar ) {
			
		$_aData = array();
		
		// Create a heading.
		$_aData[] = mb_convert_encoding( '<?php ' . PHP_EOL . $sHeadingComment, 'UTF-8', 'auto' );
		
		// Insert the data
		foreach( $aFiles as $_aFile ) {					
			$_sPath		= str_replace('\\', '/', $_aFile['path'] );
			
			$_sPath		= $this->_getRelativePath( $sBaseDirPath, $_sPath );
			$_aData[]	= "{$sIncludeFunc}( " . $sBaseDirVar . " . '" . $_sPath . "' );" . PHP_EOL;
		}
		
		// Remove the existing file.
		if ( file_exists( $sOutputFilePath ) ) {
			unlink( $sOutputFilePath );
		}
		
		// Write to a file.
		file_put_contents( $sOutputFilePath, implode( '', $_aData ), FILE_APPEND | LOCK_EX );
		
	}
	
		/**
		 * Calculates the relative path from the given path.
		 * 
		 */
		private function _getRelativePath( $from, $to ) {
			
			// some compatibility fixes for Windows paths
			$from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
			$to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
			$from = str_replace('\\', '/', $from);
			$to   = str_replace('\\', '/', $to);

			$from     = explode('/', $from);
			$to       = explode('/', $to);
			$relPath  = $to;

			foreach($from as $depth => $dir) {
				// find first non-matching dir
				if($dir === $to[$depth]) {
					// ignore this directory
					array_shift($relPath);
				} else {
					// get number of remaining dirs to $from
					$remaining = count($from) - $depth;
					if($remaining > 1) {
						// add traversals up to first matching dir
						$padLength = (count($relPath) + $remaining - 1) * -1;
						$relPath = array_pad($relPath, $padLength, '..');
						break;
					} else {
						$relPath[0] = './' . $relPath[0];
					}
				}
			}
			return ltrim( implode( '/', $relPath ), '.' );
		}	

}