<?php
defined('TYPO3_MODE') || die('Access denied.');

$table = 'fe_users';

$temporaryColumns =
    array (
        'tx_voucher_usedcode' => array (
            'exclude' => 1,
            'label' => VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xml:' . $table . '.tx_voucher_usedcode',
            'config' => array (
                'type' => 'input',
                'size' => '20',
                'max' => '256',
                'readOnly' => '1',
            )
        )
    );

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, $temporaryColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    $table,
    'tx_voucher_usedcode;;;;1-1-1',
    '',
    'after:www'
);

