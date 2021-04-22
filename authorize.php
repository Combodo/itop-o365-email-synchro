<?php
use TheNetworg\OAuth2\Client\Provider\Azure;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;

require __DIR__.'/vendor/autoload.php';

try
{
	require_once(APPROOT.'application/application.inc.php');
	require_once(APPROOT.'/application/startup.inc.php');
	require_once(APPROOT.'/application/user.preferences.class.inc.php');
	
	require_once(APPROOT.'/application/loginwebpage.class.inc.php');
	LoginWebPage::DoLoginEx(null, true); // Only admins below this point

	$iMailboxId = (int)utils::ReadParam('mailbox_id', '');
	$oMailbox = MetaModel::GetObject('MailInboxStandard', $iMailboxId);
	
	$aScopes = ['https://outlook.office.com/IMAP.AccessAsUser.All', 'offline_access'];
	$aProviderOptions = [
		'clientId'		=> $oMailbox->Get('azure_client_id'),
		'clientSecret'  => $oMailbox->Get('azure_secret'),
		'redirectUri'   => utils::GetAbsoluteUrlModulePage('itop-o365-email-synchro', 'authorize2.php'),
		//Optional
		'scopes'        => $aScopes,
		//Optional
		'defaultEndPointVersion' => '2.0'
	];
	$oProvider = new Azure($aProviderOptions);
	$oProvider->tenant = $oMailbox->Get('azure_tenant_id');
	
	$sAuthUrl = $oProvider->getAuthorizationUrl([
		'scope' => $aScopes,
	]);
	$_SESSION['oauth2state'] = $oProvider->getState();
	$_SESSION['mailbox_id'] = $oMailbox->GetKey();
	header('Location: '.$sAuthUrl);	
}
catch(Exception $e)
{
	echo $e->getMessage();
}

