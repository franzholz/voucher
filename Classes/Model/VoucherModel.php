<?php

declare(strict_types=1);

namespace JambageCom\Voucher\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016-2026 Franz Holzinger (franz@ttproducts.de)
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
use TYPO3\CMS\Backend\Form\FormResultCompiler;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Backend module model for voucher management
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 */
class VoucherModel implements SingletonInterface
{
    public array $timeFieldRow = [
        'starttime' => '0',
        'endtime' => '0',
        'title' => '',
        'code' => '',
        'reusable' => '0',
        'usecounter' => '0',
        'combinable' => '0',
        'amount_type' => '0',
        'amount' => '0',
        'tax' => '0',
        'note' => '',
        'acquired_days' => '0',
        'acquired_groups' => '',
    ];

    public array $rowArray = [];
    public array $feUserArray = [];
    public int $id = 0;
    public string $returnUrl = '';
    public string $perms_clause = '';

    protected string $table = 'tx_voucher_codes';
    protected ?FormResultCompiler $formResultCompiler = null;
    protected ?PageRenderer $pageRenderer = null;

    public function init(array $get = [], array $post = [], ?ServerRequestInterface $request = null): void
    {
        $request ??= $this->getRequest();
        $queryParams = $request?->getQueryParams() ?? $get;
        $parsedBody = is_array($request?->getParsedBody()) ? $request->getParsedBody() : $post;

        $backendUser = $this->getBackendUser();
        if ($backendUser instanceof BackendUserAuthentication) {
            $this->perms_clause = $backendUser->getPagePermsClause(1);
            $sessionData = $backendUser->getSessionData(self::class);
            $backendUser->setAndSaveSessionData(self::class, $sessionData);
        }

        $rawId = $parsedBody['id'] ?? $queryParams['id'] ?? 0;
        $this->id = (int)$rawId;

        $rawReturnUrl = (string)($parsedBody['returnUrl'] ?? $queryParams['returnUrl'] ?? '');
        $this->returnUrl = GeneralUtility::sanitizeLocalUrl($rawReturnUrl);

        $this->getPageRenderer()->addInlineLanguageLabelFile('EXT:voucher/Resources/Private/Language/locallang.xlf');
        $this->formResultCompiler = GeneralUtility::makeInstance(FormResultCompiler::class);
    }

    protected function getPageRenderer(): PageRenderer
    {
        if ($this->pageRenderer === null) {
            $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        }

        return $this->pageRenderer;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getTimeFieldRow(): array
    {
        return $this->timeFieldRow;
    }

    /**
     * @return list<string>
     */
    public function getFields(): array
    {
        return array_keys($this->getTimeFieldRow());
    }

    public function setRow(array $row): void
    {
        if (!in_array($row, $this->rowArray, true)) {
            $this->rowArray[] = $row;
        }
    }

    public function getRow(int|string $key = ''): array|false
    {
        if ($key !== '' && isset($this->rowArray[$key])) {
            return $this->rowArray[$key];
        }

        return current($this->rowArray);
    }

    public function modifyRecords(?ServerRequestInterface $request = null): void
    {
        $request ??= $this->getRequest();
        $queryParams = $request?->getQueryParams() ?? [];
        $parsedBody = is_array($request?->getParsedBody()) ? $request->getParsedBody() : [];
        $requestData = array_merge($queryParams, $parsedBody);

        $feUserArray = [];
        foreach ($requestData as $k => $v) {
            if (MathUtility::canBeInterpretedAsInteger($k) && $v !== '') {
                $feUserArray[] = (int)$k;
            }
        }

        if ($feUserArray === []) {
            $feUserArray =;
        }

        $this->feUserArray = $feUserArray;

        $dataArray = $requestData['data'] ?? null;
        if (!is_array($dataArray)) {
            return;
        }

        $allowedFields = $this->getAllowedColumns($this->table);
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($this->table);

        foreach ($dataArray as $table => $rowArray) {
            if ($table !== $this->table || !is_array($rowArray)) {
                continue;
            }

            foreach ($rowArray as $id => $row) {
                if (!is_array($row)) {
                    continue;
                }

                $title = trim((string)($row['title'] ?? ''));
                $amount = (string)($row['amount'] ?? '0');

                if ($title !== '' && $amount !== '0' && $amount !== '') {
                    $filteredRow = array_intersect_key($row, array_flip($allowedFields));

                    if (str_starts_with((string)$id, 'NEW')) {
                        $this->setRow($filteredRow);
                        foreach ($feUserArray as $uid) {
                            $insertRow = $filteredRow;
                            $insertRow['fe_users_uid'] = $uid;
                            $insertRow['pid'] = $this->id;
                            $insertRow['tstamp'] = time();
                            $insertRow['crdate'] = time();
                            $connection->insert($this->table, $insertRow);
                        }
                    } else {
                        $recordUid = (int)$id;
                        if ($recordUid > 0 && $filteredRow !== []) {
                            $filteredRow['tstamp'] = time();
                            $connection->update(
                                $this->table,
                                $filteredRow,
                                ['uid' => $recordUid]
                            );
                        }
                    }
                }
            }
        }
    }

    public function deleteRecords(int|string $uid): void
    {
        $recordUid = (int)$uid;
        if ($recordUid > 0) {
            GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable($this->table)
                ->update(
                    $this->table,
                    [
                        'deleted' => 1,
                        'tstamp' => time(),
                    ],
                    ['uid' => $recordUid]
                );
        }
    }

    public function getOutputDate(int|string $date): string
    {
        $timestamp = (int)$date;
        if ($timestamp > 0) {
            return date('d-M-Y', $timestamp);
        }

        return '-';
    }

    /**
     * @return list<string>
     */
    private function getAllowedColumns(string $table): array
    {
        $columns = array_keys($GLOBALS['TCA'][$table]['columns'] ?? []);
        return array_merge(['pid', 'tstamp', 'crdate', 'deleted'], $columns);
    }

    private function getRequest(): ?ServerRequestInterface
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? $GLOBALS['REQUEST'] ?? null;
        return $request instanceof ServerRequestInterface ? $request : null;
    }

    protected function getBackendUser(): ?BackendUserAuthentication
    {
        return ($GLOBALS['BE_USER'] ?? null) instanceof BackendUserAuthentication
            ? $GLOBALS['BE_USER']
            : null;
    }

    protected function getLanguageService(): ?LanguageService
    {
        return ($GLOBALS['LANG'] ?? null) instanceof LanguageService
            ? $GLOBALS['LANG']
            : null;
    }
}
