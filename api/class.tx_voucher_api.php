<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2016 Franz Holzinger (franz@ttproducts.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Part of the voucher (Vouchercode Manager) extension.
 *
 * API functions
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage voucher
 *
 */





class tx_voucher_api {

	static public function getRowFromCode ($theCode, $bEnable = TRUE) {

		$result = FALSE;

		if ($theCode != '') {
			$voucherTable = 'tx_voucher_codes';

			$where_clause =
				'code=' . $GLOBALS['TYPO3_DB']->fullQuoteStr(
					$theCode,
					$voucherTable
				);

			if ($bEnable) {
				$where_enable =  tx_div2007_alpha5::enableFields($voucherTable);
				$where_clause .= $where_enable;

				$where_enable = 'AND (usecounter>0 OR reusable=1 OR reusable=2)';
				$where_clause .= $where_enable;
			}

			$rowArray = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $voucherTable, $where_clause);
			if (is_array($rowArray)) {
				$result = $rowArray['0'];
			}
		}

		return $result;
	}

	static public function reduceCountOrDisable (array $codeRow) {

		if (
			$codeRow['uid'] &&
			$codeRow['deleted'] == '0' &&
			$codeRow['reusable'] != '1' &&
			$codeRow['reusable'] != '2'
		) {
			$voucherTable = 'tx_voucher_codes';
			$row = array();
			$where_clause = 'uid=' . intval($codeRow['uid']);

			if (
				$codeRow['usecounter'] > '1'
			) {
				$row['usecounter'] = $codeRow['usecounter'] - 1;
			} else if (
				$codeRow['usecounter'] == 1
			) {
				$row['usecounter'] = 0;
			}

			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				$voucherTable,
				$where_clause,
				$row
			);
		}
	}

	static public function redeemVoucher (
		$code,
		$theTable,
		&$row,
		&$newFieldList,
		&$errorCode
	) {
		$result = FALSE;

		if ($theTable == 'fe_users' && $code != '') {
			$codeRow = self::getRowFromCode($code, FALSE);
			if (
				is_array($codeRow) &&
				$codeRow['uid'] &&
				$codeRow['acquired_groups'] != ''
			) {
				$newFieldArray = array();
				if ($newFieldList != '') {
					$newFieldArray = explode(',', $newFieldList);
				}
				$origGroupArray = $row['usergroup'];
				if (!is_array($row['usergroup'])) {
					$origGroupArray = explode(',', $row['usergroup']);
				}
				$codeGroupArray = explode(',', $codeRow['acquired_groups']);
				$newGroupArray = array_merge($origGroupArray, $codeGroupArray);
				$newGroupArray = array_unique($newGroupArray);
				$row['usergroup'] = implode(',', $newGroupArray);
				$newFieldArray[] = 'usergroup';

				$time = time();
				$acquiredSeconds = 0;

				if (!empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['serverTimeZone'])) {
					$time += ($GLOBALS['TYPO3_CONF_VARS']['SYS']['serverTimeZone'] * 3600);
				}

				if ($codeRow['acquired_days']) {
					$acquiredSeconds = $codeRow['acquired_days'] * 24 * 60 * 60;
				}

				$newFieldArray = array_unique($newFieldArray);
				$newFieldList = implode(',', $newFieldArray);

				$result = self::addLimitedGroups(
					$codeRow,
					$time,
					$acquiredSeconds,
					$row['uid'],
					$codeGroupArray,
					$errorCode,
					0
				);
			}
		}

		return $result;
	}

	static public function checkCodeFormerlyUsed (
		$code,
		$rowArray,
		&$errorCode
	) {
		$result = TRUE;

		if (
			is_array($rowArray) &&
			count($rowArray)
		) {
			foreach ($rowArray as $row) {
				$codeArray = explode(',', $row['codes']);
				if (in_array($code, $codeArray)) {
					$result = FALSE;
					$errorCode = 1;
					break;
				}
			}
		}

		return $result;
	}

	/* $errorCode: 1 ... code has already been used.
	 *             2 ... database write error
	*/
	static public function addLimitedGroups (
		$codeRow,
		$time,
		$acquiredSeconds,
		$theUser,
		$groupArray,
		&$errorCode,
		$pid = 0
	) {
		$code = $codeRow['code'];
		$result = TRUE;
		$errorCode = 0;

		if (is_array($groupArray)) {
			$addGroupArray = array();

			$fieldArray = array (
				'pid' => intval($pid),
				'tstamp' => $time,
				'deleted' => 0,
				'hidden' => 0,
			);

			if ($acquiredSeconds) {
				$fieldArray['endtime'] = $time + $acquiredSeconds;
			}
			$table = 'sys_agency_fe_users_limit_fe_groups';
			$where_clause = 'fe_users_uid=' . intval($theUser);

			$rowArray =
				$GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'*',
					$table,
					$where_clause
				);

			if (
				is_array($rowArray) &&
				count($rowArray)
			) {
				$codeValid = TRUE;

				if (
					$codeRow['reusable'] != '2' &&
					$codeRow['reusable'] != '4'
				) {
					$codeValid =
						self::checkCodeFormerlyUsed(
							$code,
							$rowArray,
							$errorCode
						);
				}

				if (!$codeValid) {
					$result = FALSE;
				} else {
					$foundGroupArray = array();

					foreach ($rowArray as $row) {
						if (in_array($row['fe_groups_uid'], $groupArray)) {
							$codeArray = explode(',', $row['codes']);
							if (
								!in_array($code, $codeArray) ||
								$codeRow['reusable']
							) {
								$updateFields = $fieldArray;
								$endtime = $row['endtime'];
								if ($time > $endtime) {
									$endtime = $time;
								}
								$updateFields['endtime'] = $endtime + $acquiredSeconds; // add new time to an already acquired group
								if (!in_array($code, $codeArray)) {
									$updateFields['codes'] = $row['codes'] . ',' . $code;
								}
								$where = $where_clause . ' AND fe_groups_uid=' . intval($row['fe_groups_uid']);
								$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $updateFields);
							}
						}
						$foundGroupArray[] = $row['fe_groups_uid'];
					}

					$addGroupArray = array_diff($groupArray, $foundGroupArray);
				}
			} else {
				$addGroupArray = $groupArray;
			}

			if (count($addGroupArray)) {

				$insertFields = $fieldArray;
				$insertFields['crdate'] = $time;

				foreach ($addGroupArray as $theGroup) {
					$insertFields['fe_users_uid'] = intval($theUser);
					$insertFields['fe_groups_uid'] = intval($theGroup);
					$insertFields['codes'] = $code;

					$insertResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insertFields);
					$newId = 0;
					if ($insertResult) {
						$newId = $GLOBALS['TYPO3_DB']->sql_insert_id();
					}
					if (!$newId) {
						$result = FALSE;
						break;
					}
				}
			}
		}

		return $result;
	}

	static public function removeOutdatedGroups () {
		$result = FALSE;
		$table = 'fe_users';

		$time = time();
		$acquiredSeconds = 0;

		if (!empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['serverTimeZone'])) {
			$time += ($GLOBALS['TYPO3_CONF_VARS']['SYS']['serverTimeZone'] * 3600);
		}

		$where_clause = '1=1';
		$where_enable = tx_div2007_alpha5::enableFields($table);
		$where_clause .= $where_enable;
		$rowArray = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $table, $where_clause);

		if (
			isset($rowArray) &&
			is_array($rowArray)
		) {
			foreach ($rowArray as $row) {
				if (!$row['usergroup']) {
					continue;
				}
				$sysTable = 'sys_agency_fe_users_limit_fe_groups';
				$where_clause_feuser =
					$sysTable . '.fe_users_uid=' . intval($row['uid']);
				$where_clause = $where_clause_feuser;
				$where_clause .= ' AND ' . $sysTable . '.deleted=0 AND ' . $sysTable . '.hidden=0';
				$ctrl = $GLOBALS['TCA'][$sysTable]['ctrl'];

				if (is_array($ctrl)) {

					if ($ctrl['enablecolumns']['endtime']) {
						$field = $sysTable . '.' . $ctrl['enablecolumns']['endtime'];
						$offsetDays = 1; // offset to the current date
						$offsetSeconds = $offsetDays * 24 * 60 * 60;
						$where_clause .= ' AND (' . $field . '>0 AND ' . $field . '<' . ($GLOBALS['SIM_ACCESS_TIME'] - $offsetSeconds) . ')';
					}
				}

				$groupOutdatedArray =
					$GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
						'*',
						$sysTable,
						$where_clause,
						'',
						'',
						'',
						'fe_groups_uid'
					);

				if (
					isset($groupOutdatedArray) &&
					is_array($groupOutdatedArray) &&
					count($groupOutdatedArray)
				) {
					$outdatedGroupIds = array_keys($groupOutdatedArray);
					$currentGroupIds = explode(',', $row['usergroup']);
					$remainingGroupIds = array_diff($currentGroupIds, $outdatedGroupIds);
					$fieldArray = array();
					$fieldArray['usergroup'] = implode(',', $remainingGroupIds);

					$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
						$table,
						'uid=' . intval($row['uid']),
						$fieldArray
					);

					$fieldArray = array();
					$fieldArray['deleted'] = 1;
					$fieldArray['tstamp'] = $time;

					$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
						$sysTable,
						$where_clause,
						$fieldArray
					);
				}
			}
		}
		$result = TRUE;

		return $result;
	}

	static public function getGroupRowsByUser ($theUser, $bEnable = TRUE) {

		$result = FALSE;

		if ($theUser != '') {
			$sysTable = 'sys_agency_fe_users_limit_fe_groups';

			$where_clause =
				'fe_users_uid=' . intval($theUser);

			if ($bEnable) {
				$where_enable = tx_div2007_alpha5::enableFields($sysTable);
				$where_clause .= $where_enable;
			}

			$rowArray = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $sysTable, $where_clause);
			$result = $rowArray;
		}

		return $result;
	}

	static public function getCodesByUser (
		$theUser,
		$bEnable = TRUE
	) {
		$rowArray = self::getGroupRowsByUser($theUser, $bEnable);

		$result = FALSE;

		if (
			$rowArray != FALSE &&
			is_array($rowArray)
		) {
			$codesArray = array();
			if (is_array($rowArray)) {
				foreach ($rowArray as $row) {
					$codesArray[] = $row['codes'];
				}
			}
			$codes = implode(',', $codesArray);
			$codesArray = explode(',', $codes);
			$codesArray = array_unique($codesArray);
			$result = implode(',', $codesArray);
		}

		return $result;
	}


	static public function groupOut (
		$cObj,
		$feUserRow,
		$showGroupTimeRange = FALSE,
		$where_clause = '',
		$groupEnable = TRUE,
		$expirationText = '',
		array $headerTextArray = array() // required indexes: group, expiration, voucher
	) {
		$groupTable = 'fe_groups';
		$groupCodeRowArray = FALSE;
		$classTd = '<div class="td">';
		$classTr = '<div class="tr">';
		$classTable = '<div class="table">';
		$divEnd = '</div>';

		if ($showGroupTimeRange) {
			$groupCodeRowArray = self::getGroupRowsByUser($feUserRow['uid'], TRUE);
		}

		if ($where_clause == '') {
			$where_clause = 'uid IN (' . $feUserRow['usergroup'] . ')';
		}

		if ($groupEnable) {
			$where_enable = $cObj->enableFields($groupTable);
			$where_clause .= $where_enable;
		}

		$groupRowArray = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $groupTable, $where_clause);

		$result = '';
		$outArray = array();
		$groupOutArray = array();

		if (is_array($groupRowArray)) {
			foreach ($groupRowArray as $groupRow) {
				$out = '';
				$outDetails = '';

				if (
					$groupCodeRowArray &&
					is_array($groupCodeRowArray)
				) {
					$foundGroup = FALSE;
					foreach($groupCodeRowArray as $groupCodeRow) {
						if ($groupCodeRow['fe_groups_uid'] == $groupRow['uid']) {
							$enddate = '';
							if ($groupCodeRow['endtime']) {
								$enddate =  date('Y-m-d', $groupCodeRow['endtime']);
							}
							$outDetails = $classTd . $expirationText . ' ' . $enddate . $divEnd;
							$outDetails .= $classTd . $groupCodeRow['codes'] . $divEnd;
							$foundGroup = TRUE;
							break;
						}
					}
				}
				$out = $classTr . $classTd . $groupRow['description'] . $divEnd . $outDetails . $divEnd;
				$groupOutArray[] = $out;
			}
		}

		$headerHtml = '';
		foreach ($headerTextArray as $headerText) {
			$headerHtml .= $classTd . $headerText . $divEnd;
		}
		$outArray[] = $classTr . $headerHtml . $divEnd;
		$outArray[] = implode('', $groupOutArray) . '<br />';
		$result = $classTable . implode('', $outArray) . $divEnd;
		return $result;
	}


	/* $developer: TRUE during development. This is needed to deactivate the  browser´s cache for Javascript */
	static public function getVoucherAjaxUrl (
		$extKey,
		$cObj,
		$developer = FALSE
	) {
		$queryString = array(
			'L' => t3lib_div::_GP('L'),
			'no_cache' => 1,
			'eID' => $extKey,
		);

		if ($developer) {
			$queryString['time'] = time();
		}

		$linkConf = array('useCacheHash' => 0);

		$target = '';
		$reqURI = tx_div2007_alpha5::getTypoLink_URL_fh003(
			$cObj,
			$GLOBALS['TSFE']->id,
			$queryString,
			$target,
			$linkConf
		);

		return $reqURI;
	}

	// origin: http://stackoverflow.com/questions/1846202/php-how-to-generate-a-random-unique-alphanumeric-string/13733588#13733588
	static public function crypto_rand_secure ($minimum, $maximum) {
		$range = $maximum - $minimum;
		if ($range < 1) return $minimum; // not so random...
		$log = ceil(log($range, 2));
		$bytes = (int) ($log / 8) + 1; // length in bytes
		$bits = (int) $log + 1; // length in bits
		$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
			$rnd = $rnd & $filter; // discard irrelevant bits
		} while ($rnd >= $range);
		return $minimum + $rnd;
	}

	static public function getToken (
		$length,
		$onlyUppercase = TRUE,
		$specialCharacters = FALSE
	) {
		$token = "";
		$codeAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if (!$onlyUppercase) {
			$codeAlphabet .= strtolower($codeAlphabet);
		}
		$codeAlphabet .= '0123456789';
		if ($specialCharacters) {
			$codeAlphabet .= '§$%&/()[]{}+-#|<>';
		}
		$maximum = strlen($codeAlphabet) - 1;
		for ($i=0; $i < $length; $i++) {
			$token .= $codeAlphabet[self::crypto_rand_secure(0, $maximum)];
		}
		return $token;
	}

	static public function insertVoucher (
		&$row,
		$onlyUppercase = TRUE,
		$specialCharacters = FALSE
	) {
		$result = TRUE;

		$table = 'tx_voucher_codes';
		if (!is_array($row)) {
			$row = array();
		}
		$newRow = array();
		$time = time();
		if (!empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['serverTimeZone'])) {
			$time += ($GLOBALS['TYPO3_CONF_VARS']['SYS']['serverTimeZone'] * 3600);
		}

		$allowedFields = array_keys($GLOBALS['TCA'][$table]['columns']);
		$allowedFields = array_merge(array('tstamp', 'crdate', 'deleted'), $allowedFields);
		$title = 'AUTO';
		if (isset($row['title'])) {
			$title = $row['title'];
		}

		$newRow['tstamp'] = $time;
		$newRow['crdate'] = $time;
		$newRow['title'] = $title . '-' . date('Y-m-d H:i:s', $time);
		$newRow['code'] = self::getToken($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['codeSize']);

		foreach ($row as $field => $value) {
			if (
				isset($value) &&
				in_array($field, $allowedFields)
			) {
				$newRow[$field] = $value;
			}
		}

		$GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $newRow);
		$newId = $GLOBALS['TYPO3_DB']->sql_insert_id();

		if ($newId) {
			$newRow['uid'] = $newId;
			$row = $newRow;
		} else {
			$result = FALSE;
		}

		return $result;
	}
}

