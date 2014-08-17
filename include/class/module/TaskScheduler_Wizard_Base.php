<?php
/**
 * An abstract class of wizard hidden tabbed pages.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_Wizard_Base {

    /**
     * Stores the transient key which stores the wizard options.
     */
    protected $_sTransientKey;
    
    /**
     * Stores the main admin page class name.
     */
    protected $_sMainAdminPageClassName = 'TaskScheduler_AdminPage_Wizard';
    
    /**
     * Stores the page slug used for the wizard.
     * 
     * Assigned in the constructor.
     */
    protected $_sMainAdminPageSlug = '';    
    
    /**
     * Stores the main admin page class name.
     */
    protected $_sEditAdminPageClassName = 'TaskScheduler_AdminPage_EditModule';
    
    /**
     * Stores the page slug used for the wizard.
     * 
     * Assigned in the constructor.
     */
    protected $_sEditAdminPageSlug = '';    
    
    
    /**
     * Stores the parent tab slug.
     * 
     */
    protected $_sParentTabSlug = 'wizard';
    
    /**
     * Stores the tab and section slug for this wizard.
     * 
     * @remark    This should be defined in the final extended class.
     */
    public $sSlug;    
    
    /**
     * Stores the main wizard slug for multiple wizard screens.
     */
    public $sMainWizardSlug;
        
    /**
     * Stores the section ID of the wizard.
     * 
     * @remark    This will be automatically assigned in the constructor.
     */
    protected $_sSectionID;
        
    /**
     * Stores the next tab slug.
     * 
     * @remark    Should be defined in the final extended class.
     */ 
    public $sNextTabSlug;
    
    /**
     * Should be redefined in the extended class.
     * 
     * This is used for filter names. Currently 'occurrence' or 'action' can be set.
     */
    protected $_sModuleType = 'base';    
    
    /**
     * Indicates whether to add a label to the list of modules such as in a select field to list available modules.
     * 
     * @remark    The scope is public because the factory class modifies it when multiple wizard screens are going to be set.
     */
    public $bAddToLabelList = true;
    
    /**
     * Indicates whether the current page is the Add New wizard page or not.
     */
    protected $_bIsAddNew;
    
    /**
     * Stets up hooks and properties.
     * 
     * @param    string    $sSlug                The slug of the module.
     * @param    string    $sMainWizardSlug    The main wizard slug. This is necessary when multiple wizard screens are added.
     */
    public function __construct( $sSlug, $sMainWizardSlug='' ) {
                                
        // Properties
        $this->_setProperties( $sSlug, $sMainWizardSlug );    
        
        // Hooks
        $this->_setCallbacks();
        
        // User constructor.
        $this->construct();
        
    }
        
        /**
         * Sets up properties.
         */
        protected function _setProperties( $sSlug, $sMainWizardSlug ) {
            
            $this->sSlug                = $sSlug ? $sSlug : $this->sSlug;                    
            $this->sMainWizardSlug        = $sMainWizardSlug ? $sMainWizardSlug : $sSlug;            
            $this->_sTransientKey        = isset( $_GET['transient_key'] ) ? $_GET['transient_key'] : '';
            $this->_sSectionID            = $this->sSlug;
            $this->_sMainAdminPageSlug    = TaskScheduler_Registry::AdminPage_AddNew;
            $this->_sEditAdminPageSlug    = TaskScheduler_Registry::AdminPage_EditModule;
            $this->_bIsAddNew            = isset( $_GET['page'] ) && $this->_sMainAdminPageSlug === $_GET['page'];
            $this->sNextTabSlug            = $this->_bIsAddNew
                ? $this->sNextTabSlug
                : 'update_module';        // for the edit wizard
            
                
        }
        
    /**
     * Sets up callback functions.
     * 
     * @remark    Should be re-defined in the extended class.
     */
    protected function _setCallbacks() {
        
        // For the framework hooks 
        /// The Add New wizard
        add_filter( "tabs_{$this->_sMainAdminPageClassName}_{$this->_sMainAdminPageSlug}", array( $this, '_replyToAddInPageTab' ) );
        add_filter( "sections_{$this->_sMainAdminPageClassName}", array( $this, '_replyToAddFormSection' ) );
        add_filter( "fields_{$this->_sMainAdminPageClassName}", array( $this, '_replyToAddFormFields' ), 1, 1 );
        add_filter( "field_definition_{$this->_sMainAdminPageClassName}", array( $this, '_replyToRedefineFields' ), 10, 1 );
        add_filter( "validation_{$this->_sMainAdminPageClassName}_{$this->_sSectionID}", array( $this, 'validateSettings' ), 10, 3 );
        add_filter( "validation_{$this->_sMainAdminPageSlug}_{$this->sSlug}", array( $this, '_replytToValidateTabSettings' ), 10, 3 );    // sSlug is used as the tab slug also.
        add_filter( "validation_saved_options_{$this->_sMainAdminPageSlug}_{$this->sSlug}", array( $this, '_replyToModifySavedTabOptions' ), 10, 2 );

        /// The Edit Module wizard
        add_filter( "tabs_{$this->_sEditAdminPageClassName}_{$this->_sEditAdminPageSlug}", array( $this, '_replyToAddInPageTab' ) );
        add_filter( "sections_{$this->_sEditAdminPageClassName}", array( $this, '_replyToAddFormSection' ) );
        add_filter( "fields_{$this->_sEditAdminPageClassName}", array( $this, '_replyToAddFormFields' ), 1, 1 );
        add_filter( "field_definition_{$this->_sEditAdminPageClassName}", array( $this, '_replyToRedefineFields' ), 10, 1 );
        add_filter( "validation_{$this->_sEditAdminPageClassName}_{$this->_sSectionID}", array( $this, 'validateSettings' ), 10, 3 );
        add_filter( "validation_{$this->_sEditAdminPageSlug}_{$this->sSlug}", array( $this, '_replytToValidateTabSettings' ), 10, 3 );    // sSlug is used as the tab slug also.
        add_filter( "validation_saved_options_{$this->_sEditAdminPageSlug}_{$this->sSlug}", array( $this, '_replyToModifySavedTabOptions' ), 10, 2 );
        
        // Plugin specific hooks
        add_filter( "task_scheduler_admin_filter_field_labels_wizard_" . $this->_sModuleType, array( $this, '_replyToAddActionLabel' ) );
        add_filter( "task_scheduler_admin_filter_wizard_" . $this->_sModuleType . "_redirect_url_" . $this->sSlug, array( $this, "_replyToSetRedirectURL" ), 10, 2 );

        // Meta boxes in wp_admin/post.php need to display field values.
        add_filter( "task_scheduler_filter_fields_{$this->sSlug}", array( $this, '_replyToAddFormFields' ) );
        
    }

    /**
     * Receives the default redirecting url from the first page of the task creating wizard.
     */
    public function _replyToSetRedirectURL( $sRedirectURL, $aWizardOptions ) {

        $_sReturn = add_query_arg(
            array(    
                'tab'    =>    $this->sSlug,
            ),
            $sRedirectURL
        );        
        return $_sReturn;
        
    }    
    
    /**
     * Adds the wizard form section.
     * 
     * The structure has to follows the specification of Admin Page Framework v3.
     * It will look like the following:
     * 
       [wizard] => Array (
            [tab_slug] => wizard
            [section_id] => wizard
            [title] => Task Creation Wizard
            [page_slug] => ts_add_new
            [section_tab_slug] => 
            [description] => 
            [capability] => 
            [if] => 1
            [order] => 
            [help] => 
            [help_aside] => 
            [repeatable] => 
        )
     */
    public function _replyToAddFormSection( $aSections ) {

        $aSections[ $this->_sSectionID ] = array(
            'page_slug'            =>    "sections_{$this->_sMainAdminPageClassName}" === current_filter() 
                ? $this->_sMainAdminPageSlug
                : $this->_sEditAdminPageSlug,            
            'tab_slug'        =>    $this->sSlug,
            'section_id'    =>    $this->_sSectionID,
            'title'            =>    $this->getLabel(),
            // 'description'    =>    
        );
        return $aSections;
        
    }
    
    /**
     * Adds the wizard form fields.
     * 
     * The fields definition array must follow the specification of Admin Page Framework v3.
     * 
     * It will look like the following.
        [wizard] => Array(        // <-- section id
            [transient_key] => Array(    // <-- field id
                    [_fields_type] => page
                    [field_id] => transient_key
                    [type] => hidden
                    [hidden] => 1
                    [value] => TS_53a95af51f551
                    [section_id] => wizard
                    [section_title] => 
                    [page_slug] => 
                    [tab_slug] => 
                    [option_key] => 
                    [class_name] => 
                    [capability] => 
                    [title] => 
                    [tip] => 
                    [description] => 
                    [error_message] => 
                    [before_label] => 
                    [after_label] => 
                    [if] => 1
                    [order] => 
                    [default] => 
                    [help] => 
                    [help_aside] => 
                    [repeatable] => 
                    [sortable] => 
                    [attributes] => 
                    [show_title_column] => 1
                    [_section_index] => 
                )
            [post_title] => Array (
                    [_fields_type] => page
                    [field_id] => post_title
                    [title] => Task Name
                    [type] => text
                    [section_id] => wizard
                    [section_title] => 
                    [page_slug] => 
                    [tab_slug] => 
                    [option_key] => 
                    [class_name] => 
                    [capability] => 
                    [tip] => 
                    [description] => 
                    [error_message] => 
                    [before_label] => 
                    [after_label] => 
                    [if] => 1
                    [order] => 
                    [default] => 
                    [value] => 
                    [help] => 
                    [help_aside] => 
                    [repeatable] => 
                    [sortable] => 
                    [attributes] => 
                    [show_title_column] => 1
                    [hidden] => 
                    [_section_index] => 
                )
            
     */
    public function _replyToAddFormFields( $aAllFields ) {

        // Format the array - must give the field id as the key; otherwise (if it's numerically indexed), the framework thinks it is a repeatable section.
         $_aFields = array();
        foreach( ( array ) $this->getFields() as $_aField ) {
            if ( ! isset( $_aField['field_id'] ) ) {
                continue;
            }
            $_aField['section_id'] = $this->_sSectionID;
            $_aFields[ $_aField['field_id'] ] = $_aField;
        }
        
        // If the user does not want to add the default submit buttons, extend the method and make it return an empty value.
        $_aSubmitButtons = $this->_getSubmitButtonsField();
        if ( ! empty( $_aSubmitButtons ) ) {
            $_aFields[ $_aSubmitButtons['field_id'] ]    =    $_aSubmitButtons;
        }
        
        $aAllFields[ $this->_sSectionID ] = $_aFields; 
        return $aAllFields;
    }
        
        /**
         * Returns the submit field array which has the Back and Next buttons.
         */
        protected function _getSubmitButtonsField() {
            
            $_sButtonLabel = $this->_bIsAddNew
                ? ( 
                    'wizard_create_task' === $this->sNextTabSlug
                        ? __( 'Create', 'task-scheduler' ) 
                        : __( 'Next', 'task-scheduler' ) 
                )
                : (
                    'update_module'    === $this->sNextTabSlug
                        ? __( 'Update', 'task-scheduler' ) 
                        : __( 'Next', 'task-scheduler' ) 
                );
            $_aSubmitField = array(    
                'section_id'        =>    $this->_sSectionID,
                'field_id'            =>    'submit',
                'type'                =>    'submit',
                'label'                =>    $_sButtonLabel,
                'label_min_width'    =>    0,
                'attributes'        =>    array(
                    'field'    =>    array(
                        'style'    =>    'float:right; clear:none; display: inline;',
                    ),
                ),        
                'redirect_url'        => add_query_arg( 
                    array( 
                        'tab'                => $this->sNextTabSlug, 
                        'settings-notice'    => 0, // disables the settings notice
                        'transient_key'        => $this->_sTransientKey,
                    )
                ),
                 array(
                    'value'            =>    __( 'Back', 'task-scheduler' ),
                    // the previous url will be automatically set                     
                    'attributes'    =>    array(
                        'class'    =>    'button secondary ',
                    ),                        
                ), 
            );        
            return $_aSubmitField;
            
        }
    
    /**
     * The callback function for field definition arrays.
     */
    public function _replyToRedefineFields( $aAllFields ) {

        if ( ! isset( $aAllFields[ $this->_sSectionID ] ) || ! $this->_sTransientKey ) {
            return $aAllFields;
        }

        // Check if the modifying field(s) exists.
        if ( ! isset( $aAllFields[ $this->_sSectionID ][ 'submit' ] ) ) {
            return $aAllFields;
        }
        
        // Retrieve the wizard options.
        $_aWizardOptions = apply_filters( 'task_scheduler_admin_filter_get_wizard_options', array() );
                
        // Set the values of the wizard options to the fields
        foreach( $aAllFields[ $this->_sSectionID ] as $_sFieldID => &$_aField ) {
            
            if ( ! isset( $_aWizardOptions[ $this->_sSectionID ][ $_sFieldID ] ) ) { continue; }
            
            // If repeatable or having sub-fields.
            if ( 
                ( $_aField['repeatable'] || isset( $_aField[ 0 ] ) )
                && is_array( $_aWizardOptions[ $this->_sSectionID ][ $_sFieldID ] ) 
            ) {
                        
                // Set the values to the sub-fields.
                $_aThisFieldValues = array_values( $_aWizardOptions[ $this->_sSectionID ][ $_sFieldID ] );
                $_aField['value'] = array_shift( $_aThisFieldValues );    // extract and remove the first item.
                $_iIndex = 0;
                foreach( $_aThisFieldValues as $_vValue ) {
                    $_aField[ $_iIndex ]['value'] = $_vValue;                    
                    $_iIndex++;
                }
                continue;
            } 
            
            // Otherwise,
            $_aField['value'] = $_aWizardOptions[ $this->_sSectionID ][ $_sFieldID ];
            

        }

        // Set the Back button's url.
        $_sCurrentURLKey = remove_query_arg( array( 'transient_key', 'settings-notice', 'settings-updated' ) );
        if ( isset( $_aWizardOptions['previous_urls'][ $_sCurrentURLKey ] ) ) {
            $aAllFields[ $this->_sSectionID ][ 'submit' ][ 0 ]['href'] = $_aWizardOptions['previous_urls'][ $_sCurrentURLKey ];
        }
                
        return $aAllFields;
        
    }
        
    /**
     * The callback function for adding in-page tabs.
     */
    public function _replyToAddInPageTab( $aTabs ) {

        $aTabs[ $this->sSlug ] = array(
            'page_slug'            =>    "tabs_{$this->_sMainAdminPageClassName}_{$this->_sMainAdminPageSlug}" === current_filter() 
                ? $this->_sMainAdminPageSlug
                : $this->_sEditAdminPageSlug,
            'tab_slug'            =>    $this->sSlug,
            'title'                =>    $this->getLabel(),    // this is a hidden tab so not title is necessary.
            'parent_tab_slug'    =>    $this->_sParentTabSlug,
            'show_in_page_tab'    =>    true,            
        );
        return $aTabs;

    }
    
    /**
     * The callback function for tab settings validation.
     */
    public function _replytToValidateTabSettings( $aInput, $aOldInput, $oAdminPage ) {    

        $_aWizardOptions = array( 
            'previous_urls' => apply_filters( 'task_scheduler_admin_filter_get_wizard_options', array(), 'previous_urls' ),
        );                

        // If the user wants an error to be displayed without saving the options, an empty array will be returned.
        if ( ! $oAdminPage->hasSettingNotice( 'error' ) ) {    
            $_sNextURLKey    = remove_query_arg( array( 'transient_key', 'settings-notice', 'settings-updated' ), add_query_arg( array( 'tab' => $this->sNextTabSlug ) ) );
            $_aWizardOptions[ 'previous_urls' ][ $_sNextURLKey ] = add_query_arg( array() );    // store the current url.            
        }
        
        // Insert the wizard section. If multiple wizard screens are registered to this module, merge their options.
        $_aWizardOptions[ $this->_sSectionID ] = apply_filters( "task_scheduler_admin_filter_wizard_options_{$this->sMainWizardSlug}", $aInput[ $this->_sSectionID ] ) 
            + ( isset( $aOldInput['_wizard_options'][ $this->sMainWizardSlug ] ) ? $aOldInput['_wizard_options'][ $this->sMainWizardSlug ] : array() );
        unset( $_aWizardOptions[ $this->_sSectionID ]['submit'] );
    
        /// The other grouped sections should be updated to the merged input array.
        $_aSlugs = apply_filters( "task_scheduler_admin_filter_wizard_slugs_{$this->sMainWizardSlug}", array() );
        foreach( $_aSlugs as $_sSlug ) {
            $_aWizardOptions[ $_sSlug ] = $_aWizardOptions[ $this->_sSectionID ];
        }

        // The '_wizard_options' element will be extracted and saved as the wizard options in the wizard admin page class.
        $aInput['_wizard_options'] = $_aWizardOptions;        

        // Return the wizard options. The wizard admin page class will take care of the rest.
        return $aInput;
        
    }
    
    /**
     * Drops the module element from the saved options that merges with the user form input array.
     * 
     * This is needed to preserve newly updated repeatable field values.
     */
    public function _replyToModifySavedTabOptions( $aSavedOptions, $oAdminPage ) {
        
        unset( $aSavedOptions[ '_wizard_options' ][ $this->sSlug ] );
        return $aSavedOptions;
        
    }
        
    /**
     * Inserts a label item for this option.
     */
    public function _replyToAddActionLabel( $aLabels ) {
        
        if ( ! $this->bAddToLabelList ) {
            return $aLabels;
        }
        $aLabels[ $this->sSlug ] = $this->getLabel();
        return $aLabels;
        
    }    
    
    /**
     * Returns the label of the given slug.
     * 
     * The filter, 'task_scheduler_filter_label_{option type}_{slug}' should be defined separately outside of this class 
     * since it needs to be accessed in the front-end as well.
     */
    public function getLabel() { 
        return apply_filters( "task_scheduler_filter_label_" . $this->_sModuleType . "_" . $this->sMainWizardSlug, $this->sMainWizardSlug ); 
    }
    
    /*
     * Extensible methods.
     */
    
    public function getFields() { return array(); }
        
    public function validateSettings( $aInput, $aOldInput, $oAdminPage ) { return $aInput; }

    public function construct() {}
    
}