<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_voucher_codes'] = array (
	'ctrl' => $TCA['tx_voucher_codes']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,starttime,endtime,title,fe_users_uid,reusable,usecounter,amount,code,note,acquired_groups'
	),
	'feInterface' => $TCA['tx_voucher_codes']['feInterface'],
	'columns' => array (
		'hidden' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => array (
				'type' => 'check'
			)
		),
		'starttime' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
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
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array (
					'upper' => mktime(0,0,0,12,31,2150),
					'lower' => mktime(0, 0, 0, date('m') - 1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.fe_group',
			'config' => array (
				'type' => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.php:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.php:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.php:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		'title' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.title',
			'config' => array (
				'type' => 'input',
				'size' => '40',
				'max' => '256',
				'eval' => 'required,trim',
			)
		),
		'fe_users_uid' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.fe_users_uid',
			'config' => array (
				'type' => 'select',
				'internal_type' => 'db',
				'allowed' => 'tt_products',
				'foreign_table' => 'fe_users',
				'foreign_table_where' => ' ORDER BY fe_users.name',
				'size' => 50,
				'minitems' => 0,
				'maxitems' => 12,
			)
		),
		'reusable' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.reusable',
			'config' => array (
				'type' => 'select',
				'items' => array (
					array('LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.reusable.I.0', '0'),
					array('LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.reusable.I.1', '1'),
					array('LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.reusable.I.2', '2'),
					array('LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.reusable.I.3', '3'),
					array('LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.reusable.I.4', '4'),
				),
				'size' => 1,
				'maxitems' => 1,
			)
		),
		'usecounter' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.usecounter',
			'config' => array (
				'type' => 'input',
				'default' => '0',
				'eval' => 'int',
			)
		),
		'amount_type' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.amount_type',
			'config' => array (
				'type' => 'select',
				'items' => array (
					array('LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.amount_type.I.0', '0'),
					array('LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.amount_type.I.1', '1'),
				),
				'size' => 1,
				'maxitems' => 1,
			)
		),
		'amount' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.amount',
			'config' => array (
				'type' => 'input',
				'size' => '20',
				'max' => '20',
				'eval' => 'required,trim,double2',
			)
		),
		'code' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.code',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
		'note' => array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.note',
			'config' => array (
				'type' => 'text',
				'cols' => '48',
				'rows' => '5'
			)
		),
		'acquired_groups' => array(
			'label' => 'LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.acquired_groups',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'fe_groups',
				'foreign_table_where' => 'ORDER BY fe_groups.title',
				'size' => '6',
				'minitems' => '0',
				'maxitems' => '50'
			)
		),
		'acquired_days' => array(
			'label' => 'LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.acquired_days',
			'config' => array (
				'type' => 'input',
				'size' => '12',
				'eval' => 'int',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, title, fe_users_uid, reusable, usecounter, amount_type, amount, code, note,' .
			'--div--;LLL:EXT:' . VOUCHER_EXT . '/locallang_db.xml:tx_voucher_codes.acquired, acquired_groups, acquired_days,')
	),
	'palettes' => array (
		'1' => array('showitem' => 'starttime,endtime,fe_group'),
	)
);


?>