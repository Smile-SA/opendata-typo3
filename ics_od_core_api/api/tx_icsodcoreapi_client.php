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
 * $Id: tx_icsodcoreapi_client.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */

/**
 * @file tx_icsodcoreapi_client.php
 *
 * The API's client
 * Uses a service to manage a call of API.
 * Gets an output, then print it.
 *
 * @author    Tsi Yang <tsi@in-cite.net>
 * @package    TYPO3
 */

require_once(t3lib_extMgm::extPath('ics_od_core_api') . 'api/class.tx_icsodcoreapi_service.php');

$fob = t3lib_div::makeInstance('tx_icsodcoreapi_service');
$fob->init();
$output = $fob->main();
$fob->printOutput($output);
