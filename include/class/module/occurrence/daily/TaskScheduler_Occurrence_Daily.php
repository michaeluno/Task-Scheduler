<?php
/**
 * Handles hooks for the 'daily' occurrence option.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2015, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * Defines the 'daily' occurrence type.
 */
class TaskScheduler_Occurrence_Daily extends TaskScheduler_Occurrence_Base {
        
    /**
     * The user constructor.
     */
    public function construct() {}
        
    /**
     * Returns the label for the slug.
     */
    public function getLabel( $sSlug ) {
        return __( 'Daily', 'task-scheduler' );
    }        
    
    /**
     * Returns the description of the module.
     */
    public function getDescription( $sDescription ) {
        return __( 'Triggers actions daily at the specified time.', 'task-scheduler' );
    }
        
    /**
     * Do something when the task finishes.
     */
    public function doAfterAction( $oTask, $sExitCode ) {}        
        
    /**
     * Returns the next run time time-stamp.
     * 
     * Right before the routine is spawned, this gets called.
     * 
     * @return      integer|float     timestamp without GMT offset.
     */ 
    public function getNextRunTime( $iTimestamp, $oTask ) {

        // Extract the options for this module
        $_aOptions = $oTask->getMeta( $this->sSlug );
        if ( ! isset( $_aOptions[ 'times' ] ) || ! is_array( $_aOptions[ 'times' ] ) ) {
            return $iTimestamp;
        }

        return $this->_getClosestSetTimestamp(
            $this->_formatLastRunTime( $oTask->_last_run_time ),
            $this->_formatTimesArray( $_aOptions[ 'times' ] ),
            $this->_formatDaysArray( $_aOptions[ 'days' ] )
        );
        
    }
        /**
         * Formats the last run time.
         * @return      integer     unix timestamp without GMT offset.
         */
        private function _formatLastRunTime( $nLastRunTime ) {
            $_iLastRunTime = round( $nLastRunTime );
            return $_iLastRunTime 
                ? $_iLastRunTime
                : time();
        }
        /**
         * Sort the time array and re-index the elements.
         * 
         * @return      array
         */
        private function _formatTimesArray( array $aTimes ) {
            natsort( $aTimes );
            return array_values( $aTimes );
        }
        /**
         * Drops non-true items and convert keys into values.
         * 
         * @return      array
         */
        private function _formatDaysArray( array $aDays ) {
            $aDays = array_filter( $aDays );   
            $aDays = array_keys( $aDays );            
            sort( $aDays );
            return $aDays;
        }
        
        /**
         * Calculates the closest time from the given configurations.
         * 
         * @param       integer|float   The timestamp of the last run time of the task. Not GMT calculated.
         * @return      integer         The closest set timestamp without GMT offset.
         */
        private function _getClosestSetTimestamp( $nLastRunTimestamp, array $aTimes, array $aDays ) {

            // If today's day is checked,
            if ( $this->_isTodayChecked( $aDays ) ) {
                $_iTodaysClosestTime = $this->_getTodaysItem( $nLastRunTimestamp, $aTimes );
                if ( $_iTodaysClosestTime ) {
                    return $_iTodaysClosestTime;
                }
            }

            // This value is not GMT-calculated.
            $_iNextRunTime = $this->_getNextClosestTime( $nLastRunTimestamp, $aDays, $aTimes );
            return $_iNextRunTime;
            
        }
        /**
         * Checks whether today's weekday is in the given days array.
         * @return      boolean
         */
        private function _isTodayChecked( array $aSelectedDays ) {
                
            $_iToday       = ( int ) date( 
                'N',    // 1 to 7, Mon to Sun
                $this->_getGMTOffsetTimestamp( time() ) // current time stamp
            ); 
            return in_array( 
                $_iToday,  // GMT not calculated
                $aSelectedDays
            );
            
        }
        /**
         * Checks if a time item larger than the current time on today.
         * 
         * @return      integer     The today's closest timestamp without GMT offset.
         */
        private function _getTodaysItem( $nLastRuntime, array $aTimes ) {

            $_iGMTLastRunTime       = $this->_getGMTOffsetTimestamp( $nLastRuntime );
            $_sGMTLastRunHourMinute = date( 
                'G:i', // e.g. 3:42, 14:34 etc.
                $_iGMTLastRunTime
            );
            $_iGMTLastRunHourMinute = $this->_getTimeInSeconds( $_sGMTLastRunHourMinute );        
        
            $_iGMTCurrentTime       = $this->_getGMTOffsetTimestamp( time() );            
            $_sGMTCurrentHourMinute = date( 
                'G:i', // e.g. 3:42, 14:34 etc.
                $_iGMTCurrentTime
            );
            $_iGMTCurrentHourMinute = $this->_getTimeInSeconds( $_sGMTCurrentHourMinute );
                        
            // $aTimes is sorted as the smallest to the largest.
            foreach( $aTimes as $_sHourMinute ) {
                
                // If the set time is already passed, skip.
                $_iSetHourMinuteInSeconds = $this->_getTimeInSeconds( $_sHourMinute );      
                if ( $_iSetHourMinuteInSeconds <= $_iGMTLastRunHourMinute ) {
                    continue;
                }
                if ( $_iSetHourMinuteInSeconds <= $_iGMTCurrentHourMinute ) {                                                          
                    continue;
                }                
                
                // Return the set time as timestamp.
                $_nNextRunTime = $this->_getTodaysZeroOclockTimestamp()
                    + $_iSetHourMinuteInSeconds;

                return $_nNextRunTime;
                
            }

            // not found
            return 0;
            
        }
            
