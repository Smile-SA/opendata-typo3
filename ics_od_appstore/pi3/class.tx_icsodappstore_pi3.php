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
 * $Id: class.tx_icsodappstore_pi3.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   52: class tx_icsodappstore_pi3 extends tx_icsodappstore_common
 *   65:     function main($content, $conf)
 *  112:     function getContent($application, $date)
 *  154:     function getStatItemContent($template, $uid, $date)
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(t3lib_extMgm::extPath('ics_od_appstore') . 'lib/class.tx_icsodappstore_common.php');


/**
 * Plugin 'Statistics' for the 'ics_od_appstore' extension.
 *
 * @author	Tsi <tsi@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsodappstore
 */
class tx_icsodappstore_pi3 extends tx_icsodappstore_common {
	var $prefixId      = 'tx_icsodappstore_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_icsodappstore_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'ics_od_appstore';	// The extension key.


	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The		content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		$this->init();

		$GLOBALS['TSFE']->additionalHeaderData[$extKey] = '<script src="typo3conf/ext/ics_od_appstore/res/tablefilter.js" type="text/javascript"></script>';

		if(is_null($GLOBALS['TSFE']->fe_user->user['uid'])){
			return $this->pi_wrapInBaseClass($this->renderContentError($this->pi_getLL('nologin')));
		}

		// To prevent SQL injection
		$this->piVars['uid'] = intval($this->piVars['uid']);

		if (empty($this->piVars['uid'])) {
			$content .= $this->renderContentError($this->pi_getLL('application_not_exists'));
		} else {
			$applications = $this->getApplications(tx_icsodappstore_common::APPMODE_SINGLEUSER, null, $this->piVars['uid']);
			if (!$applications) {
				$content .= $this->renderContentError($this->pi_getLL('application_not_exists'));
			} else {
				$application = $applications[0];
				$date = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
				if (isset($this->piVars['btn_next'])) {
					$date = mktime(0, 0, 0, date('m', $this->piVars['date']), date('d', $this->piVars['date'])+30, date('Y', $this->piVars['date']));
				}
				if (isset($this->piVars['btn_previous'])) {
					$date = mktime(0, 0, 0, date('m', $this->piVars['date']), date('d', $this->piVars['date'])-30, date('Y', $this->piVars['date']));
				}
				if ($date > mktime(0, 0, 0, date('m'), date('d'), date('Y')))
					$date = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
				$content .= $this->getContent($application, $date);
			}
		}
		return $this->pi_wrapInBaseClass($content);
	}


	/**
	 * Retrieves content
	 *
	 * @param	int		$uid: uid of application
	 * @param	mktime		$date
	 * @return	string		content
	 */
	function getContent($application, $date){
		$html = $this->cObj->fileResource($this->templateFile);
		$template = array();
		$template = $this->cObj->getSubpart($html, '###TEMPLATE_STATISTIQUE###');
		$markerArray = array(
			'###CAPTION###' => htmlspecialchars($this->pi_getLL('titre')) . ' : ' . $application['title'],
			'###TITLE_DATE###' => htmlspecialchars($this->pi_getLL('date')),
			'###TITLE_COMMAND###' => htmlspecialchars($this->pi_getLL('command')),
			'###TITLE_COUNT###' => htmlspecialchars($this->pi_getLL('count')),
			'###URL###' => t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'),
			'###BTN_PREVIOUS###' => $this->prefixId . '[btn_previous]',
			'###BTN_PREVIOUS_VALUE###' => htmlspecialchars($this->pi_getLL('previous')),
			'###DISPLAY_PREVIOUS###' => (($date-30) < mktime(0, 0, 0, date('m', $application['crdate']), date('d', $application['crdate']), date('Y', $application['crdate']))? 'disabled="disabled"': ''),
			'###BTN_NEXT###' => $this->prefixId . '[btn_next]',
			'###BTN_NEXT_VALUE###' => htmlspecialchars($this->pi_getLL('next')),
			'###DISPLAY_NEXT###' => ($date == mktime(0, 0, 0, date('m'), date('d'), date('Y'))? 'disabled="disabled"': ''),
			'###DATE###' => $this->prefixId . '[date]',
			'###DATE_VALUE###' => $date,
		);

		$statItemContent = $this->getStatItemContent($template, $application['uid'], $date);

		// Restores markers of template
		$content .= $this->cObj->substituteMarkerArrayCached(
			$template,
			$markerArray,
			array('###STATISTIQUE_ITEM###' => $statItemContent)
		);

		return $content;
	}



	/**
	 * Retrieves content item
	 *
	 * @param	string		$template
	 * @param	int		$uid
	 * @param	mktime		$date
	 * @return	string		content
	 */
	function getStatItemContent($template, $uid, $date){
		$template = $this->cObj->getSubpart($template, '###STATISTIQUE_ITEM###');

		$addWhere = ' AND `date` > ' . $GLOBALS['TYPO3_DB']->fullquotestr( mktime(0, 0, 0, date('m', $date), date('d', $date)-30, date('Y', $date)), $this->tables['statistics']);
		$addWhere .= ' AND `date` < ' . $GLOBALS['TYPO3_DB']->fullquotestr( mktime(0, 0, 0, date('m', $date), date('d', $date), date('Y', $date)), $this->tables['statistics']);

		$where = array(
			'application' => $uid
		);
		$statistics = $this->getStatistics('', $where, $addWhere, 'date, cmd', 'date', '');
		if($statistics){
			foreach( $statistics as $statistic ){
				$markers = array(
					'###DATE###' => date('d-m-Y', $statistic['date']),
					'###COMMAND###' => $statistic['cmd'],
					'###COUNT###' => $statistic['count'],
				);
				$content .= $this->cObj->substituteMarkerArrayCached($template, $markers);
			}
		}
		return $content;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_appstore/pi3/class.tx_icsodappstore_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_appstore/pi3/class.tx_icsodappstore_pi3.php']);
}

?>