<?php
defined('TYPO3') || die('Access denied.');

$extensionKey = 'voucher';
$languageSubpath = '/Resources/Private/Language/';
$languageLglPath = 'LLL:EXT:core' . $languageSubpath . 'locallang_general.xlf:LGL.';

$result = [
    'ctrl' => [
        'title' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'iconfile' => 'EXT:' . $extensionKey . '/Resources/Public/Icons/VoucherCodes.gif',
        'dividers2tabs' => '1',
        'searchFields' => 'uid,title,code,note',
        'rootLevel' => -1,
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => $languageLglPath . 'hidden',
            'config' => [
                'type' => 'check'
            ]
        ],
        'starttime' => [
            'exclude' => true,
            'label' => $languageLglPath . 'starttime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
            ]
        ],
        'endtime' => [
            'exclude' => true,
            'label' => $languageLglPath . 'endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ]
            ]
        ],
        'fe_group' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'label' => $languageLglPath . 'fe_group',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => 7,
                'maxitems' => 20,
                'items' => [
                    [
                        $languageLglPath . 'hide_at_login',
                        -1,
                    ],
                    [
                        $languageLglPath . 'any_login',
                        -2,
                    ],
                    [
                        $languageLglPath . 'usergroups',
                        '--div--',
                    ],
                ],
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title',
                'default' => 0,
            ],
        ],
        'title' => [
            'exclude' => 0,
            'label' => $languageLglPath . 'title',
            'config' => [
                'type' => 'input',
                'size' => '40',
                'max' => '256',
                'eval' => 'required,trim',
                'default' => null,
            ]
        ],
        'fe_users_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.fe_users_uid',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingleBox',
                'foreign_table' => 'fe_users',
                'foreign_table_where' => ' ORDER BY fe_users.name',
                'size' => 15,
                'minitems' => 0,
                'maxitems' => 12,
                'default' => 0
            ]
        ],
        'reusable' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.reusable',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.reusable.I.0', '0'],
                    ['LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.reusable.I.1', '1'],
                    ['LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.reusable.I.2', '2'],
                    ['LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.reusable.I.3', '3'],
                    ['LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.reusable.I.4', '4'],
                 ],
                'size' => 1,
                'maxitems' => 1,
                'default' => 0
            ]
        ],
        'usecounter' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.usecounter',
            'config' => [
                'type' => 'input',
                'eval' => 'int',
                'default' => 0,
            ]
        ],
        'combinable' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.combinable',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.combinable.I.0', '0'],
                    ['LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.combinable.I.1', '1'],
                ],
                'size' => 1,
                'maxitems' => 1,
                'default' => 0,
            ]
        ],
        'amount_type' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.amount_type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.amount_type.I.0', '0'],
                    ['LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.amount_type.I.1', '1'],
                ],
                'size' => 1,
                'maxitems' => 1,
                'default' => 0,
            ]
        ],
        'amount' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.amount',
            'config' => [
                'type' => 'input',
                'size' => '20',
                'max' => '20',
                'eval' => 'required,trim,double2',
                'default' => 0,
            ]
        ],
        'tax' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.tax',
            'config' => [
                'type' => 'input',
                'size' => '12',
                'max' => '19',
                'eval' => 'trim,double2',
                'default' => 0
            ]
        ],
        'note' => [
            'label' => $languageLglPath . 'note',
            'config' => [
                'type' => 'text',
                'cols' => '48',
                'rows' => '5',
                'default' => ''
            ]
        ],
        'acquired_groups' => [
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.acquired_groups',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => '6',
                'minitems' => '0',
                'maxitems' => '50',
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title',
                'enableMultiSelectFilterTextfield' => true
            ]
        ],
        'acquired_days' => [
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.acquired_days',
            'config' => [
                'type' => 'input',
                'size' => '12',
                'eval' => 'int',
                'default' => 0
            ]
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => 'title, --palette--;;1, fe_users_uid, hidden, --palette--;;2, ' .
            '--div--;' . 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.acquired, acquired_groups, acquired_days'
        ]
    ],
    'palettes' => [
        '1' => ['showitem' => 'reusable, usecounter, combinable, amount_type, amount, tax, note'],
        '2' => ['showitem' => 'starttime,endtime,fe_group'],
    ]
];

return $result;

