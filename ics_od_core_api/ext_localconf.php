<?php
/*
 * $Id: ext_localconf.php 48705 2011-06-14 14:00:35Z mygoddess $
 */
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

$TYPO3_CONF_VARS['FE']['eID_include']['ics_od_api'] = 'EXT:ics_od_core_api/api/tx_icsodcoreapi_client.php';
?>