        /**
         * Gets the timestamp of the next closest 'date' from the checked week days.
         * 
         * @return      integer     The timestamp of the closest date from the current time.
         */
        private function _getNextClosestTime( $nTimestamp, array $aDays, array $aTimes ) {
            
            $_iGMTLastRunTIme       = $this->_getGMTOffsetTimestamp( $nTimestamp );            
            $_iTheDay               = ( int ) date( 'N', $_iGMTLastRunTIme ); 
            
            // Remove today's day from the array.
            $aDays                  = $this->_unsetArrayElementsByValue( $aDays, $_iTheDay );
            sort( $aDays );   

            $_iDaysToClosestDay     = $this->_getNumberOfDaysToClosestDay( $_iTheDay, $aDays );
                   
            // $this->_getTodaysZeroOclockTimestamp() // 
            return // strtotime( '0:00:00' )   // today's 0 o'clock timestamp without GMT
                $this->_getTodaysZeroOclockTimestamp()
                + ( $_iDaysToClosestDay * 3600 * 24 )   // seconds to the closest day
                + $this->_getSmallestTimeInSeconds( $aTimes ) // this value is presumed to be calculated with GMT so the GMT offset must be removed
                // + $this->_getGMTRemovedTimestamp(
                    // $this->_getSmallestTimeInSeconds( $aTimes ) // this value is presumed to be calculated with GMT so the GMT offset must be removed
                // )
                ;
            
        }
            private function _unsetArrayElementsByValue( $aArray, $mValue ) {
                return array_diff( $aArray, array( $mValue ) );
            }
            /**
             * 
             * @return      integer     the 'Zero'-based number of days to the found closest day.
             */
            private function _getNumberOfDaysToClosestDay( $iSubjectDay, array $aDays ) {
                
                // First loop 
                // $_iDay is 1 to 7, Mon to Sun
                $_iDistanceDays = -1;
                for ( $_iDay = $iSubjectDay; $_iDay <= 7; $_iDay++ ) {
                    $_iDistanceDays++;
                    if ( in_array( $_iDay, $aDays ) ) {
                        return $_iDistanceDays;
                    }
                }
                // Second loop
                for ( $_iDay = 1; $_iDay <= $iSubjectDay; $_iDay++ ) {
                    $_iDistanceDays++;
                    if ( in_array( $_iDay, $aDays ) ) {
                        return $_iDistanceDays;
                    }
                }
                
                // Not found. Returns the number of days of one week (zero-based).
                return 6;
                
            }
       
            /**
             * Returns today's 0 o'clock timestamp. 
             * 
             * This is tricky as "strtotime( '0:00:00' ) + GMT offset" will not return the correct time stamp in a time zone.
             * 
             * @return      integer     The unix timestamp of today's 0'oclock. Today is calculated without GMT.
             */
            private function _getTodaysZeroOclockTimestamp() {
                
                $_iGMTCurrentTimestamp  = $this->_getGMTOffsetTimestamp( time() );
                $_iGMTCurrentHourMinute = $this->_getTimeInSeconds( date( 'G:i:s', $_iGMTCurrentTimestamp ) );
                return $this->_getGMTRemovedTimestamp( $_iGMTCurrentTimestamp - $_iGMTCurrentHourMinute );
                
            }              
        
        /**
         * @remark      $aTimes should look like this.
         * `
         * array(
         *      // hour:minute:seconds
         *      '6:31'
         *      '23:42',
         *      '12:53',
         *      '4:45:93',
         * )
         * `
         * The elements will be sorted.
         * 
         * @return      integer     seconds
         */    
        private function _getSmallestTimeInSeconds( array $aTimes ) {
                     
            $_sTime = array_shift( $aTimes );
            return $this->_getTimeInSeconds( $_sTime );
                 
        }
            
            /**
             * Returns seconds from the given time
             * 
             * @param       string      $sTime      Time with the format like hour:minute:second. The second part can be omitted.
             * @return      integer     the converted seconds
             */
            private function _getTimeInSeconds( $sTime ) {

                $_aParts    = explode( ':', $sTime );
                $_iHour     = isset( $_aParts[ 0 ] )
                    ? ( int ) $_aParts[ 0 ] 
                    : 0;
                $_iMinute   = isset( $_aParts[ 1 ] )
                    ? ( int ) $_aParts[ 1 ] 
                    : 0;
                $_iSecond   = isset( $_aParts[ 2 ] )
                    ? ( int ) $_aParts[ 2 ] 
                    : 0;
                return ( int ) ( $_iHour * 3600 ) + ( $_iMinute * 60 ) + ( $_iSecond );
                
            }
        /**
         * Returns GMT calculated timestamp from the given timestamp.
         * 
         * @return      integer     timestamp (seconds)
         */
        private function _getGMTOffsetTimestamp( $iUnixTimestamp ) {
            
            self::$_iGMTOffset = isset( self::$_iGMTOffset ) 
                ? self::$_iGMTOffset
                : get_option( 'gmt_offset' );
                
            return round( $iUnixTimestamp ) + ( self::$_iGMTOffset * 60 * 60 );
            
        }
                
        /**
         * Returns a time stamp without GMT with the given GMT timestamp.
         * @since       1.1.1
         * @return      integer     timestamp in seconds
         */
        private function _getGMTRemovedTimestamp( $iGMTTimestamp ) {

            self::$_iGMTOffset = isset( self::$_iGMTOffset ) 
                ? self::$_iGMTOffset
                : get_option( 'gmt_offset' );
                
            return round( $iGMTTimestamp ) - ( self::$_iGMTOffset * 60 * 60 );
            
        }   
        
            static private $_iGMTOffset;
            
}