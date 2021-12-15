<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function () {

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_voucher_codes');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_voucher_codes');

    if (
        TYPO3_MODE == 'BE' &&
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['module']
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
                'icon' => 'EXT:voucher/Resources/Public/Images/Icons/BackendModuleController/module-icon.svg',
                'labels' => [
                    'tabs_images' => [
                        'tab' => 'EXT:' . VOUCHER_EXT . '/Resources/Public/Images/Icons/BackendModuleController/module-icon.svg',
                    ],
                    'll_ref' => 'LLL:EXT:' . VOUCHER_EXT . '/Resources/Private/Language/locallang_mod.xlf',
                ],
            ]
        );
    }
});

