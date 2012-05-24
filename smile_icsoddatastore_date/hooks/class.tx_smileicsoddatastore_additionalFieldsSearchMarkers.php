<?php

class tx_smileicsoddatastore_additionalFieldsSearchMarkers {

	function additionalFieldsSearchMarkers(&$markers, &$subpartArray, &$template, &$conf, &$pObj) {
		$this->pObj = $pObj;
		$markers['###TITLE_DATE###'] = $this->pObj->pi_getLL('search_dateTitle');
		$markers['###SUBTITLE_DATE###'] = $this->pObj->pi_getLL('search_dateSubTitle');
		$markers['###IMGALT_DATE###'] = $this->pObj->pi_getLL('search_dateImgAlt');
		$markers['###DATE_VALUE###'] = htmlspecialchars($pObj->piVars['date']);
		$markers['###DATE_VALID_VALUE###'] = $this->isValidDate($pObj->piVars['date']) ? $pObj->piVars['date'] : '';
		$markers['###DATE_ERROR###'] = $this->isValidDate($pObj->piVars['date']) || $pObj->piVars['date']=='' ? '' : '<p class="error nomargin">'.htmlspecialchars($pObj->pi_getLL('date_error_format')).'</p>';
	}

	/**
	 * Validate a date
	 *
	 * @param	string		$date
	 * @return	boolean
	 */
	function isValidDate($date) {
		if (version_compare('5.3.0', phpversion(), 'le')) {
			$format = $this->pObj->conf['displaySearch.']['dateFormat'];
			$dt = DateTime::createFromFormat($format, $date);
			if ($dt == false) {
				return false;
			}
		} else {
			// FIXME : Not configurable in PHP < 5.3.0
			$day = intval(substr($date, 0, 2));
			$month = intval(substr($date, 3, 2));
			$year = intval(substr($date, 6, 4));
			$t = checkdate($month, $day, $year);
			if ($t == false) {
				return false;
			}
		}
		return true;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/smile_icsoddatastore_date/hooks/class.tx_smileicsoddatastore_additionalFieldsSearchMarkers.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/smile_icsoddatastore_date/hooks/class.tx_smileicsoddatastore_additionalFieldsSearchMarkers.php']);
}

?>