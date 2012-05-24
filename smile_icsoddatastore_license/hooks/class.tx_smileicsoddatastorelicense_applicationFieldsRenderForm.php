<?php
class tx_smileicsoddatastorelicense_applicationFieldsRenderForm{

	function applicationFieldsRenderForm(&$markers, &$subpartArray, &$template, &$application, &$conf, &$pObj){
		$this->pObj = $pObj;
		$GLOBALS['TSFE']->additionalHeaderData[$this->pObj->extKey]='<script src="typo3conf/ext/smile_icsoddatastore_license/res/script.js" type="text/javascript"></script>';
		if($this->getSessionValues('user', 'cgu_accepted')!='on'){
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
	 * @return	[type]		...
	 */
	public function getSessionValues($type, $var) {
        return $GLOBALS["TSFE"]->fe_user->getKey($type, $var);
    }
}
?>