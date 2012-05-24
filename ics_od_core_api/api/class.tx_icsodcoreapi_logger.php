<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 In Cité Solution <technique@in-cite.net>
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
 * $Id: class.tx_icsodcoreapi_logger.php 49031 2011-06-23 09:54:36Z tsimomo $
 */

/**
 * Updates log informations.
 *
 * @author    Tsi Yang <tsi@in-cite.net>
 * @package    TYPO3
 */
class tx_icsodcoreapi_logger {

	private $key; /**< The API Key to log for. */
	private $cmd; /**< The executed command. */
	private $pid; /**< The pid of the page where the API Key is defined. */
	private $rid; /**< The record id of the API Key record. */
	private $usage; /**< The usage count. */

	/**
	 * Initializes the logger.
	 *
	 * @param	array	$params: The service parameters
	 * @return	void
	 */
	function init(array $params) {
		$this->key = $params['key'];
		$this->cmd = $params['cmd'];

		$row = $this->getRow();
		$this->pid = $row['pid'];
		$this->rid = $row['uid'];
		$this->usage = $row['countcall'];
	}

	/**
	 * Logs the call and increments the counter.
	 *
	 * @return	void
	 */
	function logCall() {
		$this->insertCall();
		$this->incrementsUsage();
	}

	/**
	 * Adds the log record to the call log.
	 *
	 * @return	void
	 */
	private function insertCall() {
		global $TYPO3_DB;

		$table = 'tx_icsodappstore_logs';
		$insertArray = array(
			'pid' => $this->pid,
			'tstamp' => time(),
			'crdate' => time(),
			'application' => $this->rid,
			'ip' => t3lib_div::getIndpEnv('REMOTE_ADDR'),
			'cmd' => $this->cmd,
		);
		$TYPO3_DB->exec_INSERTquery(
			$table,
			$insertArray
		);
	}
	/**
	 * Updates the usage counter.
	 *
	 * @return	void
	 */
	private function incrementsUsage() {
		global $TYPO3_DB;
		// Hardcoded update for concurrency support.
		$TYPO3_DB->sql_query('UPDATE tx_icsodappstore_applications SET countcall = countcall + 1, tstamp = UNIX_TIMESTAMP() WHERE uid = ' . $this->rid);
	}

	/**
	 * Retrieves the application's record.
	 *
	 * @return	array		The application's record.
	 */
	private function getRow() {
		global $TYPO3_DB;
		$rows = $TYPO3_DB->exec_SELECTgetRows(
			'*',
			'tx_icsodappstore_applications',
			'apikey = ' . $TYPO3_DB->fullquotestr($this->key, $table) . ' ' .
			'AND hidden = 0 AND deleted = 0'
		);
		return $rows[0];
	}

}
