<?php

/***************************************************************
* Extension Manager/Repository config file for ext "voucher".
***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Vouchercode Manager',
    'description' => 'Backend extension to manage voucher codes for FE users. This works together with tt_products and agency.',
    'category' => 'module',
    'author' => 'Franz Holzinger',
    'author_email' => 'franz@ttproducts.de',
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'author_company' => '',
    'version' => '0.5.1',
    'constraints' => [
        'depends' => [
            'php' => '5.5.0-7.4.99',
            'typo3' => '7.6.0-10.4.99',
            'div2007' => '1.10.15-0.0.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'typo3db_legacy' => '1.0.0-1.1.99',
        ],
    ],
];

