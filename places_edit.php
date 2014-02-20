<?php
// Interface to edit place locations as administrator
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
//
// Hermann Hartenthaler

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

require WT_ROOT.WT_MODULES_DIR.$this->getName().'/defaultconfig.php';
require WT_ROOT.'includes/functions/functions_edit.php';
require_once WT_ROOT.WT_MODULES_DIR.$this->getName().'/GovTools.php';
global $language_ISO639_1_to_ISO639_2B;

$action     = WT_Filter::post('action',  null, WT_Filter::get('action'));
$placeid    = WT_Filter::post('placeid', null, WT_Filter::get('placeid'));
$place_name = WT_Filter::post('place_name');

$controller=new WT_Controller_Simple();
$controller
		->requireAdminLogin()
		->setPageTitle(WT_I18N::translate('GOV data'))
		->pageHeader();

// ----------------------------------------------------------------------------------------------------
function search_place($name, $today, $lang, $level) {
	global $gov_client, $gov_search_types, $gov_types;
	
	$name = utf8_encode(trim($name));  // tbd: solve problem with special characters (e.g. umlaut)
	// echo '[name=', $name, '] ';
	$level = min($level, count($gov_search_types)-1); // level 0 is country; use not more levels than stored 
	// echo '[level=', $level, '] ';
	$names = array();
	// echo "[gov_search_types($level)=", implode(',', $gov_search_types[$level]), '] ';
	$list = $gov_client->searchByNameAndType($name, implode(',', $gov_search_types[$level]));
	// echo "list:<br><pre>";
	// print_r($list);
	// echo "</pre>";
	// echo '[is object=', is_object($list), '] ';
	// echo '[count_list=', count($list), '] ';
	if ( isset($list->object) ) {
		// echo '[count_list_object=', count($list->object), '] ';
		if ( count($list->object) == 1) {
			// echo "<pre>";
			// print_r($list);
			// echo "</pre>";
			$place = $list->object;
			// var_dump($place);
			// echo '[id=', $place->id, '] ';
			// echo '[name=', $gov_client->getNameAtDate($place->id, $today, 'deu'), '] ';
			$nam = GovTools::getName($place, $today, $lang);
			// echo '[nam=', $nam, '] ';
			$t = GovTools::getType($place, $today, $lang);
			$type = $gov_types[$t]['type'];
			// echo '[type=', $type, '] ';
			$object_hierarchy = array();
			GovTools::getHierarchy($place, $today, $lang, $object_hierarchy);
			$hierarchy_names = GovTools::getHierarchyNames($object_hierarchy);
			// echo '[hierarchy_names=', $hierarchy_names, '] ';
			$names[$place->id] = array ('name' => $nam, 'type' => $type, 'hierarchy' => $hierarchy_names);
		} else {
			// tbd: sort results by plausibility 
			foreach($list->object as $place) {
				//if ( is_string($place) ) { // why does this happen sometimes ???
					//echo '[place=', $place, '] ';
					// var_dump($list);
					// break;
				//	$place = $list->object;
					// echo 'new place: '; var_dump($place);
				//}
				// var_dump($place);
				// echo '[id=', $place->id, '] ';
				// echo '[name=', $gov_client->getNameAtDate($place->id, $today, 'deu'), '] ';
				$nam = GovTools::getName($place, $today, $lang);
				// echo '[nam=', $nam, '] ';
				$t = GovTools::getType($place, $today, $lang);
				// echo '[t=', $t, '] ';
				$type = $gov_types[$t]['type'];
				// echo '[type=', $type, '] ';
				$object_hierarchy = array();
				GovTools::getHierarchy($place, $today, $lang, $object_hierarchy);
				$hierarchy_names = GovTools::getHierarchyNames($object_hierarchy);
				// echo '[hierarchy_names=', $hierarchy_names, '] ';
				$names[$place->id] = array ('name' => $nam, 'type' => $type, 'hierarchy' => $hierarchy_names);
			}
		}
	}
	// var_dump($names);
	return $names;
}

