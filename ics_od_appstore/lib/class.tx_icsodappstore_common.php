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
 * $Id: class.tx_icsodappstore_common.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   58: class tx_icsodappstore_common extends tslib_pibase
 *
 *   80:     function tx_icsodappstore_common()
 *   90:     function init()
 *  102:     function renderContentError($msg)
 *  116:     function getApplications($mode, $selectFields = null, $parameter = null)
 *  275:     function existInApplicationTCA($field)
 *  293:     function getStatistics($selectFields = '', $whereFields = array(), $addWhereText = '', $groupby = '', $order = '', $limit = '', $debugger = false)
 *  345:     function renderLogo($fieldname, $fieldconf, $value)
 *
 * TOTAL FUNCTIONS: 7
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Common for plugins' for the 'ics_od_appstore' extension.
 *
 * @author	Tsi <tsi@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsodappstore
 */
class tx_icsodappstore_common extends tslib_pibase {

	const APPMODE_ALL = 0; /**< Mode all applications */
	const APPMODE_USER = 1; /**< Mode users applications */
	const APPMODE_SINGLE = 2; /**< Mode details application */
	const APPMODE_SINGLEUSER = 3; /**< Mode details user application */
	const APPMODE_SEARCH = 4; /**< Mode search */
	const APPMODE_MAX = 4; /**< Max number mode */

	var $templateFile = "typo3conf/ext/ics_od_appstore/res/template.html"; /**< Path of template file */
	var $tables = array(
		'applications' => 'tx_icsodappstore_applications',
		'statistics' => 'tx_icsodappstore_statistics',
		'users' => 'fe_users',
	); /**< database table */


	/**
	 * __Constructor
	 *
	 * @return	void
	 */
	function tx_icsodappstore_common() {
		tslib_pibase::tslib_pibase();

	}

