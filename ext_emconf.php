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
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'author_company' => 'jambage.com',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'php' => '8.2.0-8.6.99',
            'typo3' => '13.4.0-14.3.99',
            'div2007' => '2.0.0-0.0.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];

