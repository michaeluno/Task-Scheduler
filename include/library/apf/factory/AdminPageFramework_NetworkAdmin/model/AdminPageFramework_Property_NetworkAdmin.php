<?php
/**
 Admin Page Framework v3.5.11 by Michael Uno
 Generated by PHP Class Files Script Generator <https://github.com/michaeluno/PHP-Class-Files-Script-Generator>
 <http://en.michaeluno.jp/admin-page-framework>
 Copyright (c) 2013-2015, Michael Uno; Licensed under MIT <http://opensource.org/licenses/MIT>
 */
class TaskScheduler_AdminPageFramework_Property_NetworkAdmin extends TaskScheduler_AdminPageFramework_Property_Page {
    public $_sPropertyType = 'network_admin_page';
    public $sFieldsType = 'network_admin_page';
    protected function _getOptions() {
        return TaskScheduler_AdminPageFramework_WPUtility::addAndApplyFilter($GLOBALS['aTaskScheduler_AdminPageFramework']['aPageClasses'][$this->sClassName], 'options_' . $this->sClassName, $this->sOptionKey ? get_site_option($this->sOptionKey, array()) : array());
    }
    public function updateOption($aOptions = null) {
        if ($this->_bDisableSavingOptions) {
            return;
        }
        return update_site_option($this->sOptionKey, $aOptions !== null ? $aOptions : $this->aOptions);
    }
}