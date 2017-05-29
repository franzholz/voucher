<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_voucher_codes');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_voucher_codes');

// avoid that this block is loaded in the frontend or within the upgrade-wizards
if (TYPO3_MODE === 'BE' && !(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('web', 'txvoucherM1', '', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'mod1/');
}

