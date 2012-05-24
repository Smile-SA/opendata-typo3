<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 In Cité Solution <technique@in-cite.net>
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
 * $Id: class.tx_icsoddatastore_title.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */

/**
 * Class 'tx_icsoddatastore_title' to display title column label
 *
 * @author Tsi Yang <tsi@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsoddatastore
 */
class tx_icsoddatastore_title	{

	/**
	 * @param	array		$params
	 * @param	object		$pObj
	 * @return	[type]		...
	 * @author Tsi Yang <tsi@in-cite.net>
	 * @desc Display title column label
	 */
	function getRecordTitle($params, $pObj){
		if ($params['table'] == 'tx_icsoddatastore_files')	{
			if ($params['row']['record_type'] == '0')	{
				$params['title'] = $params['row']['file'];
			}	else	{
				$params['title'] = $params['row']['url'];
			}
		}
	}
}
?>