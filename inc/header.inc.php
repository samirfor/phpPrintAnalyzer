<?php
/**universal
 * Fichier contenant les en-têtes du site
 * 
 * @author Thomas Pequet
 * @version	1.0
 */
?>
<HTML>
<HEAD>
<TITLE><?=$nomSite." v".$versionSite;?> - <?=strip_tags(${"titre".$page});?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=ISO-8859-1">
<STYLE>
<?
include($ficStyle);
?>
</STYLE>
</HEAD>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$ficScript;?>"></SCRIPT>

<BODY BGCOLOR="#FFFFFF"> 

<CENTER>
