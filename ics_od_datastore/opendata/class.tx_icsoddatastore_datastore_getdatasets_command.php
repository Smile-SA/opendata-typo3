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
 * $Id: class.tx_icsoddatastore_datastore_getdatasets_command.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */

/**
 * @file class.tx_icsoddatastore_datastore_getdatasets_command.php
 *
 * Short description of the class getagencies
 *
 * @author    In Cité Solution <technique@in-cite.net>
 * @package    TYPO3.ics_od_datastore
 */

require_once(t3lib_extMgm::extPath('ics_od_core_api') . 'api/class.tx_icsodcoreapi_command.php');

class tx_icsoddatastore_datastore_getdatasets_command extends tx_icsodcoreapi_command
{

	const EMPTY_IDS_CODE = 100;
	const EMPTY_IDS_TEXT = "ids should be not empty.";
	const INVALID_IDS_CODE = 101;
	const INVALID_IDS_TEXT = "ids should be a list of numeric id.";
	const EMPTY_TYPE_CODE = 102;
	const EMPTY_TYPE_TEXT = "type should be not empty.";
	const INVALID_TYPE_CODE = 103;
	const INVALID_TYPE_TEXT = "The specified value is not valid for type.";
	const EMPTY_FILETYPE_CODE = 104;
	const EMPTY_FILETYPE_TEXT = "filetype should be not empty.";
	const INVALID_FILETYPE_CODE = 105;
	const INVALID_FILETYPE_TEXT = "The specified value is not valid for filetype.";

	var $params = array(
		'type' => 'full',
		'filetype' => 'any',
	);

	// *************************
	// * User inclusions 0
	// * DO NOT DELETE OR CHANGE THOSE COMMENTS
	// *************************

	// ... (Add additional operations here) ...
	static $types = array(
		'full',
		'url',
		//'file'
	);
	static $filetypes = array(
		'any',
		'data',
		'doc',
		'metadata'
	);
	// * End user inclusions 0

