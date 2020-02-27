<?php
defined('TYPO3_MODE') || die('Access denied.');

$result = array(
    'ctrl' => array(
        'title' => VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY crdate',
        'delete' => 'deleted',
        'enablecolumns' => array (
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ),
        'iconfile' => 'EXT:' . VOUCHER_EXT . '/icon_tx_voucher_codes.gif',
        'dividers2tabs' => '1',
        'searchFields' => 'uid,title,code,note',
        'rootLevel' => -1,
    ),
    'interface' => array (
        'showRecordFieldList' => 'hidden,starttime,endtime,title,code,fe_users_uid,reusable,usecounter,combinable, amount,tax,note,acquired_groups'
    ),
    'columns' => array (
        'hidden' => array (
            'exclude' => 1,
            'label' => DIV2007_LANGUAGE_LGL . 'hidden',
            'config' => array (
                'type' => 'check'
            )
        ),
        'starttime' => array (
            'exclude' => 1,
            'label' => DIV2007_LANGUAGE_LGL . 'starttime',
            'config' => array (
                'type' => 'input',
                'size' => '8',
                'max' => '20',
                'eval' => 'date',
                'checkbox' => '0',
                'default' => '0'
            )
        ),
        'endtime' => array (
            'exclude' => 1,
            'label' => DIV2007_LANGUAGE_LGL . 'endtime',
            'config' => array (
                'type' => 'input',
                'size' => '8',
                'max' => '20',
                'eval' => 'date',
                'checkbox' => '0',
                'default' => '0',
                'range' => array (
                    'upper' => mktime(0, 0, 0, 12, 31, 2150),
                    'lower' => mktime(0, 0, 0, date('m') - 1, date('d'), date('Y'))
                )
            )
        ),
        'fe_group' => array (
            'exclude' => 1,
            'label' => DIV2007_LANGUAGE_LGL . 'fe_group',
            'config' => array (
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => 7,
                'maxitems' => 20,
                'items' => array (
                    array('', 0),
                    array(DIV2007_LANGUAGE_LGL . 'hide_at_login', -1),
                    array(DIV2007_LANGUAGE_LGL . 'any_login', -2),
                    array(DIV2007_LANGUAGE_LGL . 'usergroups', '--div--')
                ),
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title',
                'enableMultiSelectFilterTextfield' => true
            )
        ),
        'title' => array (
            'exclude' => 0,
            'label' => DIV2007_LANGUAGE_LGL . 'title',
            'config' => array (
                'type' => 'input',
                'size' => '40',
                'max' => '256',
                'eval' => 'required,trim',
                'default' => ''
            )
        ),
        'fe_users_uid' => array (
            'exclude' => 1,
            'label' => VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.fe_users_uid',
            'config' => array (
                'type' => 'select',
                'renderType' => 'selectSingleBox',
                'foreign_table' => 'fe_users',
                'foreign_table_where' => ' ORDER BY fe_users.name',
                'size' => 15,
                'minitems' => 0,
                'maxitems' => 12,
                'default' => 0
            )
        ),
        'reusable' => array (
            'exclude' => 1,
            'label' => VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.reusable',
            'config' => array (
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array (
                    array(VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.reusable.I.0', '0'),
                    array(VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.reusable.I.1', '1'),
                    array(VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.reusable.I.2', '2'),
                    array(VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.reusable.I.3', '3'),
                    array(VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.reusable.I.4', '4'),
                ),
                'size' => 1,
                'maxitems' => 1,
                'default' => 0
            )
        ),
        'usecounter' => array (
            'exclude' => 1,
            'label' => VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.usecounter',
            'config' => array (
                'type' => 'input',
                'eval' => 'int',
                'default' => 0,
            )
        ),
        'combinable' => array (
            'exclude' => 1,
            'label' => VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.combinable',
            'config' => array (
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array (
                    array(VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.combinable.I.0', '0'),
                    array(VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.combinable.I.1', '1'),
                ),
                'size' => 1,
                'maxitems' => 1,
                'default' => 0,
            )
        ),
        'amount_type' => array (
            'exclude' => 1,
            'label' => VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.amount_type',
            'config' => array (
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array (
                    array(VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.amount_type.I.0', '0'),
                    array(VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.amount_type.I.1', '1'),
                ),
                'size' => 1,
                'maxitems' => 1,
                'default' => 0,
            )
        ),
        'amount' => array (
            'exclude' => 1,
            'label' => VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.amount',
            'config' => array (
                'type' => 'input',
                'size' => '20',
                'max' => '20',
                'eval' => 'required,trim,double2',
                'default' => 0,
            )
        ),
        'tax' => array (
            'exclude' => 1,
            'label' => VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.tax',
            'config' => array (
                'type' => 'input',
                'size' => '12',
                'max' => '19',
                'eval' => 'trim,double2',
                'default' => 0
            )
        ),
        'note' => array (
            'label' => DIV2007_LANGUAGE_LGL . 'note',
            'config' => array (
                'type' => 'text',
                'cols' => '48',
                'rows' => '5',
                'default' => ''
            )
        ),
        'acquired_groups' => array(
            'label' => VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.acquired_groups',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => '6',
                'minitems' => '0',
                'maxitems' => '50',
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title',
                'enableMultiSelectFilterTextfield' => true
            )
        ),
        'acquired_days' => array(
            'label' => VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.acquired_days',
            'config' => array (
                'type' => 'input',
                'size' => '12',
                'eval' => 'int',
                'default' => 0
            )
        ),
    ),
    'types' => array (
        '0' => array('showitem' => 'title, --palette--;;1, fe_users_uid, hidden, --palette--;;2, ' .
            '--div--;' . VOUCHER_EXT_LANGUAGE_PATH . 'locallang_db.xlf:tx_voucher_codes.acquired, acquired_groups, acquired_days')
    ),
    'palettes' => array (
        '1' => array('showitem' => 'reusable, usecounter, combinable, amount_type, amount, tax, note'),
        '2' => array('showitem' => 'starttime,endtime,fe_group'),
    )
);


return $result;

