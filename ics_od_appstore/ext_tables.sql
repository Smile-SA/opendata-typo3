#
# Table structure for table 'tx_icsodappstore_applications'
#
CREATE TABLE tx_icsodappstore_applications (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	fe_cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	apikey varchar(15) DEFAULT '' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	description text,
	old_platform varchar(255) DEFAULT '' NOT NULL,
	platforms int(11) DEFAULT '0' NOT NULL
	countcall int(11) DEFAULT '0' NOT NULL,
	maxcall int(11) DEFAULT '0' NOT NULL,
	release_date int(11) DEFAULT '0' NOT NULL,
	logo text,
	screenshot text,
	link varchar(255) DEFAULT '' NOT NULL,
	update_date int(11) DEFAULT '0' NOT NULL,
	lock_publication int(11) DEFAULT '0' NOT NULL,
	publish tinyint(4) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);

#ALTER TABLE tx_icsodappstore_applications CHANGE COLUMN platform old_platform;


#
# Table structure for table 'tx_icsodappstore_logs'
#
CREATE TABLE tx_icsodappstore_logs (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	application int(11) DEFAULT '0' NOT NULL,
	ip varchar(255) DEFAULT '' NOT NULL,
	cmd varchar(255) DEFAULT '' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_icsodappstore_month_logs'
#
CREATE TABLE tx_icsodappstore_month_logs (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	application int(11) DEFAULT '0' NOT NULL,
	ip varchar(255) DEFAULT '' NOT NULL,
	cmd varchar(255) DEFAULT '' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_icsodappstore_statistics'
#
CREATE TABLE tx_icsodappstore_statistics (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	application int(11) DEFAULT '0' NOT NULL,
	cmd varchar(255) DEFAULT '' NOT NULL,
	count int(11) DEFAULT '0' NOT NULL,
	date int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY date_appli(date, application)

);



#
# Table structure for table 'tx_icsodappstore_platforms'
#
CREATE TABLE tx_icsodappstore_platforms (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)	
);



#
# Table structure for table 'tx_icsodappstore_apps_platforms_mm'
# 
#
CREATE TABLE tx_icsodappstore_apps_platforms_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  sorting_foreign int(11) DEFAULT '0' NOT NULL,
  
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);
