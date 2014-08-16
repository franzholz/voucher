<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_voucher_codes'] = array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:' . $_EXTKEY . '/locallang_db.xml:tx_voucher_codes',
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
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'icon_tx_voucher_codes.gif',
		'searchFields' => 'uid,title,code,note',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'title, fe_users_uid, reusable, usecounter, amount, code, note, acquired_groups, hidden, starttime, endtime',
	)
);


t3lib_extMgm::addToInsertRecords('tx_voucher_codes');

t3lib_extMgm::allowTableOnStandardPages('tx_voucher_codes');

if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addModule('web', 'txvoucherM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
}

if (TYPO3_MODE == "BE" || $loadTcaAdditions == TRUE) {

	$tempColumns = Array (
		'tx_voucher_usedcode' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:' . $_EXTKEY . '/locallang_db.xml:fe_users.tx_voucher_usedcode',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'max' => '256',
				'readOnly' => '1',
			)
		)
	);
	t3lib_div::loadTCA('fe_users');
	t3lib_extMgm::addTCAcolumns('fe_users', $tempColumns, 1);
	t3lib_extMgm::addToAllTCAtypes(
		'fe_users',
		'tx_voucher_usedcode;;;;1-1-1',
		'',
		'after:www,'
	);
}

?>