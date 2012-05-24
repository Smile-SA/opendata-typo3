<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 In Cité Solution <technique@in-cite.net>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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
 * $Id: class.tx_icsoddatastore_sysfolder.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
/*
 * Original author:	Rene Fritz <r.fritz@colorcube.de>
 * Original package: DAM-Core
 * Original subpackage: Lib
 *
 * Modified by Pierrick Caillon <pierrick@in-cite.net>
 * to be included in ics_od_datastore to make the same sysfolder feature as DAM for the filegroups repository.
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   67: class tx_icsoddatastore_sysfolder
 *
 *   76:     static function getPid()
 *   87:     static function init()
 *  103:     static function getAvailable()
 *  117:     static function getPidList()
 *  128:     static function create($pid=0)
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */




/**
 * filegroups sysfolder functions
 * A sysfolder is used for filegroups record storage. The sysfolder will be created automatically.
 * In principle it could be possible to use more than one sysfolder. But that concept is not easy to handle and therefore not used yet.
 *
 * @origauthor	Rene Fritz <r.fritz@colorcube.de>
 * @origpackage DAM-Core
 * @origsubpackage Lib
 * @author Pierrick Caillon <pierrick@in-cite.net>
 */
class tx_icsoddatastore_sysfolder {

	protected static $pid = 0; /**< Storage pid */

	/**
	 * @return	integer		L'identifiant de la page.
	 * @author Pierrick Caillon <pierrick@in-cite.net>
	 * @desc Récupérer l'identifiant du dossier de stockage des filegroups.
	 */
	static function getPid() {
		if (!tx_icsoddatastore_sysfolder::$pid)
			tx_icsoddatastore_sysfolder::$pid = tx_icsoddatastore_sysfolder::init();
		return tx_icsoddatastore_sysfolder::$pid;
	}

	/**
	 * Find the filegroups folders or create one.
	 *
	 * @return	integer		The uid of the default sysfolder
	 */
	static function init()	{

		if (!is_object($GLOBALS['TYPO3_DB'])) return false;

		$aDocFolders = tx_icsoddatastore_sysfolder::getAvailable();
		$aDF = current($aDocFolders);

		return $aDF['uid'];
	}


	/**
	 * Find the filegroups folders and return an array of record arrays.
	 *
	 * @return	array		Array of rows of found filegroups folders with fields: uid, pid, title. An empty array will be returned if no folder was found.
	 */
	static function getAvailable() {
		$aRows = array();
		if ($aDocFolders = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,pid,title,doktype', 'pages', 'module='.$GLOBALS['TYPO3_DB']->fullQuoteStr('datastore', 'pages').' AND deleted=0', '', '', '', 'uid')) {
			$aRows = $aDocFolders;
		}
		return $aRows;
	}


	/**
	 * Returns pidList/list of pages uid's of filegroupss Folders
	 *
	 * @return	string		Commalist of PID's
	 */
	static function getPidList() {
		return implode(',',array_keys(tx_icsoddatastore_sysfolder::getAvailable()));
	}


	/**
	 * Create a document folders
	 *
	 * @param	integer		$pid: The PID of the sysfolder which is by default 0 to place the folder in the root.
	 * @return	void
	 */
	static function create($pid=0) {
		$fields_values = array();
		$fields_values['pid'] = $pid;
		$fields_values['sorting'] = 29999;
		$fields_values['perms_user'] = 31;
		$fields_values['perms_group'] = 31;
		$fields_values['perms_everybody'] = 31;
		$fields_values['title'] = 'Datastore';
		$fields_values['doktype'] = 254; // sysfolder
		$fields_values['module'] = 'datastore';
		$fields_values['crdate'] = time();
		$fields_values['tstamp'] = time();
		return $GLOBALS['TYPO3_DB']->exec_INSERTquery('pages', $fields_values);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_datastore/lib/class.tx_icsoddatastore_sysfolder.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_datastore/lib/class.tx_icsoddatastore_sysfolder.php']);
}

?>