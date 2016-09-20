<?php
/**
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * One of the base classes of the plugin admin page class for the wizard pages.
 * @extends        TaskScheduler_AdminPage_Wizard_Validation
 */
abstract class TaskScheduler_AdminPage_Wizard_Setup extends TaskScheduler_AdminPage_Wizard_Validation {
    
    /**
     * Sets the key of the transient that stores the wizard options.
     * 
     * @access      public          Accessed from a delegation class.
     */
    public $_sTransientKey = '';
    public $sTransientKey  = '';
    
    public function start() {                
        $this->_disableAddNewButton();
    }
        
        /**
         * Disables the Add New link of the task post type and redirect to the wizard start page.
         */
        private function _disableAddNewButton() {
        
            if ( ! $this->oProp->bIsAdmin ) { 
                return; 
            }
            
            if ( ! in_array( $this->oUtil->getPageNow(), array( 'post-new.php' ) ) ) { 
                return; 
            }
                
            if ( $this->oUtil->getCurrentPostType() != TaskScheduler_Registry::$aPostTypes[ 'task' ] ) { 
                return; 
            }
            
            TaskScheduler_PluginUtility::goToAddNewPage();        
            exit();
            
        }    
    
    /**
     * Defines the admin pages of the plugin.
     * 
     * @since    1.0.0
     */         
    public function setUp() {
            
        $this->setRootMenuPageBySlug( TaskScheduler_Registry::$aAdminPages[ 'root' ] );
        
        new TaskScheduler_AdminPage_Wizard__Page__AddNewTask(
            $this, 
            array(
                'title'     => __( 'Add New Task', 'task-scheduler' ),
                'page_slug' => TaskScheduler_Registry::$aAdminPages[ 'add_new' ],
            )
        );
        
        $this->setPluginSettingsLinkLabel( '' );
        
    }
    
    /**
     * Called when one of the registered pages by the class gets loaded.
     * 
     * @remark      The `load_{...}` action hooks and this method are loaded in this order `load_{class name}` -> `load_{page slug}` -> `load_{page slug}_{tab slug}`.
     * @return      void
     */
    public function load() {
        
        $this->_sTransientKey = isset( $_GET[ 'transient_key' ] ) && $_GET[ 'transient_key' ]
            ? $_GET[ 'transient_key' ] 
            : TaskScheduler_Registry::TRANSIENT_PREFIX . uniqid();        
        $this->sTransientKey = $this->_sTransientKey;
                                
        $this->_setPageSettings();
        $this->_registerCustomFieldTypes();
        
    }
        /**
         * @since       1.4.0
         * @return      void
         */
        private function _setPageSettings() {
        
            $this->setPageHeadingTabsVisibility( false );   
            $this->setInPageTabsVisibility( false );       
            // $this->setPageTitleVisibility( false );
            $this->setInPageTabTag( 'h2' );                
            $this->enqueueStyle( TaskScheduler_Registry::getPluginURL( '/asset/css/admin_wizard.css' ) );
            $this->setDisallowedQueryKeys( 'settings-notice' );
            $this->setDisallowedQueryKeys( 'transient_key' );
        
        }
    
        /**
         * Registers custom field types.
         * 
         * @remark    The scope is 'protected' because the extending Edit Module class will use this method.
         */
        protected function _registerCustomFieldTypes() {
            
            new TaskScheduler_DateTimeCustomFieldType( $this->oProp->sClassName );
            new TaskScheduler_TimeCustomFieldType( $this->oProp->sClassName );
            new TaskScheduler_DateCustomFieldType( $this->oProp->sClassName );
            new TaskScheduler_AutoCompleteCustomFieldType( $this->oProp->sClassName );
            new TaskScheduler_RevealerCustomFieldType( $this->oProp->sClassName );
            new TaskScheduler_PathCustomFieldType( $this->oProp->sClassName );

        }        
            
}
