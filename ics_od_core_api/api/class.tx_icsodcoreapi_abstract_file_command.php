<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010-2011 In Cité Solution <technique@in-cite.net>
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
 * $Id: class.tx_icsodcoreapi_abstract_file_command.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */

require_once(t3lib_extMgm::extPath('ics_od_core_api') . 'api/class.tx_icsopenddataapi_command.php');

/**
 * Abstract file command class.
 * Defines the generic way of providing files via API.
 *
 * @remarks Parameters :<dl>
 * <dt>mode</dt><dd>The command query mode. Accepted values: update, content. Mandatory.</dd>
 * <dt>filename</dt><dd>The name of the file to retrieve. Mandatory for mode update and content.</dd>
 * <dt>return</dt><dd>The type of content return. Defined only in when mode=content. Accepted values: url, inline. Inline returns the content in base64. Default value is url.</dt>
 * </dl>
 *
 * @author    Pierrick Caillon <pierrick@in-cite.net>
 * @package    TYPO3
 */
abstract class tx_icsopenddataapi_abstract_file_command extends tx_icsopenddataapi_command
{
	const EMPTY_MODE_CODE = 100; /**< ERROR_COMMAND_FIRST_CODE; */
	const EMPTY_MODE_TEXT = "The mode should be not empty.";
	const INVALID_MODE_CODE = 101; /**< ERROR_COMMAND_FIRST_CODE + 1; */
	const INVALID_MODE_TEXT = "The specified mode is not recognized.";
	const EMPTY_FILENAME_CODE = 102; /**< ERROR_COMMAND_FIRST_CODE + 2; */
	const EMPTY_FILENAME_TEXT = "Please, provide a filename.";
	const FILENOTFOUND_CODE = 103; /**< ERROR_COMMAND_FIRST_CODE + 3; */
	const FILENOTFOUND_TEXT = 'The specified file was not found.';
	const EMPTY_RETURN_CODE = 104; /**< ERROR_COMMAND_FIRST_CODE + 4; */
	const EMPTY_RETURN_TEXT = "The specified return type should be not empty or unspecified.";
	const INVALID_RETURN_CODE = 105; /**< ERROR_COMMAND_FIRST_CODE + 5; */
	const INVALID_RETURN_TEXT = "The specified return type is not recognized.";
	const NOHANDLER_CODE = 106; /**< ERROR_COMMAND_FIRST_CODE + 6; */
	const NOHANDLER_TEXT = "The mode is valid but no handling has been done.";

	var $params = array(
		'mode' => '',
		'filename' => '',
		'return' => 'url',
	); /**< The default parameters values. */

	var $modes = array(
		'update',
		'content',
	); /**< The valid modes values. */

	var $filenameModes = array(
		'update',
		'content',
	); /**< The modes that requires the filename parameter. */

	static $returns = array(
		'url',
		'inline',
	); /**< The valid return types. */

	/**
	 * Executes the command.
	 *
	 * @param	array		$params: The command parameters.
	 * @param	XMLWriter		$xmlwriter: The XML Writer for output.
	 * @return	void
	 */
	function execute(array $params, XMLWriter $xmlwriter)
	{
		$params = array_merge($this->params, $params);
		if (empty($params['mode']))
		{
			makeError($xmlwriter, tx_icsopenddataapi_abstract_file_command::EMPTY_MODE_CODE, tx_icsopenddataapi_abstract_file_command::EMPTY_MODE_TEXT);
			return;
		}
		if (!in_array($params['mode'], $this->modes))
		{
			makeError($xmlwriter, tx_icsopenddataapi_abstract_file_command::INVALID_MODE_CODE, tx_icsopenddataapi_abstract_file_command::INVALID_MODE_TEXT);
			return;
		}
		if (in_array($params['mode'], $this->filenameModes))
		{
			if (empty($params['filename']))
			{
				makeError($xmlwriter, tx_icsopenddataapi_abstract_file_command::EMPTY_FILENAME_CODE, tx_icsopenddataapi_abstract_file_command::EMPTY_FILENAME_TEXT);
				return;
			}
			if (!$this->isFile($params['filename']))
			{
				makeError($xmlwriter, tx_icsopenddataapi_abstract_file_command::FILENOTFOUND_CODE, tx_icsopenddataapi_abstract_file_command::FILENOTFOUND_TEXT);
				return;
			}
		}
		switch ($params['mode'])
		{
			case 'update':
				$update = $this->getLastUpdate($params['filename']);
				makeError($xmlwriter, SUCCESS_CODE, SUCCESS_TEXT);
				$xmlwriter->startElement('data');
				$xmlwriter->writeAttribute('update', date('c', $update));
				$xmlwriter->endElement();
				break;
			case 'content':
				if (empty($params['return']))
				{
					makeError($xmlwriter, tx_icsopenddataapi_abstract_file_command::EMPTY_RETURN_CODE, tx_icsopenddataapi_abstract_file_command::EMPTY_RETURN_TEXT);
					return;
				}
				if (!in_array($params['return'], tx_icsopenddataapi_abstract_file_command::$returns))
				{
					makeError($xmlwriter, tx_icsopenddataapi_abstract_file_command::INVALID_RETURN_CODE, tx_icsopenddataapi_abstract_file_command::INVALID_RETURN_TEXT);
					return;
				}
				$content = $this->getFile($params['filename'], $params['return']);
				makeError($xmlwriter, SUCCESS_CODE, SUCCESS_TEXT);
				$xmlwriter->startElement('data');
				if ($params['return'] == 'url')
					$xmlwriter->writeAttribute('url', $content);
				else
					$xmlwriter->text(base64_encode($content));
				$xmlwriter->endElement();
				break;
			default:
				if (!$this->parseCustom($xmlwriter, $params))
					makeError($xmlwriter, tx_icsopenddataapi_abstract_file_command::NOHANDLER_CODE, tx_icsopenddataapi_abstract_file_command::NOHANDLER_TEXT);
		}
	}

	/**
	 * Checks if the specified filename maps to a know file.
	 *
	 * @param string	$filename: The name of the file.
	 * @return boolean Whether filename is valid or not.
	 */
	protected abstract function isFile($filename);

	/**
	 * Retrieves the last modification time of the file.
	 *
	 * @param string	$filename: The name of the file.
	 * @return int The unix timestamp of the date of the last modification of the file.
	 */
	protected abstract function getLastUpdate($filename);

	/**
	 * Retrieves the content or the url to content of the file.
	 *
	 * @param string $filename: The name of the file.
	 * @param string $return: The type of the return value.
	 * @return string The content or the url to the content of the file.
	 * @see tx_icsopenddataapi_abstract_file_command::$returns
	 */
	protected abstract function getFile($filename, $return);

	/**
	 * Executes the child class custom modes.
	 *
	 * @param	XMLWriter		$xmlwriter: The XML Writer for output.
	 * @param	array		$params: The command parameters.
	 * @return	boolean		Whether or not the request has been parsed.
	 */
	protected function parseCustom(XMLWriter $xmlwriter, array $params)
	{
		return false;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_openddata_api/api/class.tx_icsopenddataapi_command.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_openddata_api/api/class.tx_icsopenddataapi_command.php']);
}
