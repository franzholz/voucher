<?php

namespace JambageCom\Voucher\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2019 Franz Holzinger (franz@ttproducts.de)
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
 * @author	Franz Holzinger <franz@ttproducts.de>
 *
 * @package TYPO3
 * @subpackage agency
 *
 *
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

use JambageCom\Agency\Request\Parameters;

use JambageCom\Voucher\Api\Api;

/**
 * Hook for agency markers
 */
class Agency {
    public $languageObj;
    public $bHasBeenInitialised = false;
    public $scriptRelPath = 'Classes/Hooks/Agency/Agency.php'; // Path to this script relative to the extension dir.

    public function init (
        $dataObject
    )
    {
        $this->languageObj = GeneralUtility::makeInstance(\JambageCom\Agency\Api\Localization::class);
        $cObj = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
        $conf = $GLOBALS['TSFE']->tmpl->setup['plugin.'][AGENCY_EXT . '.'];

        $this->languageObj->init(
            'voucher',  // Todo: replace voucher by setup value
            $conf['_LOCAL_LANG.'],
            'EXT:' . VOUCHER_EXT . DIV2007_LANGUAGE_SUBPATH
        );

        $this->languageObj->loadLocalLang(
            'locallang_agency.xlf'
            false
        );

        $this->bHasBeenInitialised = true;
    }

    public function needsInit ()
    {
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
    )
    {
        $cmdKey = $controlData->getCmdKey();
        $conf = $confObj->getConf();

        $voucherMarkerArray = array();

        if ($conf[$cmdKey . '.']['evalValues.']['captcha_response'] == 'voucher') {
            $voucherMarkerArray['###VOUCHER_IMAGE###'] = '<img src="' . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('voucher') . 'icon_tx_voucher_codes.gif" alt="" />';
            $labelname = 'notice';
            $voucherMarkerArray['###VOUCHER_NOTICE###'] = $this->languageObj->getLabel($labelname);
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
    )
    {
        $errorField = '';

        if (
            $theField == 'captcha_response' &&
            trim($cmdParts[0]) == 'voucher' && // Todo: do not use voucher but check the $conf setup for voucher
            isset($dataArray[$theField])
        ) {
            $rowArray = Api::getRowFromCode($dataArray[$theField]);
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
        $bInternal = false
    )
    {
        $errorText = '';

        if ($theRule) {
            $labelname = 'evalErrors_' . $theRule . '_' . $theField;
            $errorText = $this->languageObj->getLabel($labelname);
            if ($errorText) {
                $errorText = sprintf($errorText, $dataArray[$theField]);
            }
        }

        return $errorText;
    }

    public function registrationProcess_afterSaveCreate (
        Parameters $parameters,
        $theTable,
        array $dataArray,
        array $origArray,
        $token,
        array &$newRow,
        $cmd,
        $cmdKey,
        $pid,
        $extraList,
        Data $pObj
    )
    {
        $result = true;
        $newFieldList = '';
        $conf = $parameters->getConf();

        if (
            is_int($dataArray['uid']) &&
            $dataArray['captcha_response'] != '' &&
            $newRow['tx_voucher_usedcode'] == ''
        ) {
            $code = $dataArray['captcha_response'];
            $codeRow = Api::getRowFromCode($code);

            if (is_array($codeRow)) {
                Api::reduceCountOrDisable($codeRow);
            }

            if (
                !$conf['enableEmailConfirmation']
            ) {
                $errorCode = '';
                $result =
                    Api::redeemVoucher(
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
        Parameters $parameters,
        $theTable,
        array $row,
        $newFieldList,
        SetFixed $pObj,
        &$errorCode
    )
    {
        $result = true;
        $conf = $parameters->getConf();

        if (
            !$errorCode &&
            !$conf['enableAdminReview']
        ) {
            $result =
                Api::redeemVoucher(
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


