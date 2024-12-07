<?php
defined('TYPO3') || die('Access denied.');

call_user_func(function ($extensionKey, $table): void {
    $languageSubpath = '/Resources/Private/Language/';

    $temporaryColumns =
        [
            'tx_voucher_usedcode' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:' . $table . '.tx_voucher_usedcode',
                'config' => [
                    'type' => 'input',
                    'size' => '20',
                    'max' => '256',
                    'readOnly' => '1',
                ]
            ]
        ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, $temporaryColumns);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        $table,
        'tx_voucher_usedcode',
        '',
        'after:www'
    );
}, 'voucher', basename(__FILE__, '.php'));

