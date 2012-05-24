<?php
class tx_smileicsoddatastorelicense_additionalFieldsMarkers{
	function additionalFieldsMarkers(&$markers, &$subpartArray, &$template, &$row, &$conf, &$pObj){
		$this->pObj = $pObj;
		$GLOBALS['TSFE']->additionalHeaderData[$this->pObj->extKey]='<script src="typo3conf/ext/smile_icsoddatastore_license/res/script.js" type="text/javascript"></script>';
		if($GLOBALS["TSFE"]->fe_user->user){
			$sesType = 'user';
		}else{
			$sesType = 'ses';
		}
		$GP = t3lib_div::_GP($this->pObj->prefixId) ;
		if(isset($GP['cgu'])){
			$this->storeSessionValues($sesType, 'cgu_accepted', $GP['cgu']);
		}

		if($this->getSessionValues($sesType, 'cgu_accepted')!='on'){
			$markers['###URL_LICENSE###']= t3lib_div::getIndpEnv('TYPO3_REQUEST_URL') ;

			$markers['###BTN_REGISTRATION###']= $this->pObj->prefixId.'[btn_registration]' ;
			$markers['###BTN_REGISTRATION_VALUE###']= htmlspecialchars($this->pObj->pi_getLL('btn_registration')) ;

			$pictoItem = htmlspecialchars($this->pObj->pi_getLL('accept_license_to_download')) ;
			$subpartArray['###SECTION_FILE_HIDE###'] = $this->pObj->cObj->substituteMarkerArray($pictoItem, $markers) ; ;

			$cguField = $this->pObj->cObj->getSubpart($template, '###CGU_FIELD###');
			$markers['###CGU###']= $this->pObj->prefixId.'[cgu]' ;
			$markers['###CGU_LABEL###']= $this->pObj->pi_getLL('cgu_label') ;
			$configurations['parameter'] = $pObj->conf['cguLink'];
			$fileSize = filesize('fileadmin/tpl_opendata/ext/ics_od_appstore/res/SOMMAIRE_ANNUEL_version_web.pdf');
			$fileSize = t3lib_div::formatSize($fileSize, ' o| ko| Mo| Go');
			$typolink = $this->pObj->cObj->typolink(sprintf($this->pObj->pi_getLL('cgu_link_label'),$fileSize), $configurations);
			$markers['###CGU_LINK###']= $typolink ;

		}
		$subpartArray['###CGU_FIELD###'] = $this->pObj->cObj->substituteMarkerArray($cguField, $markers) ;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$type: ...
	 * @param	[type]		$var: ...
	 * @param	[type]		$content: ...
	 * @return	[type]		...
	 */
	function storeSessionValues($type, $var, $content) {
		$GLOBALS['TSFE']->fe_user->setKey($type, $var, $content);
		$GLOBALS['TSFE']->storeSessionData();
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$type: ...
	 * @param	[type]		$var: ...
	 * @return	[type]		...
	 */
	function getSessionValues($type, $var) {
        return $GLOBALS["TSFE"]->fe_user->getKey($type, $var);
    }
}
?>