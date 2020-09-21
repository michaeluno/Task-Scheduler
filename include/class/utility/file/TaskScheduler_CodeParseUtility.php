<?php
/**
 * Task Scheduler
 *
 * Provides an enhanced task management system for WordPress.
 *
 * http://en.michaeluno.jp/task-scheduler/
 * Copyright (c) 2013-2020 Michael Uno
 */

/**
 * Provides utility methods for the Test component.
 *  
 * @package     Task Scheduler
 * @since       1.5.2
*/
class TaskScheduler_CodeParseUtility extends TaskScheduler_PluginUtility {

    /**
     * Returns an array holding class names defined in the given PHP code.
     *
     * @param string $sPHPCode
     * @remark the first found class name will be returns so a file having multiple class declaration is not supported.
     * @return string
     */
    static public function getDefinedClass( $sPHPCode ) {
        preg_match_all( '/(^|\s)class\s+(.+?)\s+/i', $sPHPCode, $aMatch );
        return reset( $aMatch[ 2 ] ); // extract the fist item
    }
    /**
     * Retrieves PHP code from the given path.
     *
     * @param   string $sFilePath
     * @remark      Enclosing `<?php ?>` tags will be removed.
     * @return  string Found PHP code
     */
    static public function getPHPCode( $sFilePath ) {
        $_sCode = php_strip_whitespace( $sFilePath );
        $_sCode = preg_replace( '/^<\?php/', '', $_sCode );
        $_sCode = preg_replace( '/\?>\s+?$/', '', $_sCode );
        return $_sCode;
    }

}