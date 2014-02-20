<?php
// tbd: check if these functions are needed !
//
// webtrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010  PGV Development Team. All rights reserved.
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

require WT_ROOT.WT_MODULES_DIR.$this->getName().'/gov.php';
require WT_ROOT.WT_MODULES_DIR.$this->getName().'/defaultconfig.php';

function place_id_to_hierarchy($id) {
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

function get_placeid($place) {
	$par = explode (",", strip_tags($place));
	$par = array_reverse($par);
	$place_id = 0;
	for ($i=0; $i<count($par); $i++) {
		$par[$i] = trim($par[$i]);
		if (empty($par[$i])) $par[$i]="unknown";
		$placelist = create_possible_place_names($par[$i], $i+1);
		foreach ($placelist as $key => $placename) {
			$gov_id=
				WT_DB::prepare("SELECT gov_id FROM `##gov` WHERE gov_level=? AND gov_parent_id=? AND gov_place LIKE ? ORDER BY gov_place")
				->execute(array($i, $place_id, $placename))
				->fetchOne();
			if (!empty($gov_id)) break;
		}
		if (empty($gov_id)) break;
		$place_id = $gov_id;
	}
	return $place_id;
}

function get_p_id($place) {
	$par = explode (",", $place);
	$par = array_reverse($par);
	$place_id = 0;
	for ($i=0; $i<count($par); $i++) {
		$par[$i] = trim($par[$i]);
		$placelist = create_possible_place_names($par[$i], $i+1);
		foreach ($placelist as $key => $placename) {
			$gov_id=
				WT_DB::prepare("SELECT p_id FROM `##places` WHERE p_parent_id=? AND p_file=? AND p_place LIKE ? ORDER BY p_place")
				->execute(array($place_id, WT_GED_ID, $placename))
				->fetchOne();
			if (!empty($gov_id)) break;
		}
		if (empty($gov_id)) break;
		$place_id = $gov_id;
	}
	return $place_id;
}

function set_placeid_map($level, $parent) {
	if (!isset($levelm)) {
		$levelm=0;
	}
	$fullplace = "";
	if ($level==0)
		$levelm=0;
	else {
		for ($i=1; $i<=$level; $i++) {
			$fullplace .= $parent[$level-$i].", ";
		}
		$fullplace = substr($fullplace, 0, -2);
		$levelm = get_p_id($fullplace);
	}
	return $levelm;
}

function set_levelm($level, $parent) {
	if (!isset($levelm)) {
		$levelm=0;
	}
	$fullplace = "";
	if ($level==0)
		$levelm=0;
	else {
		for ($i=1; $i<=$level; $i++) {
			if ($parent[$level-$i]!="")
				$fullplace .= $parent[$level-$i].", ";
			else
				$fullplace .= "unknown, ";
		}
		$fullplace = substr($fullplace, 0, -2);
		$levelm = get_placeid($fullplace);
	}
	return $levelm;
}

function check_were_am_i($numls, $levelm) {
	$where_am_i=place_id_to_hierarchy($levelm);
	$i=$numls+1;
	if (!isset($levelo)) {
		$levelo[0]=0;
	}
	foreach (array_reverse($where_am_i, true) as $id=>$place2) {
		$levelo[$i]=$id;
		$i--;
	}
	return $levelo;
}

function check_place($place_names, $place) {
	if ($place == "unknown") $place="";
	if (in_array($place, $place_names)) {
		return true;
	}
}
