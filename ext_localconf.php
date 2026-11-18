<?php

declare(strict_types=1);

use JambageCom\Voucher\Hooks\Agency\Agency;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') || die('Access denied.');

(static function (string $extensionKey): void {
    $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get($extensionKey);
    $hooks = is_array($extensionConfiguration) ? ($extensionConfiguration['hooks'] ?? '') : '';

    if (is_string($hooks) && GeneralUtility::inList($hooks, 'agency')) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['registrationProcess'][] = Agency::class;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['model'][] = Agency::class;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['registrationProcess_afterSaveCreate'][] = Agency::class;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['agency']['confirmRegistrationClass'][] = Agency::class;
    }
})('voucher');
