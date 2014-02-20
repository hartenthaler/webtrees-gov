<?php
// Interface to edit place locations as administrator
//
// webtrees: Web based Family History software
// Copyright (C) 2013 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2009  PGV Development Team. All rights reserved.
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
//
// Hermann Hartenthaler

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

require WT_ROOT.WT_MODULES_DIR.$this->getName().'/defaultconfig.php';
require WT_ROOT.'includes/functions/functions_edit.php';

$action       = WT_Filter::get('action');
$parent       = WT_Filter::get('parent');
$inactive     = WT_Filter::getBool('inactive');
$mode         = WT_Filter::get('mode');
$deleteRecord = WT_Filter::get('deleteRecord');

if (!isset($parent)) $parent=0;
if (!isset($inactive)) $inactive=false;

// Take a place id and find its place in the hierarchy
// Input: place ID
// Output: ordered array of id=>name values, starting with the Top Level
// e.g. array(0=>'Top Level', 16=>'England', 19=>'London', 217=>'Westminster');
// NB This function exists in both places.php and places_edit.php
function gov_id_to_hierarchy($id) {
	$statement=
		WT_DB::prepare("SELECT gov_parent_id, gov_place FROM `##gov` WHERE gov_id=?");
	$arr=array();
	while ($id!=0) {
		$row=$statement->execute(array($id))->fetchOneRow();
		$arr=array($id=>$row->gov_place)+$arr;
		$id=$row->gov_parent_id;
	}
	return $arr;
}

// NB This function exists in both admin_places.php and places_edit.php
function getHighestIndex() {
	return (int)WT_DB::prepare("SELECT MAX(gov_id) FROM `##gov`")->fetchOne();
}

function getHighestLevel() {
	return (int)WT_DB::prepare("SELECT MAX(gov_level) FROM `##gov`")->fetchOne();
}

/**
 * Find all of the places in the hierarchy
 */
function get_place_list_loc($parent_id, $inactive=false) {
	if ($inactive) {
		$rows=
			WT_DB::prepare("SELECT gov_id, gov_place, gov_govid FROM `##gov` WHERE gov_parent_id=? ORDER BY gov_place COLLATE ".WT_I18N::$collation)
			->execute(array($parent_id))
			->fetchAll();
	} else {
		$rows=
			WT_DB::prepare(
				"SELECT DISTINCT gov_id, gov_place, gov_govid".
				" FROM `##gov`".
				" INNER JOIN `##places` ON `##gov`.gov_place=`##places`.p_place".
				" WHERE gov_parent_id=? ORDER BY gov_place COLLATE ".WT_I18N::$collation
			)
			->execute(array($parent_id))
			->fetchAll();
	}

	$placelist=array();
	foreach ($rows as $row) {
		$placelist[]=array('gov_id'=>$row->gov_id, 'place'=>$row->gov_place, 'govid'=>$row->gov_govid);
	}
	return $placelist;
}

function outputLevel($parent_id) {
	$tmp = gov_id_to_hierarchy($parent_id);
	$maxLevel = getHighestLevel();
	if ($maxLevel>8) $maxLevel = 8;
	$prefix = implode(';', $tmp);
	if ($prefix!='')
		$prefix.=';';
	$suffix=str_repeat(';', $maxLevel-count($tmp));
	$level=count($tmp);

	$rows=
		WT_DB::prepare("SELECT gov_id, gov_place, gov_govid FROM `##gov` WHERE gov_parent_id=? ORDER BY gov_place")
		->execute(array($parent_id))
		->fetchAll();

	foreach ($rows as $row) {
		echo $level,';',$prefix,$row->gov_place,$suffix,';',$row->gov_govid,"\r\n";
		if ($level < $maxLevel) {
			outputLevel($row->gov_id);
		}
	}
}

/**
 * recursively find all of the csv files on the server
 *
 * @param string $path
 */