	/**
	 * Init the plugin
	 *
	 * @return	boolean
	 */
	function init() {
		if (is_array($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_icsodappstore.'])) {
			$this->conf = array_merge($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_icsodappstore.'], $this->conf);
		}		
		if ($this->conf['template'])
			$this->templateFile = $this->conf['template'];
		return true;
	}

	/**
	 * Render the error content
	 *
	 * @param	string		$msg: The error message
	 * @return	string		The error content
	 */
	function renderContentError($msg) {
		$html = $this->cObj->fileResource($this->templateFile);
		$template = $this->cObj->getSubpart($html, '###TEMPLATE_ERROR###');
		return $this->cObj->substituteMarker($template, '###ERROR_MSG###', htmlspecialchars($msg));
	}

	/**
	 * Return applications depending on mode and paramaters
	 *
	 * @param	const		$mode: constants modes
	 * @param	string		$selectFields: list of selected fields
	 * @param	mixed		$parameter: userid for mode APPMODE_SINGLEUSER OR APPMODE_USER, search criteria for mode APPMODE_SEARCH
	 * @return	array
	 */
	function getApplications($mode, $selectFields = null, $parameter = null) {
		// Error
		if ($mode > self::APPMODE_MAX)
			return false;
		if (($mode == self::APPMODE_SINGLE || $mode == self::APPMODE_SINGLEUSER) && !$parameter )
			return false;

		$addWhere = array();
		$order = ' `'.$this->tables['applications'].'`.`tstamp` DESC ';
		$limit = '';
		$groupby = '';

		switch($mode) {
			case self::APPMODE_ALL:
				if ($parameter && is_array($parameter) && !empty($parameter)) {
					// limit
					$rows_by_page = $this->conf['list.']['colNum'] * $this->conf['list.']['rowsByCol'];
					$orderAvailable = explode(',', $this->conf['list.']['orderAvailable']);

					$order = $orderAvailable[$parameter['sort']]. ' DESC';
					$limit = ($parameter['page'] * $rows_by_page) . ',' . $rows_by_page;
				}

				$addWhere[] = '`'.$this->tables['applications'].'`.`release_date` > 0 ';
				$addWhere[] = '`'.$this->tables['applications'].'`.`release_date` < '.time();
				$addWhere[] = '`'.$this->tables['applications'].'`.`lock_publication` = 0';
				$addWhere[] = '`'.$this->tables['applications'].'`.`publish` = 1 ';
				$addWhere[] = '1 '.$this->cObj->enableFields($this->tables['applications']);
			break;

			case self::APPMODE_USER:
				if (!$parameter)
					$parameter = $GLOBALS['TSFE']->fe_user->user['uid'];

				$addWhere[] = ' `'.$this->tables['applications'].'`.`fe_cruser_id` = '.$parameter;
			break;

			case self::APPMODE_SINGLE:
				$addWhere[] = ' `'.$this->tables['applications'].'`.`uid` = '.$parameter;

				// USER CAN VIEW HIS APPLICATION
				if (!$GLOBALS['TSFE']->fe_user->user['uid']) {
					$addWhere[] = '`'.$this->tables['applications'].'`.`release_date` > 0 ';
					$addWhere[] = '`'.$this->tables['applications'].'`.`release_date` < '.time();
					$addWhere[] = '`'.$this->tables['applications'].'`.`lock_publication` = 0';
					$addWhere[] = '`'.$this->tables['applications'].'`.`publish` = 1 ';
					$addWhere[] = '1 '.$this->cObj->enableFields($this->tables['applications']);
				}else{
					$addWhere[] = ' (
						`'.$this->tables['applications'].'`.`fe_cruser_id` = '.$GLOBALS['TSFE']->fe_user->user['uid'].'
						OR (
							`'.$this->tables['applications'].'`.`release_date` > 0 AND
							`'.$this->tables['applications'].'`.`release_date` < '.time().' AND
							`'.$this->tables['applications'].'`.`lock_publication` = 0 AND
							`'.$this->tables['applications'].'`.`publish` = 1 '.$this->cObj->enableFields($this->tables['applications']).'
						)
					)';
				}

				$limit = 1;
			break;

			case self::APPMODE_SINGLEUSER:
				$addWhere[] = ' `'.$this->tables['applications'].'`.`uid` = '.$parameter;
				$addWhere[] = ' `'.$this->tables['applications'].'`.`fe_cruser_id` = '.$GLOBALS['TSFE']->fe_user->user['uid'];

				$limit = 1;
			break;

			case self::APPMODE_SEARCH:
				if ($parameter && is_array($parameter) && !empty($parameter)) {
					foreach($parameter as $whereField => $value) {
						if (!is_numeric($value)) {
							$addWhere[] = '`'.$this->tables['applications'].'`.`'.$whereField.'` = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $this->tables['applications']) . ' ';
						}else{
							$addWhere[] = '`'.$this->tables['applications'].'`.`'.$whereField.'` = ' . $value . ' ';
						}
					}
				}
			break;
		}

		$where = '';
		if (!empty($addWhere)) {
			$where = ' AND '.implode(' AND ', $addWhere);
		}

		if (!$selectFields) {
			$select = '`'.$this->tables['applications'].'`.`uid`,
				`'.$this->tables['applications'].'`.`crdate`,
				`'.$this->tables['applications'].'`.`apikey`,
				`'.$this->tables['applications'].'`.`title`,
				`'.$this->tables['applications'].'`.`description`,
				`'.$this->tables['applications'].'`.`platform`,
				`'.$this->tables['applications'].'`.`countcall`,
				`'.$this->tables['applications'].'`.`maxcall`,
				`'.$this->tables['applications'].'`.`release_date`,
				`'.$this->tables['applications'].'`.`publish`,
				`'.$this->tables['applications'].'`.`logo`,
				`'.$this->tables['applications'].'`.`screenshot`,
				`'.$this->tables['applications'].'`.`link`,
				`'.$this->tables['applications'].'`.`update_date`,
				`'.$this->tables['applications'].'`.`lock_publication`,
				`'.$this->tables['users'].'`.`name`,
				`'.$this->tables['applications'].'`.`fe_cruser_id`';
		}else{
			$selectFields = explode(',', $selectFields);
			$selectTab = array();
			if (is_array($selectFields) && !empty($selectFields)) {
				foreach ($selectFields as $field) {
					switch ($field) {
						case 'count':
							$selectTab[] = 'count(`'.$this->tables['applications'].'`.`uid`) as count';
							break;
						case 'name':
							$selectTab[] = '`'.$this->tables['users'].'`.`'.$field.'`';
							break;
						default:
							$selectTab[] = '`'.$this->tables['applications'].'`.`'.$field.'`';
							break;
					}
				}
			}
			$select = implode(',', $selectTab);
		}


		$apllications = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			$select,
			'`'.$this->tables['applications'].'`
				INNER JOIN `'.$this->tables['users'].'`
				ON `'.$this->tables['users'].'`.`uid` = `'.$this->tables['applications'].'`.`fe_cruser_id`',
			'`'.$this->tables['applications'].'`.`deleted` = 0  '.$where,
			$groupby,
			$order,
			$limit
		);

		// var_dump($GLOBALS['TYPO3_DB']->SELECTquery(
			// $select,
			// '`'.$this->tables['applications'].'`
				// INNER JOIN `'.$this->tables['users'].'`
				// ON `'.$this->tables['users'].'`.`uid` = `'.$this->tables['applications'].'`.`fe_cruser_id`',
			// '`'.$this->tables['applications'].'`.`deleted` = 0  '.$where,
			// $groupby,
			// $order,
			// $limit
		// ));
		if (is_array($apllications) && !empty($apllications))
			return $apllications;
		return false;
	}

