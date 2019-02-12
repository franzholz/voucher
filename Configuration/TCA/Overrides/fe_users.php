<?php
defined('TYPO3_MODE') || die('Access denied.');

$temporaryColumns =
    array (
        'tx_voucher_usedcode' => array (
            'exclude' => 1,
            'label' => VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xml:fe_users.tx_voucher_usedcode',
            'config' => array (
                'type' => 'input',
                'size' => '20',
                'max' => '256',
                'readOnly' => '1',
            )
        )
    );

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $temporaryColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    'tx_voucher_usedcode;;;;1-1-1',
    '',
    'after:www,'
);

