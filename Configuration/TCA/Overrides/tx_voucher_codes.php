<?php
defined('TYPO3') || die('Access denied.');

call_user_func(function ($extensionKey, $table): void {
    $languageSubpath = '/Resources/Private/Language/';

    $temporaryColumns =
        [
            'code' => [
                'exclude' => 1,
               'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:' . $table . '.code',
                'config' => [
                    'type' => 'input',
                    'size' => $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]['codeSize'],
                    'max' => $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]['codeSize'],
                    'eval' => 'required,trim',
                ]
            ]
        ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, $temporaryColumns);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        $table,
        'code',
        '',
        'after:title'
    );
}, 'voucher', basename(__FILE__, '.php'));
