<?php
// Configuration file required by GOV module
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
// $Id: defaultconfig.php 2013-11-13 22:10:21 Hermann Hartenthaler $

// tbd: remove all global variables

if (!defined('WT_WEBTREES')) {
 header('HTTP/1.0 403 Forbidden');
 exit;
}

// Create GOV tables, if not already present
try {
	WT_DB::updateSchema(WT_ROOT.WT_MODULES_DIR.$this->getName().'/db_schema/', 'GOV_SCHEMA_VERSION', 1);
} catch (PDOException $ex) {
	// The schema update scripts should never fail. If they do, there is no clean recovery.
	die($ex);
}

global $GOV_DEFAULT_TOP_VALUE;
$GOV_DEFAULT_TOP_VALUE = get_module_setting('gov', 'GOV_DEFAULT_TOP_VALUE', '' ); // default value, inserted when no top location can be found

global $GOV_DISP_SHORT_PLACE;
$GOV_DISP_SHORT_PLACE = get_module_setting('gov', 'GOV_DISP_SHORT_PLACE', '0'); // display full place name or only the actual level name

global $GOV_USE_LINK;
$GOV_USE_LINK = array(
					'piwigo'	=> get_module_setting('gov', 'GOV_USE_PIWIGO', false),	// use Piwigo to show pictures and documents for a place
					'geohack'	=> get_module_setting('gov', 'GOV_USE_GEOHACK', true),	// show link to Geohack
					'delcampe'	=> get_module_setting('gov', 'GOV_USE_DELCAMPE', true),	// show link to Delcampe postcards
					'pdf'		=> get_module_setting('gov', 'GOV_USE_PDF', false),		// show pdf documents for places
					);

global $GOV_PATH_PIWIGO;
$GOV_PATH_PIWIGO = get_module_setting('gov', 'GOV_PATH_PIWIGO', 'http://myserver/piwigo/'); // Piwigo URL example

global $GOV_PATH_PDF;
$GOV_PATH_PDF = get_module_setting('gov', 'GOV_PATH_PDF', 'places/docs/'); // path to pdf documents inside the webtrees media directory (tbd: admin should be able to change this part of the path)

// Configuration-options per location-level
global $GOV_PREFIX;
global $GOV_POSTFIX;

$GOV_PREFIX       [1]=get_module_setting('gov', 'GOV_PREFIX_1',        ''     ); // Text placed in front of the name
$GOV_POSTFIX      [1]=get_module_setting('gov', 'GOV_POSTFIX_1',       ''     ); // Text placed after the name

$GOV_PREFIX       [2]=get_module_setting('gov', 'GOV_PREFIX_2',        ''     );
$GOV_POSTFIX      [2]=get_module_setting('gov', 'GOV_POSTFIX_2',       ''     );

$GOV_PREFIX       [3]=get_module_setting('gov', 'GOV_PREFIX_3',        ''     );
$GOV_POSTFIX      [3]=get_module_setting('gov', 'GOV_POSTFIX_3',       ''     );

$GOV_PREFIX       [4]=get_module_setting('gov', 'GOV_PREFIX_4',        ''     );
$GOV_POSTFIX      [4]=get_module_setting('gov', 'GOV_POSTFIX_4',       ''     );

$GOV_PREFIX       [5]=get_module_setting('gov', 'GOV_PREFIX_5',        ''     );
$GOV_POSTFIX      [5]=get_module_setting('gov', 'GOV_POSTFIX_5',       ''     );

$GOV_PREFIX       [6]=get_module_setting('gov', 'GOV_PREFIX_6',        ''     );
$GOV_POSTFIX      [6]=get_module_setting('gov', 'GOV_POSTFIX_6',       ''     );

$GOV_PREFIX       [7]=get_module_setting('gov', 'GOV_PREFIX_7',        ''     );
$GOV_POSTFIX      [7]=get_module_setting('gov', 'GOV_POSTFIX_7',       ''     );

$GOV_PREFIX       [8]=get_module_setting('gov', 'GOV_PREFIX_8',        ''     );
$GOV_POSTFIX      [8]=get_module_setting('gov', 'GOV_POSTFIX_8',       ''     );

$GOV_PREFIX       [9]=get_module_setting('gov', 'GOV_PREFIX_9',        ''     );
$GOV_POSTFIX      [9]=get_module_setting('gov', 'GOV_POSTFIX_9',       ''     );

// instead of using WT_LOCALE we can use the following code
// $user_id = getUserID();
// echo '[LANG=', get_user_setting($user_id, 'language'), '] ';

