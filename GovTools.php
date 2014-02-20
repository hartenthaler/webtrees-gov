<?php
// tools to be used for GOV
//
// webtrees: Web based Family History software
// Copyright (C) 2013 webtrees development team.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

require_once WT_ROOT.WT_MODULES_DIR.'gov/GovTimespan.php';
 
class GovTools {
 
	public static function show_gov_search_types () {  // only used for debugging
		global $gov_search_types, $gov_types; 
		echo '<table bgcolor="#FFFFCC">';
			echo '<tr>';
				echo '<th>0</th><th>1</th><th>2</th><th>3</th>';
			echo '</tr>';
			echo '<tr>';
				foreach ($gov_search_types as $level) {
					echo '<td><table bgcolor="#FFFFFF">';
					foreach ($level as $type) {
						echo '<tr valign="top">';
							echo '<td>', $type, '</td>';
							echo '<td>', $gov_types[$type]['type'], '</td>';
							echo '<td>', $gov_types[$type]['can_represent'], '</td>';
							echo '<td>', $gov_types[$type]['can_be_place'], '</td>';
							echo '<td>', $gov_types[$type]['can_have_location'], '</td>';
						echo '</tr>';
					}
					echo '</table></td>';
				}
			echo '</tr>';
		echo '</table>';
	}
	
	private static function normalizeDates( &$object ) {
		foreach( $object as $n ) {
			if ( isset( $n->{'begin-year'} ) ) {
				$year = $n->{'begin-year'};
				$jd = cal_to_jd(CAL_GREGORIAN,1,1,$year);
				unset($n->{'begin-year'});
				$n->begin = new stdClass();
				$n->begin->precision = 0;
				$n->begin->jd = $jd;
			}
			if ( isset( $n->{'end-year'} ) ) {
				$year = $n->{'end-year'};
				$jd = cal_to_jd(CAL_GREGORIAN,1,1,$year);
				unset($n->{'end-year'});
				$n->end = new stdClass();
				$n->end->precision = 0;
				$n->end->jd = $jd;
			}
			if ( isset( $n->year ) ) {
				unset($n->year);
			}
		}
	}

	// Converts date specification given in years into julian dates.
	private static function normalizeNames( &$govObject ) {
		// convert names into an array if necessary
		if ( !is_array( $govObject->name ) ) {
			$n = $govObject->name;
			$govObject->name = array();
			$govObject->name[0] = $n;
		}
		self::normalizeDates($govObject->name);
   }
   
	private static function normalizeTypes( &$govObject ) {
		// convert types into an array if necessary
		if( !is_array( $govObject->type ) ) {
			$t = $govObject->type;
			$govObject->type = array();
			$govObject->type[0] = $t;
		  }
		self::normalizeDates($govObject->type);
   }
   
   private static function normalizeHierarchy( &$govObject ) {
		$not_top_level = true;
		if ( !isset($govObject->{'part-of'}) ) {
			$not_top_level = false;
		} else {
			if ( !is_array( $govObject->{'part-of'} ) ) {
				$p = $govObject->{'part-of'};
				$govObject->{'part-of'} = array();
				$govObject->{'part-of'}[0] = $p;
			}
		 	self::normalizeDates($govObject->{'part-of'});
	  }
	  return $not_top_level;
   }
 
