<?php defined('TYPO3_MODE') || die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ics_od_datastore']['additionalFieldsSearchMarkers'][] = 'EXT:smile_icsoddatastore_date/hooks/class.tx_smileicsoddatastore_additionalFieldsSearchMarkers.php:tx_smileicsoddatastore_additionalFieldsSearchMarkers';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ics_od_datastore']['addSearchRestriction'][] = 'EXT:smile_icsoddatastore_date/hooks/class.tx_smileicsoddatastore_addSearchRestriction.php:tx_smileicsoddatastore_addSearchRestriction';

?>