<?php
/* If accessed from a browser, exit. */
$bIsCLI = php_sapi_name() == 'cli';
$sCarriageReturn = $bIsCLI ? PHP_EOL : '<br />';

// if ( $bIsCLI ) { exit; }

/* Include necessary files */
require( dirname( __FILE__ ) . '/class/PHP_Class_Files_Minifier.php' );

/* Set necessary paths */
$sTargetBaseDir		= dirname( dirname( dirname( __FILE__ ) ) );
$sTargetDir			= $sTargetBaseDir . '/include/class';
$sResultFilePath	= $sTargetBaseDir . '/include/task-scheduler-classes.min.php';
		
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
new PHP_Class_Files_Minifier( 
	$sTargetDir, 
	$sResultFilePath, 
	array(
		'header_class'		=>	'TaskScheduler_MinifiedVersionHeader',
		'output_buffer'		=>	true,
		'header_type'		=>	'CONSTANTS',	
		'exclude_classes'	=>	array(
			// 'TaskScheduler_Bootstrap',
		),
	)
);
echo 'Done!' . $sCarriageReturn;