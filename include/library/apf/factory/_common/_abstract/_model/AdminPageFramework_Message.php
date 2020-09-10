<?php 
/**
	Admin Page Framework v3.8.22b06 by Michael Uno 
	Generated by PHP Class Files Script Generator <https://github.com/michaeluno/PHP-Class-Files-Script-Generator>
	<http://en.michaeluno.jp/task-scheduler>
	Copyright (c) 2013-2020, Michael Uno; Licensed under MIT <http://opensource.org/licenses/MIT> */
class TaskScheduler_AdminPageFramework_Message {
    public $aMessages = array();
    public $aDefaults = array('option_updated' => 'The options have been updated.', 'option_cleared' => 'The options have been cleared.', 'export' => 'Export', 'export_options' => 'Export Options', 'import' => 'Import', 'import_options' => 'Import Options', 'submit' => 'Submit', 'import_error' => 'An error occurred while uploading the import file.', 'uploaded_file_type_not_supported' => 'The uploaded file type is not supported: %1$s', 'could_not_load_importing_data' => 'Could not load the importing data.', 'imported_data' => 'The uploaded file has been imported.', 'not_imported_data' => 'No data could be imported.', 'upload_image' => 'Upload Image', 'use_this_image' => 'Use This Image', 'insert_from_url' => 'Insert from URL', 'reset_options' => 'Are you sure you want to reset the options?', 'confirm_perform_task' => 'Please confirm your action.', 'specified_option_been_deleted' => 'The specified options have been deleted.', 'nonce_verification_failed' => 'A problem occurred while processing the form data. Please try again.', 'check_max_input_vars' => 'Not all form fields could not be sent. Please check your server settings of PHP <code>max_input_vars</code> and consult the server administrator to increase the value. <code>max input vars</code>: %1$s. <code>$_POST</code> count: %2$s', 'send_email' => 'Is it okay to send the email?', 'email_sent' => 'The email has been sent.', 'email_scheduled' => 'The email has been scheduled.', 'email_could_not_send' => 'There was a problem sending the email', 'title' => 'Title', 'author' => 'Author', 'categories' => 'Categories', 'tags' => 'Tags', 'comments' => 'Comments', 'date' => 'Date', 'show_all' => 'Show All', 'show_all_authors' => 'Show all Authors', 'powered_by' => 'Thank you for creating with', 'and' => 'and', 'settings' => 'Settings', 'manage' => 'Manage', 'select_image' => 'Select Image', 'upload_file' => 'Upload File', 'use_this_file' => 'Use This File', 'select_file' => 'Select File', 'remove_value' => 'Remove Value', 'select_all' => 'Select All', 'select_none' => 'Select None', 'no_term_found' => 'No term found.', 'select' => 'Select', 'insert' => 'Insert', 'use_this' => 'Use This', 'return_to_library' => 'Return to Library', 'queries_in_seconds' => '%1$s queries in %2$s seconds.', 'out_of_x_memory_used' => '%1$s out of %2$s MB (%3$s) memory used.', 'peak_memory_usage' => 'Peak memory usage %1$s MB.', 'initial_memory_usage' => 'Initial memory usage  %1$s MB.', 'repeatable_section_is_disabled' => 'The ability to repeat sections is disabled.', 'repeatable_field_is_disabled' => 'The ability to repeat fields is disabled.', 'warning_caption' => 'Warning', 'allowed_maximum_number_of_fields' => 'The allowed maximum number of fields is {0}.', 'allowed_minimum_number_of_fields' => 'The allowed minimum number of fields is {0}.', 'add' => 'Add', 'remove' => 'Remove', 'allowed_maximum_number_of_sections' => 'The allowed maximum number of sections is {0}', 'allowed_minimum_number_of_sections' => 'The allowed minimum number of sections is {0}', 'add_section' => 'Add Section', 'remove_section' => 'Remove Section', 'toggle_all' => 'Toggle All', 'toggle_all_collapsible_sections' => 'Toggle all collapsible sections', 'reset' => 'Reset', 'yes' => 'Yes', 'no' => 'No', 'on' => 'On', 'off' => 'Off', 'enabled' => 'Enabled', 'disabled' => 'Disabled', 'supported' => 'Supported', 'not_supported' => 'Not Supported', 'functional' => 'Functional', 'not_functional' => 'Not Functional', 'too_long' => 'Too Long', 'acceptable' => 'Acceptable', 'no_log_found' => 'No log found.', 'method_called_too_early' => 'The method is called too early.', 'debug_info' => 'Debug Info', 'debug' => 'Debug', 'debug_info_will_be_disabled' => 'This information will be disabled when <code>WP_DEBUG</code> is set to <code>false</code> in <code>wp-config.php</code>.', 'click_to_expand' => 'Click here to expand to view the contents.', 'click_to_collapse' => 'Click here to collapse the contents.', 'loading' => 'Loading...', 'please_enable_javascript' => 'Please enable JavaScript for better user experience.',);
    protected $_sTextDomain = 'task-scheduler';
    static private $_aInstancesByTextDomain = array();
    public static function getInstance($sTextDomain = 'task-scheduler') {
        $_oInstance = isset(self::$_aInstancesByTextDomain[$sTextDomain]) && (self::$_aInstancesByTextDomain[$sTextDomain] instanceof TaskScheduler_AdminPageFramework_Message) ? self::$_aInstancesByTextDomain[$sTextDomain] : new TaskScheduler_AdminPageFramework_Message($sTextDomain);
        self::$_aInstancesByTextDomain[$sTextDomain] = $_oInstance;
        return self::$_aInstancesByTextDomain[$sTextDomain];
    }
    public static function instantiate($sTextDomain = 'task-scheduler') {
        return self::getInstance($sTextDomain);
    }
    public function __construct($sTextDomain = 'task-scheduler') {
        $this->_sTextDomain = $sTextDomain;
        $this->aMessages = array_fill_keys(array_keys($this->aDefaults), null);
    }
    public function getTextDomain() {
        return $this->_sTextDomain;
    }
    public function set($sKey, $sValue) {
        $this->aMessages[$sKey] = $sValue;
    }
    public function get($sKey = '') {
        if (!$sKey) {
            return $this->_getAllMessages();
        }
        return isset($this->aMessages[$sKey]) ? __($this->aMessages[$sKey], $this->_sTextDomain) : __($this->{$sKey}, $this->_sTextDomain);
    }
    private function _getAllMessages() {
        $_aMessages = array();
        foreach ($this->aMessages as $_sLabel => $_sTranslation) {
            $_aMessages[$_sLabel] = $this->get($_sLabel);
        }
        return $_aMessages;
    }
    public function output($sKey) {
        echo $this->get($sKey);
    }
    public function __($sKey) {
        return $this->get($sKey);
    }
    public function _e($sKey) {
        $this->output($sKey);
    }
    public function __get($sPropertyName) {
        return isset($this->aDefaults[$sPropertyName]) ? $this->aDefaults[$sPropertyName] : $sPropertyName;
    }
    private function ___doDummy() {
        __('The options have been updated.', 'task-scheduler');
        __('The options have been cleared.', 'task-scheduler');
        __('Export', 'task-scheduler');
        __('Export Options', 'task-scheduler');
        __('Import', 'task-scheduler');
        __('Import Options', 'task-scheduler');
        __('Submit', 'task-scheduler');
        __('An error occurred while uploading the import file.', 'task-scheduler');
        __('The uploaded file type is not supported: %1$s', 'task-scheduler');
        __('Could not load the importing data.', 'task-scheduler');
        __('The uploaded file has been imported.', 'task-scheduler');
        __('No data could be imported.', 'task-scheduler');
        __('Upload Image', 'task-scheduler');
        __('Use This Image', 'task-scheduler');
        __('Insert from URL', 'task-scheduler');
        __('Are you sure you want to reset the options?', 'task-scheduler');
        __('Please confirm your action.', 'task-scheduler');
        __('The specified options have been deleted.', 'task-scheduler');
        __('A problem occurred while processing the form data. Please try again.', 'task-scheduler');
        __('Not all form fields could not be sent. Please check your server settings of PHP <code>max_input_vars</code> and consult the server administrator to increase the value. <code>max input vars</code>: %1$s. <code>$_POST</code> count: %2$s', 'task-scheduler');
        __('Is it okay to send the email?', 'task-scheduler');
        __('The email has been sent.', 'task-scheduler');
        __('The email has been scheduled.', 'task-scheduler');
        __('There was a problem sending the email', 'task-scheduler');
        __('Title', 'task-scheduler');
        __('Author', 'task-scheduler');
        __('Categories', 'task-scheduler');
        __('Tags', 'task-scheduler');
        __('Comments', 'task-scheduler');
        __('Date', 'task-scheduler');
        __('Show All', 'task-scheduler');
        __('Show All Authors', 'task-scheduler');
        __('Thank you for creating with', 'task-scheduler');
        __('and', 'task-scheduler');
        __('Settings', 'task-scheduler');
        __('Manage', 'task-scheduler');
        __('Select Image', 'task-scheduler');
        __('Upload File', 'task-scheduler');
        __('Use This File', 'task-scheduler');
        __('Select File', 'task-scheduler');
        __('Remove Value', 'task-scheduler');
        __('Select All', 'task-scheduler');
        __('Select None', 'task-scheduler');
        __('No term found.', 'task-scheduler');
        __('Select', 'task-scheduler');
        __('Insert', 'task-scheduler');
        __('Use This', 'task-scheduler');
        __('Return to Library', 'task-scheduler');
        __('%1$s queries in %2$s seconds.', 'task-scheduler');
        __('%1$s out of %2$s MB (%3$s) memory used.', 'task-scheduler');
        __('Peak memory usage %1$s MB.', 'task-scheduler');
        __('Initial memory usage  %1$s MB.', 'task-scheduler');
        __('The allowed maximum number of fields is {0}.', 'task-scheduler');
        __('The allowed minimum number of fields is {0}.', 'task-scheduler');
        __('Add', 'task-scheduler');
        __('Remove', 'task-scheduler');
        __('The allowed maximum number of sections is {0}', 'task-scheduler');
        __('The allowed minimum number of sections is {0}', 'task-scheduler');
        __('Add Section', 'task-scheduler');
        __('Remove Section', 'task-scheduler');
        __('Toggle All', 'task-scheduler');
        __('Toggle all collapsible sections', 'task-scheduler');
        __('Reset', 'task-scheduler');
        __('Yes', 'task-scheduler');
        __('No', 'task-scheduler');
        __('On', 'task-scheduler');
        __('Off', 'task-scheduler');
        __('Enabled', 'task-scheduler');
        __('Disabled', 'task-scheduler');
        __('Supported', 'task-scheduler');
        __('Not Supported', 'task-scheduler');
        __('Functional', 'task-scheduler');
        __('Not Functional', 'task-scheduler');
        __('Too Long', 'task-scheduler');
        __('Acceptable', 'task-scheduler');
        __('No log found.', 'task-scheduler');
        __('The method is called too early: %1$s', 'task-scheduler');
        __('Debug Info', 'task-scheduler');
        __('Click here to expand to view the contents.', 'task-scheduler');
        __('Click here to collapse the contents.', 'task-scheduler');
        __('Loading...', 'task-scheduler');
        __('Please enable JavaScript for better user experience.', 'task-scheduler');
        __('Debug', 'task-scheduler');
        __('This information will be disabled when <code>WP_DEBUG</code> is set to <code>false</code> in <code>wp-config.php</code>.', 'task-scheduler');
        __('The ability to repeat sections is disabled.', 'task-scheduler');
        __('The ability to repeat fields is disabled.', 'task-scheduler');
        __('Warning.', 'task-scheduler');
    }
    }
    