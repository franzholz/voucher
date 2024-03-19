<?php
defined('TYPO3') || die('Access denied.');

call_user_func(function ($extensionKey): void {

    if (!defined ('VOUCHER_EXT')) {
        define('VOUCHER_EXT', $extensionKey);
    }

    $extensionConfiguration = [];
    $originalConfiguration = [];

    $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    )->get($extensionKey);

    if (
        isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]) &&
        is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey])
    ) {
        $originalConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey];
    }

    if (
        isset($extensionConfiguration) && is_array($extensionConfiguration
    )) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey] =
            array_merge($extensionConfiguration, $originalConfiguration);
    } else if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey])) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey] = [];
    }

    $extensionConfiguration = &$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey];

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
        $classPath = \JambageCom\Voucher\Hooks\Agency\Agency::class;

            // Agency marker hook
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$hookExtension]['registrationProcess'][] = $classPath;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$hookExtension]['model'][] = $classPath;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$hookExtension]['registrationProcess_afterSaveCreate'][] = $classPath;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$hookExtension]['confirmRegistrationClass'][] = $classPath;
    }
}, 'voucher');

