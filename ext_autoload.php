<?php

$extensionPath = t3lib_extMgm::extPath('voucher');
return array(
	'tx_voucher_module1' => $extensionPath . 'mod1/index.php',
	'tx_voucher_api' => $extensionPath . 'api/class.tx_voucher_api.php',
	'tx_voucher_agency' => $extensionPath . 'hooks/class.tx_voucher_agency.php',
	'tx_voucher_lang' => $extensionPath . 'lib/class.tx_voucher_lang.php',
);
?>