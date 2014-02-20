<?php
// GOV module for webtrees
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
// $Id: gov.php  2013-11-13 00:29:07 Hermann Hartenthaler $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

function gov_rem_prefix_from_placename($prefix_list, $place, $placelist) {
	if ($prefix_list) {
		foreach (explode(';', $prefix_list) as $prefix) {
			if ($prefix && substr($place, 0, strlen($prefix)+1)==$prefix.' ') {
				$placelist[] = substr($place, strlen($prefix)+1);
			}
		}
	}
	return $placelist;
}

function gov_rem_postfix_from_placename($postfix_list, $place, $placelist) {
	if ($postfix_list) {
		foreach (explode (';', $postfix_list) as $postfix) {
			if ($postfix && substr($place, -strlen($postfix)-1)==' '.$postfix) {
				$placelist[] = substr($place, 0, strlen($place)-strlen($postfix)-1);
			}
		}
	}
	return $placelist;
}

function gov_rem_prefix_postfix_from_placename($prefix_list, $postfix_list, $place, $placelist) {
	if ($prefix_list && $postfix_list) {
		foreach (explode (";", $prefix_list) as $prefix) {
			foreach (explode (";", $postfix_list) as $postfix) {
				if ($prefix && $postfix && substr($place, 0, strlen($prefix)+1)==$prefix.' ' && substr($place, -strlen($postfix)-1)==' '.$postfix) {
					$placelist[] = substr($place, strlen($prefix)+1, strlen($place)-strlen($prefix)-strlen($postfix)-2);
				}
			}
		}
	}
	return $placelist;
}

function gov_create_possible_place_names ($placename, $level) {
	global $GOV_PREFIX, $GOV_POSTFIX;

	$retlist = array();
	if ($level<=9) {
		$retlist = gov_rem_prefix_postfix_from_placename($GOV_PREFIX[$level], $GOV_POSTFIX[$level], $placename, $retlist); // Remove both
		$retlist = gov_rem_prefix_from_placename($GOV_PREFIX[$level], $placename, $retlist); // Remove prefix
		$retlist = gov_rem_postfix_from_placename($GOV_POSTFIX[$level], $placename, $retlist); // Remove suffix
	}
	$retlist[]=$placename; // Exact

	return $retlist;
}

// calculate distance between two geo locations
function gov_distance($lat1, $lng1, $lat2, $lng2, $miles = FALSE) { // coordinates in ° 
	$a = 6378.137; // in km
	$q = 1 / 298.257223563;
	$pi180 = M_PI / 180;
	
	$lat1 *= $pi180;
	$lng1 *= $pi180;
	$lat2 *= $pi180;
	$lng2 *= $pi180;
	
	// method 1
	// $r = 6372.797; // mean radius of Earth in km for method 1
	// $dlat = $lat2 - $lat1;
	// $dlng = $lng2 - $lng1;
	// $p = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
	// $km = $r * 2 * atan2(sqrt($p), sqrt(1 - $p));
	
	// method 2
	// uses WGS84 reference ellipsoid
	$F = ($lat1 + $lat2) / 2;
	$G = ($lat1 - $lat2) / 2;
	$l = ($lng1 - $lng2) / 2;
	$S = pow(sin($G),2) * pow(cos($l),2) + pow(cos($F),2) * pow(sin($l),2);
	$C = pow(cos($G),2) * pow(cos($l),2) + pow(sin($F),2) * pow(sin($l),2);
	$w = atan2(sqrt($S), sqrt($C));
	$D = 2 * $w *$a;
	$R = sqrt($S * $C) / $w;
	$H1 = (3 * $R - 1) / (2 * $C);
	$H2 = (3 * $R + 1) / (2 * $S);
	$x1 = $H1 * pow(sin($F),2) * pow(cos($G),2);
	$x2 = $H2 * pow(cos($F),2) * pow(sin($G),2);
	$km = $D * (1 + $q * ($x1 - $x2));
	
	return ($miles ? ($km * 0.621371192) : $km);
}

function gov_get_lati_long_placelocation ($place) {
	$parent = explode (',', $place);
	$parent = array_reverse($parent);
	$place_id = 0;
	$ex = FALSE;
	for ($i=0; $i<count($parent); $i++) {
		$parent[$i] = trim($parent[$i]);
		if (empty($parent[$i])) $parent[$i]='unknown';// GoogleMap module uses "unknown" while GEDCOM uses , ,
		$placelist = gov_create_possible_place_names($parent[$i], $i+1);
		foreach ($placelist as $placename) {
			try {
				$pl_id=
					WT_DB::prepare("SELECT pl_id FROM `##placelocation` WHERE pl_level=? AND pl_parent_id=? AND pl_place LIKE ? ORDER BY pl_place")
					->execute(array($i, $place_id, $placename))
					->fetchOne();
			} catch (PDOException $ex) {
				// echo '[ex=', $ex, '] ';
				break;
			}
			if (!empty($pl_id)) break;
		}
		if (empty($pl_id)) break;
		$place_id = $pl_id;
	}

	if (!$ex) {
		return
			WT_DB::prepare("SELECT pl_lati, pl_long, pl_level FROM `##placelocation` WHERE pl_id=? ORDER BY pl_place")
			->execute(array($place_id))
			->fetchOneRow();
	} else return;
}

function gov_get_gov_id ($place) {
	$parent = explode (',', $place);
	$parent = array_reverse($parent);
	$place_id = 0;
	for ($i=0; $i<count($parent); $i++) {
		$parent[$i] = trim($parent[$i]);
		// echo '[parent'.$i.'='.$parent[$i].']  ';
		if (empty($parent[$i])) $parent[$i]='unknown'; // GOV module uses "unknown" while GEDCOM uses , ,
		$placelist = gov_create_possible_place_names($parent[$i], $i+1);
		// echo '[placelist='; var_dump($placelist); echo ']  ';
		foreach ($placelist as $key => $placename) {
			$gov_id=
				WT_DB::prepare("SELECT gov_id FROM `##gov` WHERE gov_level=? AND gov_parent_id=? AND gov_place LIKE ? ORDER BY gov_place")
				->execute(array($i, $place_id, $placename))
				->fetchOne();
			// echo '[gov_id='.$gov_id.']  ';
			if (!empty($gov_id)) break;
		}
		if (empty($gov_id)) break;
		$place_id = $gov_id;
	}
	// echo '[gov_id='.$gov_id.']  ';
	$row=
		WT_DB::prepare("SELECT gov_govid, gov_level FROM `##gov` WHERE gov_id=? ORDER BY gov_place")
		->execute(array($place_id))
		->fetchOneRow();
	if ($row) {
		return array('govid'=>$row->gov_govid, 'level'=>$row->gov_level);
	} else {
		return array();
	}
}

