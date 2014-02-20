<?php
// Classes and libraries for module system
//
// webtrees: Web based Family History software
// Copyright (C) 2013 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
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
// based on googlemaps module
//
// Hermann Hartenthaler

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class gov_WT_Module extends WT_Module implements WT_Module_Config, WT_Module_Tab {

	public function __construct() {
		parent::__construct();
		// Load any local user translations
		if (is_dir(WT_MODULES_DIR.$this->getName().'/language')) {
			if (file_exists(WT_MODULES_DIR.$this->getName().'/language/'.WT_LOCALE.'.mo')) {
				WT_I18N::addTranslation(
					new Zend_Translate('gettext', WT_MODULES_DIR.$this->getName().'/language/'.WT_LOCALE.'.mo', WT_LOCALE)
				);
			}
		}
	}

	// Extend WT_Module
	public function getTitle() {
		return /* name of this module */ 'GOV';
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the GOV module */ WT_I18N::translate('GOV (German: Genealogisches Ortsverzeichnis) is a database for locations and their history');
	}
	
	// Implement WT_Module_Tab
	public function defaultTabOrder() {
		return 88;
	}
	
	//tbd: even if visibility of this module is set to "visitor/public" it is only shown to logedin users
	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_PUBLIC; // WT_PRIV_PUBLIC, WT_PRIV_USER, WT_PRIV_NONE, WT_PRIV_HIDE
	}
	
	// Implement WT_Module_Tab
	public function getTabContent() {
		return $this->getTabContentGOV();
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		echo $this->includeCss(WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/themes/_administration/style.css');  // tbd: this place is not ok to load admin css
		switch($mod_action) {
		case 'admin_config':
			$this->config();
			break;
		case 'admin_placecheck':
			$this->admin_placecheck();
			break;
		case 'admin_places':
		case 'places_edit':
			require_once WT_ROOT.WT_MODULES_DIR.$this->getName().'/gov.php';
			require_once WT_ROOT.WT_MODULES_DIR.$this->getName().'/defaultconfig.php';
			// tbd: these two files should be methods in this class
			require WT_ROOT.WT_MODULES_DIR.$this->getName().'/'.$mod_action.'.php';
			break;
		default:
			header('HTTP/1.0 404 Not Found');
			break;
		}
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_config';
	}

	// Implement WT_Module_Tab
	public function getPreLoadContent() {
		ob_start();
		require_once WT_ROOT.WT_MODULES_DIR.$this->getName().'/gov.php';
		require_once WT_ROOT.WT_MODULES_DIR.$this->getName().'/defaultconfig.php';
		return ob_get_clean();
	}

	// Implement WT_Module_Tab
	public function canLoadAjax() {
		return true;
	}

	// Implement WT_Module_Tab
	public function getTabContentGOV() {
		require_once WT_ROOT.WT_MODULES_DIR.$this->getName().'/gov.php';
		require_once WT_ROOT.WT_MODULES_DIR.$this->getName().'/defaultconfig.php';
		// echo '[PLAC FORM=', $this->get_plac_label(), '] ';
		
		// load the module stylesheets
		// echo '[wt_theme_url=', WT_THEME_URL, '] ';
		echo $this->getStylesheet();
		
		if (checkMapData()) {  // is there any location data for this person with family ?
			ob_start();
			// get_family_persons:	build array with ids of relevant persons and families
			// get_facts:			build array with fact information for these relevant persons and families
			// get_information:		build array with all necessary information e.g. from GOV for these facts, but do not display anything
			// gev_show_information: use this array to display contained information
			
			gov_show_information(get_information(get_facts(get_family_persons())));
			$html ='<div id="'.$this->getName().'_content">'.ob_get_clean().'</div>';
		} else {
			$html ='<table class="facts_table">';
			$html.='<tr><td class="facts_value">'.WT_I18N::translate('No GOV data for this person.');
			$html.='</td></tr>';
			if (WT_USER_IS_ADMIN) {
				$html.='<tr><td class="center">';
				$html.='<a href="module.php?mod='.$this->getName().'&amp;mod_action=admin_config">'.WT_I18N::translate('GOV preferences').'</a>';
				$html.='</td></tr>';
			}
			$html.='</table>';
		}
		return $html;
	}

	// Implement WT_Module_Tab
	public function hasTabContent() {
		global $SEARCH_SPIDER;
		return !$SEARCH_SPIDER && (array_key_exists('GOV', WT_Module::getActiveModules()) || WT_USER_IS_ADMIN);
	}

	// Implement WT_Module_Tab
	public function isGrayedOut() {
		return false;
	}
	
	private function config() {
		require WT_ROOT.WT_MODULES_DIR.$this->getName().'/defaultconfig.php';
		require WT_ROOT.'includes/functions/functions_edit.php';
		
		// GEDCOM Header "Place Label", e.g. "Stadt, Kreis, (Bundes)Land, Staat"
		$place_labels = $this->get_plac_label();
		// number of elements (tested only for 4 hierarchie levels up to now)
		$nb_plac_levels = substr_count($place_labels,',')+1;
		// echo "[nb_plac_levels=".$nb_plac_levels."] ";

		$action = WT_Filter::post('action');
		
		$controller=new WT_Controller_Page();
		$controller
			->requireAdminLogin()
			->setPageTitle('GOV')
			->pageHeader()
			->addInlineJavascript('jQuery("#tabs").tabs();');  // tbd: ???
		
		// echo '[theme=', WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/themes/_administration/style.css', '] ';	
		echo $this->includeCss(WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/themes/_administration/style.css');

		if ($action=='update') {
			set_module_setting('gov', 'GOV_DEFAULT_TOP_VALUE', $_POST['NEW_GOV_DEFAULT_TOP_LEVEL']);
			set_module_setting('gov', 'GOV_PLACE_HIERARCHY',   $_POST['NEW_GOV_PLACE_HIERARCHY']);
			set_module_setting('gov', 'GOV_DISP_SHORT_PLACE',  $_POST['NEW_GOV_DISP_SHORT_PLACE']);

			for ($i=1; $i<=9; $i++) {
				set_module_setting('gov', 'GOV_PREFIX_'.$i,  $_POST['NEW_GOV_PREFIX_'.$i]);
				set_module_setting('gov', 'GOV_POSTFIX_'.$i, $_POST['NEW_GOV_POSTFIX_'.$i]);
			}
			
			set_module_setting('gov', 'GOV_USE_PIWIGO', 	$_POST['NEW_GOV_USE_PIWIGO']);
			set_module_setting('gov', 'GOV_PATH_PIWIGO',	$_POST['NEW_GOV_PATH_PIWIGO']);
			set_module_setting('gov', 'GOV_USE_GEOHACK', 	$_POST['NEW_GOV_USE_GEOHACK']);
			set_module_setting('gov', 'GOV_USE_DELCAMPE', 	$_POST['NEW_GOV_USE_DELCAMPE']);
			set_module_setting('gov', 'GOV_USE_PDF', 		$_POST['NEW_GOV_USE_PDF']);

			AddToLog('GOV config updated', 'config');

			// read the config file again, to set the vars
			require WT_ROOT.WT_MODULES_DIR.$this->getName().'/defaultconfig.php';
			// tbd: check if there is a piwigo webservice answering at the entered piwigo address
		}
		?>
        <div id="gov">
            <div id="error"></div>
            <table id="gov_config">
                <tr>
                    <th>
                        <a class="current" href="module.php?mod=gov&amp;mod_action=admin_config">
                            <?php echo WT_I18N::translate('GOV preferences'); ?>
                        </a>
                    </th>
                    <th>
                        <a href="module.php?mod=gov&amp;mod_action=admin_places">
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
    
            <form method="post" name="configform" action="module.php?mod=gov&mod_action=admin_config">
                <input type="hidden" name="action" value="update">
                <div id="tabs">
                    <ul>
                        <li><a href="#gov_ph"><span><?php echo WT_I18N::translate('Place hierarchy'); ?></span></a></li>
                        <li><a href="#gov_fixes"><span><?php echo WT_I18N::translate('Prefixes and Suffixes'); ?></span></a></li>
                        <li><a href="#gov_links"><span><?php echo WT_I18N::translate('Links'); ?></span></a></li>
                    </ul>
    
                    <div id="gov_ph">
                        <table class="gov_edit_config">
                            <tr>
                                <th><?php echo WT_I18N::translate('Use GOV for the place hierarchy'); ?></th>
                                <td><?php echo edit_field_yes_no('NEW_GOV_PLACE_HIERARCHY', get_module_setting('gov', 'GOV_PLACE_HIERARCHY', '0')); ?></td>
                            </tr>
                            
                            <tr>
                                <th><?php echo WT_I18N::translate('Display short placenames'), help_link('GOV_DISP_SHORT_PLACE', $this->getName()); ?></th>
                                <td><?php echo edit_field_yes_no('NEW_GOV_DISP_SHORT_PLACE', $GOV_DISP_SHORT_PLACE); ?></td>
                            </tr>
                        </table>
                    </div>
    
                    <div id="gov_fixes">
                        <table class="gov_edit_config">
                            <tr>
                                <th colspan="2"><?php echo WT_I18N::translate('Default top-level value'), help_link('GOV_DEFAULT_LEVEL_0', $this->getName()); ?></th>
                                <td><input type="text" name="NEW_GOV_DEFAULT_TOP_LEVEL" value="<?php echo $GOV_DEFAULT_TOP_VALUE; ?>" size="20"></td>
                                <td>&nbsp;</td>
                            </tr>
                            
                            <tr>
                                <th class="gov_prefix" colspan="3"><?php echo WT_I18N::translate('Optional prefixes and suffixes for GOV'), help_link('GOV_NAME_PREFIX_SUFFIX', $this->getName());?></th>
                            </tr>
                            <tr id="gov_level_titles">
                                <th>&nbsp;</th>
                                <th><?php echo WT_I18N::translate('Prefixes'); ?></th>
                                <th><?php echo WT_I18N::translate('Suffixes'); ?></th>
                            <?php for ($level=1; $level < 10; $level++) { ?>
                            <tr  class="gov_levels">
                                <th>
                                    <?php
                                    if ($level==1) {
                                        echo WT_I18N::translate('Country');
                                    } else {
                                        echo WT_I18N::translate('Level'), " ", $level;
                                    }
                                    ?>
                                </th>
                                <td><input type="text" size="30" name="NEW_GOV_PREFIX_<?php echo $level; ?>" value="<?php echo $GOV_PREFIX[$level]; ?>"></td>
                                <td><input type="text" size="30" name="NEW_GOV_POSTFIX_<?php echo $level; ?>" value="<?php echo $GOV_POSTFIX[$level]; ?>"></td>
                            </tr>
                            <?php } ?>
                        </table>
                    </div>
                    
                    <div id="gov_links">
                        <table class="gov_edit_config">
                            <tr>
                                <th><?php echo WT_I18N::translate('Use Piwigo for place gallery'), help_link('GOV_USE_PIWIGO', $this->getName()); ?></th>
                                <td><?php echo edit_field_yes_no('NEW_GOV_USE_PIWIGO', get_module_setting('gov', 'GOV_USE_PIWIGO', false)); ?></td>
                            </tr>
                            
                            <tr>
                                <th><?php echo WT_I18N::translate('Path to Piwigo module'), help_link('GOV_PATH_PIWIGO', $this->getName()); ?></th>
                                <td><input type="text" size="70" name="NEW_GOV_PATH_PIWIGO" value="<?php echo $GOV_PATH_PIWIGO; // tbd: should this be get_module_setting() ??? ?>"></td>
                            </tr>
                            
                            <tr>
                                <th><?php echo WT_I18N::translate('Show link to Geohack'), help_link('GOV_USE_GEOHACK', $this->getName()); ?></th>
                                <td><?php echo edit_field_yes_no('NEW_GOV_USE_GEOHACK', get_module_setting('gov', 'GOV_USE_GEOHACK', true)); ?></td>
                            </tr>
                            
                            <tr>
                                <th><?php echo WT_I18N::translate('Show link to Delcampe postcards'), help_link('GOV_USE_DELCAMPE', $this->getName()); ?></th>
                                <td><?php echo edit_field_yes_no('NEW_GOV_USE_DELCAMPE', get_module_setting('gov', 'GOV_USE_DELCAMPE', true)); ?></td>
                            </tr>
                            
                            <tr>
                                <th><?php echo WT_I18N::translate('Show link to PDF documents'), help_link('GOV_USE_PDF', $this->getName()); ?></th>
                                <td><?php echo edit_field_yes_no('NEW_GOV_USE_PDF', get_module_setting('gov', 'GOV_USE_PDF', true)); ?></td>
                            </tr>
                                                        
                        </table>
                    </div>
    
                </div>
                <p>
                    <input type="submit" value="<?php echo WT_I18N::translate('save'); ?>">
                </p>
            </form>
        </div>
		<?php
	}

	private function admin_placecheck() {
		require WT_ROOT.WT_MODULES_DIR.$this->getName().'/defaultconfig.php';
		require_once WT_ROOT.WT_MODULES_DIR.$this->getName().'/gov.php';
		require_once WT_ROOT.'includes/functions/functions_edit.php';

		$action    = WT_Filter::get('action', '','go');
		$gedcom_id = WT_Filter::get('gedcom_id', null, WT_GED_ID);
		$country   = WT_Filter::get('country', '.+', 'XYZ');
		$state     = WT_Filter::get('state', '.+', 'XYZ');
		$matching  = WT_Filter::getBool('matching');

		if (!empty($WT_SESSION['placecheck_gedcom_id'])) {
			$gedcom_id = $WT_SESSION['placecheck_gedcom_id'];
		} else {
			$WT_SESSION['placecheck_gedcom_id'] = $gedcom_id;
		}
		if (!empty($WT_SESSION['placecheck_country'])) {
			$country = $WT_SESSION['placecheck_country'];
		} else {
			$WT_SESSION['placecheck_country'] = $country;
		}
		if (!empty($WT_SESSION['placecheck_state'])) {
			$state = $WT_SESSION['placecheck_state'];
		} else {
			$WT_SESSION['placecheck_state'] = $state;
		}

		$controller=new WT_Controller_Page();
		$controller
			->requireAdminLogin()
			->setPageTitle('GOV')
			->pageHeader();

		echo '
		    <div id="gov">
			<table id="gov_config">
				<tr>
					<th>
						<a href="module.php?mod=gov&amp;mod_action=admin_config">', WT_I18N::translate('GOV preferences'),'</a>
					</th>
					<th>
						<a href="module.php?mod=gov&amp;mod_action=admin_places">
							', WT_I18N::translate('GOV data'),'
						</a>
					</th>
					<th>
						<a class="current" href="module.php?mod=gov&amp;mod_action=admin_placecheck">
							', WT_I18N::translate('Place Check'),'
						</a>
					</th>
				</tr>
			</table>
			</div>';

		//Start of User Defined options
		echo '
			<form method="get" name="placecheck" action="module.php">
				<input type="hidden" name="mod" value="', $this->getName(), '">
				<input type="hidden" name="mod_action" value="admin_placecheck">
				<div class="gov_check">
					<label>', WT_I18N::translate('Family tree'), '</label>';
					echo select_edit_control('gedcom_id', WT_Tree::getIdList(), null, $gedcom_id, ' onchange="this.form.submit();"');
					echo '<label>', WT_I18N::translate('Country'), '</label>
					<select name="country" onchange="this.form.submit();">
						<option value="XYZ" selected="selected">', /* I18N: first/default option in a drop-down listbox */ WT_I18N::translate('&lt;select&gt;'), '</option>
						<option value="XYZ">', WT_I18N::translate('All'), '</option>';
							$rows=WT_DB::prepare("SELECT gov_id, gov_place FROM `##gov` WHERE gov_level=0 ORDER BY gov_place")
								->fetchAssoc();
							foreach ($rows as $id=>$place) {
								echo '<option value="', htmlspecialchars($place), '"';
								if ($place==$country) {
									echo ' selected="selected"';
									$par_id=$id;
								}
								echo '>', htmlspecialchars($place), '</option>';
							}
					echo '</select>';
					if ($country!='XYZ') {
						echo '<label>', /* I18N: Part of a country, state/region/county */ WT_I18N::translate('Subdivision'), '</label>
							<select name="state" onchange="this.form.submit();">
								<option value="XYZ" selected="selected">', WT_I18N::translate('&lt;select&gt;'), '</option>
								<option value="XYZ">', WT_I18N::translate('All'), '</option>';
								$places=WT_DB::prepare("SELECT gov_place FROM `##gov` WHERE gov_parent_id=? ORDER BY gov_place")
									->execute(array($par_id))
									->fetchOneColumn();
								foreach ($places as $place) {
									echo '<option value="', htmlspecialchars($place), '"', $place==$state?' selected="selected"':'', '>', htmlspecialchars($place), '</option>';
								}
								echo '</select>';
							}
					echo '<label>', WT_I18N::translate('Include fully matched places: '), '</label>';
					echo '<input type="checkbox" name="matching" value="1" onchange="this.form.submit();"';
					if ($matching) {
						echo ' checked="checked"';
					}
					echo '>';
				echo '</div>';// close div gov_check
				echo '<input type="hidden" name="action" value="go">';
			echo '</form>';//close form placecheck
			echo '<hr>';

		switch ($action) {
		case 'go':
			//Identify gedcom file
			$trees=WT_Tree::getAll();
			echo '<div id="gov_check_title">', $trees[$gedcom_id]->tree_title_html, '</div>';
			//Select all '2 PLAC ' tags in the file and create array
			$place_list=array();
			$ged_data=WT_DB::prepare("SELECT i_gedcom FROM `##individuals` WHERE i_gedcom LIKE ? AND i_file=?")
				->execute(array("%\n2 PLAC %", $gedcom_id))
				->fetchOneColumn();
			foreach ($ged_data as $ged_datum) {
				preg_match_all('/\n2 PLAC (.+)/', $ged_datum, $matches);
				foreach ($matches[1] as $match) {
					$place_list[$match]=true;
				}
			}
			$ged_data=WT_DB::prepare("SELECT f_gedcom FROM `##families` WHERE f_gedcom LIKE ? AND f_file=?")
				->execute(array("%\n2 PLAC %", $gedcom_id))
				->fetchOneColumn();
			foreach ($ged_data as $ged_datum) {
				preg_match_all('/\n2 PLAC (.+)/', $ged_datum, $matches);
				foreach ($matches[1] as $match) {
					$place_list[$match]=true;
				}
			}
			// Unique list of places
			$place_list=array_keys($place_list);

			// Apply_filter
			if ($country=='XYZ') {
				$filter='.*$';
			} else {
				$filter=preg_quote($country).'$';
				if ($state!='XYZ') {
					$filter=preg_quote($state).', '.$filter;
				}
			}
			$place_list=preg_grep('/'.$filter.'/', $place_list);

			//sort the array, limit to unique values, and count them
			$place_parts=array();
			usort($place_list, "utf8_strcasecmp");
			$i=count($place_list);

			//calculate maximum no. of levels to display
			$x=0;
			$max=0;
			while ($x<$i) {
				$levels=explode(",", $place_list[$x]);
				$parts=count($levels);
				if ($parts>$max) $max=$parts;
			$x++;}
			$x=0;

			//scripts for edit, add and refresh
			?>
			<script>
			function edit_place_location(placeid) {
				window.open('module.php?mod=gov&mod_action=places_edit&action=update&placeid='+placeid, '_blank', gmap_window_specs);
				return false;
			}

			function add_place_location(placeid) {
				window.open('module.php?mod=gov&mod_action=places_edit&action=add&placeid='+placeid, '_blank', gmap_window_specs);
				return false;
			}
			</script>
			<?php

			//start to produce the display table
			$cols=0;
			$subcol=2;
			$span=$max*$subcol;
			echo '<div id="gov">';
			echo '<div class="gov_check_details">';
			echo '<table class="gov_check_details"><tr>';
			echo '<th rowspan="3">', WT_I18N::translate('Place'), '</th>';
			echo '<th colspan="', $span, '">', WT_I18N::translate('GOV data'), '</th></tr>';
			echo '<tr>';
			while ($cols<$max) {
				// tbd: use HEAD PLAC FORM information for headings; see get_plac_label()
				if ($cols == 0) {
					echo '<th colspan="'.$subcol.'">'.WT_I18N::translate('Country').'</th>';
				} else {
					echo '<th colspan="'.$subcol.'">'.WT_I18N::translate('Level').'&nbsp;', $cols+1, '</th>';
				}
				$cols++;
			}
			echo '</tr><tr>';
			$cols=0;
			while ($cols<$max) {
				echo '<th>', WT_Gedcom_Tag::getLabel('PLAC'), '</th>',
				     '<th>', WT_I18N::translate('GOV id'), '</th>';
				$cols++;
			}
			echo '</tr>';
			$countrows=0;
			while ($x<$i) {
				$placestr="";
				$levels=explode(",", $place_list[$x]);
				$parts=count($levels);
				$levels=array_reverse($levels);
				$placestr.="<a href=\"placelist.php?action=show";
				foreach ($levels as $pindex=>$ppart) {
					$ppart=urlencode(trim($ppart));
					$placestr.="&amp;parent[$pindex]=".$ppart."";
				}
				$placestr.="\">".$place_list[$x]."</a>";
				$gedplace="<tr><td>".$placestr."</td>";
				$z=0;
				$y=0;
				$id=0;
				$level=0;
				$matched[$x]=0;// used to exclude places where the gedcom place is matched at all levels
				$mapstr_edit="<a href=\"#\" onclick=\"edit_place_location('";
				$mapstr_add="<a href=\"#\" onclick=\"add_place_location('";
				$mapstr3="";
				$mapstr4="";
				$mapstr5="')\" title='";
				$mapstr6="' >";
				$mapstr7="')\">";
				$mapstr8="</a>";
				while ($z<$parts) {
					if ($levels[$z]==' ' || $levels[$z]=='')
						$levels[$z]="unknown";// GOV module uses "unknown" while GEDCOM uses , ,

					$levels[$z]=rtrim(ltrim($levels[$z]));

					$placelist=gov_create_possible_place_names($levels[$z], $z+1); // add the necessary prefix/postfix values to the place name
					foreach ($placelist as $key=>$placename) {
						$row=
							WT_DB::prepare("SELECT gov_id, gov_place, gov_govid FROM `##gov` WHERE gov_level=? AND gov_parent_id=? AND gov_place LIKE ? ORDER BY gov_place")
							->execute(array($z, $id, $placename))
							->fetchOneRow(PDO::FETCH_ASSOC);
		
						if (!empty($row['gov_id'])) {
							$row['gov_placerequested']=$levels[$z]; // keep the actual place name that was requested so we can display that instead of what is in the db
							break;
						}
					}
					// echo '[row='; var_dump($row); echo ']  ';
					if ($row['gov_id']!='') {
						$id=$row['gov_id'];
					}

					if ($row['gov_place']!='') {
						$placestr2=$mapstr_edit.$id."&amp;level=".$level.$mapstr3.$mapstr5.$mapstr6.$row['gov_placerequested'].$mapstr8;
						if ($row['gov_place']=='unknown')
							$matched[$x]++;
					} else {
						if ($levels[$z]=="unknown") {
							$placestr2=$mapstr_add.$id."&amp;level=".$level.$mapstr3.$mapstr7."<strong>".rtrim(ltrim(WT_I18N::translate('unknown')))."</strong>".$mapstr8;$matched[$x]++;
						} else {
							$placestr2=$mapstr_add.$id."&amp;place_name=".urlencode($levels[$z])."&amp;level=".$level.$mapstr3.$mapstr7.'<span class="error">'.rtrim(ltrim($levels[$z])).'</span>'.$mapstr8;$matched[$x]++;
						}
					}
					$plac[$z]="<td>".$placestr2."</td>\n";
					
					if ($row['gov_govid']!=='') {
						$lati[$z]="<td>".$row['gov_govid']."</td>";
					} else {
						$lati[$z]="<td class='error center'><strong>X</strong></td>";$matched[$x]++;
					}

					$level++;
					$mapstr3=$mapstr3."&amp;parent[".$z."]=".addslashes($row['gov_placerequested']);
					$mapstr4=$mapstr4."&amp;parent[".$z."]=".addslashes(rtrim(ltrim($levels[$z])));
					$z++;
				}
				if ($matching) {
					$matched[$x]=1;
				}
				if ($matched[$x]!=0) {
					echo $gedplace;
					$z=0;
					while ($z<$max) {
						if ($z<$parts) {
							echo $plac[$z];
							echo $lati[$z];
						} else {
							echo '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
						}
						$z++;
					}
					echo '</tr>';
					$countrows++;
				}
				$x++;
			}
			// echo final row of table
			echo '<tr><td colspan="', $span+1, '" class="accepted">', /* I18N: A count of places */ WT_I18N::translate('Total places: %s', WT_I18N::number($countrows)), '</td></tr>';
			echo '</table></div></div>';
			break;
		default:
			// Do not run until user selects a gedcom/place/etc.
			// Instead, show some useful help info.
			echo '<div class="gov_check_top accepted">', WT_I18N::translate('This will list all the places from the selected GEDCOM file. By default this will NOT INCLUDE places that are fully matched between the GEDCOM file and the GOV table.'), '</div>';
			break;
		}
	}
	
	private function getStylesheet() {
		$module_dir = WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/';
		$stylesheet = '';
		if (file_exists($module_dir.WT_THEME_URL.'menu.css')) {
			$stylesheet .= $this->includeCss($module_dir.WT_THEME_URL.'menu.css', 'screen');
		}

		if(WT_Filter::get('mod') == $this->getName()) {
			$stylesheet .= $this->includeCss($module_dir.'themes/base/style.css');
			// $stylesheet .= $this->includeCss($module_dir.'themes/base/print.css', 'print');
			if (file_exists($module_dir.WT_THEME_URL.'style.css')) {
				$stylesheet .= $this->includeCss($module_dir.WT_THEME_URL.'style.css', 'screen');
			}
		}
		return $stylesheet;
	}

	private function includeJs() {
		global $controller;
		// some files needs an extra js script
		$theme = basename(WT_THEME_DIR);
		if (file_exists( WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/'.WT_THEME_URL.$theme.'.js')) {
			$controller->addExternalJavascript(WT_MODULES_DIR.$this->getName().'/'.WT_THEME_URL.$theme.'.js');
		}
	}

	private function includeCss($css, $type = 'all') {
		return
			'<script>
				var newSheet=document.createElement("link");
				newSheet.setAttribute("href","'.$css.'");
				newSheet.setAttribute("type","text/css");
				newSheet.setAttribute("rel","stylesheet");
				newSheet.setAttribute("media","'.$type.'");
				document.getElementsByTagName("head")[0].appendChild(newSheet);
			</script>';
	}
	
	public static function get_plac_label() { // tbd: where is this function necessary ??? 
		
		function get_plac() {
			$head = WT_GedcomRecord::getInstance('HEAD');
			// var_dump($head);
			$plac = $head->getFirstFact('PLAC');
			// var_dump($plac);
			$form = '';
			if ($plac) {
				$form  = $plac->getAttribute('FORM');
				// echo '[form=', $form, '] ';
			}
			return $form;
		}
		
		$head_plac_form = get_plac();
		if (empty($head_plac_form)) $head_plac_form = /* I18N: Do not translate; already in webtrees core */ 'City, County, State/Province, Country';
		return $head_plac_form;
	}	
}
