<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "voucher".
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Vouchercode Manager',
	'description' => 'Backend extension to manage voucher codes for FE users. Use this together with tt_products 2.7.5 or later.',
	'category' => 'module',
	'author' => 'Franz Holzinger',
	'author_email' => 'franz@ttproducts.de',
	'shy' => '',
	'dependencies' => 'div2007',
	'conflicts' => '',
	'priority' => '',
	'module' => 'mod1',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.1.0',
	'_md5_values_when_last_written' => 'a:23:{s:9:"ChangeLog";s:4:"4deb";s:16:"ext_autoload.php";s:4:"3144";s:12:"ext_icon.gif";s:4:"0832";s:17:"ext_localconf.php";s:4:"f42f";s:14:"ext_tables.php";s:4:"0f90";s:14:"ext_tables.sql";s:4:"aadc";s:25:"icon_tx_voucher_codes.gif";s:4:"0832";s:16:"locallang_db.xml";s:4:"b61d";s:10:"README.txt";s:4:"68a3";s:7:"tca.php";s:4:"9630";s:28:"api/class.tx_voucher_api.php";s:4:"488d";s:14:"doc/manual.sxw";s:4:"4d8e";s:40:"hooks/agency/class.tx_voucher_agency.php";s:4:"563c";s:26:"hooks/agency/locallang.xml";s:4:"93c3";s:29:"lib/class.tx_voucher_lang.php";s:4:"092a";s:17:"lib/locallang.xml";s:4:"b0af";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"0ad3";s:13:"mod1/edit.gif";s:4:"5fcb";s:16:"mod1/garbage.gif";s:4:"7b41";s:14:"mod1/index.php";s:4:"a3bb";s:18:"mod1/locallang.php";s:4:"afc1";s:22:"mod1/locallang_mod.php";s:4:"2ebe";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-5.4.99',
			'typo3' => '4.5-6.2.99',
			'div2007' => '0.14.1-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>