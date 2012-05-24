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
 * $Id: error_codes.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */

/**
 * @file error_codes.php
 * Defines error codes and messages.
 * When an error is encountered in the main code, theses error codes are used.
 *
 * @author    Tsi Yang <tsi@in-cite.net>
 * @package    TYPO3
 */

define('SUCCESS_CODE', 0); /**< Error codes: Success. */
define('SUCCESS_TEXT', 'OK'); /**< Error messages: Success. */
define('ERROR_KEY_CODE', 1); /**< Error codes: Invalid key. */
define('ERROR_KEY_TEXT', 'The provided API key is invalid. Please check it.'); /**< Error messages: Invalid key. */
define('ERROR_VERSION_CODE', 2); /**< Error codes: Invalid version. */
define('ERROR_VERSION_TEXT', 'The requested version does not exist. Please provide a valid one.'); /**< Error message: Invalid version. */
define('ERROR_COMMAND_CODE', 3); /**< Error codes: Invalid command. */
define('ERROR_COMMAND_TEXT', 'The requested command could not be found. Please check spelling.'); /**< Error messages: Invalid command. */
define('ERROR_KEY_EMPTY_CODE', 4); /**< Error codes: Empty key. */
define('ERROR_KEY_EMPTY_TEXT', 'Please, provide an API key.'); /**< Error messages: Empty key. */
define('ERROR_VERSION_EMPTY_CODE', 5); /**< Error codes: Empty version. */
define('ERROR_VERSION_EMPTY_TEXT', 'Please, provide an API version.'); /**< Error messages: Empty version. */
define('ERROR_COMMAND_EMPTY_CODE', 6); /**< Error codes: Empty command  */
define('ERROR_COMMAND_EMPTY_TEXT', 'Please, provide the name of the command to execute.'); /**< Error messages: Empty command */
define('ERROR_MAX_CODE', 8); /*<< Error codes: Usage limit reached. */
define('ERROR_MAX_TEXT', 'The application exceeded the maximum number of allowed connections. Try again later.'); /*<< Error messages: Usage limit reached. */
define('ALERT_DISABLED_CODE', 98); /*<< Error codes: Service unavailable because of deactivation. */
define('ALERT_DISABLED_TEXT', 'Service unavailable. The service have been manually deactivated.'); /*<< Error messages: Service unavailable because of deactivation. */
define('ALERT_MAINTENANCE_CODE', 99); /*<< Error codes: Service unavailable because of a maintance in progress. */
define('ALERT_MAINTENANCE_TEXT', 'Service unavailable. A maintenance is in progress.'); /*<< Error messages: Service unavailable because of a maintance in progress. */

define('ERROR_COMMAND_FIRST_CODE', 100); /*<< Error codes: First error code usable by commands. */
