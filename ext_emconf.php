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
    'clearCacheOnLoad' => 0,
    'author_company' => '',
    'version' => '0.9.1',
    'constraints' => [
        'depends' => [
            'php' => '8.1.0-8.6.99',
            'typo3' => '12.4.0-14.3.99',
            'div2007' => '2.0.0-0.0.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'typo3db_legacy' => '1.1.0-1.3.99',
        ],
    ],
];

