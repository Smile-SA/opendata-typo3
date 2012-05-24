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
 * $Id: class.tx_icsodcoreapi_command.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */

require_once(t3lib_extMgm::extPath('ics_od_core_api') . 'api/error_codes.php');
require_once(t3lib_extMgm::extPath('ics_od_core_api') . 'api/error_functions.php');

/**
 * Abstract command class.
 * Defines the contract for a command.
 *
 * @author    Tsi Yang <tsi@in-cite.net>, Pierrick Caillon <pierrick@in-cite.net>
 * @package    TYPO3
 */
abstract class tx_icsodcoreapi_command {

	/**
	 * Executes the command.
	 *
	 * @param	array		$params: The command parameters.
	 * @param	XMLWriter		$xmlwriter: The XML Writer for output.
	 * @return	void
	 */
	function execute(array $params, XMLWriter $xmlwriter){
		makeError($xmlwriter, ERROR_COMMAND_CODE, ERROR_COMMAND_TEXT);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_core_api/api/class.tx_icsodcoreapi_command.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_core_api/api/class.tx_icsodcoreapi_command.php']);
}
