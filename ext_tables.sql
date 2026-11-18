#
# Table structure for table 'tx_voucher_codes'
#
CREATE TABLE tx_voucher_codes (
	title varchar(256) DEFAULT '' NOT NULL,
	code varchar(256) DEFAULT '' NOT NULL,
	fe_users_uid varchar(255) DEFAULT '' NOT NULL,
	reusable int(11) DEFAULT '0' NOT NULL,
	usecounter int(11) DEFAULT '1' NOT NULL,
	combinable int(11) DEFAULT '0' NOT NULL,
	amount_type int(11) DEFAULT '0' NOT NULL,
	amount decimal(19,2) DEFAULT '0.00' NOT NULL,
	tax decimal(19,2) DEFAULT '0.00' NOT NULL,
	note text,
	acquired_days int(11) DEFAULT '0' NOT NULL,
	acquired_groups text,

	KEY code_idx (code(64))
);

#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_voucher_usedcode varchar(256) DEFAULT '' NOT NULL
);
