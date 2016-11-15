<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2015 Franz Holzinger <franz@ttproducts.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Module 'Vouchercode Manager' for the 'voucher' extension.
 *
 * $Id$
 *
 * @author Franz Holzinger <franz@ttproducts.de>
 */


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require('conf.php');
// require ($BACK_PATH . 'init.php');
// require ($BACK_PATH . 'template.php');

$GLOBALS['LANG']->includeLLFile('EXT:' . VOUCHER_EXT . '/mod1/locallang.php');

// require_once (PATH_t3lib . 'class.t3lib_scbase.php');
// require_once (PATH_t3lib . 'class.t3lib_tceforms.php');



$GLOBALS['BE_USER']->modAccess($MCONF, 1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

class tx_voucher_module1 extends t3lib_SCbase {
	public $pageinfo;
	public $tceforms;
	public $timeFieldRow =
		array(
			'starttime' => '0',
			'endtime' => '0',
			'title' => '',
			'reusable' => '0',
			'amount_type' => '0',
			'amount' => '0',
			'code' => '',
			'note' => ''
		);
	public $rowArray = array();
	public $feUserArray = array();

	/**
	 *
	 */
// 	function init ()	{
// 		parent::init();
//
// 		if (t3lib_div::_GP('clear_all_cache'))	{
// 			$this->include_once[]=PATH_t3lib.'class.t3lib_tcemain.php';
// 		}
//
// 	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */
	public function menuConfig () {
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $GLOBALS['LANG'] ->getLL('function1'),
				'2' => $GLOBALS['LANG'] ->getLL('function2'),
				'3' => $GLOBALS['LANG'] ->getLL('function3'),
			)
		);
		parent::menuConfig();
	}

	public function setRow ($row) {
		$this->rowArray[] = $row;
		$this->rowArray = array_unique($this->rowArray);
	}

	public function getRow ($key = '') {
		$rc = '';
		if ($key) {
			$rc = $this->rowArray[$key];
		} else {
			$rc = current($this->rowArray);
		}
		return $rc;
	}

		// If you chose 'web' as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	 * Main function of the module. Write the content to $this->content
	 */
	public function main () {
		global $BACK_PATH, $CLIENT, $TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (
			($this->id && $access) ||
			($GLOBALS['BE_USER']->user['admin'] && !$this->id)
		) {
			$this->tceforms = t3lib_div::makeInstance('t3lib_TCEforms');
			$this->tceforms->initDefaultBEMode();
			$this->tceforms->backPath = $BACK_PATH;

				// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form = '<form action="" method="POST" name="editform" onsubmit="return TBE_EDITOR_checkSubmit(1);">';

				// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
					function select_all(max){
						var i;
						for(i=0; i<max; i++){

							if(document.getElementById("gc"+i).checked == TRUE){
								document.getElementById("gc"+i).checked = FALSE;
								continue;
								}
							if(document.getElementById("gc"+i).checked == FALSE){
								document.getElementById("gc"+i).checked = TRUE;
								}
							}
						}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = ' . intval($this->id) . ';
				</script>
			';
			$headerSection =
				$this->doc->getHeader(
					'pages',
					$this->pageinfo,
					$this->pageinfo['_thePath']) . '<br>' . $GLOBALS['LANG'] ->sL('LLL:EXT:lang/locallang_core.php:labels.path') . ': ' .
					t3lib_div::fixed_lgd_cs($this->pageinfo['_thePath'], 50
				);



			// Render content:

			$moduleContent = $this->moduleContent();

			$this->content .= $this->doc->startPage($GLOBALS['LANG'] ->getLL('title'));
			$this->content .= $this->doc->header($GLOBALS['LANG'] ->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->section(
				'',
				$this->doc->funcMenu(
					$headerSection,
					t3lib_BEfunc::getFuncMenu(
						$this->id,
						'SET[function]',
						$this->MOD_SETTINGS['function'],
						$this->MOD_MENU['function']
					)
				)
			);
			$this->content .= $this->doc->divider(5);
			$uid = $_REQUEST['delete'];
			if ($uid)	{
				$this->deleteRecords($uid, $feUser);
			}
			$this->modifyRecords();
			$jsOut1 = $this->tceforms->printNeededJSFunctions_top();
			$this->content .= $jsOut1;
			$this->content .= $moduleContent;

			// ShortCut
			if ($GLOBALS['BE_USER']->mayMakeShortcut()) {
				$this->content .= $this->doc->spacer(20) . $this->doc->section('', $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']));
			}
			$this->content .= $this->doc->spacer(10);
			$this->content .= $this->doc->endPage();
			$this->content = $this->doc->insertStylesAndJS($this->content);
		} else {
				// If no access or if ID == zero
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content .= $this->doc->startPage($GLOBALS['LANG'] ->getLL('title'));
			$this->content .= $this->doc->header($GLOBALS['LANG'] ->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 */
	public function printContent () {

		$this->content .= $this->doc->endPage();
		echo $this->content;
	}

	public function getVoucherFields ($table, $row, $readonly = FALSE) {

		$this->tceforms->renderReadonly = $readonly;
		$out = '';

		foreach ($this->timeFieldRow as $field => $value) {
			$out .= $this->tceforms->getSingleField($table, $field, $row);
		}
		return $out;
	}

	public function modifyRecords () {

		$feUser = $_REQUEST['edit'];
		$feUserArray = array();

		// if (!$feUser && isset($_REQUEST['vcsave'])) {
		foreach ($_REQUEST as $k => $v) {
			if (
				tx_div2007_core::testInt($k) &&
				$v != ''
			) {
				$feUserArray[] = $k;
			}
		}

		if (!count($feUserArray)) {
			$feUserArray = array('0');
		}

		$this->feUserArray = $feUserArray;

		$table = 'tx_voucher_codes';
		$dataArray = $_REQUEST['data'];

		if (is_array($dataArray)) {
			foreach ($dataArray as $table => $rowArray) {
				foreach ($rowArray as $id => $row) {
					if ($row['title'] != '' && $row['amount'] != '0') {
						if (substr($id, 0, 3) == 'NEW') {
							$this->setRow($row);
							foreach ($feUserArray as $k => $uid) {
								$row['fe_users_uid'] = $uid;
								$GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $row);
							}
						} else {
										// Saving the order record
							$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
								$table,
								'uid=' . intval($id),
								$row
							);
						}
					}
				}
			}
		}
	}

	public function deleteRecords ($uid, $feUser = '0') {
		if($uid) {
			$fieldsArray = array();
			$fieldsArray['deleted'] = 1;
			$result2 =
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
					'tx_voucher_codes',
					$where,
					$fieldsArray
				);
		}
	}

	public function newVoucherInput ($table, $max) {
		$content = '';

		$row = $this->timeFieldRow;

		$theNewID = uniqid('NEW');
		$row['uid'] = $theNewID;
		$voucherOut = $this->getVoucherFields($table, $row, FALSE);

		$content .= '<table>' . $voucherOut . '</table>';
		if ($max) {
			$content .= '<br /><input type="submit" name="vouchercode" value="Gutscheincode zuordnen" />&nbsp;<input type="button" name="selectall" value="alle ausw&auml;hlen" onclick="select_all('.$max.');" /><br /><br />';
		} else {
			$content .= '<br /><input type="submit" name="vouchercode" value="Gutscheincode speichern" />&nbsp;<br /><br />';
		}

		return $content;
	}

	public function getOutputDate ($date) {
		$rc = '';
		if ($date) {
			$rc = date('d-M-Y',$date);
		} else {
			$rc = '-';
		}
		return $rc;
	}

	/**
	 * Generates the module content
	 */
	public function moduleContent () {
		$table = 'tx_voucher_codes';
		t3lib_div::loadTCA($table);

		$content = '';
		$function = (string) $this->MOD_SETTINGS['function'];
		$tableTCA = $GLOBALS['TCA'][$table]['columns'];
		$amountTypeTextArray = array();

		if (isset($GLOBALS['TCA'][$table]['columns']['amount_type']) && is_array($GLOBALS['TCA'][$table]['columns']['amount_type'])) {
			foreach ($GLOBALS['TCA'][$table]['columns']['amount_type']['config']['items'] as $k => $valArray) {
				$v = $valArray['0'];
				$amountTypeTextArray[$k] = $GLOBALS['LANG'] ->sL($v);
			}
		}

		switch($function) {
			case 1:
				$msg = '';
				$cnt = 0;
				$error = FALSE;
				$notEmpty = FALSE;

				if(
					isset($_REQUEST['uid']) &&
					$_REQUEST['uid'] &&
					isset($_REQUEST['vcsave'])
						||
					isset($_REQUEST['edit'])
				) {
					$content = '<h4>' . strtoupper('Gutscheincode Verwaltung') . '</h4>';
					$uid = $_REQUEST['uid'];
					if (!$uid)	{
						$uid = $_REQUEST['edit'];
					}
					$where = 'uid=' . intval($uid);
					$result1 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', $where);

					while ($row1 = mysql_fetch_array($result1)) {
						$content .= '
							<b>Name</b>: ' . $row1['name'] . '<br />
							<b>Adresse</b>: ' . $row1['address'] . '<br />' . $row1['zip'] . ' ' . $row1['city'] . '<br />' . $row1['country']. '
							<br /><b>E-Mail</b>: ' . $row1['email']. '<br /><br />';
						$content .= '<table><tr><td colspan="3"><h4>nicht aktive Gutscheincodes</h4></td></tr><tr><td><b>Gutscheincode</b>:</td><td><b>Typ</b>:</td><td><b>Betrag</b>:</td><td><b>G&uuml;ltigkeitszeitraum</b>:</td></tr>';
						$time = time();
						$where = 'fe_users_uid="' . $row1['uid'] . '"';
						$where .= t3lib_BEfunc::BEenableFields($table, TRUE);
						$result3 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, $where, '', 'code');
						while ($row3 = mysql_fetch_array($result3)) {
							$out = '<tr><td colspan="3"><b>' . $row3['uid'] . ':</b></td></tr>';
							$out .= $this->getVoucherFields($table, $row3);
							$content .= $out;
						}
						$GLOBALS['TYPO3_DB']->sql_free_result($result3);
						$content .= '<tr><td colspan="3"></td></tr><tr><td colspan="3"><h4>aktuelle Gutscheincodes</h4></td></tr>';

						$where = 'fe_users_uid="'.$uid.'"';
						$where .= t3lib_BEfunc::BEenableFields($table, FALSE);
						$result2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, $where, '', 'code');
						while ($row2 = mysql_fetch_array($result2)) {
							$cnt++;
							$out = '<tr><td colspan="3"><b>' . $row2['uid'] . ':</b></td></tr>';
							$out .= $this->getVoucherFields($table, $row2);
							$content .= $out;
						}
						$GLOBALS['TYPO3_DB']->sql_free_result($result2);
					}
					$content .= '<br /><input type="hidden" name="uid" value="' . $_REQUEST['edit'] . '" /><input type="submit" name="vcsave" value="speichern" />&nbsp;<input type="submit" name="back" value="zur&uuml;ck" />';
				} else {
					$where = 'fe_users_uid <> 0 AND not deleted';
					$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('count(*)', 'tx_voucher_codes', $where);

					if ($row) {
						$notEmpty = $row['count(*)'] > '0';
					}

					if($notEmpty == TRUE) {
						$content = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
							 <td style="border-bottom:1px solid #cccccc;"><b>Name</b></td>
							 <td style="border-bottom:1px solid #cccccc;"><b>E-Mail</b></td>
							 <td style="border-bottom:1px solid #cccccc;"><b>Gutscheincodes</b></td>
							 <td style="border-bottom:1px solid #cccccc;"><b>eingel&ouml;ste Gutscheine</b></td>
							 <td style="border-bottom:1px solid #cccccc;">&nbsp;</td></tr>';

						$result2 = mysql_query("Select * from tx_voucher_codes where fe_users_uid <> 0 and not deleted group by fe_users_uid order by fe_users_uid");
						while ($row2 = mysql_fetch_array($result2)) {
							$result = mysql_query("Select * from fe_users where uid='" . $row2['fe_users_uid'] . "'");
							while ($row = mysql_fetch_array($result)) {
								$content .= '<tr>
								<td style="border-left:1px solid #cccccc;border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
								<b>'. $row['name'] .'</b>
								</td>
								<td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
								'. $row['email'] .'
								</td>';
							}
							$codes1 ='';
							$codes2 = '';
							$result1 = mysql_query('Select * from tx_voucher_codes where fe_users_uid = "' . $row2['fe_users_uid'] . '" and deleted = 0');
							while ($row1 = mysql_fetch_array($result1)) {
								$codes1 .= substr($row1['code'], 0, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['codeSize']) . ', ';
							}
							$result1 = mysql_query('Select * from tx_voucher_codes where fe_users_uid = "' . $row2['fe_users_uid'] . '" and deleted = 1');
							while ($row1 = mysql_fetch_array($result1)) {
								$codes2 .= substr($row1['code'], 0, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['codeSize']) . ', ';
							}

							if($codes1 == '')
								$codes1 = '&nbsp;';
							else
								$codes1 = substr($codes1, 0, strlen($codes1) - 2);
							if($codes2 == '')
								$codes2 = '&nbsp;';
							else
								$codes2 = substr($codes2, 0, strlen($codes2) - 2);
							$content .= '<td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
							'. $codes1 .'
							</td>
							<td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
							'. $codes2 .'
							</td>
							<td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
								<input type="hidden" name="' . $row2['fe_users_uid' ] .'" value="1" />
								<input style="background-image: url(edit.gif); background-repeat:no-repeat; background-color:transparent; color: #eeeeee; cursor: pointer; border-style: none; border-color:transparent; height:20px; width:20px;" type="submit" name="edit" title="" alt="Codes bearbeiten" value="' . $row2['fe_users_uid'] . '" />
							</td></tr>';
						}
					} else {
						$content .= '<i>Keine Datens&auml;tze vorhanden.</i>';
					}
				}
			break;
			case 2:
				$msg = '';
				$notEmpty = FALSE;
				$row = $this->getRow();

				//Gutscheincode zuordnen

				if(
					$row['code'] != '' &&
					$row['amount'] != ''
				) {
					if (isset($row['code'])) {
						$content = '<h4>' . strtoupper('Gutscheincode Verwaltung') . '</h4>';
						$where = 'uid IN (' . implode(',', $this->feUserArray) . ') AND deleted = 0';
						$result1 = mysql_query('SELECT * FROM fe_users WHERE ' . $where . ' ORDER BY uid');

						while ($row1 = mysql_fetch_array($result1)){
							if($_REQUEST[$row1['uid']] == 1 || $_REQUEST[$row1['uid']] != '') {
								$content .= '<br />
								<b>Name</b>: ' . $row1['name']. '<br />
								<b>Adresse</b>: ' . $row1['address'] . '<br />' . $row1['zip'] . ' ' .$row1['city'] . '<br />' . $row1['country'] . '
								<br /><b>E-Mail</b>: ' . $row1['email'] . '<br /><br /><table>';
								$result2 = mysql_query('Select * from tx_voucher_codes where deleted = 0 and fe_users_uid = "' . $row1['uid'] . '"');
								while ($row2 = mysql_fetch_array($result2)){
									$content .= '<tr><td>
									<b>Gutscheincode</b>: ' . substr($row2['code'], 0, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['codeSize']) . '</td><td><b>Typ</b>: ' . $amountTypeTextArray[$row2['amount_type']] . '</td><td><b>Betrag</b>: ' . $row2['amount'] . '</td><td><b>G&uuml;tigkeitszeitraum</b>: ' . $this->getOutputDate($row2['starttime']) . ' - ' . $this->getOutputDate($row2['endtime']) . '</td></tr>';
								}
							}
							$content .= '</table>';
						} $content .= '<br /><input type="submit" name="back" value="zur&uuml;ck" />';
					}
				} else {
					$result = mysql_query('Select * from fe_users where deleted = 0 order by uid');
					$max = mysql_num_rows($result);
					$notEmpty = ($max > 0);
					$cnt = 0;


					if($notEmpty == TRUE) {

						$content = $this->newVoucherInput($table, $max);
						$content .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td style="border-bottom:1px solid #cccccc;"><b>Name</b></td>
								<td style="border-bottom:1px solid #cccccc;"><b>E-Mail</b></td>
								<td style="border-bottom:1px solid #cccccc;"><b>Stadt</b></td>
								<td style="border-bottom:1px solid #cccccc;"><b>UID</b></td>
								<td style="border-bottom:1px solid #cccccc;">&nbsp;</td></tr>';

						while ($row = mysql_fetch_array($result)) {
							$content .= '<tr>
							<td style="border-left:1px solid #cccccc;border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
							<b>'. $row['name'] .'</b>
							</td>
							<td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
							'. $row['email'] .'
							</td>
							<td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
							'. $row['city'] .'&nbsp;
							</td>
							<td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;text-align:right;">
							'. $row['uid'] .'
							</td>
							<td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
							<input type="checkbox" name="' . $row['uid'] . '" id="gc' . $cnt. '" value="1" /></td></tr>';
							$cnt++;
						}
						$jsOut2 = $this->tceforms->printNeededJSFunctions();
						$content .= $jsOut2;
					} else {
						$content .= '<i>Keine Datens&auml;tze vorhanden.</i>';
					}
					$content .= '</table>';
				}
			break;
			case 3:
				$content = $this->newVoucherInput($table, 0);

			//	$result1 = mysql_query("Select * from tx_voucher_codes where deleted = 0 and fe_users_uid = 0 group by code order by code");

				$rowArray = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_voucher_codes', 'deleted = 0 and fe_users_uid = 0', 'code', 'code');

				$content .= '<table>';
				foreach ($rowArray as $row1) {
		//		while ($row1 = mysql_fetch_array($result1)) {
					$content .= '
					<tr><td><b>Gutscheincode</b>:</td><td>' . substr($row1['code'], 0, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['codeSize']) . '</td>
						<td><b>Typ</b>:</td><td>' . $amountTypeTextArray[$row1['amount_type']] . '</td>
						<td><b>Betrag</b>:</td><td>' . $row1['amount'] . '</td>
						<td><b>mehrfach</b>:</td><td>' . ($row1['reusable'] ? 'Ja' : 'Nein') . '</td>
						<td><b>Startdatum</b>:</td><td>' . $this->getOutputDate($row1['starttime']) . '</td>
						<td><b>Ablaufdatum</b>:</td><td>' . $this->getOutputDate($row1['endtime']) . '</td>
						<td>
						<input style="background-image: url(garbage.gif); background-repeat:no-repeat; background-color:transparent; color: #eeeeee; cursor: pointer; border-style: none; border-color:transparent; height:20px; width:20px;" type="submit" name="delete" title="" alt="l&ouml;schen" value="' . $row1['uid'] . '" />
						</td>
					</tr>';
				}
				$content .= '</table>';
			break;
		}	// switch
		$jsOut2 = $this->tceforms->printNeededJSFunctions();
		$content .= $jsOut2;
		$content = $this->doc->section($msg, $content, 0, 1);
		return $content;
	}
} // class


	// CHECKING IF THERE ARE AN EXTENSION CLASS CONFIGURED FOR THIS CLASS:
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/voucher/mod1/index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/voucher/mod1/index.php']);
}


// Make instance:
$SOBE = t3lib_div::makeInstance('tx_voucher_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE) {
	include_once($INC_FILE);
}

$SOBE->main();
$SOBE->printContent();
