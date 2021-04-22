<?php
use Combodo\iTop\Extension\O365EmailSynchro\Controller\O365Controller;

require_once(APPROOT.'application/startup.inc.php');

$sModuleName = basename(__DIR__);
$oController = new O365Controller(MODULESROOT.$sModuleName.'/views', $sModuleName);

$oController->SetDefaultOperation('DisplayConfiguration');
$oController->HandleOperation();

// Do not close the session too early since the action may want to use SetSessionMessage...
session_write_close();