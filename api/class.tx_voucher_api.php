<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2014 Franz Holzinger (franz@ttproducts.de)
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
 * $Id$
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
			$cObj = t3lib_div::getUserObj('&tx_div2007_cobj');
			$voucherTable = 'tx_voucher_codes';

			$where_clause =
				'code=' . $GLOBALS['TYPO3_DB']->fullQuoteStr(
					$theCode,
					$voucherTable
				);

			if ($bEnable) {
				$where_enable = $cObj->enableFields($voucherTable);
				$where_clause .= $where_enable;

				$where_enable = 'AND (usecounter>0 OR reusable=1)';
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
			!$codeRow['reusable']
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
		$theTable,
		&$row,
		&$newFieldList
	) {
		if ($theTable == 'fe_users' && $row['tx_voucher_usedcode'] != '') {
			$codeRow = self::getRowFromCode($row['tx_voucher_usedcode'], FALSE);
			if (is_array($codeRow) && $codeRow['uid'] && $codeRow['acquired_groups'] != '') {
				$newFieldArray = explode(',', $newFieldList);
				$origGroupArray = explode(',', $row['usergroup']);
				$codeGroupArray = explode(',', $codeRow['acquired_groups']);
				$newGroupArray = array_merge($origGroupArray, $codeGroupArray);
				$newGroupArray = array_unique($newGroupArray);
				$row['usergroup'] = implode(',', $newGroupArray);
				$newFieldArray[] = 'usergroup';

				if ($codeRow['acquired_days']) {
					$row['endtime'] = time() + ($codeRow['acquired_days'] * 24 * 60 * 60);
					$newFieldArray[] = 'endtime';
				}

				$newFieldArray = array_unique($newFieldArray);
				$newFieldList = implode(',', $newFieldArray);
			}
		}
	}
}

?>