function search_places ($name, $level) {
	global $language_ISO639_1_to_ISO639_2B;
	
	$today = unixtojd();
	$lang = $language_ISO639_1_to_ISO639_2B[WT_LOCALE]['letter3']; // prefered language for name of place
	// echo '[lang=', $lang, '] ';
	$names = array();
	$special_names = explode (';', $name);
	// var_dump($special_names);
	foreach ($special_names as $name) {
		$names = array_merge($names, search_place($name, $today, $lang, $level));
	}
	return $names;
}

// Take a place id and find its place in the hierarchy
// Input:	place ID
// Output:	ordered array of id=>name values, starting with the Top Level
// 			e.g. array(0=>"Top Level", 16=>"England", 19=>"London", 217=>"Westminster");
function place_id_to_hierarchy ($id) {
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
	return (int) WT_DB::prepare("SELECT MAX(gov_id) FROM `##gov`")->fetchOne();
}

// ----------------------------------------------------------------------------------------------------
$where_am_i = place_id_to_hierarchy($placeid);
// echo '[placeid=', $placeid, '] ';
// var_dump($where_am_i);
$level=count($where_am_i);
$link = 'module.php?mod=gov&amp;mod_action=admin_places&amp;parent='.$placeid;

if ($action=='addrecord' && WT_USER_IS_ADMIN) {
	$statement=
		WT_DB::prepare("INSERT INTO `##gov` (gov_id, gov_parent_id, gov_level, gov_place, gov_govid) VALUES (?, ?, ?, ?, ?)");

	if (($_POST['LONG_CONTROL'] == '') || ($_POST['NEW_PLACE_LONG'] == '')) {
		$statement->execute(array(getHighestIndex()+1, $placeid, $level, $_POST['NEW_PLACE_NAME'], null, null));
	} else {
		$statement->execute(array(getHighestIndex()+1, $placeid, $level, $_POST['NEW_PLACE_NAME'], $_POST['LONG_CONTROL'][3].$_POST['NEW_PLACE_LONG']));
	}

	// autoclose window when update successful unless debug on
	if (!WT_DEBUG) {
		$controller->addInlineJavaScript('closePopupAndReloadParent();');
	}
	echo "<div class=\"center\"><button onclick=\"closePopupAndReloadParent();return false;\">", WT_I18N::translate('close'), "</button></div>";
	exit;
}

if ($action=='updaterecord' && WT_USER_IS_ADMIN) {
	$statement=
		WT_DB::prepare("UPDATE `##gov` SET gov_place=?, gov_govid=? WHERE gov_id=?");

	if (($_POST['LONG_CONTROL'] == '') || ($_POST['NEW_PLACE_LONG'] == '')) {
		$statement->execute(array($_POST['NEW_PLACE_NAME'], null, null, $placeid));
	} else {
		$statement->execute(array($_POST['NEW_PLACE_NAME'], $_POST['LONG_CONTROL'][3].$_POST['NEW_PLACE_LONG'], $placeid));
	}

	// autoclose window when update successful unless debug on
	if (!WT_DEBUG) {
		$controller->addInlineJavaScript('closePopupAndReloadParent();');
	}
	echo "<div class=\"center\"><button onclick=\"closePopupAndReloadParent();return false;\">", WT_I18N::translate('close'), "</button></div>";
	exit;
}

if ($action=="update") {
	// --- find the place in the file
	$row=
		WT_DB::prepare("SELECT gov_place, gov_govid, gov_parent_id, gov_level FROM `##gov` WHERE gov_id=?")
		->execute(array($placeid))
		->fetchOneRow();
	$place_name = $row->gov_place;
	//echo '[place_name=', $place_name, '] ';

	$parent_id = $row->gov_parent_id;
	$level = $row->gov_level;

	do {
		$row=
			WT_DB::prepare("SELECT gov_parent_id FROM `##gov` WHERE gov_id=?")
			->execute(array($parent_id))
			->fetchOneRow();
		if (!$row) {
			break;
		}
		$parent_id = $row->gov_parent_id;
	} 
	while ($row->gov_parent_id!=0);

	$success = false;

	echo '<b>', htmlspecialchars(str_replace('unknown', WT_I18N::translate('unknown'), implode(WT_I18N::$list_separator, array_reverse($where_am_i, true)))), '</b><br>';
}

