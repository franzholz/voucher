<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function () {

    define('VOUCHER_EXT', 'voucher');

    if (!defined ('VOUCHER_EXT_LANGUAGE')) {
        define('VOUCHER_EXT_LANGUAGE_PATH', 'LLL:EXT:' . VOUCHER_EXT . '/Resources/Private/Language/');
    }

    $_EXTCONF = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here:

    if (
        isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]) &&
        is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT])
    ) {
        $tmpArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT];
    } else if (isset($tmpArray)) {
        unset($tmpArray);
    }

    if (isset($_EXTCONF) && is_array($_EXTCONF)) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT] = $_EXTCONF;
        if (isset($tmpArray) && is_array($tmpArray)) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT] =
                array_merge($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT], $tmpArray);
        }
    } else if (!isset($tmpArray)) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT] = [];
    }

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['codeSize'])) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['codeSize'] = 32;
    }

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['module'])) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['module'] = 1;
    }


    if (
        TYPO3\CMS\Core\Utility\GeneralUtility::inList(
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['hooks'],
            'agency'
        )
    ) {
        $hookExtension = 'agency';
        $classPath = \JambageCom\Voucher\Agency\Agency::class;

            // Agency marker hook
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$hookExtension]['registrationProcess'][] = $classPath;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$hookExtension]['model'][] = $classPath;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$hookExtension]['registrationProcess_afterSaveCreate'][] = $classPath;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$hookExtension]['confirmRegistrationClass'][] = $classPath;
    }

});