   public static function getName( $govObject, $julianDay, $preferredLanguage ) {
        $foundBegin = GOV_NO_BEGIN;
        $foundName = null;
        $foundLanguage = null;
        $foundNameIsValidAtInterestingTime = false;
 
        self::normalizeNames($govObject);
 
        $interestingTime = new GovTimespan( $julianDay*10+2, $julianDay*10+2, true );
 
        foreach($govObject->name as $name ) {
            $nameTimeBegin = GOV_NO_BEGIN;
            $nameBegin = GOV_NO_BEGIN;
            $nameTimeEnd = GOV_NO_END;
            if( isset($name->begin) ) { 
                $nameTimeBegin = $name->begin->jd * 10 + $name->begin->precision; 
                $nameBegin = $name->begin->jd ;
            }
            if( isset($name->end) ) { $nameTimeEnd = $name->end->jd * 10 + $name->end->precision; }
 
            $nameTime = new GovTimespan( $nameTimeBegin, $nameTimeEnd, true );
 
            if ($nameBegin <= $julianDay) {
                if (($preferredLanguage != null) &&
                        $preferredLanguage == $foundLanguage &&
                        $preferredLanguage != $name->lang) {
                    // tbd: what should happen here?
                } else {
					$weDontHaveAnyNameYet = ($foundName == null);
                    $weHaveNotFoundCorrectLanguage =
                        (($preferredLanguage != null) &&
                        $preferredLanguage != $foundLanguage &&
                        $preferredLanguage == $name->lang);
                    $thisNameIsYoungerThanTheAlreadyFound =
                        (abs($nameBegin - $julianDay) < abs($foundBegin - $julianDay));
                    $thisNameIsValidAtInterestingTime = $interestingTime->overlaps($nameTime);
 
                    /*
                     * Which conditions have to be satisfied to pick this name instead a possible other?
                     * 1. We don't have any name yet -> use this one
                     * 2. We have not found the correct language -> use this one
                     * 3. The already found name is not valid at the interesting time
                     *    AND
                     *       This name is valid at the interesting time.
                     *       OR
                     *       This name is younger than the already found name.
                     */
                    if ($weDontHaveAnyNameYet || $weHaveNotFoundCorrectLanguage ||
                            (!$foundNameIsValidAtInterestingTime &&
                            ( $thisNameIsValidAtInterestingTime | $thisNameIsYoungerThanTheAlreadyFound) )) {
                        $foundName = $name;
                        $foundBegin = $nameBegin;
                        $foundLanguage = $name->lang;
                        $foundNameIsValidAtInterestingTime = $interestingTime->overlaps($nameTime);
                    }
                }
            }
        }
 
        if (($foundName == null) && ($preferredLanguage != null)) {
            // no name could be found for the specified language -> return a name in any language
            $foundName = self::getName($govObject, $julianDay, null);
        }
 
        return $foundName->value;
   }
   
   public static function getType( $govObject, $julianDay, $preferredLanguage ) {
        $foundBegin = GOV_NO_BEGIN;
        $foundType = null;
        $foundLanguage = null;
        $foundTypeIsValidAtInterestingTime = false;
  		// echo "govObject not normalized:<br><pre>";
		// print_r($govObject);
		// echo "</pre>";
        self::normalizeTypes($govObject);
 		// echo "govObject normalized:<br><pre>";
		// print_r($govObject);
		// echo "</pre>";
        $interestingTime = new GovTimespan( $julianDay*10+2, $julianDay*10+2, true );
 
        foreach($govObject->type as $type ) {
			// echo "type:<br><pre>";
			// print_r($type);
			// echo "</pre>";
            $typeTimeBegin = GOV_NO_BEGIN;
            $typeBegin = GOV_NO_BEGIN;
            $typeTimeEnd = GOV_NO_END;
            if( isset($type->begin) ) { 
                $typeTimeBegin = $type->begin->jd * 10 + $type->begin->precision; 
                $typeBegin = $type->begin->jd ;
            }
            if( isset($type->end) ) { $typeTimeEnd = $type->end->jd * 10 + $type->end->precision; }
 
            $typeTime = new GovTimespan( $typeTimeBegin, $typeTimeEnd, true );
 
            if ($typeBegin <= $julianDay) {
				if (($preferredLanguage != null) &&
						$preferredLanguage == $foundLanguage &&
						isset($type->lang) && 
						$preferredLanguage != $type->lang) {
					// tbd: what should happen here?
				} else {
					$weDontHaveAnyTypeYet = ($foundType == null);
					$weHaveNotFoundCorrectLanguage =
						(($preferredLanguage != null) &&
						$preferredLanguage != $foundLanguage &&
						isset($type->lang) && 
						$preferredLanguage == $type->lang);
					$thisTypeIsYoungerThanTheAlreadyFound =
						(abs($typeBegin - $julianDay) < abs($foundBegin - $julianDay));
					$thisTypeIsValidAtInterestingTime = $interestingTime->overlaps($typeTime);
 
					/*
					 * Which conditions have to be satisfied to pick this type instead a possible other?
					 * 1. We don't have any type yet -> use this one
					 * 2. We have not found the correct language -> use this one
					 * 3. The already found type is not valid at the interesting time
					 *    AND
					 *       This type is valid at the interesting time.
					 *       OR
					 *       This type is younger than the already found type.
					 */
					if ($weDontHaveAnyTypeYet || $weHaveNotFoundCorrectLanguage ||
							(!$foundTypeIsValidAtInterestingTime &&
							( $thisTypeIsValidAtInterestingTime | $thisTypeIsYoungerThanTheAlreadyFound) )) {
						// $foundType = isset($type->value) ? $type->value : $type;
						$foundType = $type;
						$foundBegin = $typeBegin;
						$foundLanguage = isset($type->lang) ? $type->lang : null;
						$foundTypeIsValidAtInterestingTime = $interestingTime->overlaps($typeTime);
					}
				}
            }
        }
 
        if (($foundType == null) && ($preferredLanguage != null)) {
            // no type could be found for the specified language -> return a type in any language
			// tbd: check if there is a endless loop possible
            $foundType = self::getType($govObject, $julianDay, null);
        }
		// var_dump($foundType);
        return $foundType->value;
   }
   
