<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2014 Franz Holzinger <franz@ttproducts.de>
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
 * Part of the voucher (Vouchercode Manager) extension.
 *
 * voucher hook functions
 *
 * $Id$
 *
 * @author	Stanislas Rolland <typo3(arobas)sjbr.ca>
 *
 * @package TYPO3
 * @subpackage agency
 *
 *
 */


/**
 * Hook for agency markers
 */
class tx_voucher_agency {
	public $langObj;
	public $bHasBeenInitialised = FALSE;
	public $scriptRelPath = 'hooks/agency/class.tx_voucher_agency.php'; // Path to this script relative to the extension dir.

	public function init (
		$dataObject
	) {
		$this->langObj = t3lib_div::getUserObj('&tx_agency_lang');
		$cObj = t3lib_div::getUserObj('&tx_div2007_cobj');
		$conf = array();

		$this->langObj->init(
			$this,
			$cObj,
			$conf,
			$this->scriptRelPath,
			'voucher' // Todo: replace voucher by setup value
		);
		tx_div2007_alpha5::loadLL_fh002($this->langObj, 'EXT:' . VOUCHER_EXT . '/hooks/agency/locallang.xml');

		$this->bHasBeenInitialised = TRUE;
	}

	public function needsInit () {
		return !$this->bHasBeenInitialised;
	}

	/**
	 * Sets the value of captcha markers
	 */
	public function addGlobalMarkers (
		&$markerArray,
		$controlData,
		$confObj,
		$markerObject
	) {
		$cmdKey = $controlData->getCmdKey();
		$conf = $confObj->getConf();

		$voucherMarkerArray = array();

		if ($conf[$cmdKey . '.']['evalValues.']['captcha_response'] == 'voucher') {
			$voucherMarkerArray['###VOUCHER_IMAGE###'] = '<img src="' . t3lib_extMgm::siteRelPath('voucher') . 'icon_tx_voucher_codes.gif" alt="" />';
			$labelname = 'notice';
			$voucherMarkerArray['###VOUCHER_NOTICE###'] = $this->langObj->getLL($labelname);
		} else {
			$voucherMarkerArray['###VOUCHER_IMAGE###'] = '';
		}

		$markerArray = array_merge($markerArray, $voucherMarkerArray);
	}

	/**
	 * Evaluates the voucher code
	 */
	public function evalValues (
		$staticInfoObj,
		$theTable,
		$dataArray,
		$origArray,
		$markContentArray,
		$cmdKey,
		$requiredArray,
		$checkFieldArray,
		$theField,
		$cmdParts,
		$bInternal,
		&$test,
		$dataObject
	) {
		$errorField = '';

		if (
			$theField == 'captcha_response' &&
			trim($cmdParts[0]) == 'voucher' && // Todo: do not use voucher but check the $conf setup for voucher
			isset($dataArray[$theField])
		) {
			$rowArray = tx_voucher_api::getRowFromCode($dataArray[$theField]);
			if (
				!$rowArray
			) {
				$errorField = $theField;
			}
		}

		return $errorField;
	}

	public function getFailureText (
		$failureText,
		$dataArray,
		$theField,
		$theRule,
		$label,
		$orderNo = '',
		$param = '',
		$bInternal = FALSE
	) {
		$errorText = '';

		if ($theRule) {
			$labelname = 'evalErrors_' . $theRule . '_' . $theField;
			$errorText = $this->langObj->getLL($labelname);
			if ($errorText) {
				$errorText = sprintf($errorText, $dataArray[$theField]);
			}
		}

		return $errorText;
	}

	public function registrationProcess_afterSaveCreate (
		tx_agency_controldata $controlDataObj,
		$theTable,
		array $dataArray,
		array $origArray,
		$token,
		array &$newRow,
		$cmd,
		$cmdKey,
		$pid,
		$fieldList,
		tx_agency_data $pObj
	) {
		$result = TRUE;
		$newFieldList = '';
		$conf = $controlDataObj->getConf();

		if (
			is_int($dataArray['uid']) &&
			$dataArray['captcha_response'] != '' &&
			$newRow['tx_voucher_usedcode'] == ''
		) {
			$code = $dataArray['captcha_response'];
			$codeRow = tx_voucher_api::getRowFromCode($code);

			if (is_array($codeRow)) {
				tx_voucher_api::reduceCountOrDisable($codeRow);
			}

			if (
				!$conf['enableEmailConfirmation']
			) {
				$errorCode = '';
				$result =
					tx_voucher_api::redeemVoucher(
						$code,
						'fe_users',
						$newRow,
						$newFieldList,
						$errorCode
					);
			}

			if ($result) {
				$row = array();
				$row['tx_voucher_usedcode'] = $code;
				if ($newFieldList != '') {
					$newFieldArray = explode(',', $newFieldList);
					foreach ($newFieldArray as $newField) {
						if ($newField != '') {
							$row[$newField] = $newRow[$newField];
						}
					}
				}
				$where_clause = 'uid = ' . intval($dataArray['uid']);
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
					$theTable,
					$where_clause,
					$row
				);
			}
		}

		return $result;
	}

	public function confirmRegistrationClass_preProcess (
		tx_agency_controldata $controlDataObj,
		$theTable,
		&$row,
		&$newFieldList,
		$invokingObj,
		$errorCode
	) {
		$result = TRUE;
		$conf = $controlDataObj->getConf();

		if (
			!$errorCode &&
			!$conf['enableAdminReview']
		) {
			$result =
				tx_voucher_api::redeemVoucher(
					$row['tx_voucher_usedcode'],
					$theTable,
					$row,
					$newFieldList,
					$errorCode
				);
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/voucher/hooks/agency/class.tx_voucher_agency.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/voucher/hooks/agency/class.tx_voucher_agency.php']);
}

?>