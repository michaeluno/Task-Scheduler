<?php
/**
 * The class that defines the Debug task for the task scheduler.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * The factory class for plugin extensions.
 * 
 * @filter        add            task_scheduler_filter_label_{module type}_{slug}
 * @filter        add            task_scheduler_filter_description_{module type}_{slug}
 */
abstract class TaskScheduler_Module_Factory {
        
    /**
     * The identifier of the action. 
     * 
     * Used in hook and option names. Simply give the action hook name here.
     * 
     * @remark    Use only the alphanumeric characters.
     */
    protected $sSlug = '';
            
    /**
     * Indicates the type of the action.
     * 
     * Currently only there are 'occurrence' and 'action'.
     * 
     */
    protected $_sModuleType = '';
    
    /**
     * Stores the wizard pages used for this module.
     */
    public $aWizardScreens = array();
    
    /**
     * Sets up necessary hooks and properties.
     * 
     * @param    string            $sSlug            The slug of the extension. For action type extensions, pass the action hook name.
     * @param    array|string    $asWizardClasses    An array of wizard screen classes. The first item will be the main class.
     * @param    string            $sModuleType    The option type, which can be either 'action' or 'occurrence' at the moment.
     */
    public function __construct( $sSlug='', $asWizardClasses=array(), $sModuleType='' ) {
        
        // The action hook name is used for the class slug. The slug is used for identifying the form section name, page tab, and hook names etc.
        $this->sSlug        = $sSlug ? sanitize_key( $sSlug ) : sanitize_key( $this->sSlug );
        $this->_sModuleType    = $sModuleType ? $sModuleType : $this->_sModuleType;
        $this->_sClassName    = get_class( $this );
        
        add_filter( "task_scheduler_filter_label_" . $this->_sModuleType . "_" . $this->sSlug, array( $this, 'getLabel' ) );
        add_filter( "task_scheduler_filter_description_" . $this->_sModuleType . "_" . $this->sSlug, array( $this, 'getDescription' ) );
        
        if ( 
            is_admin() 
            && (
                isset( $_GET['page'] ) && in_array( $_GET['page'], array( TaskScheduler_Registry::AdminPage_AddNew, TaskScheduler_Registry::AdminPage_EditModule ) )
                || isset( $GLOBALS['pagenow'] ) && in_array( $GLOBALS['pagenow'], array( 'post.php' ) )
            )
        ) {
        
            add_filter( 'task_scheduler_admin_filter_wizard_options', array( $this, '_replyToModifyDefaultOptions' ), 20, 1 );
            add_filter( "task_scheduler_admin_filter_wizard_options_{$this->sSlug}", array( $this, '_replyToGetWizardOptions' ) );
            add_filter( "task_scheduler_admin_filter_wizard_slugs_{$this->sSlug}", array( $this, '_replyToGetWizardSlugs' ) );
        
            // Instantiate the wizard class if exists.
            $_aWizardClasses = is_array( $asWizardClasses ) ? $asWizardClasses : array( $asWizardClasses );
            foreach( $_aWizardClasses as $_sWizardClass ) {
                $this->addWizardScreen( $_sWizardClass, $this->sSlug );
            }
            
        }
        
        // Call the user constructor
        $this->construct();        
    
    }
        
        /**
         * Returns the slugs used for the wizard group.
         */
        public function _replyToGetWizardSlugs( $aSlugs ) {
            return $this->getWizardScreenSlugs( $aSlugs );
        }
    
        /**
         * Modifies the options array saved to the wizard transient.
         */
        public function _replyToGetWizardOptions( array $aSubmit ) {
            
            $_aWizardOptions    = apply_filters( 'task_scheduler_admin_filter_get_wizard_options', array() );
            $_aWizardSlugs        = array_reverse( $this->getWizardScreenSlugs() );
            foreach( $_aWizardSlugs as $_sSlug )  {
                if ( isset( $_aWizardOptions[ $_sSlug ] ) && is_array( $_aWizardOptions[ $_sSlug ] ) ) {
                    $aSubmit = $aSubmit + $_aWizardOptions[ $_sSlug ];
                }                
            }
            return $aSubmit;
            
        }
        
