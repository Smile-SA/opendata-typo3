<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2011 In Cité Solution <technique@in-cite.net>
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
 * $Id: class.tx_icsodcoreapi_service.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */

require_once(PATH_tslib . 'class.tslib_fe.php');
require_once(t3lib_extMgm::extPath('ics_od_core_api') . 'api/class.tx_icsodcoreapi_factory.php');
require_once(t3lib_extMgm::extPath('ics_od_core_api') . 'api/class.tx_icsodcoreapi_logger.php');
require_once(t3lib_extMgm::extPath('ics_od_core_api') . 'api/error_codes.php');
require_once(t3lib_extMgm::extPath('ics_od_core_api') . 'api/error_functions.php');
require_once(t3lib_extMgm::extPath('ics_od_core_api') . 'lib/xml2json/xml2json.php');

/**
 * Handles an API query call.
 * This service dispatches the call to the requested command.
 * It checks the call parameters, retrieves the requested command instance and
 * executes it.
 *
 * @author    Tsi Yang <tsi@in-cite.net>, Pierrick Caillon <pierrick@in-cite.net>
 * @package    TYPO3
 */
class tx_icsodcoreapi_service {
	/**
	 * The request parameters.
	 */
	var $params = array(
		'key' => '',
		'version' => '',
		'cmd' => '',
		'output' => '',
		'param' => array(),
	); /**< Params array :
				key : key of developpers application
				version : version of command
				cmd : command to execute
				output : output format
				param : array where key/value pairs are "param of command" => "param value" */

	/**
	 * Initializes the service.
	 * Initializes the eID subsystem.
	 * Uses the HTTP GET/POST parameters to initializes the service parameters.
	 *
	 * @return	void
	 */
	function init() {
		$this->feUserObj = tslib_eidtools::initFeUser(); // Initialize FE user object
		tslib_eidtools::connectDB(); //Connect to database
		tslib_fe::includeTCA();
		foreach ($this->params as $gp => $var) {
			if (!is_null(t3lib_div::_GP($gp))) {
				$this->params[$gp] = t3lib_div::_GP($gp);
			}
		}
	}

	/**
	 * Checks the call of API and writes the output with an XMLWriter
	 *
	 * @return	string,		Content
	 */
	function main() {
		$xmlwriter = new XMLWriter();
		//-- Starts a XMLWriter
		$xmlwriter->openMemory();
		$xmlwriter->startDocument('1.0', 'utf-8', 'yes');
		$xmlwriter->setIndent(true);
		$xmlwriter->setIndentString("\t");
		//-- Starts the xml element "opendata"
		$xmlwriter->startElement('opendata');

		$this->writeRequest($xmlwriter);
		$this->writeAnswer($xmlwriter);

		//-- Ends the xml element "opendata"
		$xmlwriter->endElement();
		//-- Ends the document and returns the buffer.
		$xmlwriter-> endDocument();
		$content = $xmlwriter->outputMemory();

		return $content;
	}

