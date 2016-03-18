<?php
/**
 * Provides methods to clone posts.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 */

 /**
  * Clones a post.
  * 
  * Used by the post listing table action links.
  * 
  * @since      1.2.0
  */
class TaskScheduler_ClonePost extends TaskScheduler_PluginUtility {
    
    /**
     * The source post object.
     */
    protected $_oSourcePost;
    
    /**
     * Sets up properties.
     */
    public function __construct( $iPostID ) {
        
        $this->_oSourcePost  = get_post( $iPostID );
        
    }
    
    /**
     * Performs clone.
     * 
     * @return      integer|WP_Error         The newly created post ID or WP_Error on failuer
     */
    public function perform() {
        
        $_oiNewPost   = $this->insertPost( 
            $this->_getPostColumns( $this->_oSourcePost )    // columns
            + array(
                'tax_input' => $this->_getTaxInputArgument( $this->_oSourcePost )
            )
            + $this->getPostMetas( $this->_oSourcePost->ID ),
            $this->_oSourcePost->post_type // post type slug
        );
        
        // Give a unique post slug.
        if ( ! is_wp_error( $_oiNewPost ) ) {
            $_oiNewPost = $this->_updatePostSlug( get_post( $_oiNewPost ) );
        }
        
        return $_oiNewPost;        
        
    }
        
        /**
         * Give a unique post slug.
         * @return      integer|WP_Error     The ID of the post if the post is successfully updated in the database. Otherwise returns 0.
         */
        private function _updatePostSlug( $_oNewPost ) {
                        
            $_sNewPostName = wp_unique_post_slug(
                $_oNewPost->post_name, 
                $_oNewPost->ID, 
                $_oNewPost->post_status,
                $_oNewPost->post_type, 
                $_oNewPost->post_parent
            );

            $_aNewPost = array();
            $_aNewPost[ 'ID' ]        = $_oNewPost->ID;
            $_aNewPost[ 'post_name' ] = $_sNewPostName;

            // Update the post into the database
            return wp_update_post( $_aNewPost );
            
        }    

        /**
         * Retrieves post column values.
         * @return     array
         */
        private function _getPostColumns( $_oPost ) {

            $_oCurrentUser = wp_get_current_user();
            return array(
                'comment_status' => $_oPost->comment_status,
                'ping_status'    => $_oPost->ping_status,
                'post_author'    => $_oCurrentUser->ID,
                'post_content'   => $_oPost->post_content,
                'post_excerpt'   => $_oPost->post_excerpt,
                'post_name'      => $_oPost->post_name,
                'post_parent'    => $_oPost->post_parent,
                'post_password'  => $_oPost->post_password,
                'post_status'    => $_oPost->post_status,
                'post_title'     => sprintf(
                    __( 'Copy of %1$s', 'amazon-auto-links' ),
                    $_oPost->post_title
                ),
                'post_type'      => $_oPost->post_type,
                'to_ping'        => $_oPost->to_ping,
                'menu_order'     => $_oPost->menu_order
            );                
        }
    
        /**
         * Retrieves associated taxonomies.
         * 
         * @return      array
         */
        private function _getTaxInputArgument( $oSourcePost ) {
            
            $_aTaxonomy = array();
            foreach( get_object_taxonomies( $oSourcePost->post_type ) as $_isIndex => $_sTaxonomySlug ) {
                $_aTaxonomy[ $_sTaxonomySlug ] = wp_get_object_terms(
                    $oSourcePost->ID, 
                    $_sTaxonomySlug, 
                    array( 'fields' => 'slugs' )
                );
            }
            return $_aTaxonomy;
            
        }    

}