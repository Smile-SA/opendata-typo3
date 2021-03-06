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
 * $Id: class.tx_icsoddatastore_datastore_getfileformats_command.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */

/**
 * @file class.tx_icsoddatastore_datastore_getfileformats_command.php
 *
 * Short description of the class datastore_getagencies
 *
 * @author    In Cité Solution <technique@in-cite.net>
 * @package    TYPO3.ics_od_datastore
 */

require_once(t3lib_extMgm::extPath('ics_od_core_api') . 'api/class.tx_icsodcoreapi_command.php');

class tx_icsoddatastore_datastore_getfileformats_command extends tx_icsodcoreapi_command
{


	// *************************
	// * User inclusions 0
	// * DO NOT DELETE OR CHANGE THOSE COMMENTS
	// *************************

	// ... (Add additional operations here) ...

	// * End user inclusions 0

	/**
	 * Executes the command.
	 *
	 * @param	array		$params The command parameters.
	 * @param	XMLWriter		$xmlwriter The XML Writer for output.
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

		// * End user inclusions 1


		// Create a datasource object for retrieving fileformats
		$datasource = t3lib_div::getUserObj($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ics_od_datastore']['datasource']['fileformat']);

		$fileformats = array();

		$fileformats = $datasource->getFileformatsAll($params);

		// *************************
		// * User inclusions 2
		// * DO NOT DELETE OR CHANGE THOSE COMMENTS
		// *************************

		// ... (Add additional operations here) ...

		// * End user inclusions 2


		$elements = $this->transformResultsForOutput($fileformats);
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
	 * @param	array		$fileformats A collection of fileformats
	 * @return	Elements		array
	 */
	protected function transformResultsForOutput(array $fileformats)
	{
		$elements = array();
		foreach ($fileformats as $fileformat)
		{
			$element = array();

			$element['id'] = $fileformat['id'];
			$element['name'] = $fileformat['name'];
			$element['description'] = $fileformat['description'];
			$element['mimetype'] = $fileformat['mimetype'];
			$element['extension'] = $fileformat['extension'];
			$element['picto'] = $fileformat['picto'];
			$element['sorting'] = $fileformat['sorting'];
			$element['searchable'] = $fileformat['searchable'];

			// *************************
			// * User inclusions 4
			// * DO NOT DELETE OR CHANGE THOSE COMMENTS
			// *************************

			// ... (Add additional operations here) ...
			array_pop($element);
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
			$xmlwriter->startElement('fileformat');
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
} // End of class tx_icsoddatastore_datastore_getfileformats_command
