<?php 
/**
	Admin Page Framework v3.9.0b10 by Michael Uno 
	Generated by PHP Class Files Script Generator <https://github.com/michaeluno/PHP-Class-Files-Script-Generator>
	<http://en.michaeluno.jp/task-scheduler>
	Copyright (c) 2013-2021, Michael Uno; Licensed under MIT <http://opensource.org/licenses/MIT> */
class TaskScheduler_AdminPageFramework_Form_Model___SectionConditioner extends TaskScheduler_AdminPageFramework_FrameworkUtility {
    public $aSectionsets = array();
    public function __construct() {
        $_aParameters = func_get_args() + array($this->aSectionsets,);
        $this->aSectionsets = $_aParameters[0];
    }
    public function get() {
        return $this->_getSectionsConditioned($this->aSectionsets);
    }
    private function _getSectionsConditioned(array $aSections = array()) {
        $_aNewSections = array();
        foreach ($aSections as $_sSectionID => $_aSection) {
            if (!$this->_isAllowed($_aSection)) {
                continue;
            }
            $_aNewSections[$_sSectionID] = $_aSection;
        }
        return $_aNewSections;
    }
    protected function _isAllowed(array $aDefinition) {
        if (!current_user_can($aDefinition['capability'])) {
            return false;
        }
        return ( boolean )$aDefinition['if'];
    }
    }
    