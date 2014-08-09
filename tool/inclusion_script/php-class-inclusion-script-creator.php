<?php
/* If accessed from a browser, exit. */
$bIsCLI = php_sapi_name() == 'cli';
$sCarriageReturn = $bIsCLI ? PHP_EOL : '<br />';

// if ( $bIsCLI ) { exit; }

/* Include necessary files */
require( dirname( __FILE__ ) . '/class/PHP_Class_Files_Inclusion_Script_Creator.php' );

/* Set necessary paths */
$sTargetBaseDir		= dirname( dirname( dirname( __FILE__ ) ) );
$sResultFilePath	= $sTargetBaseDir . '/include/task-scheduler-include-class-files.php';
$sResultFilePath_2	= $sTargetBaseDir . '/include/task-scheduler-include-class-files-admin.php';
		
/* Check the permission to write. */
if ( ! file_exists( $sResultFilePath ) ) {
	file_put_contents( $sResultFilePath, '', FILE_APPEND | LOCK_EX );
}
if ( 
	( file_exists( $sResultFilePath ) && ! is_writable( $sResultFilePath ) )
	|| ! is_writable( dirname( $sResultFilePath ) ) 	
) {
	exit( sprintf( 'The permission denied. Make sure if the folder, %1$s, allows to modify/create a file.', dirname( $sResultFilePath ) ) );
}

/* Create a minified version of the framework. */
echo 'Started...' . $sCarriageReturn;

// for the front-end
new PHP_Class_Files_Inclusion_Script_Creator( 
	$sTargetBaseDir,
	array( $sTargetBaseDir . '/include/library', $sTargetBaseDir . '/include/class' ), 	// scan directory paths
	$sResultFilePath, 
	array(
		'header_class'		=>	'TaskScheduler_InclusionScriptHeader',
		'output_buffer'		=>	true,
		'header_type'		=>	'CONSTANTS',	
		'exclude_classes'	=>	array(),
		'include_function'	=>	'include',
		'base_dir_var'  	=>	'TaskScheduler_Registry::$sDirPath',
		'search'			=>	array(
			'allowed_extensions'	=>	array( 'php' ),	// e.g. array( 'php', 'inc' )
			'exclude_dir_paths'		=>	array( $sTargetBaseDir . '/include/class/admin' ),
			'exclude_dir_names'		=>	array(),
			'is_recursive'			=>	true,
		),			
	)
);
// for admin pages
new PHP_Class_Files_Inclusion_Script_Creator( 
	$sTargetBaseDir,
	array( $sTargetBaseDir . '/include/class/admin' ), 	// scan directory paths
	$sResultFilePath_2, 
	array(
		'header_class'		=>	'TaskScheduler_InclusionScriptHeader',
		'output_buffer'		=>	true,
		'header_type'		=>	'CONSTANTS',	
		'exclude_classes'	=>	array(),
		'base_dir_var'  	=>	'TaskScheduler_Registry::$sDirPath',
		'include_function'	=>	'include',
		'search'			=>	array(
			'allowed_extensions'	=>	array( 'php' ),	// e.g. array( 'php', 'inc' )
			'exclude_dir_paths'		=>	array( $sTargetBaseDir . '/include/class/admin' ),
			'exclude_dir_names'		=>	array(),
			'is_recursive'			=>	true,
		),			
	)
);
echo 'Done!' . $sCarriageReturn;