// convert language codes (only some relevant examples, see http://www-01.sil.org/iso639-3/ 
// webtrees language codes (WT_LOCALE) are using ISO639-1 (with extensions like en_US)
// GOV uses in its database ISO639-2B (but for German "deu" is used (ISO639-2T) instead of "ger")
// in this array the webtrees languages are set to FALSE; they are set to TRUE by some code later on
global $language_ISO639_1_to_ISO639_2B;
$language_ISO639_1_to_ISO639_2B = array(
'af' => array ('letter3'=>'afr', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Afrikaans', 'French'=>'afrikaans', 'German'=>'Afrikaans', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'af_ZA'),
'ar' => array ('letter3'=>'ara', 'webtrees'=>FALSE, 'local'=>'العربية', 'English'=>'Arabic', 'French'=>'arabe', 'German'=>'Arabisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'ar_SA'),
'bg' => array ('letter3'=>'bul', 'webtrees'=>FALSE, 'local'=>'български', 'English'=>'Bulgarian', 'French'=>'bulgare', 'German'=>'Bulgarisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'bg_BG'),
'bs' => array ('letter3'=>'bos', 'webtrees'=>FALSE, 'local'=>'bosanski', 'English'=>'Bosnian', 'French'=>'bosniaque', 'German'=>'Bosnisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>''),
'ca' => array ('letter3'=>'cat', 'webtrees'=>FALSE, 'local'=>'català', 'English'=>'Catalan', 'French'=>'catalan', 'German'=>'Katalanisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'ca_ES'),
'cs' => array ('letter3'=>'cze', 'webtrees'=>FALSE, 'local'=>'čeština', 'English'=>'Czech', 'French'=>'tchèque', 'German'=>'Tschechisch', 'UI_GOV'=>'cs', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'cs_CZ'),
'da' => array ('letter3'=>'dan', 'webtrees'=>FALSE, 'local'=>'dansk', 'English'=>'Danish', 'French'=>'danois', 'German'=>'Dänisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'da_DK'),
'de' => array ('letter3'=>'deu', 'webtrees'=>FALSE, 'local'=>'Deutsch', 'English'=>'German', 'French'=>'allemand', 'German'=>'Deutsch', 'UI_GOV'=>'de', 'UI_delcampe'=>'G', 'UI_geonames'=>'de', 'UI_piwigo'=>'de_DE'),  // "deu" is ISO639-2T, but used in GOV !
'dv' => array ('letter3'=>'div', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Maldivian', 'French'=>'maldivien', 'German'=>'Maledivisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>''),
'el' => array ('letter3'=>'gre', 'webtrees'=>FALSE, 'local'=>'Ελληνικά', 'English'=>'Modern Greek', 'French'=>'grec moderne', 'German'=>'Neugriechisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'el_GR'),
'en' => array ('letter3'=>'eng', 'webtrees'=>FALSE, 'local'=>'English', 'English'=>'English', 'French'=>'anglais', 'German'=>'Englisch', 'UI_GOV'=>'en', 'UI_delcampe'=>'E', 'UI_geonames'=>'en', 'UI_piwigo'=>'en_US'),
'en_AU' => array ('letter3'=>'eng', 'webtrees'=>FALSE, 'local'=>'Australian English', 'English'=>'Australian English', 'French'=>'anglais', 'German'=>'australisches Englisch', 'UI_GOV'=>'en', 'UI_delcampe'=>'E', 'UI_geonames'=>'en', 'UI_piwigo'=>'en_US'),  // used in webtrees (extra)
'en_CA' => array ('letter3'=>'eng', 'webtrees'=>FALSE, 'local'=>'Canadian English', 'English'=>'Canadian English', 'French'=>'anglais', 'German'=>'kanadisches Englisch', 'UI_GOV'=>'en', 'UI_delcampe'=>'E', 'UI_geonames'=>'en', 'UI_piwigo'=>'en_US'),  // used in webtrees (extra)
'en_GB' => array ('letter3'=>'eng', 'webtrees'=>FALSE, 'local'=>'British English', 'English'=>'British English', 'French'=>'anglais', 'German'=>'britisches Englisch', 'UI_GOV'=>'en', 'UI_delcampe'=>'E', 'UI_geonames'=>'en', 'UI_piwigo'=>'en_GB'),  // used in webtrees
'en_US' => array ('letter3'=>'eng', 'webtrees'=>FALSE, 'local'=>'U.S. English', 'English'=>'US English', 'French'=>'anglais', 'German'=>'amerikanisches Englisch', 'UI_GOV'=>'en', 'UI_delcampe'=>'E', 'UI_geonames'=>'en', 'UI_piwigo'=>'en_US'),  // used in webtrees
'es' => array ('letter3'=>'spa', 'webtrees'=>FALSE, 'local'=>'español', 'English'=>'Spanish', 'French'=>'espagnol', 'German'=>'Spanisch', 'UI_GOV'=>'', 'UI_delcampe'=>'S', 'UI_geonames'=>'es', 'UI_piwigo'=>'es_ES'),
'et' => array ('letter3'=>'est', 'webtrees'=>FALSE, 'local'=>'eesti', 'English'=>'Estonian', 'French'=>'estonien', 'German'=>'Estnisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'et_EE'),
'fa' => array ('letter3'=>'per', 'webtrees'=>FALSE, 'local'=>'فارسی', 'English'=>'Persian', 'French'=>'persan', 'German'=>'Persisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'fa_IR'),
'fi' => array ('letter3'=>'fin', 'webtrees'=>FALSE, 'local'=>'suomi', 'English'=>'Finnish', 'French'=>'finnois', 'German'=>'Finnisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'fi_FI'),
'fo' => array ('letter3'=>'fao', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Faroese', 'French'=>'féroïen', 'German'=>'Färöisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>''),
'fr' => array ('letter3'=>'fre', 'webtrees'=>FALSE, 'local'=>'français', 'English'=>'French', 'French'=>'français', 'German'=>'Französisch', 'UI_GOV'=>'fr', 'UI_delcampe'=>'F', 'UI_geonames'=>'fr', 'UI_piwigo'=>'fr_FR'),
'fr_CA' => array ('letter3'=>'fre', 'webtrees'=>FALSE, 'local'=>'français', 'English'=>'French', 'French'=>'français', 'German'=>'Französisch (Kanada)', 'UI_GOV'=>'fr', 'UI_delcampe'=>'F', 'UI_geonames'=>'fr', 'UI_piwigo'=>'fr_CA'),  // used in webtrees (extra)
'gl' => array ('letter3'=>'glg', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Galician', 'French'=>'galicien', 'German'=>'Galicisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'gl_ES'),
'he' => array ('letter3'=>'heb', 'webtrees'=>FALSE, 'local'=>'עברית', 'English'=>'Hebrew', 'French'=>'hébreu', 'German'=>'Hebräisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'he_IL'),
'hr' => array ('letter3'=>'hrv', 'webtrees'=>FALSE, 'local'=>'hrvatski', 'English'=>'Croatian', 'French'=>'croate', 'German'=>'Kroatisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'hr_HR'),
'hu' => array ('letter3'=>'hun', 'webtrees'=>FALSE, 'local'=>'magyar', 'English'=>'Hungarian', 'French'=>'hongrois', 'German'=>'Ungarisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'hu_HU'),
'id' => array ('letter3'=>'ind', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Indonesian', 'French'=>'indonésien', 'German'=>'Bahasa Indonesia', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'id_ID'),
'is' => array ('letter3'=>'ice', 'webtrees'=>FALSE, 'local'=>'íslenska', 'English'=>'Icelandic', 'French'=>'islandais', 'German'=>'Isländisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'is_IS'),
'it' => array ('letter3'=>'ita', 'webtrees'=>FALSE, 'local'=>'italiano', 'English'=>'Italian', 'French'=>'italien', 'German'=>'Italienisch', 'UI_GOV'=>'', 'UI_delcampe'=>'I', 'UI_geonames'=>'it', 'UI_piwigo'=>'it_IT'),
'ja' => array ('letter3'=>'jpn', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Japanese', 'French'=>'japonais', 'German'=>'Japanisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'ja_JP'),
'ka' => array ('letter3'=>'geo', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Georgian', 'French'=>'géorgien', 'German'=>'Georgisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'ka_GE'),
'ko' => array ('letter3'=>'kor', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Korean', 'French'=>'coréen', 'German'=>'Koreanisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'ko_KR'),
'la' => array ('letter3'=>'lat', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Latin', 'French'=>'latin', 'German'=>'Latein', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>''),
'lt' => array ('letter3'=>'lit', 'webtrees'=>FALSE, 'local'=>'lietuvių', 'English'=>'Lithuanian', 'French'=>'lituanien', 'German'=>'Litauisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'lt_LT'),
'lv' => array ('letter3'=>'lav', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Latvian', 'French'=>'letton', 'German'=>'Lettisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'lv_LV'),
'mi' => array ('letter3'=>'mao', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Maori', 'French'=>'maori', 'German'=>'Maori-Sprache', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>''),
'mr' => array ('letter3'=>'mar', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Marathi', 'French'=>'marathe', 'German'=>'Marathi', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>''),
'ms' => array ('letter3'=>'may', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Malay', 'French'=>'malais', 'German'=>'Malaiisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'ms_MY'),
'nb' => array ('letter3'=>'nob', 'webtrees'=>FALSE, 'local'=>'norsk bokmål', 'English'=>'Norwegian Bokmål', 'French'=>'norvégien bokmål', 'German'=>'Bokmål', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'nb_NO'),
'ne' => array ('letter3'=>'nep', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Nepali', 'French'=>'népalais', 'German'=>'Nepali', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>''),
'nl' => array ('letter3'=>'dut', 'webtrees'=>FALSE, 'local'=>'Nederlands', 'English'=>'Dutch', 'French'=>'néerlandais', 'German'=>'Niederländisch', 'UI_GOV'=>'nl', 'UI_delcampe'=>'D', 'UI_geonames'=>'nl', 'UI_piwigo'=>'nl_NL'),
'nn' => array ('letter3'=>'nno', 'webtrees'=>FALSE, 'local'=>'nynorsk', 'English'=>'Norwegian Nynorsk', 'French'=>'norvégien nynorsk', 'German'=>'Nynorsk', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'nn_NO'),
'oc' => array ('letter3'=>'oci', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Occitan', 'French'=>'occitan', 'German'=>'Okzitanisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>''),  // post 1500
'pl' => array ('letter3'=>'pol', 'webtrees'=>FALSE, 'local'=>'polski', 'English'=>'Polish', 'French'=>'polonais', 'German'=>'Polnisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'pl', 'UI_piwigo'=>'pl_PL'),
'pt' => array ('letter3'=>'por', 'webtrees'=>FALSE, 'local'=>'português', 'English'=>'Portuguese', 'French'=>'portugais', 'German'=>'Portugiesisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'pt', 'UI_piwigo'=>'pt_PT'),
'pt_BR' => array ('letter3'=>'por', 'webtrees'=>FALSE, 'local'=>'português do Brasil', 'English'=>'Portuguese', 'French'=>'portugais', 'German'=>'Portugiesisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'pt_BR'),  // used in webtrees
'ro' => array ('letter3'=>'rum', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Romanian', 'French'=>'roumain', 'German'=>'Rumänisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>''),
'ru' => array ('letter3'=>'rus', 'webtrees'=>FALSE, 'local'=>'русский', 'English'=>'Russian', 'French'=>'russe', 'German'=>'Russisch', 'UI_GOV'=>'ru', 'UI_delcampe'=>'', 'UI_geonames'=>'ru', 'UI_piwigo'=>'ru_RU'),  // for transcription GOV uses "rus>eng" or "rus>deu" 
'sk' => array ('letter3'=>'slo', 'webtrees'=>FALSE, 'local'=>'slovenčina', 'English'=>'Slovak', 'French'=>'slovaque', 'German'=>'Slowakisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'sk_SK'),
'sl' => array ('letter3'=>'slv', 'webtrees'=>FALSE, 'local'=>'slovenščina', 'English'=>'Slovenian', 'French'=>'slovène', 'German'=>'Slowenisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'sl_SI'),
'sr' => array ('letter3'=>'srp', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Serbian', 'French'=>'serbe', 'German'=>'Serbisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'sr_RS'),
'sr@Latn' => array ('letter3'=>'srp', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Serbian (Latin characters)', 'French'=>'serbe', 'German'=>'Serbisch (lateinische Buchstaben)', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'sr_RS'),  // Latin characters; used in webtrees (extra)
'sv' => array ('letter3'=>'swe', 'webtrees'=>FALSE, 'local'=>'svenska', 'English'=>'Swedish', 'French'=>'suédois', 'German'=>'Schwedisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'sv_SE'),
'ta' => array ('letter3'=>'tam', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Tamil', 'French'=>'tamoul', 'German'=>'Tamil', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'ta_IN'),
'tr' => array ('letter3'=>'tur', 'webtrees'=>FALSE, 'local'=>'Türkçe', 'English'=>'Turkish', 'French'=>'turc', 'German'=>'Türkisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'tr_TR'),
'tt' => array ('letter3'=>'tat', 'webtrees'=>FALSE, 'local'=>'Татар', 'English'=>'Tatar', 'French'=>'tatar', 'German'=>'Tatarisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>''),
'uk' => array ('letter3'=>'ukr', 'webtrees'=>FALSE, 'local'=>'українська', 'English'=>'Ukrainian', 'French'=>'ukrainien', 'German'=>'Ukrainisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'uk_UA'),  // for transcription GOV uses "ukr>eng" or "ukr>deu" 
'vi' => array ('letter3'=>'vie', 'webtrees'=>FALSE, 'local'=>'Tiếng Việt', 'English'=>'Vietnamese', 'French'=>'vietnamien', 'German'=>'Vietnamesisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>'vi_VN'),
'yi' => array ('letter3'=>'yid', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Yiddish', 'French'=>'yiddish', 'German'=>'Jiddisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'', 'UI_piwigo'=>''),
'zh' => array ('letter3'=>'chi', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Chinese', 'French'=>'chinois', 'German'=>'Chinesisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'zh', 'UI_piwigo'=>'zh_CN'),  // used in webtrees
'zh_CN' => array ('letter3'=>'chi', 'webtrees'=>FALSE, 'local'=>'简体中文', 'English'=>'Chinese (simplified characters)', 'French'=>'chinois', 'German'=>'Chinesisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'zh', 'UI_piwigo'=>'zh_CN'),  // used in webtrees (extra)
'zh_TW' => array ('letter3'=>'chi', 'webtrees'=>FALSE, 'local'=>'', 'English'=>'Chinese', 'French'=>'chinois', 'German'=>'Chinesisch', 'UI_GOV'=>'', 'UI_delcampe'=>'', 'UI_geonames'=>'zh', 'UI_piwigo'=>'zh_TW'),
);

// Add error or succes message
function GOVaddMessage($controller, $type, $msg) {
	// tbd: for what is type=success used ???
	// tbd: check css for these classes !
	if ($type == "success") $class = "ui-state-highlight";
	if ($type == "error") $class = "ui-state-error";
	AddToLog($msg, $type);
	echo 'ERROR: ', $msg;
	// tbd: meaning of $controller ???		
	if (isset($controller)) $controller->addInlineJavaScript('
				jQuery("#error").text("'.$msg.'").addClass("'.$class.'").show("normal");
				setTimeout(function() {
					jQuery("#error").hide("normal");
				}, 10000);		
			');	
}

// set webtree languages 'true' and check consistency
function set_check_languages () {
	global $language_ISO639_1_to_ISO639_2B;
	$wt_languages = WT_I18N::installed_languages();
	foreach ($wt_languages as $code2=>$name) {
		// echo '[code2=', $code2, '] ';
		if (isset($language_ISO639_1_to_ISO639_2B[$code2])) {
			$language_ISO639_1_to_ISO639_2B[$code2]['webtrees'] = TRUE;
			if ($wt_languages[$code2] !== $language_ISO639_1_to_ISO639_2B[$code2]['local']) {
				GOVaddMessage(null, 'error', WT_I18N::translate('Webtrees language code "%s" inconsistent with table "language_ISO639_1_to_ISO639_2B": %s / %s.', $code2, $wt_languages[$code2], $language_ISO639_1_to_ISO639_2B[$code2]['local']));
			}
		} else {
			GOVaddMessage(null, 'error', WT_I18N::translate('Webtrees language code "%s" is not supported by table "language_ISO639_1_to_ISO639_2B".', $code2));
		}
	}
}
set_check_languages();
// var_dump($language_ISO639_1_to_ISO639_2B);

// webtrees function WT_Stats::iso3166() in library/WT/Stats.php can be used to convert country(!) 3-codes to 2-codes
// convert language(!) 3-codes to 2-codes
global $language_3_to_2;
$language_3_to_2 = array(
	'ara' => 'ar', 
	'bul' => 'bg', 
	'bos' => 'bs', 
	'cat' => 'ca', 
	'cze' => 'cs', 
	'dan' => 'da', 
	'deu' => 'de',   // "deu" is ISO639-2T, but used in GOV !
	'ger' => 'de',
	'gre' => 'el', 
	'eng' => 'en',   // in webtrees there are en_GB and en_US used too
	'spa' => 'es', 
	'est' => 'et', 
	'per' => 'fa', 
	'fin' => 'fi', 
	'fre' => 'fr', 
	'heb' => 'he', 
	'hrv' => 'hr', 
	'hun' => 'hu', 
	'ice' => 'is', 
	'ita' => 'it', 
	'lat' => 'la', 
	'lit' => 'lt', 
	'nob' => 'nb', 
	'dut' => 'nl', 
	'nno' => 'nn', 
	'pol' => 'pl', 
	'por' => 'pt',  // in webtrees there is pt_BR used too
	'rus' => 'ru',   
	'slo' => 'sk',
	'slv' => 'sl',
	'swe' => 'sv',
	'tur' => 'tr', 
	'tat' => 'tt', 
	'ukr' => 'uk',  
	'vie' => 'vi', 
	'chi' => 'zh',
);
// var_dump($language_3_to_2);

if (!defined('WT_GOV_SOAP_COMPLEX')) {
	define('WT_GOV_SOAP_COMPLEX', 'http://gov.genealogy.net/services/ComplexService?wsdl', true);
    // http://wiki-de.genealogy.net/GOV/Webservice/PHP
}
global $gov_client;
$gov_client = new SoapClient(WT_GOV_SOAP_COMPLEX);

global $gov_types;
$gov_types = array(  // tbd: allow translation of GOV types ???
	124 => array ('type'=>'Abtei', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	76 => array ('type'=>'Adeliges Gut', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	193 => array ('type'=>'Alm', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	57 => array ('type'=>'Amt (dänisches)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	78 => array ('type'=>'Amt (kreisähnlich)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	1 => array ('type'=>'Amt (Verwaltung)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	2 => array ('type'=>'Amtsbezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	3 => array ('type'=>'Amtsgericht', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	202 => array ('type'=>'Amtsgerichtsbezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	99 => array ('type'=>'Amtshauptmannschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	134 => array ('type'=>'arrondissement', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	251 => array ('type'=>'autonome Gemeinschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	118 => array ('type'=>'Bahnhof', 'can_represent'=>false, 'can_be_place'=>true, 'can_have_location'=>true),
	190 => array ('type'=>'Ballei', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	4 => array ('type'=>'Bauerschaft', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	192 => array ('type'=>'Besatzungszone', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	5 => array ('type'=>'Bezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	110 => array ('type'=>'Bezirksamt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	146 => array ('type'=>'Bezirkshauptmannschaft/Politischer Bezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	6 => array ('type'=>'Bistum', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	91 => array ('type'=>'Bistumsregion', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	234 => array ('type'=>'Borough', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	7 => array ('type'=>'Bundesland', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	195 => array ('type'=>'Bundessozialgericht', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	130 => array ('type'=>'Bundesstaat', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	198 => array ('type'=>'Bundesverwaltungsgericht', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	8 => array ('type'=>'Burg', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	97 => array ('type'=>'Bürgermeisterei', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	135 => array ('type'=>'canton', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	159 => array ('type'=>'Chutor', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	136 => array ('type'=>'commune', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	9 => array ('type'=>'Dekanat', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	10 => array ('type'=>'Departement', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	217 => array ('type'=>'Direktionsbezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	170 => array ('type'=>'Distrikt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	194 => array ('type'=>'Distrikts-Amt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	11 => array ('type'=>'Diözese', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	203 => array ('type'=>'Domanialamt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	82 => array ('type'=>'Domkapitel (Herrschaft)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	12 => array ('type'=>'Dompfarrei', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	55 => array ('type'=>'Dorf', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>true),
	205 => array ('type'=>'Dorfrat', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	140 => array ('type'=>'Einheitsgemeinde', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	139 => array ('type'=>'Einschicht', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	67 => array ('type'=>'Einöde', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>true),
	148 => array ('type'=>'Erfüllende Gemeinde', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	96 => array ('type'=>'Erzbistum', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	182 => array ('type'=>'Erzstift', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	219 => array ('type'=>'Expositur', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>false),
	13 => array ('type'=>'Filiale', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	14 => array ('type'=>'Flecken (Gebietskörperschaft)', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>false),
	233 => array ('type'=>'Flecken (Siedlung)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	15 => array ('type'=>'Flurname', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	109 => array ('type'=>'Forstgutsbezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	102 => array ('type'=>'Forsthaus', 'can_represent'=>false, 'can_be_place'=>true, 'can_have_location'=>true),
	16 => array ('type'=>'Freistaat', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	89 => array ('type'=>'Friedhof', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	221 => array ('type'=>'Fylke', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	115 => array ('type'=>'Försterei', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>true),
	60 => array ('type'=>'Fürstentum', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	17 => array ('type'=>'Gebäude', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	18 => array ('type'=>'Gemeinde', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>true),
	169 => array ('type'=>'Gemeinde (schwedisch)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	156 => array ('type'=>'Gemeindebezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	158 => array ('type'=>'Gemeindeteil', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	174 => array ('type'=>'Generalbezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	228 => array ('type'=>'Gerichtsamt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	19 => array ('type'=>'Gerichtsbezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	112 => array ('type'=>'Gespanschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	165 => array ('type'=>'Gnotschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	157 => array ('type'=>'Gouvernement', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	20 => array ('type'=>'Grafschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	61 => array ('type'=>'Großherzogtum', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	189 => array ('type'=>'Großpriorat', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	21 => array ('type'=>'Gut (Gebäude)', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	108 => array ('type'=>'Gutsbezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	75 => array ('type'=>'Güterdistrikt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	119 => array ('type'=>'Haltestelle', 'can_represent'=>false, 'can_be_place'=>true, 'can_have_location'=>true),
	83 => array ('type'=>'Hansestadt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	79 => array ('type'=>'Harde', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	68 => array ('type'=>'Hauptort', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>true),
	22 => array ('type'=>'Herrschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	23 => array ('type'=>'Herzogtum', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	183 => array ('type'=>'Hochstift', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	24 => array ('type'=>'Hof', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	154 => array ('type'=>'Honschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	236 => array ('type'=>'Häuser', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	229 => array ('type'=>'Häusergruppe', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	231 => array ('type'=>'Höfe', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	107 => array ('type'=>'Insel', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	88 => array ('type'=>'Judet', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	214 => array ('type'=>'Kaiserreich', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	184 => array ('type'=>'Kammerschreiberei', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	25 => array ('type'=>'Kanton', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	242 => array ('type'=>'Katasteramt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	172 => array ('type'=>'Katastralgemeinde', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	66 => array ('type'=>'Kirchdorf', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>true),
	26 => array ('type'=>'Kirche', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	210 => array ('type'=>'Kirchenbund', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	92 => array ('type'=>'Kirchengemeinde', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	27 => array ('type'=>'Kirchenkreis', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	28 => array ('type'=>'Kirchenprovinz', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	29 => array ('type'=>'Kirchspiel', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	227 => array ('type'=>'Kirchspiellandgemeinde', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	84 => array ('type'=>'Kirchspielvogtei', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	81 => array ('type'=>'Kloster (Gebiet)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	30 => array ('type'=>'Kloster (Gebäude)', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	185 => array ('type'=>'Klosteramt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	121 => array ('type'=>'Kolonie', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	113 => array ('type'=>'Komitat', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	191 => array ('type'=>'Kommende', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	153 => array ('type'=>'Kommissariat', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	143 => array ('type'=>'kommune', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	32 => array ('type'=>'Kreis', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	222 => array ('type'=>'Kreis (mittlere Verwaltungsebene)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	101 => array ('type'=>'Kreisdirektion', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	95 => array ('type'=>'kreisfreie Stadt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	175 => array ('type'=>'Kreisgebiet', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	100 => array ('type'=>'Kreishauptmannschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	33 => array ('type'=>'Kurfürstentum', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	31 => array ('type'=>'Königreich', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	34 => array ('type'=>'Land', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	152 => array ('type'=>'Landbürgermeisterei', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	73 => array ('type'=>'Landdrostei', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	35 => array ('type'=>'Landeskirche', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	201 => array ('type'=>'Landeskommissarbezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	196 => array ('type'=>'Landessozialgericht', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	216 => array ('type'=>'Landesteil', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	199 => array ('type'=>'Landesverwaltungsgericht', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	211 => array ('type'=>'Landgebiet', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	85 => array ('type'=>'Landgemeinde', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	105 => array ('type'=>'Landgericht', 'can_represent'=>false, 'can_be_place'=>true, 'can_have_location'=>false),
	223 => array ('type'=>'Landgericht (älterer Ordnung)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	128 => array ('type'=>'Landgrafschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	212 => array ('type'=>'Landherrenschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	36 => array ('type'=>'Landkreis', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	149 => array ('type'=>'Landratsamt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	47 => array ('type'=>'Landschaft (Region)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	80 => array ('type'=>'Landschaft (Verwaltung)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	252 => array ('type'=>'local government', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	167 => array ('type'=>'Mandatsgebiet', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	62 => array ('type'=>'Markgrafschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	145 => array ('type'=>'Markt', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>true),
	180 => array ('type'=>'Marktgemeinde', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	87 => array ('type'=>'Mühle', 'can_represent'=>false, 'can_be_place'=>true, 'can_have_location'=>true),
	37 => array ('type'=>'Oberamt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	207 => array ('type'=>'Oberamtsbezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	116 => array ('type'=>'Oberförsterei', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>true),
	151 => array ('type'=>'Oberlandesgericht', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	138 => array ('type'=>'Oberlandratsbezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	38 => array ('type'=>'Oblast', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	226 => array ('type'=>'Obmannschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	39 => array ('type'=>'Ort', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	164 => array ('type'=>'Ortsbezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	144 => array ('type'=>'Ortschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	163 => array ('type'=>'Ortsgemeinde', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	40 => array ('type'=>'Ortsteil', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>true),
	247 => array ('type'=>'Ortsteil (Verwaltung)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	41 => array ('type'=>'Pfarr-Rektorat', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	65 => array ('type'=>'Pfarrdorf', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>true),
	42 => array ('type'=>'Pfarrei', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	43 => array ('type'=>'Pfarrkuratie', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	44 => array ('type'=>'Pfarrverband', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	224 => array ('type'=>'Pfleggericht', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	243 => array ('type'=>'Propstei', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	176 => array ('type'=>'Protektorat', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	45 => array ('type'=>'Provinz', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	168 => array ('type'=>'Provinz (schwedisch)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	232 => array ('type'=>'Randort', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	63 => array ('type'=>'Rayon', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	46 => array ('type'=>'Regierungsbezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	133 => array ('type'=>'Region (französisch)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	137 => array ('type'=>'Region (Gebietskörperschaft)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	155 => array ('type'=>'Region (katholische Kirche)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	206 => array ('type'=>'Regionalkirchenamt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	125 => array ('type'=>'Reichsabtei', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	142 => array ('type'=>'Reichsgau', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	215 => array ('type'=>'Reichshälfte', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	173 => array ('type'=>'Reichskommissariat', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	77 => array ('type'=>'Reichskreis', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	177 => array ('type'=>'Reichsritterschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	93 => array ('type'=>'Reichsstadt', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>true),
	225 => array ('type'=>'Rentamt (älterer Ordnung)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	186 => array ('type'=>'Rentkammer', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	56 => array ('type'=>'Republik', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	178 => array ('type'=>'Ritterkanton', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	179 => array ('type'=>'Ritterkreis', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	188 => array ('type'=>'Ritterorden', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	204 => array ('type'=>'Ritterschaftliches Amt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	181 => array ('type'=>'Rotte', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	166 => array ('type'=>'Ruine', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	48 => array ('type'=>'Samtgemeinde', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	111 => array ('type'=>'Schloss', 'can_represent'=>false, 'can_be_place'=>true, 'can_have_location'=>true),
	248 => array ('type'=>'Schulzenamt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	120 => array ('type'=>'Siedlung', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	238 => array ('type'=>'Siedlung städtischen Typs', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	237 => array ('type'=>'Siedlungsrat', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	160 => array ('type'=>'Sowjetrepublik', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	197 => array ('type'=>'Sozialgericht', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	49 => array ('type'=>'Sprengel', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	50 => array ('type'=>'Staat', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	71 => array ('type'=>'Staatenbund', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	218 => array ('type'=>'Stadt (Einheitsgemeinde)', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>false),
	150 => array ('type'=>'Stadt (Gebietskörperschaft)', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>false),
	51 => array ('type'=>'Stadt (Siedlung)', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	162 => array ('type'=>'Stadt- und Landgemeinde', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	52 => array ('type'=>'Stadtbezirk', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	171 => array ('type'=>'Stadthauptmannschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	53 => array ('type'=>'Stadtkreis', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	213 => array ('type'=>'Stadtrat', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	54 => array ('type'=>'Stadtteil', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	103 => array ('type'=>'Standesamt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	230 => array ('type'=>'Streusiedlung', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	126 => array ('type'=>'Syssel', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	86 => array ('type'=>'Teilprovinz', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	240 => array ('type'=>'Ujesd', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	244 => array ('type'=>'unbenutzt (1)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	245 => array ('type'=>'unbenutzt (2)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	246 => array ('type'=>'unbenutzt (3)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	249 => array ('type'=>'unbenutzt (4)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	250 => array ('type'=>'unbenutzt (5)', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	58 => array ('type'=>'Unionsrepublik', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	235 => array ('type'=>'Unitary Authority', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	117 => array ('type'=>'Unterförsterei', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>true),
	98 => array ('type'=>'veraltet (Amtsgericht Gebäude)', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	74 => array ('type'=>'veraltet (Gut)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	147 => array ('type'=>'veraltet (Politischer Bezirk)', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	104 => array ('type'=>'veraltet (Standesamt (Gebäude))', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	122 => array ('type'=>'Verbandsgemeinde', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	239 => array ('type'=>'Verwaltungsamt', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	161 => array ('type'=>'Verwaltungsbezirk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	94 => array ('type'=>'Verwaltungsgemeinschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	200 => array ('type'=>'Verwaltungsgericht', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	127 => array ('type'=>'Verwaltungsverband', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	114 => array ('type'=>'Vest', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	70 => array ('type'=>'Vogtei', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	72 => array ('type'=>'Volksrepublik', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	64 => array ('type'=>'Vorwerk', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	131 => array ('type'=>'Weichbild', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	69 => array ('type'=>'Weiler', 'can_represent'=>true, 'can_be_place'=>false, 'can_have_location'=>true),
	129 => array ('type'=>'Wohnplatz', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	59 => array ('type'=>'Wojewodschaft', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>false),
	241 => array ('type'=>'Wolost', 'can_represent'=>true, 'can_be_place'=>true, 'can_have_location'=>true),
	90 => array ('type'=>'Wüstung', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
	187 => array ('type'=>'zu überprüfen', 'can_represent'=>false, 'can_be_place'=>false, 'can_have_location'=>true),
);

global $gov_search_types; // tbd: admin should be able to change these lists and add more than the 4 levels
$gov_search_types = array (
	0 => array ( 16, 31, 34, 50, 56, 58, 71, 72, 192, 214, 215, ),  // country
	1 => array ( 5, 7, 20, 22, 23, 25, 33, 45, 46, 59, 60, 61, 86, 130, 133, 137, 142, 146, 160, 161, 167, 168, 176, 178, 212, 216, ),
	2 => array ( 10, 32, 36, 37, 38, 53, 62, 77, 78, 80, 94, 122, 127, 134, 157, 175, 179, 207, 222, 234, ),  // county
	3 => array ( 18, 21, 24, 39, 40, 51, 52, 55, 64, 66, 67, 68, 69, 83, 85, 90, 93, 95, 107, 120, 129, 136, 144,
		145, 158, 163, 172, 229, 230, 231, 232, 233, 236, 241, ), // city
	);
