<?php
// GOV Module help text.
//
// This file is included from the application help_text.php script.
// It simply needs to set $title and $text for the help topic $help_topic
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
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
// Hermann Hartentaler

if (!defined('WT_WEBTREES') || !defined('WT_SCRIPT_NAME') || WT_SCRIPT_NAME!='help_text.php') {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

switch ($help) {

case 'GOV_DEFAULT_LEVEL_0':
	$title=WT_I18N::translate('Default top-level value');
	$text=WT_I18N::translate('Here the default value for the highest level in the hierarchy of places can be defined. If a place cannot be found, this name is added as the highest level (i.e. country) and the database is searched again. For example: "Chicago, Illinois" is expanded to "Chicago, Illinois, USA", if "USA" is defined here as default.');
	break;

case 'GOV_NAME_PREFIX_SUFFIX':
	$title=WT_I18N::translate('Optional prefixes and suffixes for GOV');
	$text=WT_I18N::translate('Some place names may be written with optional prefixes and suffixes.  For example “Orange” versus “Orange County”.  If the family tree contains the full place names, but the GOV database contains the short place names, then you should specify a list of the prefixes and suffixes to be disregarded.  Multiple options should be separated with semicolons. For example: “County;County of” or “Township;Twp;Twp.”.');
	break;

case 'GOV_USE_PIWIGO':
	$title=WT_I18N::translate('Use Piwigo for place gallery');
	$text=WT_I18N::translate('<p>If you like to show pictures for your places, you can use the Piwigo module. You have to download it from http://piwigo.org and install it on your server. Then add your pictures of places to the Piwigo albums and sub-albums for every place. One picture can be in several albums.</p><p>There are two methods to find the pictures based on the GOV id of a place. You can use one of them or both:<ul><li>set a unique permalink to an albums using the GOV id of the place or</li><li>tag pictures using the GOV id as tag.</li></ul></p><p>On the GOV tab you will see a link to Piwigo only, if there are pictures with this GOV id. If there are pictures in an album and tagged pictures, you will see two links (but if the tagged pictures are also in the album you will not see these pictures twice).</p>');
	break;
	
case 'GOV_PATH_PIWIGO':
	$title=WT_I18N::translate('Path to Piwigo module');
	$text=WT_I18N::translate('Specify the path (URL) to your Piwigo module, e.g. http://myserver/piwigo/');
	break;

case 'GOV_USE_GEOHACK':
	$title=WT_I18N::translate('Show link to Geohack');
	$text=WT_I18N::translate('If longitude and latitude coordinates of a place are known, you can show a link to geohack to display maps and many other additional information for this place.');
	break;

case 'GOV_USE_DELCAMPE':
	$title=WT_I18N::translate('Show link to Delcampe postcards');
	$text=WT_I18N::translate('Delcampe is a commercial service offering millions of postcards for places.');
	break;

case 'GOV_USE_PDF':
	$title=WT_I18N::translate('Show PDF documents');
	// tbd: use %s and $GOV_PATH_PDF instead of "places/docs/", but at this point $GOV_PATH_PDF is not defined
	// tbd: add "or use any filename in your places/docs/ folder (ending with .pdf) and give it a title with the format "&lang;gov-id&rang;.&lang;language&rang;", where &lang;gov-id&rang; is mandatory and the second part with the language code is optional.
	$text=WT_I18N::translate('<p>You can provide pdf documents for places (cities, villages) in the sub-folder places/docs/ of your webtrees data/media folder for your tree. The filename has to have the following format: "&lang;gov-id&rang;.&lang;language&rang;.&lang;comment&rang;.pdf". The &lang;gov-id&rang; is mandatory and has to be written in the same way as it is done inside GOV (small and capital letters). The second part &lang;language&rang; is the standard webtrees language code like "de" or "en_US"; this field is optional. If there are files for one GOV-id with and without a &lang;language&rang; field, the file which fits to the selected language of the user will be used; if there is no language fit, than the file without the &lang;language&rang; field will be used. The optional field &lang;comment&rang; can be used to store additional information in the file name, which is not used by this module. Multiple dots before the ".pdf" have to be eliminated.</p><p>You have to add these files as a media object to your tree. To do this they have to be connected to a person, a family or a source. Maybe it is a good idea to create a virtual person with a name like "Any Place" and connect all place documents to this person which is not connected to any other person in your tree. This would make it easier to manage your place documents.</p><p>You can upload a thumbnail image (.png or .jpg) together with the pdf file. This picture will be shown as a thumbnail instead of the generic pdf thumbnail.</p><p>Examples for valid filenames are:<ul><li>FRONAUJO62PP.de.Frohnau.pdf,</li><li>FRONAUJO62PP..Frohnau.pdf,</li><li>FRONAUJO62PP.de.pdf,</li><li>FRONAUJO62PP.pdf</li></ul></p>');
	break;
		
// Help texts for places_edit.php

case 'GOV_PLIF_LOCALFILE':
	$title=WT_I18N::translate('Enter filename');
	$text=WT_I18N::translate('Select a file from the list of files already on the server which contains the GOV data in CSV format.');
	break;

case 'GOV_PLE_ACTIVE':
	$title=WT_I18N::translate('Show inactive places');
	$text=
		'<p>'.
		WT_I18N::translate('By default, the list shows only those places which can be found in your family trees. You may have details for other places, such as those imported in bulk from an external file. Selecting this option will show all places, including ones that are not currently used.').
		'</p><p class="warning">'.
		WT_I18N::translate('If you have a large number of inactive places, it can be slow to generate the list.').
		'</p>';
	break;

// Help text for Place Hierarchy display

case 'GOV_DISP_SHORT_PLACE':
	$title=WT_I18N::translate('Display short placenames');
	$text=WT_I18N::translate('Here you can choose between two types of displaying places names in hierarchy. If set "Yes" the place is shown as a short name, if "No" the full hierarchy names are shown.<br /><b>Examples:<br />Full name: </b>Chicago, Illinois, USA<br /><b>Short name: </b>Chicago<br />or<br /><b>Full name: </b>Illinois, USA<br /><b>Short name: </b>Illinois');
	break;
}
