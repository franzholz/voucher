<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_voucher_codes');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_voucher_codes');

if (TYPO3_MODE == 'BE') {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('web', 'txvoucherM1', '', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'mod1/');
}

