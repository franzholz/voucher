<?php
defined('TYPO3_MODE') || die('Access denied.');

$table = 'tx_voucher_codes';

$temporaryColumns =
    array (
        'code' => array (
            'exclude' => 1,
            'label' => VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:' . $table . '.code',
            'config' => array (
                'type' => 'input',
                'size' => $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['codeSize'],
                'max' => $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['codeSize'],
                'eval' => 'required,trim',
            )
        )
    );

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, $temporaryColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    $table,
    'code',
    '',
    'after:title'
);


