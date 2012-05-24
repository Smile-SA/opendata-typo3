<?php
/*
 * $Id: getfileformats.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
$doc = new DOMDocument();
$xsl = new XSLTProcessor();
$doc->load('documentationapi.xsl');
$xsl->importStyleSheet($doc);
$doc->load('getfileformats.xml');
echo $xsl->transformToXML($doc);