	/**
	 * Executes the command.
	 *
	 * @param	$params		array The command parameters.
	 * @param	$xmlwriter		XMLWriter The XML Writer for output.
	 * @return	void
	 */
	function execute(array $params, XMLWriter $xmlwriter)
	{
		$params = array_merge($this->params, $params);

		if (empty($params['ids']))
		{
			makeError($xmlwriter, tx_icsoddatastore_datastore_getdatasets_command::EMPTY_IDS_CODE, tx_icsoddatastore_datastore_getdatasets_command::EMPTY_IDS_TEXT);
			return;
		}

		// *************************
		// * User inclusions 1
		// * DO NOT DELETE OR CHANGE THOSE COMMENTS
		// *************************

		// ... (Add additional operations here) ...
		$ids = t3lib_div::trimExplode(',', $params['ids'], true);
		foreach ($ids as $id)
		{
			if (!is_numeric($id))
			{
				makeError($xmlwriter, tx_icsoddatastore_datastore_getdatasets_command::INVALID_IDS_CODE, tx_icsoddatastore_datastore_getdatasets_command::INVALID_IDS_TEXT);
				return;
			}
		}
		if (empty($params['type']))
		{
			makeError($xmlwriter, tx_icsoddatastore_datastore_getdatasets_command::EMPTY_TYPE_CODE, tx_icsoddatastore_datastore_getdatasets_command::EMPTY_TYPE_TEXT);
			return;
		}
		if (!in_array($params['type'], tx_icsoddatastore_datastore_getdatasets_command::$types))
		{
			makeError($xmlwriter, tx_icsoddatastore_datastore_getdatasets_command::INVALID_TYPE_CODE, tx_icsoddatastore_datastore_getdatasets_command::INVALID_TYPE_TEXT);
			return;
		}
		if (empty($params['filetype']))
		{
			makeError($xmlwriter, tx_icsoddatastore_datastore_getdatasets_command::EMPTY_FILETYPE__CODE, tx_icsoddatastore_datastore_getdatasets_command::EMPTY_FILETYPE__TEXT);
			return;
		}
		if (!in_array($params['filetype'], tx_icsoddatastore_datastore_getdatasets_command::$filetypes))
		{
			makeError($xmlwriter, tx_icsoddatastore_datastore_getdatasets_command::INVALID_FILETYPE_CODE, tx_icsoddatastore_datastore_getdatasets_command::INVALID_FILETYPE_TEXT);
			return;
		}
		// * End user inclusions 1


		// Create a datasource object for retrieving datasets
		$datasource = t3lib_div::getUserObj($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ics_od_datastore']['datasource']['dataset']);

		$datasets = $datasource->getDadasetsFilter($params);

		// *************************
		// * User inclusions 2
		// * DO NOT DELETE OR CHANGE THOSE COMMENTS
		// *************************

		// ... (Add additional operations here) ...
		$datasets = $datasets['datasets'];
		// * End user inclusions 2


		$elements = $this->transformResultsForOutput($datasets);
		makeError($xmlwriter, SUCCESS_CODE, SUCCESS_TEXT);

		// *************************
		// * User inclusions 3
		// * DO NOT DELETE OR CHANGE THOSE COMMENTS
		// *************************

		// ... (Add additional operations here) ...

		// * End user inclusions 3


		$this->writeOutput($xmlwriter, $elements);
	}

	/**
	 * Transforms results for output
	 *
	 * @param	array		$agencys A collection of agencys
	 * @return	Elements		array
	 */
	protected function transformResultsForOutput(array $datasets)
	{
		$elements = array();
		foreach ($datasets as $dataset)
		{
			$element = array();

			$element['id'] = $dataset['id'];
			$element['title'] = $dataset['title'];
			$element['released'] = date('c', $dataset['released']);
			$element['updated'] = date('c', $dataset['updated']);

			if (empty($dataset['files']))
			{
				$element['files'] = null;
			}
			else
			{
				$element['files'] = array();
				foreach ($dataset['files'] as $file)
				{
					$el_file = array();
					$el_file['type'] = $file['type'];
					$el_file['url'] = $file['url'];
					$el_file['format'] = $file['format'];
					$el_file['md5'] = $file['md5'];
					if ($file['size'])
						$el_file['size'] = $file['size'];
					else
						$el_file['size'] = null;
					$element['files'][] = $el_file;
				}
			}

			// $element['description'] = $dataset['description'];
			// $element['agency'] = (string)$dataset['agency'];
			// $element['contact'] = $dataset['contact'];
			// $element['licence'] = $dataset['licence'];
			// $element['time_period'] = $dataset['time_period'];
			// $element['frequency'] = $dataset['frequency'];
			// $element['publisher'] = $dataset['publisher'];
			// $element['author'] = $dataset['author'];
			// $element['manager'] = $dataset['manager'];
			// $element['owner'] = $dataset['owner'];
			// $element['technical_data'] = $dataset['technical_data'];

			// *************************
			// * User inclusions 4
			// * DO NOT DELETE OR CHANGE THOSE COMMENTS
			// *************************

			// ... (Add additional operations here) ...

			// * End user inclusions 4


			$elements[] = $element;
		}

		// *************************
		// * User inclusions 5
		// * DO NOT DELETE OR CHANGE THOSE COMMENTS
		// *************************

		// ... (Add additional operations here) ...

		// * End user inclusions 5


		return $elements;
	}

	/**
	 * Writes output
	 *
	 * @param	XMLWriter		$xmlwriter the writer
	 * @param	array		$elements
	 * @return	void
	 */
	protected function writeOutput(XMLWriter $xmlwriter, array $elements)
	{
		$xmlwriter->startElement('data');
		foreach ($elements as $element)
		{
			$xmlwriter->startElement('dataset');
			foreach ($element as $key => $value)
			{
				if ($key == 'files' && !is_null($value))
				{
					$this->writeOutputElement_Files($xmlwriter, $value);
				}
				else
				{
					$xmlwriter->startElement($key);
					$xmlwriter->text($value);
					$xmlwriter->endElement();
				}
			}
			$xmlwriter->endElement();
		}
		$xmlwriter->endElement();
	}

	/**
	 * Writes output files
	 *
	 * @param	XMLWriter		$xmlwriter the writer
	 * @param	array		$elements
	 * @return	void
	 */
	protected function writeOutputElement_Files(XMLWriter $xmlwriter, array $elements)
	{
		$xmlwriter->startElement('files');
		foreach ($elements as $element)
		{
			$xmlwriter->startElement('file');
			foreach ($element as $key => $value)
			{
				$xmlwriter->startElement($key);
				$xmlwriter->text($value);
				$xmlwriter->endElement();
			}
			$xmlwriter->endElement();
		}
		$xmlwriter->endElement();

	}
} // End of class tx_icsoddatastore_datastore_getdatasets_command
