<?php
/*
 * $Id: ext_localconf.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_icsoddatastore_pi1.php', '_pi1', 'list_type', 0);


t3lib_extMgm::addPItoST43($_EXTKEY, 'pi2/class.tx_icsoddatastore_pi2.php', '_pi2', 'list_type', 0);


//--- API commands ---


// --- datastore_getagencies
$TYPO3_CONF_VARS['EXTCONF']['ics_od_core_api']['command']['1.0']['datastore_getagencies'] = 'EXT:ics_od_datastore/opendata/class.tx_icsoddatastore_datastore_getagencies_command.php:tx_icsoddatastore_datastore_getagencies_command';
$TYPO3_CONF_VARS['EXTCONF']['ics_od_datastore']['datasource']['agency'] = 'EXT:ics_od_datastore/opendata/datasource/class.tx_icsoddatastore_agency_datasource.php:tx_icsoddatastore_agency_datasource';
// --- datastore_getlicences
$TYPO3_CONF_VARS['EXTCONF']['ics_od_core_api']['command']['1.0']['datastore_getlicences'] = 'EXT:ics_od_datastore/opendata/class.tx_icsoddatastore_datastore_getlicences_command.php:tx_icsoddatastore_datastore_getlicences_command';
$TYPO3_CONF_VARS['EXTCONF']['ics_od_datastore']['datasource']['licence'] = 'EXT:ics_od_datastore/opendata/datasource/class.tx_icsoddatastore_licence_datasource.php:tx_icsoddatastore_licence_datasource';
// --- datastore_getfileformats
$TYPO3_CONF_VARS['EXTCONF']['ics_od_core_api']['command']['1.0']['datastore_getfileformats'] = 'EXT:ics_od_datastore/opendata/class.tx_icsoddatastore_datastore_getfileformats_command.php:tx_icsoddatastore_datastore_getfileformats_command';
$TYPO3_CONF_VARS['EXTCONF']['ics_od_datastore']['datasource']['fileformat'] = 'EXT:ics_od_datastore/opendata/datasource/class.tx_icsoddatastore_fileformat_datasource.php:tx_icsoddatastore_fileformat_datasource';


// --- Datasource connexions for commands datastore_getagencies, datastore_getlicences, datastore_getdatasets, datastore_searchdatasets
$TYPO3_CONF_VARS['EXTCONF']['ics_od_datastore']['datasourceconnect']['typo3db_opendatapkg']['host'] = TYPO3_db_host;
$TYPO3_CONF_VARS['EXTCONF']['ics_od_datastore']['datasourceconnect']['typo3db_opendatapkg']['login'] = TYPO3_db_username;
$TYPO3_CONF_VARS['EXTCONF']['ics_od_datastore']['datasourceconnect']['typo3db_opendatapkg']['password'] = TYPO3_db_password;
$TYPO3_CONF_VARS['EXTCONF']['ics_od_datastore']['datasourceconnect']['typo3db_opendatapkg']['base'] = TYPO3_db;

// *************************
// * User inclusions typo3db_opendatapkg connexion
// * DO NOT DELETE OR CHANGE THOSE COMMENTS
// *************************

// ... (Add additional operations here) ...
// --- datastore_getdatasets, datastore_searchdatasets
$TYPO3_CONF_VARS['EXTCONF']['ics_od_core_api']['command']['1.0']['datastore_getdatasets'] = 'EXT:ics_od_datastore/opendata/class.tx_icsoddatastore_datastore_getdatasets_command.php:tx_icsoddatastore_datastore_getdatasets_command';
$TYPO3_CONF_VARS['EXTCONF']['ics_od_core_api']['command']['1.0']['datastore_searchdatasets'] = 'EXT:ics_od_datastore/opendata/class.tx_icsoddatastore_datastore_searchdatasets_command.php:tx_icsoddatastore_datastore_searchdatasets_command';
$TYPO3_CONF_VARS['EXTCONF']['ics_od_datastore']['datasource']['dataset'] = 'EXT:ics_od_datastore/opendata/datasource/class.tx_icsoddatastore_dataset_datasource.php:tx_icsoddatastore_dataset_datasource';

// * End user inclusions typo3db_opendatapkg connexion


?>