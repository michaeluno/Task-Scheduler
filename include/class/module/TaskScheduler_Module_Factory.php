<?php
/**
 * The class that defines the Debug task for the task scheduler.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * The factory class for plugin extensions.
 * 
 * @filter        add            task_scheduler_filter_label_{module type}_{slug}
 * @filter        add            task_scheduler_filter_description_{module type}_{slug}
 */
abstract class TaskScheduler_Module_Factory extends TaskScheduler_PluginUtility {
        
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

    protected $_sClassName = '';
    
    /**
     * Sets up necessary hooks and properties.
     * 
     * @param    string          $sSlug             The slug of the extension. For action type extensions, pass the action hook name.
     * @param    array|string    $asWizardClasses   An array of wizard screen classes. The first item will be the main class.
     * @param    string          $sModuleType       The option type, which can be either 'action' or 'occurrence' at the moment.
     */
    public function __construct( $sSlug='', $asWizardClasses=array(), $sModuleType='' ) {
        
        $this->_setProperties( $sSlug, $sModuleType );
        $this->_setHooks( $asWizardClasses );
        $this->construct();        
    
    }
        /**
         * Sets up class properties.
         * @since       1.0.1
         */
        private function _setProperties( $sSlug, $sModuleType ) {
         
            // The action hook name is used for the class slug. The slug is used for identifying the form section name, page tab, and hook names etc.
            $this->sSlug          = $sSlug 
                ? sanitize_key( $sSlug ) 
                : sanitize_key( $this->sSlug );
            $this->_sModuleType   = $sModuleType 
                ? $sModuleType 
                : $this->_sModuleType;
            $this->_sClassName    = get_class( $this );
         
        }
        /**
         * Sets up hooks.
         * @since       1.0.1
         */
        private function _setHooks( $asWizardClasses ) {
            
            add_filter( 
                "task_scheduler_filter_label_" . $this->_sModuleType . "_" . $this->sSlug, 
                array( $this, 'getLabel' ) 
            );
            add_filter( 
                "task_scheduler_filter_description_" . $this->_sModuleType . "_" . $this->sSlug, 
                array( $this, 'getDescription' ) 
            );
            
            if ( ! $this->_isInAdminWizard() ) {
                return;
            }
                        
            add_filter( 'task_scheduler_admin_filter_wizard_options', array( $this, '_replyToModifyDefaultOptions' ), 20, 1 );
            add_filter( "task_scheduler_admin_filter_wizard_options_{$this->sSlug}", array( $this, '_replyToGetWizardOptions' ) );
            add_filter( "task_scheduler_admin_filter_wizard_slugs_{$this->sSlug}", array( $this, '_replyToGetWizardSlugs' ) );
        
            // Instantiate the wizard class if exists.
            $_aWizardClasses = is_array( $asWizardClasses ) 
                ? $asWizardClasses 
                : array( $asWizardClasses );
            foreach( $_aWizardClasses as $_sWizardClass ) {
                $this->addWizardScreen( $_sWizardClass, $this->sSlug );
            }
            
        }
            /**
             * Checks whether the currently loading page is in the task scheduler wizard page or not.
             * @since       1.0.1
             * @return      boolean
             */
            private function _isInAdminWizard() {
                
                if ( ! is_admin() ) {
                    return false;
                }
                $_aQuery = $this->___getURLQuery(); // this also allows ajax requests referred from those pages. This is needed to allow Ajax requests for some custom field types, such as `path`.
                if ( 
                    isset( $_aQuery[ 'page' ] )
                    && in_array( 
                        $_aQuery[ 'page' ],
                        array( 
                            TaskScheduler_Registry::$aAdminPages[ 'add_new' ],
                            TaskScheduler_Registry::$aAdminPages[ 'edit_module' ]
                        ),
                        true
                    )
                ) {
                    return true;
                }
                if ( isset( $GLOBALS[ 'pagenow' ] ) && 'post.php' === $GLOBALS[ 'pagenow' ] ) {
                    return true;
                }
                return false;
             
            }

                /**
                 * @return false|string
                 * @since  1.6.1
                 */
                private function ___getReferrer() {
                    static $_sCachedReferrer;
                    if ( isset( $_sCachedReferrer ) ) {
                        return $_sCachedReferrer;
                    }
                    $_sCachedReferrer = wp_get_referer();;
                    return $_sCachedReferrer;
                }
                /**
                 * @since  1.6.1
                 * @return array
                 */
                private function ___getURLQuery() {
                    if ( ! $this->isDoingAjax() ) {
                        return $this->getHTTPQueryGET( array(), array() );
                    }
                    parse_str(
                        parse_url( $this->___getReferrer(), PHP_URL_QUERY ), // query string such as `foo=bar&abc=xyz`
                        $_aQuery
                    );
                    return $this->getHTTPQueryGET( array(), array() ) + $_aQuery;
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
            $_aWizardSlugs      = array_reverse( $this->getWizardScreenSlugs() );
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
     * @remark   do not check is_admin() as the wizard screen class uses front end hooks.
     * @param    string    $sWizardClassName    The wizard class name.
     * @param    string    $sSlug               The identifier to pass to the wizard class. This is set when the constructor calls this method.
     */
    public function addWizardScreen( $sWizardClassName, $sSlug='' ) {
        
        if ( ! class_exists( $sWizardClassName ) ) { 
            return; 
        }

        $_bIsFirst              = empty( $this->aWizardScreens );
        $_iNth                  = count( $this->aWizardScreens ) + 1;
        $_sMainWizardSlug       = $this->_getMainWizardSlug( $sSlug );
        $sSlug                  = $_bIsFirst 
            ? $sSlug 
            : $_sMainWizardSlug . '_' . $_iNth;
        $_oWizardScreen         = new $sWizardClassName( $sSlug, $_sMainWizardSlug );
        $this->aWizardScreens[] = array(
            'class_name'    => $sWizardClassName,
            'slug'          => $sSlug,
            'instance'      => $_oWizardScreen,
            'is_main'       => $_bIsFirst,
        );
        if ( $_bIsFirst ) { 
            return; 
        }
        
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