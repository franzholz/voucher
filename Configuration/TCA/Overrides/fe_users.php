<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function($extKey) {

    $table = 'fe_users';

    $temporaryColumns =
        [
            'tx_voucher_usedcode' => [
                'exclude' => 1,
                'label' => VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xml:' . $table . '.tx_voucher_usedcode',
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
}, VOUCHER_EXT);
