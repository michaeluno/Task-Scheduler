<?php
/**
 * Task Scheduler
 * 
 * Provides an enhanced task management system for WordPress.
 * 
 * http://en.michaeluno.jp/amazon-auto-inks/
 * Copyright (c) 2014-2020 Michael Uno; Licensed GPLv2
 */

/**
 * Provides an abstract base for adding form sections.
 * 
 * @since       1.4.0
 */
abstract class TaskScheduler_AdminPage_Section_Base extends TaskScheduler_AdminPage_RootBase {

    /**
     * Stores the factory object.
     * @var TaskScheduler_AdminPageFramework
     */
    public $oFactory;

    /**
     * Stores the associated page slug with the adding section.
     */
    public $sPageSlug;    

    /**
     * Stores the associated tab slug with the adding section.
     */
    public $sTabSlug;    

    /**
     * Stores the section ID.
     */
    public $sSectionID;

    /**
     * Sets up hooks and properties.
     * @param TaskScheduler_AdminPageFramework $oFactory
     * @param $sPageSlug
     * @param array $aSectionDefinition
     */
    public function __construct( TaskScheduler_AdminPageFramework $oFactory, $sPageSlug, array $aSectionDefinition=array() ) {
        
        $this->oFactory     = $oFactory;
        $this->sPageSlug    = $sPageSlug;
        $aSectionDefinition = $aSectionDefinition + $this->_getArguments() + array(
            'tab_slug'      => '',
            'section_id'    => '',
        );
        $this->sTabSlug     = $aSectionDefinition[ 'tab_slug' ];
        $this->sSectionID   = $aSectionDefinition[ 'section_id' ];

        if ( ! $this->sSectionID ) {
            return;
        }
        $this->_addSection( $oFactory, $sPageSlug, $aSectionDefinition );
        
        $this->_construct( $oFactory );
        
    }

    /**
     * @param TaskScheduler_AdminPageFramework $oFactory
     * @param $sPageSlug
     * @param array $aSectionDefinition
     */
    private function _addSection( $oFactory, $sPageSlug, array $aSectionDefinition ) {
        
        add_action( 
            'validation_' . $oFactory->oProp->sClassName . '_' . $this->sSectionID,
            array( $this, 'validate' ), 
            10, 
            4 
        );
        
        $oFactory->addSettingSections(
            $sPageSlug,    // target page slug
            $aSectionDefinition
        );        
        
        // Set the target section id
        $oFactory->addSettingFields(
            $this->sSectionID
        );
        
        // Call the user method
        $this->addFields( $oFactory, $this->sSectionID );

    }

    /**
     * Called when adding fields.
     * @remark      This method should be overridden in each extended class.
     * @param TaskScheduler_AdminPageFramework $oFactory
     * @param $sSectionID
     */
    public function addFields( $oFactory, $sSectionID ) {}

    /**
     * Called upon form validation.
     *
     * @callback        filter      'validation_{class name}_{section id}'
     * @param array $aInputs
     * @param array $aOldInputs
     * @param TaskScheduler_AdminPageFramework $oAdminPage
     * @param array $aSubmitInfo
     * @return array
     */
    public function validate( $aInputs, $aOldInputs, $oAdminPage, $aSubmitInfo ) {
    
        $_bVerified = true;
        $_aErrors   = array();
                 
        // An invalid value is found. Set a field error array and an admin notice and return the old values.
        if ( ! $_bVerified ) {
            $oAdminPage->setFieldErrors( $_aErrors );     
            $oAdminPage->setSettingNotice( __( 'There was something wrong with your input.', 'task-scheduler' ) );
            return $aOldInputs;
        }
                
        return $aInputs;
        
    }        

}
