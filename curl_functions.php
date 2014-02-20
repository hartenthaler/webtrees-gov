<?php
// Piwigo webservice functions required by GOV module
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// copied from Piwigo plugin PIWIGOMEDIA (GNU version 2)
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

function curl_post($url, array $post = NULL, array $options = array())
{
    $defaults = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => $url,
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 1,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_POSTFIELDS => http_build_query($post)
    );

    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));
    if( ! $result = curl_exec($ch)) {
		//tbd: write webtrees logfile
        trigger_error(curl_error($ch));
    }
    curl_close($ch);
    return $result;
}

function curl_get($url, array $get = NULL, array $options = array())
{
    $defaults = array(
        CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). 
            http_build_query($get),
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_TIMEOUT => 4 // timeout in s
    );

    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));
    if( ! $result = curl_exec($ch)) {
		//tbd: write webtrees logfile
        trigger_error(curl_error($ch));
    }
    curl_close($ch);
    return $result;
}

function pwm_build_link($cat_id=null, $cat_page=null, $site=null) {
    $args = array();
    if (!is_null($cat_id))
        $args[] = 'category='.$cat_id;
    if (!is_null($cat_page))
        $args[] = 'cat_page='.$cat_page;
    if (!is_null($site))
        $args[] = 'site='.$site;
    return $_SERVER['PHP_SELF'].'?'.implode('&amp;', $args);
}


function pwm_get_category($categories, $cat_id) {
    foreach($categories as $cat) {
        if ($cat->id == $cat_id) {
            return $cat;
        }
    }
    return null;
}

?>
