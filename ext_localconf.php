<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

define('VOUCHER_EXT', $_EXTKEY);

if (!defined ('VOUCHER_EXT_LANGUAGE')) {
    define('VOUCHER_EXT_LANGUAGE_PATH', 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/');
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

if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['module'])) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['module'] = 1;
}

$classPath = 'EXT:' . $_EXTKEY . '/hooks/agency/class.tx_voucher_agency.php:&tx_voucher_agency';

    // Agency marker hook
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['registrationProcess'][] = $classPath;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['model'][] = $classPath;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['registrationProcess_afterSaveCreate'][] = $classPath;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['confirmRegistrationClass'][] = $classPath;