function checkMapData() {
	global $controller;
	$xrefs="'".$controller->record->getXref()."'";
	// echo '[xrefs='.$xrefs.']';
	$families = $controller->record->getSpouseFamilies();
	// var_dump($families);
	foreach ($families as $family) {
		$xrefs.=", '".$family->getXref()."'";
	}
	// echo '[xrefs='.$xrefs.']';
	return WT_DB::prepare("SELECT COUNT(*) AS tot FROM `##placelinks` WHERE pl_gid IN (".$xrefs.") AND pl_file=?")
		->execute(array(WT_GED_ID))
		->fetchOne();
}

function list_files_dir($dir) { // tbd: function is not used, so delete it
	global $controller;

	$result = array();
	if (is_dir($dir)) {
		$files = scandir($dir);
		foreach($files as $file) 	{
			if(!(is_dir($dir."/$file"))) {
				$result[] = $file;
			}
		}
	} else GOVaddMessage($controller, 'error', WT_I18N::translate('Path %s for pdf documents does not exist.', $dir));
	return $result;
}

// Fetch a list of all pdf documents in the database
function all_pdf_objects($media_folder, $media_path, $filter) {
	return WT_DB::prepare(
		"SELECT SQL_CACHE SQL_CALC_FOUND_ROWS TRIM(LEADING ? FROM m_filename) AS media_path, 'OBJE' AS type, m_titl, m_id AS xref, m_file AS ged_id, m_gedcom AS gedrec, m_filename" .
		" FROM  `##media`" .
		" JOIN  `##gedcom_setting` ON (m_file = gedcom_id AND setting_name = 'MEDIA_DIRECTORY')" .
		" JOIN  `##gedcom`         USING (gedcom_id)" .
		" WHERE setting_value=?" .
		" AND   m_filename LIKE CONCAT(?, ?, '%', '.pdf')" .
		// " AND   (SUBSTRING_INDEX(m_filename, '/', -1) LIKE CONCAT(?, '%', '.pdf')" .
		// "  OR   m_titl LIKE CONCAT('%', ?, '%')" .
		// ")" .
		" AND   m_filename NOT LIKE 'http://%'" .
		" AND   m_filename NOT LIKE 'https://%'"
	)->execute(array($media_path, $media_folder, $media_path, $filter))->fetchAll();
}

function search_pdf_object($govid) {
	global $MEDIA_DIRECTORY, $GOV_PATH_PDF;

	$media = null;
	$pdf_object = null;
	$default_object = null;
	$rows = all_pdf_objects($MEDIA_DIRECTORY, $GOV_PATH_PDF, $govid);
	// var_dump($rows);
	foreach ($rows as $key=>$row) {
		$parts = explode('.', $row->media_path);
		// var_dump($parts);
		if (($parts[0] == $govid) && ($parts[count($parts)-1] == 'pdf')) { // this should be always true
			if ($parts[1] == WT_LOCALE) {
				$pdf_object = $rows[$key];
				break;
			} else if (($parts[1] == 'pdf') || ($parts[1] == '')) { // use this as default if there is no language specific file
				$default_object = $rows[$key];
			}
		}
	}
	if (!isset($pdf_object)) {
		// tbd: search in the database for media objects with a title with the format "<gov-id>.<language>", where <gov-id> is the mandatory GOV id and the second part is the optional language code; the corresponding file has to be in the places/docs/ subfolder of the $MEDIA_DIRECTORY and has to be a pdf file.
		// prio 1 = new found file where in the title both fits (gov-id and language)
		// prio 2 = already found $default_object if this is set
		// prio 3 = new found file where in the title the gov-id fits
		if (isset($default_object)) $media = WT_Media::getInstance($default_object->xref, $default_object->ged_id);
	} else {
		$media = WT_Media::getInstance($pdf_object->xref, $pdf_object->ged_id);
	}
	return $media;
}

function search_thumbnail($pdf_file) { // $pdf_file is filename without path   // tbd: function is not used, so delete it
	global $MEDIA_DIRECTORY, $GOV_PATH_PDF;

	$dir = WT_DATA_DIR.$MEDIA_DIRECTORY.'thumbs'.DIRECTORY_SEPARATOR.$GOV_PATH_PDF;
	$result = null;
	if (isset($pdf_file) && $pdf_file !== '') {
		foreach (array('.png','.jpg') as $filetype) {
			if (file_exists(str_replace('.pdf', $filetype, $dir.$pdf_file))) $result = str_replace('.pdf', $filetype, $dir.$pdf_file);
		}
	}
	return $result;
}

