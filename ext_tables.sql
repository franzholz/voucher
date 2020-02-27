#
# Table structure for table 'tx_voucher_codes'
#
CREATE TABLE tx_voucher_codes (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	code tinytext NOT NULL,
	fe_users_uid int(11) DEFAULT '0' NOT NULL,
	reusable int(11) DEFAULT '0' NOT NULL,
	usecounter int(11) DEFAULT '1' NOT NULL,
	combinable int(11) DEFAULT '0' NOT NULL,
	amount_type int(11) DEFAULT '0' NOT NULL,
	amount decimal(19,2) DEFAULT '0.00' NOT NULL,
	tax decimal(19,2) DEFAULT '0.00' NOT NULL,
	note text NOT NULL,
	acquired_days int(11) DEFAULT '0' NOT NULL,
	acquired_groups tinytext,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_voucher_usedcode varchar(50) DEFAULT '',
);

