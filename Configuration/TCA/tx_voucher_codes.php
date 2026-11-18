<?php

declare(strict_types=1);

defined('TYPO3') || die('Access denied.');

return (static function (string $extensionKey): array {
    $languageSubpath = '/Resources/Private/Language/';
    $languageLglPath = 'LLL:EXT:core' . $languageSubpath . 'locallang_general.xlf:LGL.';
    $languageDbPath = 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes.';

    return [
        'ctrl' => [
            'title' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_db.xlf:tx_voucher_codes',
            'label' => 'title',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'default_sortby' => 'ORDER BY crdate',
            'delete' => 'deleted',
            'enablecolumns' => [
                'disabled' => 'hidden',
                'starttime' => 'starttime',
                'endtime' => 'endtime',
                'fe_group' => 'fe_group',
            ],
            'iconfile' => 'EXT:' . $extensionKey . '/Resources/Public/Icons/VoucherCodes.gif',
            'searchFields' => 'uid,title,code,note',
            'security' => [
                'ignorePageTypeRestriction' => true,
            ],
            'rootLevel' => -1,
        ],
        'columns' => [
            'hidden' => [
                'exclude' => true,
                'label' => $languageLglPath . 'hidden',
                'config' => [
                    'type' => 'check',
                    'renderType' => 'checkboxToggle',
                ],
            ],
            'starttime' => [
                'exclude' => true,
                'label' => $languageLglPath . 'starttime',
                'config' => [
                    'type' => 'datetime',
                    'default' => 0,
                ],
            ],
            'endtime' => [
                'exclude' => true,
                'label' => $languageLglPath . 'endtime',
                'config' => [
                    'type' => 'datetime',
                    'default' => 0,
                    'range' => [
                        'upper' => mktime(0, 0, 0, 1, 1, 2038),
                    ],
                ],
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
                            'label' => $languageLglPath . 'hide_at_login',
                            'value' => -1,
                        ],
                        [
                            'label' => $languageLglPath . 'any_login',
                            'value' => -2,
                        ],
                        [
                            'label' => $languageLglPath . 'usergroups',
                            'value' => '--div--',
                        ],
                    ],
                    'exclusiveKeys' => '-1,-2',
                    'foreign_table' => 'fe_groups',
                    'foreign_table_where' => 'ORDER BY fe_groups.title',
                ],
            ],
            'title' => [
                'exclude' => false,
                'label' => $languageLglPath . 'title',
                'config' => [
                    'type' => 'input',
                    'size' => 40,
                    'max' => 256,
                    'eval' => 'trim',
                    'required' => true,
                ],
            ],
            'fe_users_uid' => [
                'exclude' => true,
                'label' => $languageDbPath . 'fe_users_uid',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingleBox',
                    'foreign_table' => 'fe_users',
                    'foreign_table_where' => 'ORDER BY fe_users.name',
                    'size' => 15,
                    'minitems' => 0,
                    'maxitems' => 12,
                ],
            ],
            'reusable' => [
                'exclude' => true,
                'label' => $languageDbPath . 'reusable',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        ['label' => $languageDbPath . 'reusable.I.0', 'value' => 0],
                        ['label' => $languageDbPath . 'reusable.I.1', 'value' => 1],
                        ['label' => $languageDbPath . 'reusable.I.2', 'value' => 2],
                        ['label' => $languageDbPath . 'reusable.I.3', 'value' => 3],
                        ['label' => $languageDbPath . 'reusable.I.4', 'value' => 4],
                    ],
                    'default' => 0,
                ],
            ],
            'usecounter' => [
                'exclude' => true,
                'label' => $languageDbPath . 'usecounter',
                'config' => [
                    'type' => 'number',
                    'default' => 0,
                ],
            ],
            'combinable' => [
                'exclude' => true,
                'label' => $languageDbPath . 'combinable',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        ['label' => $languageDbPath . 'combinable.I.0', 'value' => 0],
                        ['label' => $languageDbPath . 'combinable.I.1', 'value' => 1],
                    ],
                    'default' => 0,
                ],
            ],
            'amount_type' => [
                'exclude' => true,
                'label' => $languageDbPath . 'amount_type',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        ['label' => $languageDbPath . 'amount_type.I.0', 'value' => 0],
                        ['label' => $languageDbPath . 'amount_type.I.1', 'value' => 1],
                    ],
                    'default' => 0,
                ],
            ],
            'amount' => [
                'exclude' => true,
                'label' => $languageDbPath . 'amount',
                'config' => [
                    'type' => 'number',
                    'format' => 'decimal',
                    'size' => 20,
                    'required' => true,
                    'default' => 0,
                ],
            ],
            'tax' => [
                'exclude' => true,
                'label' => $languageDbPath . 'tax',
                'config' => [
                    'type' => 'number',
                    'format' => 'decimal',
                    'size' => 12,
                    'default' => 0,
                ],
            ],
            'note' => [
                'label' => $languageLglPath . 'note',
                'config' => [
                    'type' => 'text',
                    'cols' => 48,
                    'rows' => 5,
                    'default' => '',
                ],
            ],
            'acquired_groups' => [
                'label' => $languageDbPath . 'acquired_groups',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectMultipleSideBySide',
                    'size' => 6,
                    'minitems' => 0,
                    'maxitems' => 50,
                    'exclusiveKeys' => '-1,-2',
                    'foreign_table' => 'fe_groups',
                    'foreign_table_where' => 'ORDER BY fe_groups.title',
                ],
            ],
            'acquired_days' => [
                'label' => $languageDbPath . 'acquired_days',
                'config' => [
                    'type' => 'number',
                    'size' => 12,
                    'default' => 0,
                ],
            ],
        ],
        'types' => [
            '0' => [
                'showitem' => 'title, --palette--;;1, fe_users_uid, hidden, --palette--;;2, '
                    . '--div--;' . $languageDbPath . 'acquired, acquired_groups, acquired_days',
            ],
        ],
        'palettes' => [
            '1' => ['showitem' => 'reusable, usecounter, combinable, amount_type, amount, tax, note'],
            '2' => ['showitem' => 'starttime, endtime, fe_group'],
        ],
    ];
})('voucher');
