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
 * $Id: class.tx_icsodcoreapi_factory.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */

/**
 * Command factory.
 *
 * @author    Tsi Yang <tsi@in-cite.net>, Pierrick Caillon <pierrick@in-cite.net>
 * @package    TYPO3
 */
class tx_icsodcoreapi_factory {

	private $version; /**< Version of API */
	private $command; /**< The action */
	private $patternCommand; /**< pattern command */

	/**
	 * Initializes the factory.
	 *
	 * @param	string		$version: The version of API.
	 * @return	Boolean		<code>True</code> is the version is valid otherwise <code>False</code>.
	 */
	function init($version) {
		global $TYPO3_CONF_VARS;

		$this->version = $version;
		if ((!isset($TYPO3_CONF_VARS['EXTCONF']['ics_od_core_api']['command'][$version]) &&
			!isset($TYPO3_CONF_VARS['EXTCONF']['ics_od_core_api']['patterncommand'][$version])) ||
			(empty($TYPO3_CONF_VARS['EXTCONF']['ics_od_core_api']['command'][$version]) &&
			empty($TYPO3_CONF_VARS['EXTCONF']['ics_od_core_api']['patterncommand'][$version])))
			return false;
		$this->command = $TYPO3_CONF_VARS['EXTCONF']['ics_od_core_api']['command'][$version];
		$this->patternCommand = $TYPO3_CONF_VARS['EXTCONF']['ics_od_core_api']['patterncommand'][$version];
		return true;
	}

	/**
	 * Retrieves a new command for the specified action.
	 *
	 * @param	string		$command: The action.
	 * @return	tx_icsodcoreapi_command		The instance of the requested action's command. <code>Null</code> if the action is not found.
	 */
	function getCommand($command) {
		if (is_array($this->patternCommand)) {
			foreach ($this->patternCommand as $commandRef) {
				list($pattern, $classRef) = $commandRef;
				if (preg_match($pattern, $command) == 1) {
					$className = $this->getUserObjClass($classRef);
					$commandObj = t3lib_div::makeInstance($className, $command);
					if ($commandObj && is_a($commandObj, 'tx_icsodcoreapi_pattern_command') && $commandObj->isValid())
						return $commandObj;
				}
			}
		}
		$commandObj = t3lib_div::getUserObj($this->command[$command]);
		if ($commandObj && is_a($commandObj, 'tx_icsodcoreapi_command'))
			return $commandObj;
		return null;
	}

	/**
	 * Get class name
	 *
	 * @param	string		$classRef: path class
	 * @return	string		class name
	 */
	private function getUserObjClass($classRef) {
		if (strpos($classRef, ':') !== false) {
			list($file, $class) = t3lib_div::revExplode(':', $classRef, 2);
			$requireFile = t3lib_div::getFileAbsFileName($file);
			if ($requireFile) t3lib_div::requireOnce($requireFile);
		}
		else {
			$class = $classRef;
		}
		return $class;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_core_api/api/class.tx_icsodcoreapi_factory.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_core_api/api/class.tx_icsodcoreapi_factory.php']);
}
