<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function () {

    define('VOUCHER_EXT', 'voucher');

    if (!defined ('VOUCHER_EXT_LANGUAGE')) {
        define('VOUCHER_EXT_LANGUAGE_PATH', 'LLL:EXT:' . VOUCHER_EXT . '/Resources/Private/Language/');
    }


    $extensionConfiguration = [];
    $originalConfiguration = [];

    if (
        defined('TYPO3_version') &&
        version_compare(TYPO3_version, '9.0.0', '>=')
    ) {
        $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )->get(VOUCHER_EXT);
    } else { // before TYPO3 9
        $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][VOUCHER_EXT]);
    }

    if (
        isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]) &&
        is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT])
    ) {
        $originalConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT];
    }

    if (
        isset($extensionConfiguration) && is_array($extensionConfiguration
    )) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT] =
            array_merge($extensionConfiguration, $originalConfiguration);
    } else if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT])) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT] = [];
    }

    $extensionConfiguration = &$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT];

    if (!isset($extensionConfiguration['codeSize'])) {
        $extensionConfiguration['codeSize'] = 32;
    }

    if (!isset($extensionConfiguration['module'])) {
        $extensionConfiguration['module'] = 1;
    }

    if (
        TYPO3\CMS\Core\Utility\GeneralUtility::inList(
            $extensionConfiguration['hooks'],
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

