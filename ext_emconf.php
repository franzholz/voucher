<?php

/***************************************************************
* Extension Manager/Repository config file for ext "voucher".
***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Vouchercode Manager',
    'description' => 'Backend extension to manage voucher codes for FE users. This works together with tt_products and agency.',
    'category' => 'module',
    'author' => 'Franz Holzinger',
    'author_email' => 'franz@ttproducts.de',
    'shy' => '',
    'dependencies' => 'div2007',
    'priority' => '',
    'module' => 'mod1',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'author_company' => '',
    'version' => '0.4.2',
    'constraints' => array(
        'depends' => array(
            'php' => '5.5.0-7.99.99',
            'typo3' => '7.6.0-8.99.99',
            'div2007' => '1.10.5-0.0.0',
        ),
        'conflicts' => array(
        ),
        'suggests' => array(
        ),
    ),
);

