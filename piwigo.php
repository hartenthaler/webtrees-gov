<?php
// Piwigo module for webtrees
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

function piwigo_check($type, $search) { // type is 'permalink' or 'tag'
	global $GOV_PATH_PIWIGO;
	require_once('curl_functions.php');
	
	// echo '[search_permalink=', $search_permalink, '] ';
	$found = -1; // counts number of found images if > 0 (-1: permalink not found; 0: permalink found, but no images)
	$id = null;
	$url = '';
    $status = "ok";
	$breadcrumb = '';
	$images = array();
	$ws_url = $GOV_PATH_PIWIGO.'ws.php';
	// echo '[ws_url=', $ws_url, '] ';

    while (1) {
        if (is_null($ws_url)) {
            $status = 'not_configured';
            break;
        }

        $results = json_decode(
            curl_get(
                $ws_url, 
                array(
                    'format'	=> 'json', 
                    'method'	=> ($type == 'permalink') ? 'pwg.categories.getList' : 'pwg.tags.getList', 
                    'recursive'	=> 'true'
                )
            )
        );
        if (is_null($results)) {
            $status = 'http_get_failed';
            break;
        }

		if ($results->stat !== "ok") {
            $status = 'ws_status_bad';
            break;
        }
		
        $results = ($type == 'permalink') ? $results->result->categories : $results->result->tags;
		// var_dump($results);

        foreach ($results as $result) {
			// var_dump($result);
			if ($type == 'permalink') {
				if ($result->permalink == $search) {
					$found = $result->nb_images;
					$breadcrumb = piwigo_breadcrumb($result, $results);
				}
			} else if ($type == 'tag') { 
				if ($result->name == $search) {
					$found = $result->counter;
				}
			}
			if ($found > 0) {
				$id = $result->id;
				$url = $result->url;
				$images = piwigo_get_images($type, $result->id, $ws_url);
				if ($images['status'] !== "ok") $status = $images['status'];
				$images = $images['images'];
				break;
			}
        }
		break;
    }
	$result = array (
						'status'		=> $status,
						'label'			=> ($type == 'permalink') ? WT_I18N::translate('Album pictures') : WT_I18N::translate('Tagged pictures'),
						'found'			=> $found,
						'id'			=> $id,
						'url'			=> $url,
						'breadcrumb'	=> $breadcrumb,
						'images'		=> $images,
					 ); 
	return $result;
} // end of function piwigo_check

function piwigo_breadcrumb($cat, $cats_res) {
	$names = array();

	foreach (explode(',', $cat->uppercats) as $parent_id) {
		foreach ($cats_res as $c) {
			if ($c->id == $parent_id) {
				$names[] = $c->name;
				break;
			}
		}
	}
	return implode('/', $names);
}

function piwigo_get_images($type, $id, $ws_url) { // type is 'permalink' or 'tag'
	$status = "ok";
    $this_page = 0;
    $per_page = 100;

	// echo '[type=', $type, '] ';
	// echo '[id=', $id, '] ';
	$img_res = json_decode(
		curl_get(
			$ws_url, 
			array(
				'format'	=> 'json', 
				'method'	=> ($type == 'permalink') ? 'pwg.categories.getImages' : 'pwg.tags.getImages', 
				'cat_id'	=> $id,
				'tag_id'	=> $id,
				'per_page'	=> $per_page,
				'page'		=> $this_page,
			)
		)
	);

	$images = array();
	if (is_null($img_res)) {
		$status = 'get_images_failed';
	} else {
		if ($img_res->stat !== "ok") {
            $status = 'ws_status_bad';
        } else {
			// tbd: what should happen if there are more than $per_page images?
			$images = $img_res->result->images;
		}
	}
	// var_dump($images);
	return array (
		'status' => $status,
		'images' => $images);
}
