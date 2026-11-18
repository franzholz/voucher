<?php

declare(strict_types=1);

namespace JambageCom\Voucher\Hooks\Agency;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019-2026 Franz Holzinger (franz@ttproducts.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://gnu.org.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

use JambageCom\Agency\Api\Localization;
use JambageCom\Agency\Request\Parameters;
use JambageCom\Voucher\Api\Api;

/**
 * Part of the voucher (Vouchercode Manager) extension.
 *
 * Hook for agency markers and registration validation
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 */
class Agency
{
    public ?Localization $languageObj = null;
    public bool $bHasBeenInitialised = false;
    public string $scriptRelPath = 'Classes/Hooks/Agency/Agency.php';

    public function init(mixed $dataObject = null): void
    {
        $this->languageObj = GeneralUtility::makeInstance(Localization::class);

        $request = $GLOBALS['TYPO3_REQUEST'] ?? $GLOBALS['REQUEST'] ?? null;
        $frontendTypoScriptSetupArray = [];
        if ($request instanceof ServerRequestInterface) {
            $frontendTypoScript = $request->getAttribute('frontend.typoscript');
            if ($frontendTypoScript !== null && method_exists($frontendTypoScript, 'getSetupArray')) {
                $frontendTypoScriptSetupArray = $frontendTypoScript->getSetupArray();
            }
        }

        $agencyExt = defined('AGENCY_EXT') ? AGENCY_EXT : 'agency';
        $languageSubpath = defined('DIV2007_LANGUAGE_SUBPATH') ? DIV2007_LANGUAGE_SUBPATH : '/Resources/Private/Language/';
        $conf = $frontendTypoScriptSetupArray['plugin.'][$agencyExt . '.'] ?? [];

        $this->languageObj->init(
            'voucher',
            $conf['_LOCAL_LANG.'] ?? [],
            'EXT:voucher' . $languageSubpath
        );

        $this->languageObj->loadLocalLang(
            'locallang_agency.xlf',
            false
        );

        $this->bHasBeenInitialised = true;
    }

    public function needsInit(): bool
    {
        return !$this->bHasBeenInitialised;
    }

    /**
     * Sets the value of captcha markers
     */
    public function addGlobalMarkers(
        array &$markerArray,
        object $controlData,
        object $confObj,
        object $markerObject
    ): void {
        if ($this->needsInit()) {
            $this->init();
        }

        $cmdKey = $controlData->getCmdKey();
        $conf = $confObj->getConf();
        $voucherMarkerArray = [];

        if (($conf[$cmdKey . '.']['evalValues.']['captcha_response'] ?? '') === 'voucher') {
            $iconPath = PathUtility::getPublicResourceWebPath('EXT:voucher/Resources/Public/Icons/VoucherCodes.gif');
            $voucherMarkerArray['###VOUCHER_IMAGE###'] = '<img src="' . htmlspecialchars($iconPath) . '" alt="" />';
            $voucherMarkerArray['###VOUCHER_NOTICE###'] = $this->languageObj?->getLabel('notice') ?? '';
        } else {
            $voucherMarkerArray['###VOUCHER_IMAGE###'] = '';
        }

        $markerArray = array_merge($markerArray, $voucherMarkerArray);
    }

    /**
     * Evaluates the voucher code
     */
    public function evalValues(
        mixed $staticInfoObj,
        string $theTable,
        array $dataArray,
        array $origArray,
        array $markContentArray,
        string $cmdKey,
        array $requiredArray,
        array $checkFieldArray,
        string $theField,
        array $cmdParts,
        bool $bInternal,
        mixed &$test,
        mixed $dataObject
    ): string {
        $errorField = '';

        if (
            $theField === 'captcha_response'
            && trim((string)($cmdParts[0] ?? '')) === 'voucher'
            && isset($dataArray[$theField])
        ) {
            $rowArray = Api::getRowFromCode((string)$dataArray[$theField]);
            if (!$rowArray) {
                $errorField = $theField;
            }
        }

        return $errorField;
    }

    public function getFailureText(
        string $failureText,
        array $dataArray,
        string $theField,
        string $theRule,
        string $label,
        string|int $orderNo = '',
        string $param = '',
        bool $bInternal = false
    ): string {
        if ($this->needsInit()) {
            $this->init();
        }

        $errorText = '';

        if ($theRule !== '') {
            $labelname = 'evalErrors_' . $theRule . '_' . $theField;
            $errorText = (string)($this->languageObj?->getLabel($labelname) ?? '');
            if ($errorText !== '') {
                $errorText = sprintf($errorText, (string)($dataArray[$theField] ?? ''));
            }
        }

        return $errorText;
    }

    public function registrationProcess_afterSaveCreate(
        Parameters $parameters,
        string $theTable,
        array $dataArray,
        array $origArray,
        string $token,
        array &$newRow,
        string $cmd,
        string $cmdKey,
        int $pid,
        string $extraList,
        object $pObj
    ): bool {
        $result = true;
        $newFieldList = '';
        $conf = $parameters->getConf();
        $uid = (int)($dataArray['uid'] ?? 0);
        $code = (string)($dataArray['captcha_response'] ?? '');
        $usedCode = (string)($newRow['tx_voucher_usedcode'] ?? '');

        if (
            $uid > 0
            && $code !== ''
            && $usedCode === ''
        ) {
            $codeRow = Api::getRowFromCode($code);

            if (is_array($codeRow)) {
                Api::reduceCountOrDisable($codeRow);
            }

            if (empty($conf['enableEmailConfirmation'])) {
                $errorCode = 0;
                $result = Api::redeemVoucher(
                    $code,
                    'fe_users',
                    $newRow,
                    $newFieldList,
                    $errorCode
                );
            }

            if ($result) {
                $updateFields = [
                    'tx_voucher_usedcode' => $code,
                ];

                if ($newFieldList !== '') {
                    $newFieldArray = GeneralUtility::trimExplode(',', $newFieldList, true);
                    foreach ($newFieldArray as $newField) {
                        if (array_key_exists($newField, $newRow)) {
                            $updateFields[$newField] = $newRow[$newField];
                        }
                    }
                }

                GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getConnectionForTable($theTable)
                    ->update(
                        $theTable,
                        $updateFields,
                        ['uid' => $uid]
                    );
            }
        }

        return $result;
    }

    public function confirmRegistrationClass_preProcess(
        Parameters $parameters,
        string $theTable,
        array &$row,
        string &$newFieldList,
        object $pObj,
        int|string &$errorCode
    ): bool {
        $result = true;
        $conf = $parameters->getConf();

        if (
            !$errorCode
            && empty($conf['enableAdminReview'])
        ) {
            $intErrorCode = (int)$errorCode;
            $result = Api::redeemVoucher(
                (string)($row['tx_voucher_usedcode'] ?? ''),
                $theTable,
                $row,
                $newFieldList,
                $intErrorCode
            );
            if ($intErrorCode !== 0) {
                $errorCode = $intErrorCode;
            }
        }

        return $result;
    }
}
