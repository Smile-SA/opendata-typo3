<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
$tempColumns = array (
    'tx_smileicsoddatastorerss_highlight' => array (
        'exclude' => 0,
        'label' => 'LLL:EXT:smile_icsoddatastore_rss/locallang_db.xml:tx_icsoddatastore_filegroups.tx_smileicsoddatastorerss_highlight',
        'config' => array (
            'type' => 'check',
            'default' => 1,
        )
    ),
);

t3lib_div::loadTCA('tx_icsoddatastore_filegroups');
t3lib_extMgm::addTCAcolumns('tx_icsoddatastore_filegroups',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tx_icsoddatastore_filegroups','tx_smileicsoddatastorerss_highlight;;;;1-1-1');



t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
t3lib_extMgm::addPlugin(array(
    'LLL:EXT:smile_icsoddatastore_rss/locallang_db.xml:tt_content.list_type_pi1',
    $_EXTKEY . '_pi1',
    t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:smile_icsoddatastore_rss/pi1/flexform.xml');



// Static templates
t3lib_extMgm::addStaticFile( $_EXTKEY, 'static/rss/', 'RSS' );
?>