function show_pdf_thumbnail($mediaobject, $mediatitle, $thumb, $thumbsize, $thumb_default) {  // tbd: this function has to be rewritten
	$square = false;
	if ($mediaobject) {
		$html = '';
		if($thumb == true) {
			$mediasrc = $mediaobject->getServerFilename();
			$thumbwidth = $thumbsize; $thumbheight = $thumbsize;
			$type = $mediaobject->mimeType();
			if($type == 'image/jpeg' || $type == 'image/png') {

				list($width_orig, $height_orig) = @getimagesize($mediasrc);

				switch ($type) {
					case 'image/jpeg':
						$image = @imagecreatefromjpeg($mediasrc);
						break;
					case 'image/png':
						$image = @imagecreatefrompng($mediasrc);
						break;
				}

				// fallback if image is in the database but not on the server
				if(isset($width_orig) && isset($height_orig)) {
					$ratio_orig = $width_orig/$height_orig;
				}
				else {
					$ratio_orig = 1;
				}

				if($square == true) {
					if ($thumbwidth/$thumbheight > $ratio_orig) {
					   $new_height = $thumbwidth/$ratio_orig;
					   $new_width = $thumbwidth;
					} else {
					   $new_width = $thumbheight*$ratio_orig;
					   $new_height = $thumbheight;
					}
				}
				else {
					if ($width_orig > $height_orig) {
						$new_height = $thumbheight/$ratio_orig;
						$new_width 	= $thumbwidth;
					} elseif ($height_orig > $width_orig) {
					   $new_width 	= $thumbheight*$ratio_orig;
					   $new_height 	= $thumbheight;
					} else {
						$new_width 	= $thumbwidth;
						$new_height = $thumbheight;
					}
				}

				$process = @imagecreatetruecolor(round($new_width), round($new_height));

				@imagecopyresampled($process, $image, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
				$square == true ? $thumb = imagecreatetruecolor($thumbwidth, $thumbheight) : $thumb = imagecreatetruecolor($new_width, $new_height);
				@imagecopyresampled($thumb, $process, 0, 0, 0, 0, $thumbwidth, $thumbheight, $thumbwidth, $thumbheight);

				@imagedestroy($process);
				@imagedestroy($image);

				$square == true ? $width = round($thumbwidth) : $width = round($new_width);
				$square == true ? $height = round($thumbheight) : $height = round($new_height);
				ob_start();imagejpeg($thumb,null,100);$thumb = ob_get_clean(); // the thumbnails are always of the type jpeg.
				$html = '<a' .
						' class="'          	. 'gallery'                         			 	. '"' .
						' href="'           	. $mediaobject->getHtmlUrlDirect('main')    		. '"' .
						' type="'           	. $mediaobject->mimeType()                  		. '"' .
						' data-obje-url="'  	. $mediaobject->getHtmlUrl()                		. '"' .
						' data-obje-note="' 	. htmlspecialchars($mediaobject->getNote())			. '"' .
						' data-obje-xref="'		. $mediaobject->getXref()							. '"' .
						' data-title="'     	. strip_tags($mediaobject->getFullName())   		. '"' .
						'><img src="data:image/jpeg;base64,'.base64_encode($thumb).'" dir="auto" title="'.$mediatitle.'" alt="'.$mediatitle.'" width="'.$width.'" height="'.$height.'"/></a>'; // need size to fetch it with jquery (for pdf conversion)
			}
		}
		else {
			$html = $mediaobject->displayImage();
		}
		return $html;
	}
}

function show_link_gov($search, $lang, $title, $target, $thumbnail, $icon_height) {
	$html = '<a href="https://gov.genealogy.net/item/show/'.$search.'?lang='.$lang.'" target="'.$target.'">';
	$html .= '<div title="'.$title.'"><span>';
	$html .= '<img src="'.$thumbnail.'" height="'.$icon_height.'" alt="'.$title.'"></img>&nbsp;';
	$html .= WT_I18N::translate('Link to GOV');
	$html .= '</span></div>';
	$html .= '</a>';
	return $html;
}

function search_images_piwigo($place) {
	global $GOV_PATH_PIWIGO;
	if (substr($GOV_PATH_PIWIGO,strlen($GOV_PATH_PIWIGO)-1,1) !== "/") $GOV_PATH_PIWIGO .= "/";
	$piwigo_error_msg = array(
		'not_configured'		=> WT_I18N::translate('Piwigo URL must be configured at GOV preferences page (tab "Links").'),
		'http_get_failed'		=> WT_I18N::translate('Error while reading from Piwigo web-service at %s. Please verify configuration and try again.', $GOV_PATH_PIWIGO),
		'ws_status_bad'			=> WT_I18N::translate('Piwigo web-service status is not ok.'),      
		'get_images_failed'		=> WT_I18N::translate('Error reading image information using Piwigo web-service.'),
		);
	$results = array();
	$i = 0;
	foreach (array('permalink', 'tag') as $type) {
		$results[$i] = piwigo_check($type, $place);
		if ($results[$i]['status'] !== "ok") {
			GOVaddMessage(null, 'error', $piwigo_error_msg[$results[$i]['status']]);
		}
		$i++;
	}

	// check for duplicated images
	if (($results[0]['found'] > 0) && ($results[1]['found'] > 0)) {
		foreach ($results[1]['images'] as $image1) {
			foreach ($results[0]['images'] as $image0) {
				if ($image1->page_url == $image0->page_url) {
					// echo '[duplicated image=', $image1->page_url, '] ';
					$image1->page_url = '###duplicated###';
					$results[1]['found']--;
					break;
				}
			}
		}
		// echo '[remaining found tagged images=', $results[1]['found'], '] ';
		// var_dump($results);
		// remove duplicated images if necessary
		if ($results[1]['found'] > 0) {
			foreach ($results[1]['images'] as $key => $row) {
				if ($row->page_url == '###duplicated###') {
					 unset($results[1]['images'][$key]); 
				}  
			}
			$results[1]['images'] = array_values($results[1]['images']); // reindex the image array
		}
	}
	return $results;
}

function show_link_piwigo($path, $lang, $title, $title2, $target, $thumbnail, $icon_height, $text) {
	$html = '<a href="'.$path.'&lang='.$lang.'" target="'.$target.'">';
	// echo '[html=', str_replace('<', '§', $html), '] ';
	$html .= '<div title="'.$title.'"><div title="'.$title2.'"><span>';
	$html .= '<img src="'.$thumbnail.'" height="'.$icon_height.'" alt="'.$title.'"></img>&nbsp;';
	$html .= $text;
	$html .= '</span></div></div>';
	$html .= '</a>';
	return $html;
}

function show_thumbnails_piwigo($results, $title, $target) {
	foreach ($results as $result) {
		if ($result['found'] > 0) {
			$html = '<div title="'.$title.'"><ul>';
			foreach ($result['images'] as $image) {
				// echo '[image-thumb=', $image->derivatives->thumb->url, ' / ', $image->derivatives->thumb->width, ' / ', $image->derivatives->thumb->height, '] ';
				$html .= '<li><a href="'.$image->page_url.'" target="'.$target.'">';
				$html .= '<div title="'.$title.': '.$image->name.'"><span>';
				$html .= '<img src="'.$image->derivatives->thumb->url.'" width="'.$image->derivatives->thumb->width.'px" height="'.$image->derivatives->thumb->height.'px" alt="'.$image->name.'"></img>&nbsp;';
				$html .= $image->name;
				// echo '[comment-length=', strlen($image->comment), '] ';
				if (strlen($image->comment) > 0) $html .= ' ('.$image->comment.')';
				$html .= '</span></div>';
				$html .= '</a></li>';
			}
			$html .= '</ul></div>';
		}
	}
	return $html;
}

function show_link_geohack($search, $lang, $title, $target, $thumbnail, $icon_height, $text, $pagename) {
	$html = '<a href="https://toolserver.org/~geohack/geohack.php?language='.$lang;
	$html .= '&amp;params='.$search.'_type:city(8000000)&amp;pagename='.$pagename.'" target="'.$target.'">';
	$html .= '<div title="'.$title.'"><span>';
	$html .= '<img src="'.$thumbnail.'" height="'.$icon_height.'" alt="'.$title.'"></img>&nbsp;';
	$html .= $text;
	$html .= '</span></div>';
	$html .= '</a>';
	return $html;
}

function search_string_delcampe($place, $place_name) {
	$or = '%7C';
	$plus = '%2B'; // to mask blanks inside a place name
	$dq = '%22'; // double quote = %22 = " to escape "-" in place names, like Baden-Baden or to seperate parts when using the "or" operator
	$place_array = explode(',', $place);
	$place_array = explode('>', $place_array[0]); // remove "<span dir="auto">" in front of the name
	$place_array = explode(';', $place_array[1]); // select part after <span> and search for ";" which may be used inside a place name
	$place1 = delete_accents($place_name); // delcampe search does not support accents or umlauts
	$place2 = delete_accents($place_array[0]); // select frist part of place name
	// echo '[place1=', $place1, '/', strlen($place1), '] ';
	// echo '[place2=', $place2, '/', strlen($place2), '] ';
	$use_place2 = false;
	if ((strpos($place2, $place1) === false) && (strpos($place1, $place2) === false) && ($place2 !== $place_name)) $use_place2 = true;
	if ($place1 !== $place_name) $place1 = $dq.$place1.$dq.$or.$dq.urlencode($place_name).$dq; // if there are accents in $place_name try a urlencoded version too
	if ((strpos($place1, $dq) === false) && (strpos($place1, '-') !== false)) $place1 = $dq.$place1.$dq; // for place names like Baden-Baden
	if ((strpos($place2, $dq) === false) && (strpos($place2, '-') !== false)) $place2 = $dq.$place2.$dq;
	// echo '[place1=', $place1, '] ';
	// echo '[place2=', $place2, '] ';
	if ($use_place2) {
		if (strpos($place1, $dq) === false) $place1 = $dq.$place1.$dq;
		if (strpos($place2, $dq) === false) $place2 = $dq.$place2.$dq;
		$search = $place1.$or.$place2;
	} else {
		$search = ((strlen($place1) < strlen($place2)) ? $place1 : $place2);
	}
	$search = str_replace(' ', $plus, $search);
	// echo '[search=', $search, '] ';
	return $search;
}

function show_link_delcampe($search, $lang, $title, $target, $thumbnail, $icon_height, $text) {
	$html = '<a href="http://www.delcampe.net/items?language='.$lang;
	$html .= '&searchString='.$search.'&catLists%5B%5D=-2&searchOptionForm%5BsearchMode%5D=extended&searchOptionForm%5BsearchInDescription%5D=Y" ';
	$html .= 'target="'.$target.'">';
	$html .= '<div title="'.$title.'"><span>';
	$html .= '<img src="'.$thumbnail.'" height="'.$icon_height.'" alt="'.$title.'"></img>&nbsp;';
	$html .= $text;
	$html .= '</span></div>';
	$html .= '</a>';
	return $html;
}

function get_family_persons () {
	global $controller;
	// tbd: compare this code with the code of Fancy Familytree to find the people (check visibility rights) !
	
	$famids = array();
	$famids[] = array($controller->record->getXref(), 'own-id');
	
	$families = $controller->record->getSpouseFamilies();
	foreach ($families as $family) {
		$famids[] = array($family->getXref(), 'own-family', $family->getSpouse($controller->record)->getXref());
		foreach ($family->getChildren() as $child) {
			$famids[] = array($child->getXref(), 'child');
		}
	}
	return $famids;
}

function get_facts ($famids) {
	$indifacts = array();
	// new data structure of indifacts = array ($fact, $facttype, $id_person, [$id_spouse])
	// var_dump($famids);
	foreach ($famids as $famid) {
		$person_family = WT_Family::getInstance($famid[0]);
		switch($famid[1]) {
		case 'own-id':
			// echo '[own-id: famid1=', $famid[1], '] ';
			foreach ($person_family->getFacts() as $fact) {
				$indifacts[] = array($fact, 'own-id', $famid[0]);
			}		
			break;
		case 'own-family':
			// echo '[own-id: famid1=', $famid[1], '] ';
			foreach ($person_family->getFacts() as $fact) {
				$indifacts[] = array($fact, 'own-family', $famid[0], $famid[2]);
			}		
			break;
		case 'child':
			// echo '[child: famid1=', $famid[1], '] ';
			// tbd: fill $indifacts
			$indifacts[] = array($person_family->getFirstFact('BIRT'), 'child', $famid[0]);
			break;
		}
	}
	// sort_facts($indifacts);  // tbd: modify sort_facts
	return $indifacts;
}

function get_information ($indifacts) {
	global $gov_client; // tbd: used in function show_gov_place - eliminate!
	global $controller;
	require_once('GovTools.php');
	
	$markers = array();
	$places = array();
	
	$i = 0; // index $indifacts
	// var_dump($indifacts);
	foreach ($indifacts as $factrec) {
		$fact										= $factrec[0];
		$fact_type									= $factrec[1];
		$famid										= $factrec[2];
		if ($fact_type == 'own-family') $spouse_id	= $factrec[3];
		// echo '[famid=', $famid, '] ';
		// echo '[fact_type=', $fact_type, '] ';
		// if ($fact_type == 'child') var_dump($fact);
		
		if (isset($fact) && !$fact->getPlace()->isEmpty()) {
			// check if there is a _GOV record (only the first redord of this type is used) or a
			// NOTE record with GOV information (search is done only for the first NOTE GOV record)
			//var_dump($fact->getGedcom());
			$ctgov1 = preg_match("/\d _GOV (.*)/", $fact->getGedcom(), $match01);
			//echo '[ctgov1='.$ctgov1.'] ';
			//var_dump($match01);
			if ($ctgov1) {
				$checkedId = $gov_client->checkObjectId($match01[1]);
				if($match01[1] == $checkedId) {
				} else if( $checkedId == '' ) {
					$ctgov1 = FALSE;
					GOVaddMessage($controller, 'error', WT_I18N::translate('GOV id %s in "_GOV" record is invalid.', $match01[1]));
				} else {
					GOVaddMessage($controller, 'error', WT_I18N::translate('GOV id %s in "_GOV" record has been replaced with %s.', $match01[1], $checkedId));
					$match01[1] = $checkedId;
				}
			}
			
			// echo strlen(trim($fact->getGedcom())); // length is 2 more than visible characters and trim dosen't solve this problem
			// var_dump($fact->getNotes()); // what does function getNotes ?
			$ctgov2 = preg_match("/\d NOTE GOV: (.*)/", $fact->getGedcom(), $match02);
			// var_dump($match02);

			if ($ctgov2) {
				$ctgov3 = preg_match("/GOV: http:\/\/gov\.genealogy\.net\/item\/show\/(.*)/", $match02[0], $match03);
				// echo '[ctgov3='.$ctgov3.'] ';
				// var_dump($match03);
				if ($ctgov3) {
					$match03[1] = substr($match03[1], 0, ((strpos($match03[1], '_')) ? 13 : 12));  // tbd: length is not always 12 or 13, so this is not ok! But sometimes match03 has only 12 visible characters, but a length of 14, which leads to problems checking this GOV id
					// var_dump($match02);
					// echo '§1[match02[1]=', $match02[1], '] [length=', strlen($match02[1]), '] ';
					// echo '§1[match03[1]=', $match03[1], '] [length=', strlen($match03[1]), '] ';
					$match02[1] = $match03[1];
				}
				// echo '[match02[1]=', $match02[1], '] [length=', strlen($match02[1]), '] ';
				$checkedId = $gov_client->checkObjectId($match02[1]);
				// echo '[checkedId=', $checkedId, '] ';
				if($match02[1] == $checkedId) {
				} else if( $checkedId == '' ) {
					$ctgov2 = FALSE;
					GOVaddMessage($controller, 'error', WT_I18N::translate('GOV id %s in NOTE GOV record is invalid.', $match02[1]));
				} else {
					GOVaddMessage($controller, 'error', WT_I18N::translate('GOV id %s in NOTE GOV record has been replaced with %s.', $match02[1], $checkedId));
					$match02[1] = $checkedId;
				}
			}
			//echo '[ctgov2='.$ctgov2.'] ';
			//var_dump($match02);
			if ($ctgov1) {
				if ($ctgov2) {
					if ($match01[1] !== $match02[1]) {
						$ctgov2 = FALSE;
						GOVaddMessage($controller, 'error', WT_I18N::translate('Inconsistent "_GOV %s" and "NOTE GOV %s" records.', $match01[1], $match02[1]));
					}
				}
			} else {
				if ($ctgov2) {
					$ctgov1 = $ctgov2;
					$match01[1] = $match02[1];
				}
			}
			//echo '[ctgov1='.$ctgov1.'] ';
			//echo '[match=', $match01[1], '] ';
			$govid_switch = "";
			if ($ctgov1) {
				$govid_switch = "record";
				// check if this GOV id from the GEDCOM record is the same in our database table
				$govid_level = gov_get_gov_id($fact->getPlace()->getGedcomName());
				// echo '[govid_level='; var_dump($govid_level); echo ']  ';
				if ((count($govid_level) != 0) && ($govid_level['govid'] != NULL)) {
					if ($match01[1] !== $govid_level['govid']) {
						GOVaddMessage($controller, 'error', WT_I18N::translate('Inconsistent "_GOV or NOTE GOV %s" records and internal GOV database (%s) records.', $match01[1], $govid_level['govid']));
						// GOV id in record has higher priority than entry in GOV database table
					}
				}
			} else {
				$govid_level = gov_get_gov_id($fact->getPlace()->getGedcomName());
				// echo '[govid_level='; var_dump($govid_level); echo ']  ';
				if ((count($govid_level) != 0) && ($govid_level['govid'] != NULL)) {
					$checkedId = $gov_client->checkObjectId($govid_level['govid']);
					// echo '[checkedId=', $checkedId, '] ';
					if($govid_level['govid'] == $checkedId) {
						$govid_switch = "database";
					} else if( $checkedId == '' ) {
						GOVaddMessage($controller, 'error', WT_I18N::translate('GOV id %s" in GOV database is invalid.', $govid_level['govid']));
					} else {
						GOVaddMessage($controller, 'error', WT_I18N::translate('GOV id "%s" in GOV database has been replaced with "%s".', $govid_level['govid'], $checkedId));
						$govid_level['govid'] = $checkedId;
						$govid_switch = "database";
					}
				}
			}
			if ($govid_switch == "record") $govid_level['govid'] = $match01[1];
			// echo '[govid_switch=', $govid_switch, '] ';
			
			$gov_place_names = array();
			if ($govid_switch !== '') {
				// echo '[govid=', $govid_level['govid'], '] ';
				$gov_object = $gov_client->getObject($govid_level['govid']);
				if (isset($gov_object->name)) {
					// tbd: decide if 'qual1' is meaningfull (BEF, FROM, ABT, ...)
					// tbd: decide if 'date2' is meaningfull
					// var_dump($fact->getDate());
					// var_dump($gov_object->name);
					// echo '[minJD=', $fact->getDate()->date1->minJD, '] ';
					// echo '[maxJD=', $fact->getDate()->date1->maxJD, '] ';
					$name_date_min = $gov_client->getNameAtDate($govid_level['govid'], $fact->getDate()->date1->minJD, 'deu');
					// echo '[name_date_min=', $name_date_min, '] ';
					$name_date_max = $gov_client->getNameAtDate($govid_level['govid'], $fact->getDate()->date1->maxJD, 'deu');
					// echo '[name_date_max=', $name_date_max, '] ';
					$date_min = jdtogregorian ($fact->getDate()->date1->minJD);
					// echo '[date_min=', $date_min, '] ';
					$date_max = jdtogregorian ($fact->getDate()->date1->maxJD);
					// echo '[date_max=', $date_max, '] ';
					
					foreach ($gov_object->name as $name) { // tbd: prepare GOV place names
						// var_dump($name);
						if (isset($name->value)) {
							// echo '[', $name->lang, '/', $name->value, '] ';
						// echo $name->{'begin-year'}, '/', $name->{'end-year'}, '] ';
						} else {
							// echo '[', $name, '] ';
						}
					}
				}
			}
			// tbd: remove these examples
			// $gov_place_names[] = array('time' => 'today', 'language' => 'deu', 'name' => 'Freiburg');
			// $gov_place_names[] = array('time' => 'today', 'language' => 'eng', 'name' => 'Fribourg');
			// $gov_place_names[] = array('time' => 'min', 'language' => '', 'name' => '', 'date' => $date_min);
			// $gov_place_names[] = array('time' => 'max', 'language' => '', 'name' => '', 'date' => $date_max);
			// var_dump($gov_place_names);
			
			// calculate and check lati/long
			// prio 1: GEDCOM MAP-LATI/LONG record; prio 2: placelocation from webtrees Google Maps Module; prio 3: GEDCOM ADDR record
			$lati = null;
			$long = null;
			$height = null;
			$ctstat= '';
			// prio 1: GEDCOM MAP-LATI/LONG record
			$ctla = preg_match("/\d LATI (.*)/", $fact->getGedcom(), $match11);
			$ctlo = preg_match("/\d LONG (.*)/", $fact->getGedcom(), $match12);

			if (!$ctla || !$ctlo) {
				// prio 2: placelocation from webtrees Google Maps Module
				$latlongval = gov_get_lati_long_placelocation($fact->getPlace()->getGedcomName());
				// var_dump($latlongval);
				if ($latlongval && $latlongval->pl_lati && $latlongval->pl_long) {
					$ctla = TRUE;
					$ctlo = TRUE;
					$ctstat= "GM module";
					$match11[1] = $latlongval->pl_lati;
					$match12[1] = $latlongval->pl_long;
				} else {
					// prio 3: GEDCOM ADDR record
					// check for coordinates of ADDR record using Google Maps™ API
					// http://www.google.com/permissions/
					// "... an unregistered Google Brand Feature should be followed by the superscripted letter TM  ..."
					$ctad1 = preg_match("/\d ADDR (.*)/", $fact->getGedcom(), $match21);
					// tbd: ADDR is more complex; see http://wiki-de.genealogy.net/GEDCOM/ADDR-Tag
					// supported usage: n ADDR <street> / n+1 CONT <city>
					// echo '[ctad1='.$ctad1.'] ';
					// var_dump($match21);
					if ($ctad1) {
						$google_search = $match21[1];
						$ctad2 = preg_match("/\d CONT (.*)/", $fact->getGedcom(), $match22);
						// echo '[ctad='.$ctad2.'] ';
						// var_dump($match22);
						if ($ctad2) $google_search = $google_search.', '.$match22[1];

						// try to get coordinates via Google Map API™
						require_once('google_maps.php');
						$Geocoder = new GoogleMapsGeocoder();
						$Geocoder->setAddress($google_search);
						$Geocoder->setLanguage(WT_LOCALE);
						$https = true;
						$response = $Geocoder->geocode($https);
						print_r($response);
						if ($response['status'] == "OK") {
							$ctla = TRUE;
							$ctlo = TRUE;
							$ctstat= "ADDR";
							$match11[1] = $response['results'][0]['geometry']['location']['lat'];
							$match12[1] = $response['results'][0]['geometry']['location']['lng'];
							$formatted_address = $response['results'][0]['formatted_address'];
							// echo '[formatted_address=', $formatted_address, '] ';
						}
					}
				}
			} else $ctstat= "MAP record";
			// echo '[ctstat='.$ctstat.'] ';
			// echo '[ctla='.$ctla.'] '; var_dump($match11);
			// echo '[ctlo='.$ctlo.'] '; var_dump($match12);

			if ($ctstat == '') {
				if ($govid_switch !== '') {
					// get $lati/$long from GOV location
					$gov_object = $gov_client->getObject(($govid_switch == "record") ? $match01[1] : $govid_level['govid']);
					// var_dump($gov_object);
					if (isset($gov_object->position)) {
						// echo '§1=';
						// var_dump($gov_object->position);
						$lati = $gov_object->position->lat;
						$long = $gov_object->position->lon;
						if (isset($gov_object->position->height)) $height = $gov_object->position->height;
						$ctstat = 'GOV';
					}
				}
			} else {
				$lati = str_replace(array('N', 'S', ','), array('', '-', '.') , $match11[1]);
				$long = str_replace(array('E', 'W', ','), array('', '-', '.') , $match12[1]);
				// echo '[lati/long=', $lati, '/', $long, '] ';
				
				if ($govid_switch !== '') {
					// check distance between $lati/$long and GOV location
					$gov_object = $gov_client->getObject(($govid_switch == "record") ? $match01[1] : $govid_level['govid']);
					// var_dump($gov_object);
					if (isset($gov_object->position)) {
						// echo '§2=';
						// var_dump($gov_object->position);
						$gov_lati = $gov_object->position->lat;
						$gov_long = $gov_object->position->lon;
						if (isset($gov_object->position->height)) $height = $gov_object->position->height;
						// echo '[lati='.$lati.'] '; echo '[long='.$long.'] ';
						// echo '[gov_lati='.$gov_lati.'] '; echo '[gov_long='.$gov_long.'] ';
						$km = round(gov_distance($lati, $long, $gov_lati, $gov_long), 3);
						// echo '[distance=', $km, '] ';
						$precision = array(200, 100, 15, 5, 2, 1, 1, 1, 1); // in km for hierarchie level 0, 1, 2, ...
						$limit = $precision[($govid_switch == "record") ? 3 : $govid_level['level']];
						// echo '[limit=', $limit, '] ';
						if ($km > $limit) {
							// tbd: depending on WT_LOCALE convert km to miles and replace "." by ","
							switch($ctstat) {
							case "ADDR":
								GOVaddMessage($controller, 'error', WT_I18N::translate('Distance between position of "%s" in your ADDR record and in GOV database is %s km.', $formatted_address, $km)); 
								break;
							case "MAP record":
								GOVaddMessage($controller, 'error', WT_I18N::translate('Distance between position of "%s" in your MAP record and in GOV database is %s km.', $fact->getPlace()->getFullName(), $km));
								break;
							case "GM module":
								GOVaddMessage($controller, 'error', WT_I18N::translate('Distance between position of "%s" in the webtrees Google Maps™ module and in GOV database is %s km.', $fact->getPlace()->getFullName(), $km));
								break;
							} // end switch
						} // end if
					} // end if
				} // end if
			} // end if

			if ($govid_switch !== '') {
				$i++;
				$markers[$i] = array(  // tbd: store fact information in $markers and place information in $places
					'class'				=> 'optionbox',
					'fact_label'		=> $fact->getLabel(),
					'info'				=> $fact->getValue(),
					'date'				=> $fact->getDate(),
					'image'				=> $fact->Icon(),
					'name'				=> '',
				);

				if (($fact_type == 'own-family') || ($fact_type == 'child')) {
					if ($fact_type == 'own-family') {
						$person = WT_Family::getInstance($spouse_id);
					} else {
						$person = WT_Family::getInstance($famid);
					}
					switch ($person->getSex()) {
					case'F':
						$markers[$i]['class'] = 'person_boxF';
						if ($fact_type == 'child') $markers[$i]['fact_label'] = WT_I18N::translate('birth of daughter');
						break;
					case 'M':
						$markers[$i]['class'] = 'person_box';
						if ($fact_type == 'child') $markers[$i]['fact_label'] = WT_I18N::translate('birth of son');
						break;
					default:
						$markers[$i]['class'] = 'person_boxNN';
						if ($fact_type == 'child') $markers[$i]['fact_label'] = WT_I18N::translate('birth of child');
						break;
					}
					$markers[$i]['image'] = $person->displayImage();
					$markers[$i]['name'] = '<a href="' . $person->getHtmlUrl() . '"' . $person->getFullName() . '</a>';
				} // end if
				
				$found = false;
				for ($j = 0; $j < count($places); $j++) {
					if ($govid_level['govid'] == $places[$j]['govid']) {
						$places[$j]['index'][] = $i;
						$found = true;
						break;
					}
				}
				if (!$found) $places[$j] = array(
					'govid'				=> $govid_level['govid'],
					'level'				=> ($govid_switch == "record") ? 3 : $govid_level['level'],
					'index'				=> array($i),
					'lati'				=> $lati,
					'long'				=> $long,
					'height'			=> $height,
					'place'				=> $fact->getPlace()->getFullName(),
					'gov_place_names'	=> $gov_place_names,
				);
				// var_dump($places);
			} // end if
		} // end if
	} // end foreach
	// echo '§1 [markers='; var_dump($markers); echo '] ';
	return array (	'places' => $places,
					'markers' => $markers);
}

function show_gov_place($place) {
	global $language_ISO639_1_to_ISO639_2B, $language_3_to_2;
	global $gov_client; // tbd: remove it (move code to function get_information
	
	$gov_object = $gov_client->getObject($place['govid']);
	// var_dump($gov_object);
	// echo "<pre>";
	// print_r($gov_object);
	// echo "</pre>";
	$language3 = $language_ISO639_1_to_ISO639_2B[WT_LOCALE]['letter3'];
	// echo '[language3='.$language3.']  ';
	// try webtrees language of user, "eng", "deu" and any language
	$prio_languages = array ($language3, 'eng', 'deu');
	$place_name = "";
	foreach ($prio_languages as $prio_language) {
		// echo '[prio_language='.$prio_language.']  ';
		if (count($gov_object->name) > 1) {
			foreach ($gov_object->name as $language) {
				// var_dump($language);
				// echo '[language->lang=', $language->lang, '] ';
				if ($language->lang == $prio_language) {
					// echo "found!";
					$place_name = $language->value;
					break;
				}
			}
			if ($place_name !== '') break;
		} else {
			$place_name = $gov_object->name->value;
			break;
		}
	}
	if ($place_name == "") {
		// tbd: what should happen if there is still no place_name definied
	}
	echo '<h1>', $place_name, '</h1>';
	echo '<p>', WT_I18N::translate('The name of this place in (%s) is %s.', $language_ISO639_1_to_ISO639_2B[$language_3_to_2[$prio_language]]['local'], $place_name), '</p>';
	
	$gov_date = unixtojd();
	$name_date = $gov_client->getNameAtDate($place['govid'], $gov_date, $language3);
	echo '<p>', WT_I18N::translate('The name of this place in your language (%s) is today %s.', $language_ISO639_1_to_ISO639_2B[WT_LOCALE]['local'], $name_date), '</p>'; // tbd: show only if different
	
	if (isset($place['lati']) && isset($place['long'])) {
		$la = ($place['lati'] >= 0) ? WT_I18N::translate('%s°N', $place['lati']) : WT_I18N::translate('%s°S', -$place['lati']);
		$lo = ($place['long'] >= 0) ? WT_I18N::translate('%s°E', $place['long']) : WT_I18N::translate('%s°W', -$place['long']);
		
		$html = '<h2>';
		$html .= WT_I18N::translate('Position');
		$html .= '</h2><p>';
		$html .= WT_I18N::translate('This place is located at  %s / %s.', $la, $lo);
		if (isset($place['height'])) $html .= ' '.WT_I18N::translate('The elevation is %s m.', $place['height']);
		$html .= '</p>';
		echo $html;
	}
	
	return $place_name;
}

function show_gov_links($place, $place_name) {
	global $language_ISO639_1_to_ISO639_2B;
	global $GOV_USE_LINK;
	require_once('piwigo.php');
	
	$icon_height = '20px'; // tbd: move to css or let the admin define it
	$target = 'gov'; // in which window external http links should be opened; other values are e.g. '_blank" or '_self'   // tbd: admin should be able to modify it
	
	$html = '<h2>';
	$html .= WT_I18N::translate('Links');
	$html .= '</h2>';
	$html .= '<ul>';
	
	// Link GOV
	$search = $place['govid'];
	$lang = $language_ISO639_1_to_ISO639_2B[WT_LOCALE]['UI_GOV'];
	$title = 'GOV';
	$thumbnail = WT_MODULES_DIR.'gov/images/computergenealogie.png';
	$html .= '<li>';
	$html .= show_link_gov($search, $lang, $title, $target, $thumbnail, $icon_height);
	$html .= '</li>';
	
	// Link Piwigo
	if ($GOV_USE_LINK['piwigo']) {
		$results = search_images_piwigo($place['govid']);
		if (($results[0]['found'] > 0) || ($results[1]['found'] > 0)) {
			$lang = $language_ISO639_1_to_ISO639_2B[WT_LOCALE]['UI_piwigo'];
			$title = 'Piwigo';
			$thumbnail = WT_MODULES_DIR.'gov/images/piwigo.png';
			foreach ($results as $result) {
				if ($result['found'] > 0) {
					$path = $result['url'];
					if (!isset($result['breadcrumb'])) $result['breadcrumb'] = "Piwigo tag";
					$title2 = $result['breadcrumb'];
					$text = $result['label'];
					$html .= '<li>';
					$html .= show_link_piwigo($path, $lang, $title, $title2, $target, $thumbnail, $icon_height, $text);
					$html .= '</li>';
				}
			}
			$html .= show_thumbnails_piwigo($results, $title, $target);
		}
	}
	
	// Link geohack
	if ($GOV_USE_LINK['geohack'] && isset($place['lati']) && isset($place['long'])) {
		$search = $place['lati'].';'.$place['long'];
		$lang = WT_LOCALE;
		$title = 'Geohack';
		$thumbnail = WT_MODULES_DIR.'gov/images/geohack.png';
		$text = $title;
		$pagename = $place_name;
		$html .= '<li>';
		$html .= show_link_geohack($search, $lang, $title, $target, $thumbnail, $icon_height, $text, $pagename);
		$html .= '</li>';
	}
	
	// Link to postcards from Delcampe
	if ($GOV_USE_LINK['delcampe']) {
		$search = search_string_delcampe($place['place'], $place_name);
		$lang = $language_ISO639_1_to_ISO639_2B[WT_LOCALE]['UI_delcampe'];
		if ($lang == "") $lang = $language_ISO639_1_to_ISO639_2B['en']['UI_delcampe'];
		$title = 'Delcampe';
		$thumbnail = WT_MODULES_DIR.'gov/images/delcampe.png';
		$text = WT_I18N::translate('Postcards');
		$html .= '<li>';
		$html .= show_link_delcampe($search, $lang, $title, $target, $thumbnail, $icon_height, $text);
		$html .= '</li>';
	}

	// Link to pdf media object
	if ($GOV_USE_LINK['pdf']) {
		$pdf_object = search_pdf_object($place['govid']);
		if (isset($pdf_object)) {
			// var_dump($pdf_object);
			$title = 'pdf document for '.$place['govid'];
			$thumbnail_size = 80;
			$thumbnail_default = WT_MODULES_DIR.'gov/images/pdf.png';
			$html .= '<li>';
			$html .= show_pdf_thumbnail($pdf_object, $title, false, $thumbnail_size, $thumbnail_default);
			$html .= '</li>';
		}
	}
	
	$html .= '</ul>';
	echo $html;
	return;	
} // end function show_gov_links

function show_event($place, $marker) {
	global $language_ISO639_1_to_ISO639_2B;
	global $gov_client; // tbd: remove this !
	echo '<table><tr>';
		echo '<td>';
			echo $marker['fact_label'], '<br>&nbsp;<br>';
			echo $marker['image'], '<br>&nbsp;<br>';
		echo '</td>';
			
		echo '<td>';
			if ($marker['info']) {
				echo '<span class="field">', WT_Filter::escapeHtml($marker['info']), '</span><br>';
			}
			
			if ($marker['name']) {
				echo $marker['name'], '<br>';
			}
			
			echo '<a href="placelist.php?action=show&amp;', 'parent[0]=', '">', $place['place'], '</a><br>';
			
			if (isset($marker['date'])) {
				echo $marker['date']->Display(true), '<br>';
			}
			
			if(isset($place['govid'])) {
				$gov_object = $gov_client->getObject($place['govid']);
				if (isset($gov_object->name)) {
					$language3 = $language_ISO639_1_to_ISO639_2B[WT_LOCALE]['letter3'];
					$shownamedate = 0;
					$marker['date']->date1->minJD = $marker['date']->date1->minJD - 400000;  // test
					// echo '[test=', $marker['date']->date1->minJD, '; ', $marker['date']->date1->maxJD;
					if ($marker['date']) {
						// tbd: check if name on minJD is the same as on maxJD
						if ($marker['date']->date1->minJD == $marker['date']->date1->maxJD) {
							$shownamedate = 1;
							$name_date = $gov_client->getNameAtDate($place['govid'], $marker['date']->date1->minJD, $language3);
						} else {
							$shownamedate = 2;
							$name_date_min = $gov_client->getNameAtDate($place['govid'], $marker['date']->date1->minJD, $language3);
							// echo '[name_date_min=', $name_date_min, '] ';
							$name_date_max = $gov_client->getNameAtDate($place['govid'], $marker['date']->date1->maxJD, $language3);
							// echo '[name_date_max=', $name_date_max, '] ';
							if ($name_date_min == $name_date_max) {
								$shownamedate = 1;
								$name_date = $name_date_min;
							} else {
								$date_min = jdtogregorian ($marker['date']->date1->minJD);
								// echo '[date_min=', $date_min, '] ';
								$date_max = jdtogregorian ($marker['date']->date1->maxJD);
							}
						}
						if ($shownamedate == 1) { // tbd: show only if name is different
							echo '<p>', WT_I18N::translate('The name of this place was %s on %s.', $name_date, $marker['date']->Display(true)), '</p>';  // tbd: use "in" instead of "on" if only month/year or year is known
						} elseif ($shownamedate == 2) {
							echo '<p>', WT_I18N::translate('The name of this place was %s on %s and %s on %s.', $name_date_min, $date_min, $name_date_max, $date_max), '</p>'; // tbd: format of date
						}
					}
				}
			}
		echo '</td>';
	echo '</tr></table>';
} // end function show_event

function gov_show_information ($markers_places) {
	$places = $markers_places['places'];	
	$markers = $markers_places['markers'];

	echo '<table border="0" width="100%" class="facts_table">';
	echo '<tr><td valign="top" width="100%">';
		echo '<div id="map_content">';
			if (count($places) > 0) {
				echo '<div style="overflow: auto; overflow-x: hidden; overflow-y: auto;">';
					echo '<table class="facts_table">';
					foreach ($places as $place) {
						echo '<tr>';
							echo '<td class="facts_label" rowspan=', count($place['index']), ' style="white-space: normal">';			
								if(isset($place['govid'])) {
									$place_name = show_gov_place($place);
									show_gov_links($place, $place_name);
								}
							echo '</td>';
						
							foreach ($place['index'] as $index) {
								$marker = $markers[$index];
								echo '<td class="', $marker['class'], '">';
									show_event($place, $marker);
								echo '</td>';
							} // end foreach
						echo '</tr>';
					}
					echo '</table>';
				echo '</div><br>';
			} else echo WT_I18N::translate('No information found.');
		echo '</div>';
	echo '</td></tr></table>';
} // end of function gov_show_information

function delete_accents($str) {
	return str_replace(    array( 
                                'À', 'Â', 'Ä', 'Á', 'Ã', 'Å', 
                                'Î', 'Ï', 'Ì', 'Í',  
                                'Ô', 'Ö', 'Ò', 'Ó', 'Õ', 'Ø',  
                                'Ù', 'Û', 'Ü', 'Ú',  
                                'É', 'È', 'Ê', 'Ë',  
                                'Ç', 'Ÿ', 'Ñ', 'Ý',
								'Š',
								'é','è','ê','ë','ě',
								'ü','ö','ä',
								'ř',
                            ), 
                            array( 
                                'A', 'A', 'A', 'A', 'A', 'A',  
                                'I', 'I', 'I', 'I',  
                                'O', 'O', 'O', 'O', 'O', 'O',  
                                'U', 'U', 'U', 'U',  
                                'E', 'E', 'E', 'E',  
                                'C', 'Y', 'N', 'Y',
								'S', 
								'e','e','e','e','e',
								'u','o','a',
								'r',
								
                            ), 
                            $str
                        ); 
	}