	/**
	 * Check if columns exist in TCA applications
	 *
	 * @param	string		$field
	 * @return	boolean
	 */
	function existInApplicationTCA($field) {
		global $TCA;
		t3lib_div::loadTCA($this->tables['applications']);
		return array_key_exists($field, $TCA[$this->tables['applications']]['columns']);
	}

	/**
	 * Return application statistics
	 *
	 * @param	string		$selectFields: selected fields
	 * @param	array		$whereFields: restriction statistics fields
	 * @param	string		$addWhereText: restriction fields
	 * @param	string		$groupby: group by
	 * @param	string		$order: order by
	 * @param	string		$limit: limit
	 * @param	boolean		$debugger: activate debugger
	 * @return	array
	 */
	function getStatistics($selectFields = '', $whereFields = array(), $addWhereText = '', $groupby = '', $order = '', $limit = '', $debugger = false) {
		$addWhere = array();

		foreach($whereFields as $whereField => $value) {
			if (!is_numeric($value)) {
				$addWhere[] = '`'.$this->tables['statistics'].'`.`'.$whereField.'` = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $this->tables['statistics']) . ' ';
			}else{
				$addWhere[] = '`'.$this->tables['statistics'].'`.`'.$whereField.'` = ' . $value . ' ';
			}
		}

		if (!$selectFields) {
			$selectFields = '`'.$this->tables['statistics'].'`.`uid`, `'.$this->tables['statistics'].'`.`application`, `'.$this->tables['statistics'].'`.`cmd`, `'.$this->tables['statistics'].'`.`count`, `'.$this->tables['statistics'].'`.`date`';
		}

		if (!empty($addWhere))
			$addWhereText .= ' AND '.implode(' AND ', $addWhere);

		$statistics = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			$selectFields,
			'`'.$this->tables['statistics'].'`',
			'1 '.$this->cObj->enableFields($this->tables['statistics']).' '.$addWhereText,
			$groupby,
			$order,
			$limit
		);

		if ($debugger) {
			var_dump($GLOBALS['TYPO3_DB']->SELECTquery(
				$selectFields,
				'`'.$this->tables['statistics'].'`',
				'1 '.$this->cObj->enableFields($this->tables['statistics']).' '.$addWhereText,
				'',
				$order,
				$limit
			));
			var_dump($statistics);
		}

		if (is_array($statistics) && !empty($statistics))
			return $statistics;
		return false;
	}

	/**
	 * Render logo content
	 *
	 * @param	string		$fieldname
	 * @param	array		$fieldconf
	 * @param	mixed		$value
	 * @return	string		content
	 */
	function renderLogo($fieldname, $fieldconf, $value) {
		global $TSFE;
		$files = t3lib_div::trimExplode(',', (is_array($value)) ? $value['files'] : $value, true);

		$imgTS = $this->conf['list.']['logo.'];
		if (count($files)>0 && file_exists( $fieldconf['config']['uploadfolder'] . '/' . $files[0]) ) {
			$imgTS['file'] = $fieldconf['config']['uploadfolder'] . '/' . $files[0];
		}

		$bigImage = $this->cObj->IMG_RESOURCE( $imgTS );
		if ($bigImage && $bigImage!="")
			return '<img src="' . $bigImage . '" alt="Logo" title="logo"/>';

		return '';
	}
}



?>