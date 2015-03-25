<?php
/**
 * One of the abstract classes of the TaskScheduler_Routine class.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_Routine_Taxonomy extends TaskScheduler_Routine_Thread {
    
    /**
     * Checks if the routine has the speicified taxonomy term(s).
     */
    public function hasTerm( $asTerm, $sTaxonomy='' )  {
        
        $sTaxonomy = $sTaxonomy ? $sTaxonomy : TaskScheduler_Registry::$aTaxonomies[ 'system' ];
        return has_term( $asTerm, $sTaxonomy, $this->ID );
        
    }
 
}