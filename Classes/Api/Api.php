<?php

declare(strict_types=1);

namespace JambageCom\Voucher\Api;

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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Page\PageInformation;

use JambageCom\Div2007\Utility\FrontendUtility;
use JambageCom\Div2007\Utility\HtmlUtility;

/**
 * Part of the voucher (Vouchercode Manager) extension.
 *
 * API functions
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 */
class Api
{
    private const TABLE_VOUCHER_CODES = 'tx_voucher_codes';
    private const TABLE_LIMIT_GROUPS = 'sys_agency_fe_users_limit_fe_groups';

    public static function getRowFromCode(
        string $theCode,
        bool $bEnable = true
    ): array|false {
        if ($theCode === '') {
            return false;
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_VOUCHER_CODES);

        if (!$bEnable) {
            $queryBuilder->getRestrictions()->removeAll();
        }

        $queryBuilder
            ->select('*')
            ->from(self::TABLE_VOUCHER_CODES)
            ->where(
                $queryBuilder->expr()->eq(
                    'code',
                    $queryBuilder->createNamedParameter($theCode)
                )
            );

        if ($bEnable) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->gt('usecounter', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
                    $queryBuilder->expr()->eq('reusable', $queryBuilder->createNamedParameter(1, Connection::PARAM_INT)),
                    $queryBuilder->expr()->eq('reusable', $queryBuilder->createNamedParameter(2, Connection::PARAM_INT))
                )
            );
        }

        $row = $queryBuilder->executeQuery()->fetchAssociative();

        return $row !== false ? $row : false;
    }

    public static function reduceCountOrDisable(array $codeRow): void
    {
        $uid = (int)($codeRow['uid'] ?? 0);
        $deleted = (int)($codeRow['deleted'] ?? 0);
        $reusable = (int)($codeRow['reusable'] ?? 0);
        $useCounter = (int)($codeRow['usecounter'] ?? 0);

        if (
            $uid > 0
            && $deleted === 0
            && $reusable !== 1
            && $reusable !== 2
        ) {
            $updateData = [];
            if ($useCounter > 1) {
                $updateData['usecounter'] = $useCounter - 1;
            } elseif ($useCounter === 1) {
                $updateData['usecounter'] = 0;
            }

            if ($updateData !== []) {
                GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getConnectionForTable(self::TABLE_VOUCHER_CODES)
                    ->update(
                        self::TABLE_VOUCHER_CODES,
                        $updateData,
                        ['uid' => $uid]
                    );
            }
        }
    }

    public static function redeemVoucher(
        string $code,
        string $theTable,
        array &$row,
        string &$newFieldList,
        int &$errorCode
    ): bool {
        $result = false;

        if ($theTable === 'fe_users' && $code !== '') {
            $codeRow = static::getRowFromCode($code, false);
            if (
                is_array($codeRow)
                && !empty($codeRow['uid'])
                && !empty($codeRow['acquired_groups'])
            ) {
                $newFieldArray = $newFieldList !== ''
                    ? GeneralUtility::trimExplode(',', $newFieldList, true)
                    : [];

                $origGroupArray = is_array($row['usergroup'] ?? null)
                    ? $row['usergroup']
                    : GeneralUtility::intExplode(',', (string)($row['usergroup'] ?? ''), true);

                $codeGroupArray = GeneralUtility::intExplode(',', (string)$codeRow['acquired_groups'], true);
                $newGroupArray = array_unique(array_merge($origGroupArray, $codeGroupArray));
                $row['usergroup'] = implode(',', $newGroupArray);
                $newFieldArray[] = 'usergroup';

                $time = self::getCurrentTimestamp();
                $acquiredSeconds = 0;

                if (!empty($codeRow['acquired_days'])) {
                    $acquiredSeconds = (int)$codeRow['acquired_days'] * 86400;
                }

                $newFieldList = implode(',', array_unique($newFieldArray));

                $result = static::addLimitedGroups(
                    $codeRow,
                    $time,
                    $acquiredSeconds,
                    (int)($row['uid'] ?? 0),
                    $codeGroupArray,
                    $errorCode,
                    0
                );
            }
        }

        return $result;
    }

    public static function checkCodeFormerlyUsed(
        string $code,
        array $rowArray,
        int &$errorCode
    ): bool {
        foreach ($rowArray as $row) {
            $codeArray = GeneralUtility::trimExplode(',', (string)($row['codes'] ?? ''), true);
            if (in_array($code, $codeArray, true)) {
                $errorCode = 1;
                return false;
            }
        }

        return true;
    }

    /**
     * @param int $errorCode 1 = code has already been used, 2 = database write error
     */
    public static function addLimitedGroups(
        array $codeRow,
        int $time,
        int $acquiredSeconds,
        int $theUser,
        array $groupArray,
        int &$errorCode,
        int $pid = 0
    ): bool {
        $code = (string)($codeRow['code'] ?? '');
        $result = true;
        $errorCode = 0;

        $fieldArray = [
            'pid' => $pid,
            'tstamp' => $time,
            'deleted' => 0,
            'hidden' => 0,
        ];

        if ($acquiredSeconds > 0) {
            $fieldArray['endtime'] = $time + $acquiredSeconds;
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(self::TABLE_LIMIT_GROUPS);

        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();
        $rowArray = $queryBuilder
            ->select('*')
            ->from(self::TABLE_LIMIT_GROUPS)
            ->where(
                $queryBuilder->expr()->eq(
                    'fe_users_uid',
                    $queryBuilder->createNamedParameter($theUser, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $addGroupArray = [];

        if ($rowArray !== []) {
            $codeValid = true;
            $reusable = (int)($codeRow['reusable'] ?? 0);

            if ($reusable !== 2 && $reusable !== 4) {
                $codeValid = static::checkCodeFormerlyUsed($code, $rowArray, $errorCode);
            }

            if (!$codeValid) {
                return false;
            }

            $foundGroupArray = [];

            foreach ($rowArray as $row) {
                $feGroupsUid = (int)$row['fe_groups_uid'];
                if (in_array($feGroupsUid, $groupArray, true)) {
                    $codeArray = GeneralUtility::trimExplode(',', (string)($row['codes'] ?? ''), true);
                    if (!in_array($code, $codeArray, true) || $reusable > 0) {
                        $updateFields = $fieldArray;
                        $endtime = (int)$row['endtime'];
                        if ($time > $endtime) {
                            $endtime = $time;
                        }
                        $updateFields['endtime'] = $endtime + $acquiredSeconds;
                        if (!in_array($code, $codeArray, true)) {
                            $codeArray[] = $code;
                            $updateFields['codes'] = implode(',', $codeArray);
                        }

                        $connection->update(
                            self::TABLE_LIMIT_GROUPS,
                            $updateFields,
                            [
                                'fe_users_uid' => $theUser,
                                'fe_groups_uid' => $feGroupsUid,
                            ]
                        );
                    }
                }
                $foundGroupArray[] = $feGroupsUid;
            }

            $addGroupArray = array_diff($groupArray, $foundGroupArray);
        } else {
            $addGroupArray = $groupArray;
        }

        if ($addGroupArray !== []) {
            $insertFields = $fieldArray;
            $insertFields['crdate'] = $time;

            foreach ($addGroupArray as $theGroup) {
                $insertFields['fe_users_uid'] = $theUser;
                $insertFields['fe_groups_uid'] = (int)$theGroup;
                $insertFields['codes'] = $code;

                $affectedRows = $connection->insert(self::TABLE_LIMIT_GROUPS, $insertFields);
                if ($affectedRows < 1) {
                    $errorCode = 2;
                    $result = false;
                    break;
                }
            }
        }

        return $result;
    }

    public static function removeOutdatedGroups(): bool
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $feUsersConnection = $connectionPool->getConnectionForTable('fe_users');
        $limitConnection = $connectionPool->getConnectionForTable(self::TABLE_LIMIT_GROUPS);

        $time = self::getCurrentTimestamp();
        $simAccessTime = GeneralUtility::makeInstance(Context::class)
            ->getPropertyFromAspect('date', 'timestamp', time());

        $userQueryBuilder = $feUsersConnection->createQueryBuilder();
        $rowArray = $userQueryBuilder
            ->select('*')
            ->from('fe_users')
            ->executeQuery()
            ->fetchAllAssociative();

        $endtimeField = $GLOBALS['TCA'][self::TABLE_LIMIT_GROUPS]['ctrl']['enablecolumns']['endtime'] ?? 'endtime';
        $offsetSeconds = 86400; // 1 day offset

        foreach ($rowArray as $row) {
            if (empty($row['usergroup'])) {
                continue;
            }

            $feUserUid = (int)$row['uid'];
            $limitQueryBuilder = $limitConnection->createQueryBuilder();
            $limitQueryBuilder->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
                ->add(GeneralUtility::makeInstance(HiddenRestriction::class));

            $limitQueryBuilder
                ->select('*')
                ->from(self::TABLE_LIMIT_GROUPS)
                ->where(
                    $limitQueryBuilder->expr()->eq(
                        'fe_users_uid',
                        $limitQueryBuilder->createNamedParameter($feUserUid, Connection::PARAM_INT)
                    )
                );

            if ($endtimeField !== '') {
                $limitQueryBuilder->andWhere(
                    $limitQueryBuilder->expr()->gt($endtimeField, $limitQueryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
                    $limitQueryBuilder->expr()->lt($endtimeField, $limitQueryBuilder->createNamedParameter($simAccessTime - $offsetSeconds, Connection::PARAM_INT))
                );
            }

            $outdatedRows = $limitQueryBuilder->executeQuery()->fetchAllAssociative();

            if ($outdatedRows !== []) {
                $outdatedGroupIds = array_column($outdatedRows, 'fe_groups_uid');
                $outdatedRecordUids = array_column($outdatedRows, 'uid');

                $currentGroupIds = GeneralUtility::intExplode(',', (string)$row['usergroup'], true);
                $remainingGroupIds = array_diff($currentGroupIds, $outdatedGroupIds);

                $feUsersConnection->update(
                    'fe_users',
                    ['usergroup' => implode(',', $remainingGroupIds)],
                    ['uid' => $feUserUid]
                );

                foreach ($outdatedRecordUids as $recordUid) {
                    $limitConnection->update(
                        self::TABLE_LIMIT_GROUPS,
                        [
                            'deleted' => 1,
                            'tstamp' => $time,
                        ],
                        ['uid' => (int)$recordUid]
                    );
                }
            }
        }

        return true;
    }

    public static function getGroupRowsByUser(int|string $theUser, bool $bEnable = true): array|false
    {
        if ($theUser === '' || (int)$theUser <= 0) {
            return false;
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_LIMIT_GROUPS);

        if (!$bEnable) {
            $queryBuilder->getRestrictions()->removeAll();
        }

        return $queryBuilder
            ->select('*')
            ->from(self::TABLE_LIMIT_GROUPS)
            ->where(
                $queryBuilder->expr()->eq(
                    'fe_users_uid',
                    $queryBuilder->createNamedParameter((int)$theUser, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }

    public static function getCodesByUser(int|string $theUser, bool $bEnable = true): string|false
    {
        $rowArray = static::getGroupRowsByUser($theUser, $bEnable);

        if (!is_array($rowArray) || $rowArray === []) {
            return false;
        }

        $codesArray = [];
        foreach ($rowArray as $row) {
            if (!empty($row['codes'])) {
                $codesArray = array_merge(
                    $codesArray,
                    GeneralUtility::trimExplode(',', (string)$row['codes'], true)
                );
            }
        }

        return implode(',', array_unique($codesArray));
    }

    public static function groupOut(
        ?ContentObjectRenderer $cObj,
        array $feUserRow,
        bool $showGroupTimeRange = false,
        string $whereClause = '',
        bool $groupEnable = true,
        string $expirationText = '',
        array $headerTextArray = []
    ): string {
        $groupTable = 'fe_groups';
        $groupCodeRowArray = false;
        $classTd = '<div class="td">';
        $classTr = '<div class="tr">';
        $classTable = '<div class="table">';
        $divEnd = '</div>';
        $xhtmlFix = HtmlUtility::determineXhtmlFix();

        if ($showGroupTimeRange && !empty($feUserRow['uid'])) {
            $groupCodeRowArray = static::getGroupRowsByUser((int)$feUserRow['uid'], true);
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($groupTable);

        if ($groupEnable) {
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        } else {
            $queryBuilder->getRestrictions()->removeAll();
        }

        $queryBuilder->select('*')->from($groupTable);

        if ($whereClause === '') {
            $userGroupIds = GeneralUtility::intExplode(',', (string)($feUserRow['usergroup'] ?? ''), true);
            if ($userGroupIds === []) {
                $userGroupIds =;
            }
            $queryBuilder->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($userGroupIds, Connection::PARAM_INT_ARRAY)
                )
            );
        } else {
            $queryBuilder->where($whereClause);
        }

        $groupRowArray = $queryBuilder->executeQuery()->fetchAllAssociative();
        $groupOutArray = [];

        foreach ($groupRowArray as $groupRow) {
            $outDetails = '';

            if (is_array($groupCodeRowArray)) {
                foreach ($groupCodeRowArray as $groupCodeRow) {
                    if ((int)$groupCodeRow['fe_groups_uid'] === (int)$groupRow['uid']) {
                        $enddate = !empty($groupCodeRow['endtime'])
                            ? date('Y-m-d', (int)$groupCodeRow['endtime'])
                            : '';
                        $outDetails = $classTd . $expirationText . ' ' . $enddate . $divEnd;
                        $outDetails .= $classTd . htmlspecialchars((string)$groupCodeRow['codes']) . $divEnd;
                        break;
                    }
                }
            }
            $groupOutArray[] = $classTr . $classTd . (string)$groupRow['description'] . $divEnd . $outDetails . $divEnd;
        }

        $headerHtml = '';
        foreach ($headerTextArray as $headerText) {
            $headerHtml .= $classTd . $headerText . $divEnd;
        }

        $outArray = [
            $classTr . $headerHtml . $divEnd,
            implode('', $groupOutArray) . '<br' . $xhtmlFix . '>',
        ];

        return $classTable . implode('', $outArray) . $divEnd;
    }

    /**
     * @param bool $developer true during development to deactivate the browser's cache for JavaScript
     */
    public static function getVoucherAjaxUrl(
        string $extKey,
        ContentObjectRenderer $cObj,
        bool $developer = false
    ): string {
        $request = self::getRequest();
        $languageId = 0;

        if ($request instanceof ServerRequestInterface) {
            $language = $request->getAttribute('language');
            if ($language !== null && method_exists($language, 'getLanguageId')) {
                $languageId = $language->getLanguageId();
            } else {
                $languageId = (int)($request->getQueryParams()['L'] ?? 0);
            }
        }

        $queryString = [
            'L' => $languageId,
            'no_cache' => 1,
            'eID' => $extKey,
        ];

        if ($developer) {
            $queryString['time'] = time();
        }

        $id = 0;
        if ($request instanceof ServerRequestInterface) {
            /** @var PageInformation|null $pageInformation */
            $pageInformation = $request->getAttribute('frontend.page.information');
            if ($pageInformation instanceof PageInformation) {
                $id = $pageInformation->getId();
            }
        }

        $linkConf = ['useCacheHash' => 0];
        $target = '';

        return FrontendUtility::getTypoLink_URL(
            $cObj,
            $id,
            $queryString,
            $target,
            $linkConf
        );
    }

    public static function crypto_rand_secure(int $minimum, int $maximum): int
    {
        if ($maximum <= $minimum) {
            return $minimum;
        }

        return random_int($minimum, $maximum);
    }

    public static function getToken(
        int $length,
        bool $onlyUppercase = true,
        bool $specialCharacters = false
    ): string {
        $token = '';
        $codeAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if (!$onlyUppercase) {
            $codeAlphabet .= strtolower($codeAlphabet);
        }
        $codeAlphabet .= '0123456789';
        if ($specialCharacters) {
            $codeAlphabet .= '§$%&/()[]{}+-#|<>';
        }

        $maximum = strlen($codeAlphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $maximum)];
        }

        return $token;
    }

    public static function insertVoucher(
        array &$row,
        bool $onlyUppercase = true,
        bool $specialCharacters = false
    ): bool {
        $time = self::getCurrentTimestamp();
        $codeSize = self::getConfiguredCodeSize();

        $allowedFields = array_keys($GLOBALS['TCA'][self::TABLE_VOUCHER_CODES]['columns'] ?? []);
        $allowedFields = array_merge(['tstamp', 'crdate', 'deleted'], $allowedFields);

        $title = isset($row['title']) && $row['title'] !== '' ? (string)$row['title'] : 'AUTO';

        $newRow = [
            'tstamp' => $time,
            'crdate' => $time,
            'title' => $title . '-' . date('Y-m-d H:i:s', $time),
            'code' => static::getToken($codeSize, $onlyUppercase, $specialCharacters),
        ];

        foreach ($row as $field => $value) {
            if (isset($value) && in_array($field, $allowedFields, true)) {
                $newRow[$field] = $value;
            }
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(self::TABLE_VOUCHER_CODES);

        $affectedRows = $connection->insert(self::TABLE_VOUCHER_CODES, $newRow);
        if ($affectedRows > 0) {
            $newRow['uid'] = (int)$connection->lastInsertId();
            $row = $newRow;
            return true;
        }

        return false;
    }

    private static function getCurrentTimestamp(): int
    {
        $time = time();
        if (!empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['serverTimeZone'])) {
            $time += ((int)$GLOBALS['TYPO3_CONF_VARS']['SYS']['serverTimeZone'] * 3600);
        }

        return $time;
    }

    private static function getConfiguredCodeSize(): int
    {
        $config = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('voucher');
        return is_array($config) && isset($config['codeSize']) && (int)$config['codeSize'] > 0
            ? (int)$config['codeSize']
            : 32;
    }

    private static function getRequest(): ?ServerRequestInterface
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? $GLOBALS['REQUEST'] ?? null;
        return $request instanceof ServerRequestInterface ? $request : null;
    }
}
