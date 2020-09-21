<?php
class TaskScheduler_AdminPage_Tab_Unit extends TaskScheduler_AdminPage_Tab_Base {

    protected function _getArguments() {
        return array(
            'tab_slug'    => 'tests',
            'title'       => 'Unit Tests',
        );
    }

    protected function _construct( $oAdminPage ) {

        // Form sections
//        new TaskScheduler_AdminPage_Section_Unit( $oAdminPage, $this->sPageSlug );

    }

    protected function _loadTab( $oAdminPage ) {
        $_aTagLabels = $this->_getTagLabelsForCheckBox();
        $oAdminPage->addSettingFields(
            '_default',
            array(
                'title'     => 'Categories',
                'field_id'  => '_categories',
                'save'      => 'false',
                'type'      => 'checkbox',
                'label'     => $_aTagLabels,
                'select_all_button'  => true,
                'select_none_button' => true,
                'attributes'        => array(
                    'class' => 'test-categories',
                ),
                'if'        => ! empty( $_aTagLabels ),
            ),
            array(
                'title'     => 'Tags',
                'field_id'  => '_tags',
                'save'      => 'false',
                'type'      => 'text',
                'class'     => array(
                    'input' => 'width-full test-tags',
                ),
                'description'   => 'Type tags separated with commas. Tags refer to terms set to the <code>@tags</code> annotation in test method doc-blocks.'
            ),
            array(
                'title'     => 'Error',
                'field_id'  => '_error',
                'save'      => 'false',
                'type'      => '_custom',
                'content'   => array( "<p class='error'>No files found.</p>" ),
                'label'     => $_aTagLabels,
                'if'        => empty( $_aTagLabels ),
            ),
            array(
                'title'             => '',
                'field_id'          => '_buttons',
                'show_title_column' => false,
                'type'              => 'inline_mixed',
                'save'              => false,
                'content'           => array(
                    array(
                        'title'             => '',
                        'show_title_column' => false,
                        'field_id'          => '_copy',
                        'save'              => 'false',
                        'type'              => 'submit',
                        'href'              => '#',
                        'value'             => 'Copy Errors to Clipboard',
                        'attributes'        => array(
                            'class' => 'button button-secondary copy-to-clipboard',
                        ),
                    ),
                    array(
                        'title'             => '',
                        'show_title_column' => false,
                        'field_id'          => '_clear',
                        'save'              => 'false',
                        'type'              => 'submit',
                        'href'              => '#',
                        'value'             => 'Clear',
                        'attributes'        => array(
                            'class' => 'button button-secondary clear-log',
                        ),
                    ),
                    array(
                        'title'             => '',
                        'show_title_column' => false,
                        'field_id'          => '_test',
                        'save'              => 'false',
                        'type'              => 'submit',
                        'value'             => 'Start',
                        'attributes'        => array(
                            'class' => 'button button-primary ts-tests',
                            'disabled' => empty( $_aTagLabels )
                                ? 'disabled'
                                : null,
                        ),
                    ),
                ),
            ),
            array(
                'show_title_column' => false,
                'field_id'          => '_results',
                'save'              => false,
                'content'           => "<div class='results-container'>"
                        . "<h4 class='results-title'>Results</h4>"
                        . "<div class='results'></div>"
                    . "</div>",
            )
        );

    }
        /**
         * @return array
         */
        protected function _getTagLabelsForCheckBox() {
            return $this->_getTagLabels( TaskScheduler_Registry::$sDirPath . '/test/run/unit', array( 'TaskScheduler_UnitTest_Base' ) );
        }
            protected function _getTagLabels( $sScanDirPath, array $aBaseClassNames ) {
                $_oFinder = new TaskScheduler_ClassFinder( $sScanDirPath, $aBaseClassNames );
                $_aFiles  = $_oFinder->getFiles();
                $_aKeys   = array_keys( $_aFiles );
                return ( array ) array_combine( $_aKeys, $_aKeys );
            }

    /**
     * Write scratches here to test something.
     * @param TaskScheduler_AdminPageFramework $oAdminPage
     * @callback        action      do_{page slug}_{tab slug}
     */
    protected function _doTab( $oAdminPage ) {
        $this->_printFiles();
    }
        protected function _printFiles() {
            echo "<div class='files-container'>";
            echo "<h4>Test Files</h4>";
            $_oFinder = new TaskScheduler_ClassFinder( TaskScheduler_Registry::$sDirPath . '/test/run/unit', array( 'TaskScheduler_UnitTest_Base' ) );
            TaskScheduler_Debug::dump( $_oFinder->getFiles() );
            echo "</div>";
        }    

}