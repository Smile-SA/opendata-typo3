<?php defined('TYPO3_MODE') || die('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_smileicsoddatastorerss_pi1.php', '_pi1', 'list_type', 0);

if (t3lib_extMgm::isLoaded('ics_od_categories')) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['smile_icsoddatastore_rss']['additionalFieldsRSSMarkers'][] = 'EXT:ics_od_categories/class.tx_icsodcategories_datastore.php:tx_icsodcategories_datastore';
}

?>