	/**
	 * Writes the request element to the XML output.
	 *
	 * @param	XMLWriter		$xmlwriter: The output writer.
	 * @return	void
	 */
	private function writeRequest(XMLWriter $xmlwriter) {
		$xmlwriter->startElement('request');
		$xmlwriter->text(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'));
		$xmlwriter->endElement();
	}

	/**
	 * Writes the answer element to the XML output.
	 *
	 * @param	XMLWriter		$xmlwriter: The output writer.
	 * @return	void
	 */
	private function writeAnswer(XMLWriter $xmlwriter) {
		$xmlwriter->startElement('answer');
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ics_od_core_api']);
		if ($extConf && intval($extConf['shutdown']))
			makeError($xmlwriter, ALERT_DISABLED_CODE, ALERT_DISABLED_TEXT);
		elseif ($extConf && intval($extConf['maintenance']))
			makeError($xmlwriter, ALERT_MAINTENANCE_CODE, ALERT_MAINTENANCE_TEXT);
		elseif ($this->checkCall($xmlwriter)) {
			if (!$this->checkKey()) {
				makeError($xmlwriter, ERROR_KEY_CODE, ERROR_KEY_TEXT);
			}
			else { //
				if ($this->isCallLimitReached()) {
					makeError($xmlwriter, ERROR_MAX_CODE, ERROR_MAX_TEXT);
				}
				else { // Init the version
					$oFactory = t3lib_div::makeInstance('tx_icsodcoreapi_factory');
					$initResult = $oFactory->init($this->params['version']);

					if (!$initResult) {
						makeError($xmlwriter, ERROR_VERSION_CODE, ERROR_VERSION_TEXT);
					}
					else { // Create a new command
						$oClasscommand = $oFactory->getCommand($this->params['cmd']);

						if (!is_object($oClasscommand)) {
							makeError($xmlwriter, ERROR_COMMAND_CODE, ERROR_COMMAND_TEXT);
						}
						else { // Execute the command and update log
							$oClasscommand->execute($this->params['param'], $xmlwriter);
							$this->logCall();
						}
					}
				}
			}
		}
		$xmlwriter->endElement();
	}
	/**
	 * Checks if the required parameters are specified.
	 * Outputs the error status itself.
	 *
	 * @param	XMLWriter		$xmlwriter: The XMLWriter for output.
	 * @return	boolean		<code>True</code> if the parameters are specified otherwise <code>false</code>.
	 */
	private function checkCall(XMLWriter $xmlwriter){
		if (empty($this->params['version'])) {
			makeError($xmlwriter, ERROR_VERSION_EMPTY_CODE, ERROR_VERSION_EMPTY_TEXT);
			return false;
		}
		if (empty($this->params['key'])) {
			makeError($xmlwriter, ERROR_KEY_EMPTY_CODE, ERROR_KEY_EMPTY_TEXT);
			return false;
		}
		if (empty($this->params['cmd'])) {
			makeError($xmlwriter, ERROR_COMMAND_EMPTY_CODE, ERROR_COMMAND_EMPTY_TEXT);
			return false;
		}
		return true;
	}

	/**
	 * Checks if the API Key is valid.
	 *
	 * @return	Boolean		<code>True</code> if the API Key is valid otherwise <code>false</code>.
	 */
	private function checkKey() {
		global $TYPO3_DB;
		$rows = $TYPO3_DB->exec_SELECTgetRows(
			'uid',
			'tx_icsodappstore_applications',
			'apikey = ' . $TYPO3_DB->fullQuoteStr($this->params['key'], $table) . ' ' .
			'AND hidden = 0 AND deleted = 0'
		);
		return (!empty($rows));
	}

	/**
	 * Displays the output.
	 *
	 * @param	string		$output: The XML Output to print.
	 * @return	string		content
	 */
	function printOutput($output) {
		global $TYPO3_CONF_VARS;

		switch( strtoupper($this->params['output']) ){
			case 'JSON':
				$type = 'application/json';
				$output = xml2json::transformXmlStringToJson($output);
				break;
			default:
				$type = 'application/xml';
		}
        header('Content-Type: ' . $type . '; charset=' . (($TYPO3_CONF_VARS['BE']['forceCharset']) ? ($TYPO3_CONF_VARS['BE']['forceCharset']) : ('iso-8859-1')));
		header('Content-Length: ' . strlen($output));
		header('Access-Control-Allow-Origin: *');
		echo $output;
	}

	/**
	 * Logs the call.
	 *
	 * @return	void
	 */
	private function logCall() {
		$logger = t3lib_div::makeInstance('tx_icsodcoreapi_logger');
		$logger->init($this->params);
		$logger->logCall();
	}

	/**
	 * Checks if the period call limit is reached.
	 *
	 * @return	Boolean		<code>True</code> if the limit is reached otherwise <code>false</code>.
	 */
	private function isCallLimitReached() {
		global $TYPO3_DB;
		$rows = $TYPO3_DB->exec_SELECTgetRows(
			'countcall, maxcall',
			'tx_icsodappstore_applications',
			'apikey = ' . $TYPO3_DB->fullQuoteStr($this->params['key'], $table) . ' ' .
			'AND hidden = 0 AND deleted = 0'
		);
		return (intval($rows[0]['countcall']) >= intval($rows[0]['maxcall']));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_core_api/api/class.tx_icsodcoreapi_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_core_api/api/class.tx_icsodcoreapi_service.php']);
}
