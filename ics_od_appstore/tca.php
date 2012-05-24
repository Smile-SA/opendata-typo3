<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_icsodappstore_applications'] = array (
	'ctrl' => $TCA['tx_icsodappstore_applications']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,apikey,fe_cruser_id,title,description,platform,countcall,maxcall,release_date,logo,screenshot,link,update_date,lock_publication'
	),
	'feInterface' => $TCA['tx_icsodappstore_applications']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'apikey' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_applications.apikey',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'max' => '15',	
				'eval' => 'required,trim',
			)
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_applications.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required,trim',
			)
		),
		'description' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_applications.description',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '15',
			)
		),
		'platform' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_applications.platform',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'trim',
			)
		),
		'countcall' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_applications.countcall',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'int',
			)
		),
		'maxcall' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_applications.maxcall',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'int',
			)
		),
		'release_date' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_applications.release_date',		
			'config' => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0'
			)
		),
		'publish' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_applications.publish',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'logo' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_applications.logo',		
			'config' => array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',	
				'max_size' => 20,	
				'uploadfolder' => 'uploads/tx_icsodappstore',
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'screenshot' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_applications.screenshot',		
			'config' => array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',	
				'max_size' => 400,	
				'uploadfolder' => 'uploads/tx_icsodappstore',
				'size' => 3,	
				'minitems' => 0,
				'maxitems' => 3,
			)
		),
		'link' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_applications.link',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'trim',
			)
		),
		'update_date' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_applications.update_date',		
			'config' => array (
				'type'     => 'input',
				'size'     => '12',
				'max'      => '20',
				'eval'     => 'datetime',
				'checkbox' => '0',
				'default'  => '0'
			)
		),
		'lock_publication' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_applications.lock_publication',		
			'config' => array (
				'type' => 'radio',
				'items' => array (
					array('LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_applications.lock_publication.I.0', '0'),
					array('LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_applications.lock_publication.I.1', '1'),
				),
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, apikey, fe_cruser_id, title, description, platform, countcall, maxcall, release_date, publish, logo, screenshot, link, update_date, lock_publication')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);




$TCA['tx_icsodappstore_logs'] = array (
	'ctrl' => $TCA['tx_icsodappstore_logs']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,application,ip,cmd'
	),
	'feInterface' => $TCA['tx_icsodappstore_logs']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'application' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_logs.application',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'int',
			)
		),
		'ip' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_logs.ip',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'trim',
			)
		),
		'cmd' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_logs.cmd',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'trim',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, application, ip, cmd')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_icsodappstore_statistics'] = array (
	'ctrl' => $TCA['tx_icsodappstore_statistics']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,application,cmd,count,date'
	),
	'feInterface' => $TCA['tx_icsodappstore_statistics']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'application' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_statistics.application',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'int',
			)
		),
		'cmd' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_statistics.cmd',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'trim',
			)
		),
		'count' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_statistics.count',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'int',
			)
		),
		'date' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_od_appstore/locallang_db.xml:tx_icsodappstore_statistics.date',		
			'config' => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0'
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, application, cmd, count, date')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>