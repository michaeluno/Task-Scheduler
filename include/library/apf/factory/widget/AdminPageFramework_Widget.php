<?php 
/**
	Admin Page Framework v3.8.21b01 by Michael Uno 
	Generated by PHP Class Files Script Generator <https://github.com/michaeluno/PHP-Class-Files-Script-Generator>
	<http://en.michaeluno.jp/task-scheduler>
	Copyright (c) 2013-2019, Michael Uno; Licensed under MIT <http://opensource.org/licenses/MIT> */
abstract class TaskScheduler_AdminPageFramework_Widget_Router extends TaskScheduler_AdminPageFramework_Factory {
    public function __construct($oProp) {
        parent::__construct($oProp);
        $this->oUtil->registerAction('widgets_init', array($this, '_replyToDetermineToLoad'));
    }
    public function _replyToLoadComponents() {
        return;
    }
    public function __call($sMethodName, $aArguments = null) {
        if ($this->oProp->bAssumedAsWPWidget) {
            if (in_array($sMethodName, $this->oProp->aWPWidgetMethods)) {
                return call_user_func_array(array($this->oProp->oWidget, $sMethodName), $aArguments);
            }
        }
        return parent::__call($sMethodName, $aArguments);
    }
    public function __get($sPropertyName) {
        if ($this->oProp->bAssumedAsWPWidget) {
            if (isset($this->oProp->aWPWidgetProperties[$sPropertyName])) {
                return $this->oProp->oWidget->$sPropertyName;
            }
        }
        return parent::__get($sPropertyName);
    }
    }
    abstract class TaskScheduler_AdminPageFramework_Widget_Model extends TaskScheduler_AdminPageFramework_Widget_Router {
        function __construct($oProp) {
            parent::__construct($oProp);
            $this->oUtil->registerAction("set_up_{$this->oProp->sClassName}", array($this, '_replyToRegisterWidget'));
            if ($this->oProp->bIsAdmin) {
                add_filter('validation_' . $this->oProp->sClassName, array($this, '_replyToSortInputs'), 1, 3);
            }
        }
        public function _replyToSortInputs($aSubmittedFormData, $aStoredFormData, $oFactory) {
            return $this->oForm->getSortedInputs($aSubmittedFormData);
        }
        public function _replyToHandleSubmittedFormData($aSavedData, $aArguments, $aSectionsets, $aFieldsets) {
            if (empty($aSectionsets) || empty($aFieldsets)) {
                return;
            }
            $this->oResource;
        }
        public function _replyToRegisterWidget() {
            if (!is_object($GLOBALS['wp_widget_factory'])) {
                return;
            }
            $GLOBALS['wp_widget_factory']->widgets[$this->oProp->sClassName] = new TaskScheduler_AdminPageFramework_Widget_Factory($this, $this->oProp->sWidgetTitle, $this->oUtil->getAsArray($this->oProp->aWidgetArguments));
            $this->oProp->oWidget = $GLOBALS['wp_widget_factory']->widgets[$this->oProp->sClassName];
        }
    }
    abstract class TaskScheduler_AdminPageFramework_Widget_View extends TaskScheduler_AdminPageFramework_Widget_Model {
        public function content($sContent, $aArguments, $aFormData) {
            return $sContent;
        }
        public function _printWidgetForm() {
            echo $this->oForm->get();
        }
    }
    abstract class TaskScheduler_AdminPageFramework_Widget_Controller extends TaskScheduler_AdminPageFramework_Widget_View {
        public function setUp() {
        }
        public function load() {
        }
        public function enqueueStyles($aSRCs, $aCustomArgs = array()) {
            if (method_exists($this->oResource, '_enqueueStyles')) {
                return $this->oResource->_enqueueStyles($aSRCs, array($this->oProp->sPostType), $aCustomArgs);
            }
        }
        public function enqueueStyle($sSRC, $aCustomArgs = array()) {
            if (method_exists($this->oResource, '_enqueueStyle')) {
                return $this->oResource->_enqueueStyle($sSRC, array($this->oProp->sPostType), $aCustomArgs);
            }
        }
        public function enqueueScripts($aSRCs, $aCustomArgs = array()) {
            if (method_exists($this->oResource, '_enqueueScripts')) {
                return $this->oResource->_enqueueScripts($aSRCs, array($this->oProp->sPostType), $aCustomArgs);
            }
        }
        public function enqueueScript($sSRC, $aCustomArgs = array()) {
            if (method_exists($this->oResource, '_enqueueScript')) {
                return $this->oResource->_enqueueScript($sSRC, array($this->oProp->sPostType), $aCustomArgs);
            }
        }
        protected function setArguments(array $aArguments = array()) {
            $this->oProp->aWidgetArguments = $aArguments;
        }
    }
    abstract class TaskScheduler_AdminPageFramework_Widget extends TaskScheduler_AdminPageFramework_Widget_Controller {
        protected $_sStructureType = 'widget';
        public function __construct() {
            $_sThisClassName = get_class($this);
            $_bAssumedAsWPWidget = 0 === func_num_args();
            $_aDefaults = array('', array(), 'edit_theme_options', 'task-scheduler');
            $_aParameters = $_bAssumedAsWPWidget ? $this->___getConstructorParametersUsedForThisClassName($_sThisClassName) + $_aDefaults : func_get_args() + $_aDefaults;
            $this->___setProperties($_aParameters, $_sThisClassName, $_bAssumedAsWPWidget);
            parent::__construct($this->oProp);
        }
        private function ___setProperties($aParameters, $sThisClassName, $_bAssumedAsWPWidget) {
            $sWidgetTitle = $aParameters[0];
            $aWidgetArguments = $aParameters[1];
            $sCapability = $aParameters[2];
            $sTextDomain = $aParameters[3];
            $_sPropertyClassName = isset($this->aSubClassNames['oProp']) ? $this->aSubClassNames['oProp'] : 'TaskScheduler_AdminPageFramework_Property_' . $this->_sStructureType;
            $this->oProp = new $_sPropertyClassName($this, null, $sThisClassName, $sCapability, $sTextDomain, $this->_sStructureType);
            $this->oProp->sWidgetTitle = $sWidgetTitle;
            $this->oProp->aWidgetArguments = $aWidgetArguments;
            $this->oProp->bAssumedAsWPWidget = $_bAssumedAsWPWidget;
            if ($_bAssumedAsWPWidget) {
                $this->oProp->aWPWidgetMethods = get_class_methods('WP_Widget');
                $this->oProp->aWPWidgetProperties = get_class_vars('WP_Widget');
            }
        }
        private function ___getConstructorParametersUsedForThisClassName($sClassName) {
            if (!isset($GLOBALS['wp_widget_factory'])) {
                return array();
            }
            if (!is_object($GLOBALS['wp_widget_factory'])) {
                return array();
            }
            if (!isset($GLOBALS['wp_widget_factory']->widgets[$sClassName])) {
                return array();
            }
            $_oWPWidget = $GLOBALS['wp_widget_factory']->widgets[$sClassName];
            return array($_oWPWidget->oCaller->oProp->sWidgetTitle, $_oWPWidget->oCaller->oProp->aWidgetArguments, $_oWPWidget->oCaller->oProp->sCapability, $_oWPWidget->oCaller->oProp->sTextDomain,);
        }
    }
    