function findFiles($path) {
	global $placefiles;
	if (file_exists($path)) {
		$dir = dir($path);
		while (false !== ($entry = $dir->read())) {
			if ($entry!='.' && $entry!='..' && $entry!='.svn') {
				if (is_dir($path.'/'.$entry)) {
					findFiles($path.'/'.$entry);
				} elseif (strstr($entry, '.csv')!==false) {
					$placefiles[] = preg_replace('~'.WT_MODULES_DIR.$this->getName().'/extra~', '', $path).'/'.$entry;
				}
			}
		}
		$dir->close();
	}
}

$controller=new WT_Controller_Page();
$controller->requireAdminLogin();

if ($action=='ExportFile' && WT_USER_IS_ADMIN) {
	Zend_Session::writeClose();
	$tmp = gov_id_to_hierarchy($parent);
	$maxLevel = getHighestLevel();
	if ($maxLevel>8) $maxLevel=8;
	$tmp[0] = 'places';
	$outputFileName=preg_replace('/[:;\/\\\(\)\{\}\[\] $]/', '_', implode('-', $tmp)).'.csv';
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.$outputFileName.'"');
	echo '"', WT_I18N::translate('Level'), '";"', WT_I18N::translate('Country'), '";';
	if ($maxLevel>0) echo '"', WT_I18N::translate('State'), '";';
	if ($maxLevel>1) echo '"', WT_I18N::translate('County'), '";';
	if ($maxLevel>2) echo '"', WT_I18N::translate('City'), '";';
	if ($maxLevel>3) echo '"', WT_I18N::translate('Place'), '";';
	if ($maxLevel>4) echo '"', WT_I18N::translate('Place'), '";';
	if ($maxLevel>5) echo '"', WT_I18N::translate('Place'), '";';
	if ($maxLevel>6) echo '"', WT_I18N::translate('Place'), '";';
	if ($maxLevel>7) echo '"', WT_I18N::translate('Place'), '";';
	echo '"', WT_I18N::translate('GOV id'), '";"', WT_EOL;
	outputLevel($parent);
	exit;
}

$controller
	->setPageTitle(WT_I18N::translate('GOV'))
	->pageHeader();

?>
<div id=gov">
<table id="gov_config">
	<tr>
		<th>
			<a href="module.php?mod=gov&amp;mod_action=admin_config">
				<?php echo WT_I18N::translate('GOV preferences'); ?>
			</a>
		</th>
		<th>
			<a class="current" href="module.php?mod=gov&amp;mod_action=admin_places">
				<?php echo WT_I18N::translate('GOV data'); ?>
			</a>
		</th>
		<th>
			<a href="module.php?mod=gov&amp;mod_action=admin_placecheck">
				<?php echo WT_I18N::translate('Place Check'); ?>
			</a>
		</th>
	</tr>
</table>
<?php

