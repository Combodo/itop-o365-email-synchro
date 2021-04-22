<?php
use TheNetworg\OAuth2\Client\Provider\Azure;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;

require __DIR__.'/vendor/autoload.php';

require_once(APPROOT.'application/application.inc.php');
require_once(APPROOT.'/application/startup.inc.php');
require_once(APPROOT.'/application/user.preferences.class.inc.php');

require_once(APPROOT.'/application/loginwebpage.class.inc.php');
LoginWebPage::DoLoginEx(null, true); // Only admins below this point
	
if (!isset($_GET['code']))
{
	echo "<p>Missing parameter 'code'</p>";
	if (isset($_GET['error']))
	{
		echo "<p>Error: <tt>{$_GET['error']}</tt></p>";
	}
	if (isset($_GET['error_description']))
	{
		echo "<p>Error description:<br>{$_GET['error_description']}</p>";
	}
	echo '<p><a href="./test.php">Try again...</a></p>';
	exit;
	
	// Check given state against previously stored one to mitigate CSRF attack
}
elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state']) || empty($_SESSION['mailbox_id']))
{
	
	unset($_SESSION['oauth2state']);
	unset($_SESSION['mailbox_id']);
	echo "Invalid state or mailbox_id in session.";
	exit;
	
}
else
{
	try
	{
		$iMailboxId = $_SESSION['mailbox_id'];
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
		
		// Try to get an access token (using the authorization code grant)
		$token = $oProvider->getAccessToken('authorization_code', [
			'code' => $_GET['code']
		]);
		
		// Test that the token is valid for connecting to the mailbox
		
		$oCM = new ClientManager([]);
		
		$oClient = $oCM->make([
			'host'          => 'outlook.office365.com',
			'port'          => 993,
			'encryption'    => 'ssl',
			'validate_cert' => true,
			'username'      => $oMailbox->Get('login'),
			'password'		=> $token->getToken(),
			'protocol'      => 'imap',
			'authentication' => 'oauth',
		]);
		//Connect to the IMAP Server
		$oClient->connect();
		
		// It works: disconnect and record the token
		$oClient->disconnect();
		
		$oMailbox->Set('oauth_token', json_encode($token->jsonSerialize()));
		$oMailbox->Set('azure_authorization', 'ok');
		$oMailbox->DBUpdate();
		
		cmdbAbstractObject::SetSessionMessage(get_class($oMailbox), $oMailbox->GetKey(), 'UI:Class_Object_NotUpdated', 'Authorization token recorded', 'info', 0, true /* must not exist */);
		header('Location: '.utils::GetAbsoluteUrlAppRoot().'pages/UI.php?operation=details&class='.get_class($oMailbox).'&id='.$oMailbox->getKey());
	}
	catch (Exception $e)
	{
		echo "<p>Damned, an exception occured: ".$e->getMessage()."</p>";
		print_r($e);
	}

}
