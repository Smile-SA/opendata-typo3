<?php

class tx_smileicsoddatastore_addSearchRestriction {

	function addSearchRestriction(&$whereClause, &$queryJoin, &$conf, &$pObj) {
		$this->pObj = $pObj;

		if (isset($pObj->piVars['date']) && $this->isValidDate($pObj->piVars['date'])) {
			$whereClause .= $this->getSQLWhere($this->strDateToTimestamp($pObj->piVars['date']));
		}
	}

	/**
	 * Validate a date
	 *
	 * @param	string		$date
	 * @return	boolean
	 */
	function isValidDate($date) {
		$format = $this->pObj->conf['displaySearch.']['dateFormat'];
		$format = $format ? $format : 'd/m/Y';
		if (version_compare('5.3.0', phpversion(), 'le')) {
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

	/**
	 * Convert string date to timestamp
	 *
	 * @param	unknown_type		$date
	 * @return	[type]		...
	 */
	function strDateToTimestamp($date) {
		$format = $this->pObj->conf['displaySearch.']['dateFormat'];
		$format = $format ? $format : 'd/m/Y';
		if (version_compare('5.3.0', phpversion(), 'le')) {
			$dt = DateTime::createFromFormat($format, $date);
			return $dt->getTimestamp();
		} else {
			// FIXME : Not configurable in PHP < 5.3.0
			$day = intval(substr($date, 0, 2));
			$month = intval(substr($date, 3, 2));
			$year = intval(substr($date, 6, 4));
			return mktime(0, 0, 0, $month, $day, $year);
		}
	}

	/**
	 * SQL Where
	 *
	 * @param	timestamp
	 * @return	string
	 */
	function getSQLWhere($ts) {
		$where = ' AND `tx_icsoddatastore_filegroups`.`update_date`>='.$ts;
		return $where;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/smile_icsoddatastore_date/hooks/class.tx_smileicsoddatastore_addSearchRestriction.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/smile_icsoddatastore_date/hooks/class.tx_smileicsoddatastore_addSearchRestriction.php']);
}

?>