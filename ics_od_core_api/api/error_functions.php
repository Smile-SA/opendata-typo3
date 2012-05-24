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
 * $Id: error_functions.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */

/**
 * @file error_functions.php
 * Defines the helper functions for error output.
 *
 * @author    Tsi Yang <tsi@in-cite.net>
 * @package    TYPO3
 */

/**
 * Output the specified error to an XML Writer.
 *
 * @param	XMLWriter		$xmlwriter: The XML Writer used for status output.
 * @param	Integer		$code: The error code.
 * @param	String		$text: The error message.
 * @return	void
 */
function makeError(XMLWriter $xmlwriter, $code, $text) {
	$xmlwriter->startElement('status');
	$xmlwriter->writeAttribute('code', $code);
	$xmlwriter->writeAttribute('message', $text);
	$xmlwriter->endElement();
}