if ($action=='ImportGedcom') {
	$placelist=array();
	$j=0;
	$statement=
		WT_DB::prepare("SELECT i_gedcom FROM `##individuals` WHERE i_file=? UNION ALL SELECT f_gedcom FROM `##families` WHERE f_file=?")
		->execute(array(WT_GED_ID, WT_GED_ID));
	while ($gedrec=$statement->fetchColumn()) {
		$i = 1;
		$placerec = get_sub_record(2, '2 PLAC', $gedrec, $i);
		while (!empty($placerec)) {
			if (preg_match("/2 PLAC (.+)/", $placerec, $match)) {
				$placelist[$j] = array();
				$placelist[$j]['place'] = trim($match[1]);
				if (preg_match("/4 _GOV (.*)/", $placerec, $match)) {
// tbd: check if there is a _GOV record or a NOTE with GOV id
							$placelist[$j]['govid'] = 'xxx';
				}
				else $placelist[$j]['govid'] = NULL;
				$j = $j + 1;
			}
			$i = $i + 1;
			$placerec = get_sub_record(2, '2 PLAC', $gedrec, $i);
		}
	}
	asort($placelist);

	$prevPlace = '';
	$prevLati = '';
	$prevLong = '';
	$placelistUniq = array();
	$j = 0;
	foreach ($placelist as $k=>$place) {
		if ($place['place'] != $prevPlace) {
			$placelistUniq[$j] = array();
			$placelistUniq[$j]['place'] = $place['place'];
			$placelistUniq[$j]['govid'] = $place['govid'];
			$j = $j + 1;
		} else if (($place['place'] == $prevPlace) && ($place['govid'] != $prevLati)) {
			if ($placelistUniq[$j-1]['govid'] == 0) {
				$placelistUniq[$j-1]['govid'] = $place['govid'];
			} else if ($place['govid'] != '0') {
				echo 'Difference: previous value = ', $prevPlace, ', ', $prevLati, ', ', ' current = ', $place['place'], ', ', $place['govid'], '<br>';
			}
		}
		$prevPlace = $place['place'];
		$prevLati = $place['govid'];
	}

	$highestIndex = getHighestIndex();

	$default_zoom_level=array(4, 7, 10, 12);
	foreach ($placelistUniq as $k=>$place) {
        $parent=preg_split('/ *, */', $place['place']);
		$parent=array_reverse($parent);
		$parent_id=0;
		for ($i=0; $i<count($parent); $i++) {
			if (!isset($default_zoom_level[$i]))
				$default_zoom_level[$i]=$default_zoom_level[$i-1];
			$escparent=$parent[$i];
			if ($escparent == '') {
				$escparent = 'Unknown';
			}
			$row=
				WT_DB::prepare("SELECT gov_id, gov_govid FROM `##gov` WHERE gov_level=? AND gov_parent_id=? AND gov_place LIKE ?")
				->execute(array($i, $parent_id, $escparent))
				->fetchOneRow();
			if ($i < count($parent)-1) {
				// Create higher-level places, if necessary
				if (empty($row)) {
					$highestIndex++;
					WT_DB::prepare("INSERT INTO `##gov` (gov_id, gov_parent_id, gov_level, gov_place) VALUES (?, ?, ?, ?)")
						->execute(array($highestIndex, $parent_id, $i, $escparent));
					echo htmlspecialchars($escparent), '<br>';
					$parent_id=$highestIndex;
				} else {
					$parent_id=$row->gov_id;
				}
			} else {
				// Create lowest-level place, if necessary
				if (empty($row->gov_id)) {
					$highestIndex++;
					WT_DB::prepare("INSERT INTO `##gov` (gov_id, gov_parent_id, gov_level, gov_place, gov_govid) VALUES (?, ?, ?, ?, ?)")
						->execute(array($highestIndex, $parent_id, $i, $escparent, $place['govid']));
					echo htmlspecialchars($escparent), '<br>';
//				} else {
//					if (empty($row->gov_long) && empty($row->gov_govid) && $place['lati']!='0' && $place['long']!='0') {
//						WT_DB::prepare("UPDATE `##gov` SET gov_govid=?, gov_long=? WHERE gov_id=?")
//							->execute(array($place['lati'], $place['long'], $row->gov_id));
//						echo htmlspecialchars($escparent), '<br>';
//					}
				}
			}
		}
	}
	$parent=0;
}