if ($action=='add') {
	// --- find the parent place in the file
	if ($placeid != 0) {
		if (!isset($place_name)) $place_name  = '';
		$parent_id = $placeid;
		do {
			$row=
				WT_DB::prepare("SELECT gov_govid, gov_parent_id, gov_level FROM `##gov` WHERE gov_id=?")
				->execute(array($parent_id))
				->fetchOneRow();
			$parent_id = $row->gov_parent_id;
		} while ($row->gov_parent_id!=0);
	}
	else {
		if (!isset($place_name)) $place_name  = '';
		$parent_id = 0;
		$level = 0;
	}
	
	$success = false;

	if (!isset($place_name) || ($place_name == "")) echo '<b>', WT_I18N::translate('unknown');
	else echo '<b>', $place_name;
	if ( $level > 0)
		echo ', ', htmlspecialchars(str_replace('unknown', WT_I18N::translate('unknown'), implode(WT_I18N::$list_separator, array_reverse($where_am_i, true)))), '</b><br>';
	echo '</b><br>';
}

// include_once 'wt_v3_places_edit.js.php';
// $api='v3';

?>

<form method="post" id="editplaces" name="editplaces" action="module.php?mod=gov&amp;mod_action=places_edit">
	<input type="hidden" name="action" value="<?php echo $action; ?>record">
	<input type="hidden" name="placeid" value="<?php echo $placeid; ?>">
	<input type="hidden" name="level" value="<?php echo $level; ?>">
	<input type="hidden" name="parent_id" value="<?php echo $parent_id; ?>">

	<table class="facts_table">
        <tr>
            <td class="descriptionbox"><?php echo WT_Gedcom_Tag::getLabel('PLAC'); ?>
            </td>
            <td class="optionbox"><input type="text" id="new_gov_name" name="NEW_PLACE_NAME" value="<?php echo htmlspecialchars($place_name); ?>" size="25" class="address_input">
                <div id="INDI_PLAC_pop" style="display: inline;">
                <?php echo print_specialchar_link('NEW_PLACE_NAME'); ?></div>
            </td>
            <td class="optionbox">
                <label for="new_gov_name"><a href="#" onclick="showLocation_all(document.getElementById('new_gov_name').value); return false">&nbsp;<?php echo WT_I18N::translate('Search'); ?></a></label>
            </td>
        </tr>
        <tr>
            <td colspan="3">  
                <table class="gov_table"> 
                	<?php
						$hits = search_places($place_name, $level);
						if ( count($hits) == 0 ) {
							echo WT_I18N::translate('No GOV data found. Please log in to GOV and enter your place there.'); // tbd: add link to GOV or prepare GOV entry and use GOV write webservice
						} else {
							echo '<tr>';
								echo '<th>', WT_I18N::translate('Name'), '</th>';
								echo '<th>', WT_I18N::translate('Type'), '</th>';
								echo '<th>', WT_I18N::translate('belongs to'), '</th>';
								echo '<th>', WT_I18N::translate('GOV id'), '</th>';
							echo '</tr>';
							
							$target = 'gov'; // tbd: use global variable target // tbd: doesn't open in new window !!!
							$lang = '?lang=' . $language_ISO639_1_to_ISO639_2B[WT_LOCALE]['UI_GOV'];
							foreach ($hits as $id=>$nth) {
								echo '<tr>';
									echo '<td>', $nth['name'], '</td>';
									echo '<td>', $nth['type'], '</td>';
									echo '<td>', $nth['hierarchy'], '</td>';
									echo '<td>', '<a href="https://gov.genealogy.net/item/show/', $id, $lang, '" target=', $target, '>', $id, '</a>', '</td>';
								echo '</tr>';
							}
						}
					?>
                </table>
            </td>
        </tr>
	</table>
    <?php // GovTools::show_gov_search_types(); // debug/check the used gov_search_types  ?>
    <p>&nbsp;</p><p>&nbsp;</p>

	<p id="save-cancel">
		<input type="submit" class="save" value="<?php echo WT_I18N::translate('save'); ?>">
		<input type="button" class="cancel" value="<?php echo WT_I18N::translate('close'); ?>" onclick="window.close();">
	</p>
</form>
