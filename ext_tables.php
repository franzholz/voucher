<?php
defined('TYPO3') || die('Access denied.');

call_user_func(function ($extensionKey): void {

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_voucher_codes');

}, 'voucher');
