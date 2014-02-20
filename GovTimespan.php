<?php
// functions to handle GOV dates and timespans
//
// copied from GOV portal, see http://wiki-de.genealogy.net/GOV/Webservice

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

define('GOV_PRECISION_DAY',		2);
define('GOV_PRECISION_MONTH',	1);
define('GOV_PRECISION_YEAR',	0);
define('GOV_NO_BEGIN',			(PHP_INT_MAX*-1)+1);
define('GOV_NO_END',			PHP_INT_MAX);
	
class GovTimespan { 
	var $begin;
	var $end;
	 
	function GovTimespan( $begin, $end, $julianDays = FALSE ) {
		if( $julianDays ) {
			$this->begin = $begin;
			$this->end = $end;
		 } else {
			// input is in years
			$this->setBeginDate( $begin, 0, 0);
			$this->setEndDate( $begin, 0, 0);
		 }
	}
	 
	function hasBeginMonth() {
		return ($this->begin % 10) > 0;
	}
	 
	function hasEndMonth() {
		return ($this->end % 10) > 0;
	}
	 
	function getBeginYear() {
		$cal = cal_from_jd($this->begin,CAL_GREGORIAN);
		return $cal['year'];
	}
	 
	function getEndYear() {
		$cal = cal_from_jd($this->end,CAL_GREGORIAN);
		return $cal['year'];
	}
	 
	function setBeginDate( $year, $month, $day ) {
		if( $month == 0 ) { $myMonth = 1;} else {$myMonth = $month;}
		if( $day == 0 ) {$myDay = 1;} else {$myDay = $day;}
		$julianDay = cal_to_jd(CAL_GREGORIAN,$myMonth,$myDay,$year);
	 
		$this->begin = ($julianDay * 10) + $this->calculatePrecisionIndicator($month, $day);
	}
	 
	function setEndDate( $year, $month, $day ) {
		if( $month == 0 ) { $myMonth = 1;} else {$myMonth = $month;}
		if( $day == 0 ) {$myDay = 1;} else {$myDay = $day;}
		$julianDay = cal_to_jd(CAL_GREGORIAN,$myMonth,$myDay,$year);
	 
		$this->end = ($julianDay * 10) + $this->calculatePrecisionIndicator($month, $day);
	}
	 
	function calculatePrecisionIndicator( $month, $day ) {
		if( $month == 0 ) return GOV_PRECISION_YEAR;
		if( $day == 0 ) return GOV_PRECISION_YEAR;
		return GOV_PRECISION_DAY;
	}
	 
	/** Check if the timespans overlap. */
	function overlaps( $timespan ) {
		if ($timespan == null) {
			$result = true;
		} else {
			if ((!$this->hasBeginMonth() && !$timespan->hasEndMonth()) ||
					(!$this->hasEndMonth() && !$timespan->hasBeginMonth())) {
				/* Timespans overlap only at a value that contains a year (no month or day).
				* Be lenient in this case.
				*/
				$result = ($this->getBeginYear() < $timespan->getEndYear()) &&
					($this->getEndYear() > $timespan->getBeginYear());
			} else {
				$result = ($this->begin < $timespan->end) && ($this->end > $timespan->begin);
			}
		}
	 
		return $result;
	}
}
?>
