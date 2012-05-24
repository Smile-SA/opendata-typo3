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
 * $Id: class.tx_icsoddatastore_datastore_searchdatasets_command.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
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

/**
 * Short description of the class getagencies
 *
 * @file class.tx_icsoddatastore_datastore_searchdatasets_command.php
 * @author    In Cité Solution <technique@in-cite.net>
 * @package    TYPO3.ics_od_datastore
 */
class tx_icsoddatastore_datastore_searchdatasets_command extends tx_icsodcoreapi_command
{

	const EMPTY_AGENCIES_CODE = 100;
	const EMPTY_AGENCIES_TEXT = "agencies should be not empty.";
	const INVALID_AGENCIES_CODE = 101;
	const INVALID_AGENCIES_TEXT = "The specified value is not valid for agencies.";
	const EMPTY_FILEFORMATS_CODE = 102;
	const EMPTY_FILEFORMATS_TEXT = "fileformats should be not empty.";
	const INVALID_FILEFORMATS_CODE = 103;
	const INVALID_FILEFORMATS_TEXT = "The specified value is not valid for fileformats.";
	const EMPTY_LICENCES_CODE = 104;
	const EMPTY_LICENCES_TEXT = "licences should be not empty.";
	const INVALID_LICENCES_CODE = 105;
	const INVALID_LICENCES_TEXT = "The specified value is not valid for licences.";
	const EMPTY_RELEASED_CODE = 106;
	const EMPTY_RELEASED_TEXT = "released should be not empty.";
	const INVALID_RELEASED_CODE = 107;
	const INVALID_RELEASED_TEXT = "The specified value is not valid for released.";
	const EMPTY_UPDATED_CODE = 108;
	const EMPTY_UPDATED_TEXT = "updated should be not empty.";
	const INVALID_UPDATED_CODE = 109;
	const INVALID_UPDATED_TEXT = "The specified value is not valid for updated.";

	var $params = array(
	);

	// *************************
	// * User inclusions 0
	// * DO NOT DELETE OR CHANGE THOSE COMMENTS
	// *************************

	// ... (Add additional operations here) ...

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


		// *************************
		// * User inclusions 1
		// * DO NOT DELETE OR CHANGE THOSE COMMENTS
		// *************************

		// ... (Add additional operations here) ...
		if (!empty($params['agencies']))
		{
			$agencies = t3lib_div::trimExplode(',', $params['agencies']);
			foreach( $agencies as $agency)
			{
				if (!is_numeric($agency))
				{
					makeError($xmlwriter, tx_icsoddatastore_datastore_searchdatasets_command::INVALID_AGENCIES_CODE, tx_icsoddatastore_datastore_searchdatasets_command::INVALID_AGENCIES_TEXT);
					return;
				}
			}
		}
		if (!empty($params['fileformats']))
		{
		}
		if (!empty($params['licences']))
		{
			$licences = t3lib_div::trimExplode(',', $params['licences']);
			foreach( $licences as $licence)
			{
				if (!is_numeric($licence))
				{
					makeError($xmlwriter, tx_icsoddatastore_datastore_searchdatasets_command::INVALID_LICENCES_CODE, tx_icsoddatastore_datastore_searchdatasets_command::INVALID_LICENCES_TEXT);
					return;
				}
			}
		}
		if (!empty($params['released']))
		{
			if (!strtotime($params['released']))
			{
				makeError($xmlwriter, tx_icsoddatastore_datastore_searchdatasets_command::INVALID_RELEASED_CODE, tx_icsoddatastore_datastore_searchdatasets_command::INVALID_RELEASED_TEXT);
				return;
			}
		}
		if (!empty($params['updated']))
		{
			if (!strtotime($params['updated']))
			{
				makeError($xmlwriter, tx_icsoddatastore_datastore_searchdatasets_command::INVALID_UPDATED_CODE, tx_icsoddatastore_datastore_searchdatasets_command::INVALID_UPDATED_TEXT);
				return;
			}
		}
		if (!empty($params['limit']))
		{
			if (empty($params['page']))
				$params['page'] = 1;
		}
		// * End user inclusions 1


		// Create a datasource object for retrieving datasets
		$datasource = t3lib_div::getUserObj($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ics_od_datastore']['datasource']['dataset']);

		$datasets = $datasource->getDadasetsFilter($params, false);

		// *************************
		// * User inclusions 2
		// * DO NOT DELETE OR CHANGE THOSE COMMENTS
		// *************************

		// ... (Add additional operations here) ...

		// * End user inclusions 2


		$elements = $this->transformResultsForOutput($datasets['datasets']);
		makeError($xmlwriter, SUCCESS_CODE, SUCCESS_TEXT);

		// *************************
		// * User inclusions 3
		// * DO NOT DELETE OR CHANGE THOSE COMMENTS
		// *************************

		// ... (Add additional operations here) ...
		$pages = $datasets['count'] / $params['limit'];
		$pages = is_float($pages)? intval($pages +1) : $pages;
		$data = array(
			'count' => $datasets['count'],
			'pages' => $pages,
			'limit' => $params['limit'],
			'elements' => $elements,
		);
		// * End user inclusions 3

		$this->writeOutput($xmlwriter, $data, $params);
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
	protected function writeOutput(XMLWriter $xmlwriter, array $data, array $params)
	{
		$xmlwriter->startElement('data');
		if ($params['page'] && $params['limit'])
		{
			$xmlwriter->writeAttribute('items', $data['count']);
			$xmlwriter->writeAttribute('pages', $data['pages']);
			$xmlwriter->writeAttribute('limit', $data['limit']);
		}
		$elements = $data['elements'];
		foreach ($elements as $element)
		{
			$xmlwriter->startElement('dataset');
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

} // End of class tx_icsoddatastore_datastore_searchdatasets_command
