<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

(static function (string $extensionKey, string $table): void {
    $temporaryColumns = [
        'tx_voucher_usedcode' => [
            'exclude' => true,
            'label' => 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang_db.xlf:' . $table . '.tx_voucher_usedcode',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 256,
                'readOnly' => true,
            ],
        ],
    ];

    ExtensionManagementUtility::addTCAcolumns($table, $temporaryColumns);
    ExtensionManagementUtility::addToAllTCAtypes(
        $table,
        'tx_voucher_usedcode',
        '',
        'after:www'
    );
})('voucher', basename(__FILE__, '.php'));