	public static function getHierarchy( $govObject, $julianDay, $lang, &$hierarchy_array ) {
		global $gov_client;
		$foundBegin = GOV_NO_BEGIN;
		$foundHierarchy = null;
        $foundHierarchyIsValidAtInterestingTime = false;
		// var_dump($govObject);
		// echo '[id=', $govObject->id, '] ';
   		// echo "govObject not normalized:<br><pre>";
		// print_r($govObject);
		// echo "</pre>";
        if (self::normalizeHierarchy($govObject)) {
			// echo "govObject normalized:<br><pre>";
			// print_r($govObject);
			// echo "</pre>";
			$interestingTime = new GovTimespan( $julianDay*10+2, $julianDay*10+2, true );
	 
			foreach($govObject->{'part-of'} as $hierarchy ) {
				// var_dump($hierarchy);
				$hierarchyTimeBegin = GOV_NO_BEGIN;
				$hierarchyBegin = GOV_NO_BEGIN;
				$hierarchyTimeEnd = GOV_NO_END;
				if( isset($hierarchy->begin) ) { 
					$hierarchyTimeBegin = $hierarchy->begin->jd * 10 + $hierarchy->begin->precision; 
					$hierarchyBegin = $hierarchy->begin->jd ;
				}
				if( isset($hierarchy->end) ) { $hierarchyTimeEnd = $hierarchy->end->jd * 10 + $hierarchy->end->precision; }
	 
				$hierarchyTime = new GovTimespan( $hierarchyTimeBegin, $hierarchyTimeEnd, true );
	 
				if ($hierarchyBegin <= $julianDay) {
					$weDontHaveAnyHierarchyYet = ($foundHierarchy == null);
					$weHaveNotFoundCorrectLanguage =
					$thisHierarchyIsYoungerThanTheAlreadyFound =
						(abs($hierarchyBegin - $julianDay) < abs($foundBegin - $julianDay));
					$thisHierarchyIsValidAtInterestingTime = $interestingTime->overlaps($hierarchyTime);
	
					/*
					 * Which conditions have to be satisfied to pick this hierarchy instead a possible other?
					 * 1. We don't have any hierarchy yet -> use this one
					 * 2. The already found hierarchy is not valid at the interesting time
					 *    AND
					 *       This hierarchy is valid at the interesting time.
					 *       OR
					 *       This hierarchy is younger than the already found hierarchy.
					 */
					if ($weDontHaveAnyHierarchyYet || 
							(!$foundHierarchyIsValidAtInterestingTime &&
							( $thisHierarchyIsValidAtInterestingTime | $thisHierarchyIsYoungerThanTheAlreadyFound) )) {
						$foundHierarchy = $hierarchy->ref;
						$foundName = self::getName( $govObject, $julianDay, $lang );
						$foundBegin = $hierarchyBegin;
						$foundHierarchyIsValidAtInterestingTime = $interestingTime->overlaps($hierarchyTime);
					}
				}
			}
			// echo '[foundHierarchy=', $foundHierarchy, '] ';
			if ( count($hierarchy_array) == 0 ) {
				$hierarchy_array[$foundHierarchy] = 'top'; // will be replaced in next recursion or in last recursion
			} else {
				end($hierarchy_array);
				$hierarchy_array[key($hierarchy_array)] = $foundName; // replace last top value
				$hierarchy_array[$foundHierarchy] = 'top'; // will be replaced in next recursion or in last recursion
			}
			// var_dump($hierarchy_array);
			$govObject2 = $gov_client->getObject($foundHierarchy);
			// var_dump($govObject2);
			self::getHierarchy( $govObject2, $julianDay, $lang, $hierarchy_array );
			return;
		} else { // reached top level
			$foundName = self::getName( $govObject, $julianDay, $lang );
			if ( count($hierarchy_array) == 0 ) {
				$hierarchy_array[$govObject->id] = ''; // started and ended with top level
			} else {
				end($hierarchy_array);
				$hierarchy_array[key($hierarchy_array)] = $foundName;
			}
			// var_dump($hierarchy_array);
			return; 
		}
	}
   
	public static function getHierarchyNames ( $hierarchy ) {
		// var_dump($hierarchy);
		return implode(', ', $hierarchy);
	}
}
 
?>
