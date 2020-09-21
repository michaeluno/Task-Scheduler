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
 * Provides an abstract base for adding pages.
 * 
 * @since       1.4.0
 */
abstract class TaskScheduler_AdminPage_Page_Base extends TaskScheduler_AdminPage_RootBase {

    /**
     * Stores the factory object.
     */
    public $oFactory;

    /**
     * Stores the associated page slug with the adding section.
     */
    public $sPageSlug;

    /**
     * Sets up hooks and properties.
     * @param TaskScheduler_AdminPageFramework $oFactory
     * @param array $aPageArguments
     */
    public function __construct( TaskScheduler_AdminPageFramework $oFactory, array $aPageArguments=array() ) {
        
        $this->oFactory     = $oFactory;
        $aPageArguments     = $aPageArguments + $this->_getArguments();
        $this->sPageSlug    = $aPageArguments[ 'page_slug' ];
        $this->_addPage( $aPageArguments );
        $this->_construct( $oFactory );
                
    }
    
    private function _addPage( array $aPageArguments ) {
        
        $this->oFactory->addSubMenuItems(
            $aPageArguments
            + array(
                'page_slug'     => null,
                'title'         => null,
                'screen_icon'   => null,
            )                
        );
        // add_action( "load_{$this->sPageSlug}", array( $this, 'replyToSetResources' ) );
        add_action( "load_{$this->sPageSlug}", array( $this, 'replyToLoadPage' ) );
        add_action( "do_{$this->sPageSlug}", array( $this, 'replyToDoPage' ) );
        add_action( "do_after_{$this->sPageSlug}", array( $this, 'replyToDoAfterPage' ) );
        add_filter( "validation_{$this->sPageSlug}", array( $this, 'validate' ), 10, 4 );
        
    }

    /**
     * @callback    action      load_{page slug}
     * @param TaskScheduler_AdminPageFramework $oFactory
     */
    public function replyToSetResources( TaskScheduler_AdminPageFramework $oFactory ) {
        
        $this->oFactory->enqueueStyle( 
            TaskScheduler_Registry::getPluginURL( 'asset/css/' . $this->sPageSlug . '.css' ),
            $this->sPageSlug
        );            
        
    }
    
    /**
     * Called when the page loads.
     */
     public function replyToLoadPage( $oFactory ) {
         $this->_loadPage( $oFactory );
     }
     public function replyToDoPage( $oFactory ) {
         $this->_doPage( $oFactory );
     }
     public function replyToDoAfterPage( $oFactory ) {
         $this->_doAfterPage( $oFactory );
     }
     public function validate( $aInputs, $aOldInputs, $oFactory, $aSubmitInfo ){}

     protected function _loadPage( $oFactory ) {}
     protected function _doPage( $oFactory ) {}
     protected function _doAfterPage( $oFactory ) {}

}