        /**
         * Modifies the options array set to the wizard form options.
         * 
         * This is for multiple wizard screens.
         */
        public function _replyToModifyDefaultOptions( $aOptions ) {
                
            // Retrieve the slugs used for the wizard. The result array is reversed to make the later options take precedence.
            $_aWizardSlugs = array_reverse( $this->getWizardScreenSlugs() );
                            
            // Merge the section options into one.
            $_aModuleWizardOptions = array();
            foreach( $_aWizardSlugs as $_sSlug )  {
                if ( isset( $aOptions[ $_sSlug ] ) ) {
                    $_aModuleWizardOptions = $aOptions[ $_sSlug ] + $_aModuleWizardOptions;
                }
            }
            foreach( $_aWizardSlugs as $_sSlug ) {
                if ( isset( $aOptions[ $_sSlug ] ) ) {
                    $aOptions[ $_sSlug ] = $_aModuleWizardOptions;
                }
            }
            return $aOptions;
            
        }        
            private function getWizardScreenSlugs( $aSlugs=array() ) {
                
                foreach( $this->aWizardScreens as $_aWizardScreen ) {
                    $aSlugs[] = $_aWizardScreen['slug'];
                }
                return array_unique( $aSlugs );
                
            }
        
    /**
     * Add wizard pages.
     * 
     * In the constructor, "{module class name}_Wizard" will be automatically added.
     * 
     * @remark    do not check is_admin() as the wizard screen class uses front end hooks.
     * @param    string    $sWizardClassName    The wizard class name.
     * @param    string    $sSlug                The identifier to pass to the wizard class. This is set when the constructor calls this method.
     */
    public function addWizardScreen( $sWizardClassName, $sSlug='' ) {
        
        if ( ! class_exists( $sWizardClassName ) ) { return; }

        $_bIsFirst            = empty( $this->aWizardScreens );
        $_iNth                = count( $this->aWizardScreens ) + 1;
        $_sMainWizardSlug    = $this->_getMainWizardSlug( $sSlug );
        $sSlug                = $_bIsFirst ? $sSlug : $_sMainWizardSlug . '_' . $_iNth;
        $_oWizardScreen     = new $sWizardClassName( $sSlug, $_sMainWizardSlug );
        $this->aWizardScreens[] =  array(
            'class_name'    =>    $sWizardClassName,
            'slug'            =>    $sSlug,
            'instance'        =>    $_oWizardScreen,
            'is_main'        =>    $_bIsFirst,
        );
        if ( $_bIsFirst ) { return; }
        
        // At this point, the added wizard screen is not the first one. 
        
        /// Disable the label so that the default slug will be only used.
        $_oWizardScreen->bAddToLabelList = false;    
        $_oWizardScreen->sMainWizardSlug = $_sMainWizardSlug;
        
        /// So modify the destination screen of the previous one.
        $_oPreviousWizardScreen = isset( $this->aWizardScreens[ $_iNth - 2 ] ) ? $this->aWizardScreens[ $_iNth - 2 ][ 'instance' ] : null;
        if ( is_object( $_oPreviousWizardScreen ) ) {
            $_oPreviousWizardScreen->sNextTabSlug = $sSlug;
        }    
        
    }
        /**
         * Returns the slug used by the default wizard.
         */
        private function _getMainWizardSlug( $sDefault ) {
            
            foreach( $this->aWizardScreens as $_aWizard ) {
                if ( $_aWizard['is_main'] ) {
                    return $_aWizard['slug'];
                }
            }
            return $sDefault;
            
        }
        
    /**
     * Returns the label for the slug.
     * 
     * @remark    This method should be overridden in the extended class.
     */
    public function getLabel( $sSlug ) {
        return $this->sSlug;
    }    
    
    /**
     * Returns the module description.
     */
    public function getDescription( $sDescription )  {
        return $sDescription;
    }
    
    /*
     * Extensible methods.
     */
    /**
     * The user constructor.
     */
    public function construct() {}    

}