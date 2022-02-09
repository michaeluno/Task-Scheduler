<?php
/**
 * A base class for meta box classes.
 * 
 * @since            1.0.0
 */
abstract class TaskScheduler_MetaBox_Base extends TaskScheduler_AdminPageFramework_MetaBox {
// abstract class TaskScheduler_MetaBox_Base extends AdminPageFramework_MetaBox {
    
    /**
     * Stores the task object.
     * @since       1.0.0
     * @since       1.0.1       Changed the name from `$_oTask` and made it public to be accessed from outside.
     */
    public $oTask;
        
    public function start() {
        
        if ( isset( $GLOBALS['pagenow'] ) && 'post.php' === $GLOBALS['pagenow'] ) {
            add_action( 'current_screen', array( $this, '_replyToRegisterFieldTypes' ) );
            add_action( 'admin_enqueue_scripts', array( $this, '_replyToAddCSS' ), 10, 1 );
        }
        
    }
            
    public function _replyToRegisterFieldTypes( $oScreen ) {
        
        if ( 
            ! in_array( 
                $oScreen->post_type, 
                array( 
                    TaskScheduler_Registry::$aPostTypes[ 'task' ], 
                    TaskScheduler_Registry::$aPostTypes[ 'thread' ] 
                ) 
            ) 
        ) { 
            return; 
        }    
        
        // Register custom field types
        $_sClassName = get_class( $this );

        new TaskScheduler_DateTimeCustomFieldType( $_sClassName );
        new TaskScheduler_TimeCustomFieldType( $_sClassName );     
        new TaskScheduler_DateCustomFieldType( $_sClassName );        
        
    }
            
    public function _replyToAddCSS( $sHook ) {
        
        global $post;

        if ( ! in_array( $sHook, array( 'post.php', 'post-new.php' ) ) ) { 
            return; 
        }        
        if ( ! in_array( $post->post_type, array( TaskScheduler_Registry::$aPostTypes[ 'task' ], TaskScheduler_Registry::$aPostTypes[ 'thread' ] ) ) ) { 
            return; 
        }
        
        wp_enqueue_style( 
            'task_scheduler_meta_box_css', 
            TaskScheduler_Registry::getPluginURL( 'asset/css/meta_box.css' ) 
        );
        
    }
    
    /**
     * Returns field definition arrays of a particular module.
     * 
     * This is used to display the stored values of module options.
     * 
     * @remark   Called in the `field_definition_{class name}` filter hook.
     * @param    string   $sModuleSlug        The key name that holds the module options such as 'fixed_interval', 'send_email' that are used as the module slug.
     * @param    array    $aModularOptions    An array holding the stored modular options.
     * @return   array
     */
    protected function _getModuleFields( $sModuleSlug, array $aModularOptions ) {

        $_aFields = array();
        if ( empty( $aModularOptions ) ) { 
            return $_aFields; 
        }

        $_aModularFields = $this->_getModularFieldsBySlug( $sModuleSlug ); 
        foreach( $aModularOptions as $_sKey => $_aisValue ) {
            
            // Check if the parsing option key exists in the fields array.
            if ( ! isset( $_aModularFields[ $_sKey ] ) ) { 
                continue; 
            }
            $_aModularField = $_aModularFields[ $_sKey ];
            
            $_aisValue = TaskScheduler_Utility::isJSON( $_aisValue ) 
                ? json_decode( $_aisValue, true ) 
                : $_aisValue;
            $_aisValue = maybe_unserialize( $_aisValue );
            
            if ( in_array( $_aModularField['type'], array( 'select', 'radio' ) ) && ! is_array( $_aisValue ) ) {
                $_aisValue = isset( $_aModularField['label'][ $_aisValue ] )
                    ? $_aModularField['label'][ $_aisValue ]
                    : $_aisValue;
            }
            
            $_aModularField = array(
                'attributes'    => array(
                    'readonly'  => 'readonly',
                    'name'      => '',
                    'class'     => 'read-only',
                ),
                'field_id'      => $_aModularField['field_id'],
                'type'          => $this->_getFieldTypeToOnlyDisplay( $_aisValue, $_aModularField['type'] ),
                'value'         => $_aisValue,
                'title'         => $_aModularField['title'],
                // 'show_title_column'    =>  true,
            );            
            $_aModularField['attributes']['cols'] = 'textarea' == $_aModularField['type'] 
                ? 42 
                : null;
            if ( is_array( $_aisValue ) ) {
                $_aModularField['before_fieldset'] = "<div class='task-scheduler-module-options-value-container'>"
                        . TaskScheduler_PluginUtility::getListFromAssociativeArray( array( __( 'Key', 'task-scheduler' ) => __( 'Value', 'task-scheduler' ) ) + $_aisValue )
                    . "</div>";
            }            
            
            $_aFields[ $_aModularField[ 'field_id' ] ] = $_aModularField;            
            
        }
        return $_aFields;
        
    }
        /**
         * @since       1.4.0
         * @return      array
         */
        private function _getModularFieldsBySlug( $sModuleSlug ){

            $_aModularFields = array();
            $_aWizardSlugs   = $this->oUtil->getAsArray( 
                apply_filters( "task_scheduler_admin_filter_wizard_slugs_{$sModuleSlug}", array() )
            );
            foreach( $_aWizardSlugs as $_sSlug ) {
                $_aWizardFieldsWithSection    = $this->oUtil->getAsArray( 
                    apply_filters( "task_scheduler_filter_fields_{$_sSlug}", array() )
                );
                $_aWizardFields               = $this->oUtil->getElementAsArray(
                    $_aWizardFieldsWithSection,
                    array( $_sSlug )
                );
                $_aModularFields              = $_aModularFields + $_aWizardFields;
            }        
            unset( $_aModularFields[ 'prevnext' ] );
            return $_aModularFields;
            
        }    
    
    /**
     * Returns the module edit link.
     * @since       1.0.0
     */
    protected function _getChangeButton( $sTabSlug ) {
        $_sModuleEditPageURL = esc_url(
            TaskScheduler_PluginUtility::getModuleEditPageURL(
                array(
                    'transient_key'  => TaskScheduler_Registry::TRANSIENT_PREFIX . uniqid(),
                    'tab'            => $sTabSlug,
                    'post'           => absint( TaskScheduler_Utility::getHTTPQueryGET( 'post' ) ),
                )
            )
        );            
        return "<div style='text-align: right; padding: 0.5em 0 1em;'>"
                . "<a class='button button-secondary button-large' href='{$_sModuleEditPageURL}'>" 
                    . __( 'Edit', 'task-scheduler' ) 
                . "</a>"
            . "</div>"
        ;
    }
    
    /**
     * Returns the field type for displaying field values.
     */
    protected function _getFieldTypeToOnlyDisplay( $aisValue, $sSetFieldType ) {
        
        if ( is_array( $aisValue ) ) {
            return 'hidden';
        }
        if ( 'textarea' === $sSetFieldType ) {
            return $sSetFieldType;
        }
        return 'text';
        
    }    
    
}
