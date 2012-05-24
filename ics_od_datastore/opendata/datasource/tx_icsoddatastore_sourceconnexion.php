<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 In Cité Solution <technique@in-cite.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/*
 * $Id: tx_icsoddatastore_sourceconnexion.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */

/**
 * @file tx_icsoddatastore_sourceconnexion.php
 *
 * Connexion functions to datasource
 *
 * @author    In Cité Solution <technique@in-cite.net>
 * @package    TYPO3.ics_od_datastore
 */


/**
 * Connect to the datasource opendatapkg type typo3db
 *
 * @return	object		The connexion to the datasource
 */
function typo3db_opendatapkg_connect()
{

	$host = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ics_od_datastore']['datasourceconnect']['typo3db_opendatapkg']['host'];
	$login = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ics_od_datastore']['datasourceconnect']['typo3db_opendatapkg']['login'];
	$password = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ics_od_datastore']['datasourceconnect']['typo3db_opendatapkg']['password'];
	$base = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ics_od_datastore']['datasourceconnect']['typo3db_opendatapkg']['base'];

	$_datasourceDB = t3lib_div::makeInstance('t3lib_DB');
	try {
		$_datasourceDB->connectDB($host, $login, $password, $base);
	}
	catch (Exception $e)
	{
		return $e->getMessage();
	}

	// *************************
	// * User inclusions typo3db_opendatapkg_connect
	// * DO NOT DELETE OR CHANGE THOSE COMMENTS
	// *************************

	// ... (Add additional operations here) ...

	// * End user inclusions typo3db_opendatapkg_connect


	return $_datasourceDB;
}

