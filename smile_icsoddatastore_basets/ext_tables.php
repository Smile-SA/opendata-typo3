<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
t3lib_extMgm::addStaticFile($_EXTKEY,'static/felogin/', 'Ext. felogin');
t3lib_extMgm::addStaticFile($_EXTKEY,'static/mmforum/', 'Ext. mmforum');
t3lib_extMgm::addStaticFile($_EXTKEY,'static/srfeuserregister/', 'Ext. srfeuserregister');
?>