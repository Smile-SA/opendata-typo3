#
# Table structure for table 'tx_icsoddatastore_filegroups'
#
CREATE TABLE tx_icsoddatastore_filegroups (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	description text,
	technical_data text,
	files int(11) DEFAULT '0' NOT NULL,
	agency int(11) DEFAULT '0' NOT NULL,
	contact int(11) DEFAULT '0' NOT NULL,
	licence int(11) DEFAULT '0' NOT NULL,
	release_date int(11) DEFAULT '0' NOT NULL,
	update_date int(11) DEFAULT '0' NOT NULL,
	time_period varchar(255) DEFAULT '' NOT NULL,
	update_frequency varchar(255) DEFAULT '' NOT NULL,
    publisher int(11) DEFAULT '0' NOT NULL,
    creator int(11) DEFAULT '0' NOT NULL,
    manager int(11) DEFAULT '0' NOT NULL,
    owner int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_icsoddatastore_fileformats'
#
CREATE TABLE tx_icsoddatastore_fileformats (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	name varchar(255) DEFAULT '' NOT NULL,
	description text,
	mimetype varchar(100) DEFAULT '' NOT NULL,
	extension varchar(20) DEFAULT '' NOT NULL,
	picto text,
	searchable tinyint(4) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);





#
# Table structure for table 'tx_icsoddatastore_licences'
#
CREATE TABLE tx_icsoddatastore_licences (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	name varchar(255) DEFAULT '' NOT NULL,
	link varchar(255) DEFAULT '' NOT NULL,
	logo varchar(255) DEFAULT '' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_icsoddatastore_downloads'
#
CREATE TABLE tx_icsoddatastore_downloads (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	filegroup int(11) DEFAULT '0' NOT NULL,
	ip varchar(40) DEFAULT '' NOT NULL,
	file int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);




#
# Table structure for table 'tx_icsoddatastore_files_filegroup_mm'
# 
#
CREATE TABLE tx_icsoddatastore_files_filegroup_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  sorting_foreign int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tx_icsoddatastore_files'
#
CREATE TABLE tx_icsoddatastore_files (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	file text,
	format int(11) DEFAULT '0' NOT NULL,
	filegroup int(11) DEFAULT '0' NOT NULL,
	type int(11) DEFAULT '0' NOT NULL,
	url  varchar(255) DEFAULT '' NOT NULL,
	record_type int(11) DEFAULT '0' NOT NULL,
	md5 varchar(255) DEFAULT '' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tx_icsoddatastore_tiers'
#
CREATE TABLE tx_icsoddatastore_tiers (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    name varchar(255) DEFAULT '' NOT NULL,
    description text,
    email tinytext NOT NULL,
    website varchar(255) DEFAULT '' NOT NULL,
	logo varchar(255) DEFAULT '' NOT NULL,
	address varchar(255) DEFAULT '' NOT NULL,
	zipcode varchar(10) DEFAULT '' NOT NULL,
	city varchar(50) DEFAULT '' NOT NULL,
	country varchar(40) DEFAULT '' NOT NULL,	
	latitude tinytext NOT NULL,
	longitude tinytext NOT NULL,
   
    PRIMARY KEY (uid),
    KEY parent (pid)
);


#
# Table structure fo table 'tx_icsoddatastore_filetypes'
#
CREATE TABLE tx_icsoddatastore_filetypes (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
	name varchar(255) DEFAULT '' NOT NULL,
    description text,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);