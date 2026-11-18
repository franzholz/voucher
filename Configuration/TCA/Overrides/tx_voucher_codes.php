<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') || die('Access denied.');

(static function (string $extensionKey, string $table): void {
    $config = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get($extensionKey);
    $codeSize = is_array($config) && isset($config['codeSize']) && (int)$config['codeSize'] > 0
        ? (int)$config['codeSize']
        : 32;

    $temporaryColumns = [
        'code' => [
            'exclude' => true,
            'label' => 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang_db.xlf:' . $table . '.code',
            'config' => [
                'type' => 'input',
                'size' => $codeSize,
                'max' => $codeSize,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
    ];

    ExtensionManagementUtility::addTCAcolumns($table, $temporaryColumns);
    ExtensionManagementUtility::addToAllTCAtypes(
        $table,
        'code',
        '',
        'after:title'
    );
})('voucher', basename(__FILE__, '.php'));
