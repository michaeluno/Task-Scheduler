<?php
/* 
	Plugin Name: Task Scheduler
	Plugin URI: http://en.michaeluno.jp/
	Description: Creates unattended periodic access to the site.
	Author: miunosoft (Michael Uno)
	Author URI: http://michaeluno.jp
	Version: 1.0.0b05
	Requirements: PHP 5.2.4 or above, WordPress 3.3 or above. MySQL above 5.5.24  
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

include_once( dirname( __FILE__ ). '/include/class/boot/TaskScheduler_Bootstrap.php' );
new TaskScheduler_Bootstrap( __FILE__ );