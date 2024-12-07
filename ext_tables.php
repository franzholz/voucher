<?php
defined('TYPO3') || die('Access denied.');

call_user_func(function ($extensionKey): void {

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_voucher_codes');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_voucher_codes');

    if (
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]['module']
    ) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
            'web',
            'txvoucherM1',
            '',
            '',
            [
                'routeTarget' => \JambageCom\Voucher\Controller\BackendModuleController::class . '::handleRequest',
                'access' => 'user,group',
                'name' => 'web_txvoucherM1',
                'icon' => 'EXT:' . $extensionKey . '/Resources/Public/Images/Icons/BackendModuleController/module-icon.svg',
                'labels' => [
                    'tabs_images' => [
                        'tab' => 'EXT:' . $extensionKey . '/Resources/Public/Images/Icons/BackendModuleController/module-icon.svg',
                    ],
                    'll_ref' => 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang_mod.xlf',
                ],
            ]
        );
    }
}, 'voucher');
