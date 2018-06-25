<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_voucher_codes');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_voucher_codes');

if (
    TYPO3_MODE == 'BE' &&
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['module']
) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'web',
        'txvoucherM1',
        '',
        '',
        array(
            'routeTarget' => \JambageCom\Voucher\Controller\BackendModuleController::class . '::mainAction',
            'access' => 'user,group',
            'name' => 'web_txvoucherM1',
            'labels' => array(
                'tabs_images' => array(
                    'tab' => 'EXT:' . $_EXTKEY . '/Resources/Public/Images/Icons/BackendModuleController/module-icon.svg',
                ),
                'll_ref' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod.xlf',
            ),
        )
    );
}


