<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (!defined ('VOUCHER_EXT')) {
	define('VOUCHER_EXT', $_EXTKEY);
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

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_products']['extendingTCA'][] = VOUCHER_EXT;

$classPath = 'EXT:' . $_EXTKEY . '/hooks/agency/class.tx_voucher_agency.php:&tx_voucher_agency';

	// Agency marker hook
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['registrationProcess'][] = $classPath;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['model'][] = $classPath;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['registrationProcess_afterSaveCreate'][] = $classPath;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['confirmRegistrationClass'][] = $classPath;

?>