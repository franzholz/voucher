<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (!defined ('VOUCHER_EXT')) {
	define('VOUCHER_EXT', $_EXTKEY);
}

$_EXTCONF = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here:

if (
	isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]) &&
	is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY])
) {
	$tmpArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY];
} else if (isset($tmpArray)) {
	unset($tmpArray);
}

if (isset($_EXTCONF) && is_array($_EXTCONF)) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY] = $_EXTCONF;
	if (isset($tmpArray) && is_array($tmpArray)) {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY] =
			array_merge($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY], $tmpArray);
	}
} else if (!isset($tmpArray)) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY] = array();
}

if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['codeSize'])) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['codeSize'] = 32;
}

if (
	isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch']) &&
	is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch'])
) {
	// TYPO3 4.5 with livesearch
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch'] = array_merge(
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch'],
		array(
			'tx_voucher_codes' => 'tx_voucher_codes'
		)
	);
}

$classPath = 'EXT:' . $_EXTKEY . '/hooks/agency/class.tx_voucher_agency.php:&tx_voucher_agency';

	// Agency marker hook
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['registrationProcess'][] = $classPath;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['model'][] = $classPath;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['registrationProcess_afterSaveCreate'][] = $classPath;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['confirmRegistrationClass'][] = $classPath;