if ($action=='ImportFile') {
	$placefiles = array();
	findFiles(WT_MODULES_DIR.$this->getName().'/extra');
	sort($placefiles);
?>
<form method="post" enctype="multipart/form-data" id="importfile" name="importfile" action="module.php?mod=gov&mod_action=admin_places">
	<input type="hidden" name="action" value="ImportFile2">
	<table class="gov_plac_edit">
		<tr>
			<th><?php echo WT_I18N::translate('File containing GOV information (CSV)'); ?></th>
			<td><input type="file" name="placesfile" size="50"></td>
		</tr>
		<?php if (count($placefiles)>0) { ?>
		<tr>
			<th><?php echo WT_I18N::translate('Server file containing GOV information (CSV)'), help_link('GOV_PLIF_LOCALFILE','gov'); ?></th>
			<td>
				<select name="localfile">
					<option></option>
					<?php foreach ($placefiles as $p=>$placefile) { ?>
					<option value="<?php echo htmlspecialchars($placefile); ?>"><?php
						if (substr($placefile, 0, 1)=="/") echo substr($placefile, 1);
						else echo $placefile; ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<th><?php echo WT_I18N::translate('Delete all existing GOV data before importing the file.'); ?></th>
			<td><input type="checkbox" name="cleardatabase"></td>
		</tr>
		<tr>
			<th><?php echo WT_I18N::translate('Do not create new locations, just import GOV information for existing locations.'); ?></th>
			<td><input type="checkbox" name="updateonly"></td>
		</tr>
		<tr>
			<th><?php echo WT_I18N::translate('Overwrite existing GOV data.'); ?></th>
			<td><input type="checkbox" name="overwritedata"></td>
		</tr>
	</table>
	<input id="savebutton" type="submit" value="<?php echo WT_I18N::translate('Continue adding'); ?>"><br>
</form>
<?php
	exit;
}

if ($action=='ImportFile2') {
	$country_names=array();
	foreach (WT_Stats::iso3166() as $key=>$value) {
		$country_names[$key]=WT_I18N::translate($key);
	}
	if (isset($_POST['cleardatabase'])) {
		WT_DB::exec("DELETE FROM `##gov` WHERE 1=1");
	}
	if (!empty($_FILES['placesfile']['tmp_name'])) {
		$lines = file($_FILES['placesfile']['tmp_name']);
	} elseif (!empty($_REQUEST['localfile'])) {
		$lines = file(WT_MODULES_DIR.$this->getName().'/extra'.$_REQUEST['localfile']);
	}
	// Strip BYTE-ORDER-MARK, if present
	if (!empty($lines[0]) && substr($lines[0], 0, 3)==WT_UTF8_BOM) $lines[0]=substr($lines[0], 3);
	asort($lines);
	$highestIndex = getHighestIndex();
	$placelist = array();
	$j = 0;
	$maxLevel = 0;
	foreach ($lines as $p => $placerec) {
		$fieldrec = explode(';', $placerec);
		if ($fieldrec[0] > $maxLevel) $maxLevel = $fieldrec[0];
	}
	$fields = count($fieldrec);
	foreach ($lines as $p => $placerec) {
		$fieldrec = explode(';', $placerec);
		if (is_numeric($fieldrec[0]) && $fieldrec[0]<=$maxLevel) {
			$placelist[$j] = array();
			$placelist[$j]['place'] = '';
			for ($ii=$fields-4; $ii>1; $ii--) {
				if ($fieldrec[0] > $ii-2) $placelist[$j]['place'] .= $fieldrec[$ii].',';
			}
			foreach ($country_names as $countrycode => $countryname) {
				if ($countrycode == strtoupper($fieldrec[1])) {
					$fieldrec[1] = $countryname;
					break;
				}
			}
			$placelist[$j]['place'] .= $fieldrec[1];
			// -2 ???
			$placelist[$j]['govid'] = $fieldrec[$fields-2];
			$j = $j + 1;
		}
	}

	$prevPlace = '';
	$prevLati = '';
	$placelistUniq = array();
	$j = 0;
	foreach ($placelist as $k=>$place) {
		if ($place['place'] != $prevPlace) {
			$placelistUniq[$j] = array();
			$placelistUniq[$j]['place'] = $place['place'];
			$placelistUniq[$j]['govid'] = $place['govid'];
			$j = $j + 1;
		} else if (($place['place'] == $prevPlace) && ($place['govid'] != $prevLati)) {
			if (($placelistUniq[$j-1]['govid'] == 0)) {
				$placelistUniq[$j-1]['govid'] = $place['govid'];
			} else if (($place['govid'] != '0')) {
				echo 'Difference: previous value = ', $prevPlace, ', ', $prevLati, ', ', ' current = ', $place['place'], ', ', $place['govid'], '<br>';
			}
		}
		$prevPlace = $place['place'];
		$prevLati = $place['govid'];
	}

	foreach ($placelistUniq as $k=>$place) {
		$parent = explode(',', $place['place']);
		$parent = array_reverse($parent);
		$parent_id=0;
		for ($i=0; $i<count($parent); $i++) {
			$escparent=$parent[$i];
			if ($escparent == '') {
				$escparent = 'unknown';
			}
			$row=
				WT_DB::prepare("SELECT gov_id, gov_govid FROM `##gov` WHERE gov_level=? AND gov_parent_id=? AND gov_place LIKE ? ORDER BY gov_place")
				->execute(array($i, $parent_id, $escparent))
				->fetchOneRow();
			if (empty($row)) {       // this name does not yet exist: create entry
				if (!isset($_POST['updateonly'])) {
					$highestIndex = $highestIndex + 1;
					if (($place['govid'] == '') || (($i+1) < count($parent))) {
						WT_DB::prepare("INSERT INTO `##gov` (gov_id, gov_parent_id, gov_level, gov_place) VALUES (?, ?, ?, ?)")
							->execute(array($highestIndex, $parent_id, $i, $escparent));
					} else {
						$gov_govid = $place['govid'];
						WT_DB::prepare("INSERT INTO `##gov` (gov_id, gov_parent_id, gov_level, gov_place, gov_govid) VALUES (?, ?, ?, ?, ?)")
							->execute(array($highestIndex, $parent_id, $i, $escparent, $place['govid']));
					}
					$parent_id = $highestIndex;
				}
			} else {
				$parent_id = $row->gov_id;
				if ((isset($_POST['overwritedata'])) && ($i+1 == count($parent))) {
					WT_DB::prepare("UPDATE `##gov` SET gov_govid=? WHERE gov_id=?")
						->execute(array($place['govid'], $parent_id));
				}
			}
		}
	}
	$parent=0;
}

if ($action=='DeleteRecord') {
	$exists=
		WT_DB::prepare("SELECT 1 FROM `##gov` WHERE gov_parent_id=?")
		->execute(array($deleteRecord))
		->fetchOne();

	if (!$exists) {
		WT_DB::prepare("DELETE FROM `##gov` WHERE gov_id=?")
			->execute(array($deleteRecord));
	} else {
		echo '<table class="facts_table"><tr><td>', WT_I18N::translate('Location not removed: this location contains sub-locations'), '</td></tr></table>';
	}
}

?>
<script>
function updateList(inactive) {
	window.location.href='<?php if (strstrb($_SERVER['REQUEST_URI'], '&inactive')) { $uri=strstrb($_SERVER['REQUEST_URI'], '&inactive');} else { $uri=$_SERVER['REQUEST_URI']; } echo $uri, '&inactive='; ?>'+inactive;
}

function edit_place_location(placeid) {
	window.open('module.php?mod=gov&mod_action=places_edit&action=update&placeid='+placeid, '_blank', gmap_window_specs);
	return false;
}

function add_place_location(placeid) {
	window.open('module.php?mod=gov&mod_action=places_edit&action=add&placeid='+placeid, '_blank', gmap_window_specs);
	return false;
}

function delete_place(placeid) {
	var answer=confirm('<?php echo WT_I18N::translate('Remove this location?'); ?>');
	if (answer == true) {
		window.location = '<?php echo $_SERVER['REQUEST_URI']; ?>&action=DeleteRecord&deleteRecord=' + placeid;
	}
}
</script>
<?php
echo '<div id="gov_breadcrumb">';
$where_am_i=gov_id_to_hierarchy($parent);
foreach (array_reverse($where_am_i, true) as $id=>$place) {
	if ($id==$parent) {
		if ($place != 'unknown') {
			echo htmlspecialchars($place);
		} else {
			echo WT_I18N::translate('unknown');
		}
	} else {
		echo '<a href="module.php?mod=gov&mod_action=admin_places&parent=', $id, '&inactive=', $inactive, '">';
		if ($place != 'unknown') {
			echo htmlspecialchars($place), '</a>';
		} else {
			echo WT_I18N::translate('unknown'), '</a>';
		}
	}
	echo ' - ';
}
echo '<a href="module.php?mod=gov&mod_action=admin_places&parent=0&inactive=', $inactive, '">', WT_I18N::translate('Top Level'), '</a></div>';
echo '<form name="active" method="post" action="module.php?mod=gov&mod_action=admin_places&parent=', $parent, '&inactive=', $inactive, '"><div id="gov_active">';
echo '<label for="inactive">', WT_I18N::translate('Show inactive GOV records'), '</label>';
echo '<input type="checkbox" name="inactive" id="inactive"';
if ($inactive) echo ' checked="checked"';
echo ' onclick="updateList(this.checked)"';
echo '>',  help_link('GOV_PLE_ACTIVE','gov'), '</div></form>';

$placelist=get_place_list_loc($parent, $inactive);
echo '<div class="gov_plac_edit">';
echo '<table class="gov_plac_edit"><tr>';
echo '<th>', WT_Gedcom_Tag::getLabel('PLAC'), '</th>';
echo '<th>', WT_Gedcom_Tag::getLabel('GOV id'), '</th>';
echo '<th>';
echo WT_I18N::translate('Edit'), '</th><th>', WT_I18N::translate('Delete'), '</th></tr>';
if (count($placelist) == 0)
	echo '<tr><td colspan="7" class="accepted">', WT_I18N::translate('No GOV records found'), '</td></tr>';
foreach ($placelist as $place) {
	echo '<tr><td><a href="module.php?mod=gov&mod_action=admin_places&parent=', $place['gov_id'], '&inactive=', $inactive, '">';
	if ($place['place'] != 'unknown')
			echo htmlspecialchars($place['place']), '</a></td>';
		else
			echo WT_I18N::translate('unknown'), '</a></td>';
	echo '<td>', $place['govid'], '</td>';
	echo '<td class="narrow"><a href="#" onclick="edit_place_location(', $place['gov_id'], ');return false;" class="icon-edit" title="', WT_I18N::translate('Edit'), '"></a></td>';
	$noRows=
		WT_DB::prepare("SELECT COUNT(gov_id) FROM `##gov` WHERE gov_parent_id=?")
		->execute(array($place['gov_id']))
		->fetchOne();
	if ($noRows==0) { ?>
		<td><a href="#" onclick="delete_place(<?php echo $place['gov_id']?>);return false;" class="icon-delete" title="<?php echo WT_I18N::translate('Remove'); ?>"></a></td>
<?php       } else { ?>
		<td><i class="icon-delete-grey"></i></td>
<?php       } ?>
	</tr>
	<?php
}
?>
</table>
</div>

<table id="gov_manage">
	<tr>
		<td>
			<?php echo WT_I18N::translate('Add  a new GOV record'); ?>
		</td>
		<td>
			<form action="#" onsubmit="add_place_location(this.parent_id.options[this.parent_id.selectedIndex].value); return false;">
				<?php echo select_edit_control('parent_id', $where_am_i, WT_I18N::translate('Top Level'), $parent); ?>
				<input type="submit" value="<?php echo WT_I18N::translate('Add'); ?>">
			</form>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo WT_I18N::translate('Import all places from a family tree'); ?>
		</td>
		<td>
			<form action="module.php" method="get">
				<input type="hidden" name="mod" value="gov">
				<input type="hidden" name="mod_action" value="admin_places">
				<input type="hidden" name="action" value="ImportGedcom">
				<?php echo select_edit_control('ged', WT_Tree::getNameList(), null, WT_GEDCOM); ?>
				<input type="submit" value="<?php echo WT_I18N::translate('Import'); ?>">
			</form>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo WT_I18N::translate('Upload GOV data'); ?>
		</td>
		<td>
			<form action="module.php" method="get">
				<input type="hidden" name="mod" value="gov">
				<input type="hidden" name="mod_action" value="admin_places">
				<input type="hidden" name="action" value="ImportFile">
				<input type="submit" value="<?php echo WT_I18N::translate('Upload'); ?>">
			</form>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo WT_I18N::translate('Download GOV data'); ?>
		</td>
		<td>
			<form action="module.php" method="get">
				<input type="hidden" name="mod" value="gov">
				<input type="hidden" name="mod_action" value="admin_places">
				<input type="hidden" name="action" value="ExportFile">
				<?php echo select_edit_control('parent', $where_am_i, WT_I18N::translate('All'), WT_GED_ID); ?>
				<input type="submit" value="<?php echo WT_I18N::translate('Download'); ?>">
			</form>
		</td>
	</tr>
</table>
</div>