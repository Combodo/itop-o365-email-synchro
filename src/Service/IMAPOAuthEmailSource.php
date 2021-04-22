<?php
// Copyright (C) 2021 Combodo SARL
//
//   This program is free software; you can redistribute it and/or modify
//   it under the terms of the GNU Lesser General Public License as published by
//   the Free Software Foundation; version 3 of the License.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of the GNU General Public License
//   along with this program; if not, write to the Free Software
//   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
/**
 * @copyright   Copyright (C) 2021 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */
namespace Combodo\iTop\Extension\O365EmailSynchro\Service;

use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;
use League\OAuth2\Client\Token\AccessToken;
use TheNetworg\OAuth2\Client\Provider\Azure;
use DBObjectSearch;
use DBObjectSet;
use EmailSource;
use IssueLog;
use MetaModel;
use MessageFromMailbox;


/**
 * Read messages from an IMAP mailbox using the pure PHP webklex/php-imap library (instead of PHP's IMAP extension)
 * This library supports the SASL XOAUTH2 protocol required by Office365
 */
class IMAPOAuthEmailSource extends EmailSource
{
	protected $rImapConn = null;
	protected $sLogin = '';
	protected $sMailbox = '';
	/**
	 * 
	 * @var League\OAuth2\Client\Token\AccessToken
	 */
	protected $oAccessToken = null;
	
	/** @var \Webklex\PHPIMAP\Client $oClient */
	protected $oClient = null;
	
	public function __construct($sServer, $iPort, $sLogin, $sPwd, $sMailbox, $sJsonToken, $aOptions)
	{
		parent::__construct();
		$this->sLastErrorSubject = '';
		$this->sLastErrorMessage = '';
		$this->sLogin = $sLogin;
		$this->sMailbox = $sMailbox;
		$this->oAccessToken = new AccessToken(json_decode($sJsonToken, true));

		if ($this->oAccessToken->hasExpired())
		{
			IssueLog::Debug('oAuth Access token has expired. trying to refresh it...');
			$oSearch = new DBObjectSearch('MailInboxStandard');
			$oSearch->AddCondition('server', $sServer);
			$oSearch->AddCondition('login', $sLogin);
			$oSearch->AddCondition('protocol', 'imap_oauth');
			$oSet = new DBObjectSet($oSearch);
			$oMailbox = $oSet->Fetch();
			if ($oMailbox)
			{
				$aScopes = ['https://outlook.office.com/IMAP.AccessAsUser.All', 'offline_access'];
				$aProviderOptions = [
					'clientId'		=> $oMailbox->Get('azure_client_id'),
					'clientSecret'  => $oMailbox->Get('azure_secret'),
					'redirectUri'   => 'http://localhost/dev/extensions/combodo-mail-to-ticket-automation/combodo-email-synchro/test2.php',
					//Optional
					'scopes'        => $aScopes,
					//Optional
					'defaultEndPointVersion' => '2.0'
				];
				$provider = new Azure($aProviderOptions);
				$provider->tenant = $oMailbox->Get('azure_tenant_id');
				
				$this->oAccessToken = $provider->getAccessToken('refresh_token', [
					'refresh_token' => $this->oAccessToken->getRefreshToken()
				]);
				
				// Store the new access token
				IssueLog::Debug('Updating the oAuth access token, for mailbox: id='.$oMailbox->GetKey());
				$oMailbox->Set('oauth_token', json_encode($this->oAccessToken->jsonSerialize()));
				$oMailbox->Set('azure_authorization', 'ok');
				try
				{
					$oMailbox->DBUpdate();
					IssueLog::Debug('oAuth access token updated in the Mailbox object.');
				}
				catch (Exception $e)
				{
					IssueLog::Error('Failed to store the updated oAuth access token, got exception: '.$e->getMessage());
				}
				
			}
			else
			{
				IssueLog::Debug('Failed to store the updated oAuth access token, Mailbox not found.');
			}
			IssueLog::Debug('oAuth Access token successfully refreshed.');
		}
		else
		{
			IssueLog::Debug('oAuth Access token is valid.');
		}
		
		$oCM = new ClientManager([]);
		
		$this->oClient = $oCM->make([
			'host'          => $sServer,
			'port'          => $iPort,
			'encryption'    => 'ssl',
			'validate_cert' => true,
			'username'      => $sLogin,
			'password'		=> $this->oAccessToken->getToken(),
			'protocol'      => 'imap',
			'authentication' => 'oauth',
		]);
		//Connect to the IMAP Server
		$this->oClient->connect();
	}
	
	/**
	 * Get the number of messages to process
	 * @return integer The number of available messages
	 */
	public function GetMessagesCount()
	{
		/** @var \Webklex\PHPIMAP\Folder $oFolder */
		$oFolder = $this->oClient->getFolderByPath($this->sMailbox);
		$iCount = $oFolder->query()->all()->count();
		
		return $iCount;
	}
	
	/**
	 * Retrieves the message of the given index [0..Count]
	 * @param $index integer The index between zero and count
	 * @return \MessageFromMailbox
	 */
	public function GetMessage($index)
	{
		$oFolder = $this->oClient->getFolderByPath($this->sMailbox);
		$oMessage = $oFolder->query()->getMessageByMsgn(1+$index);
		
		$oHeader = $oMessage->getHeader();
		$sRawHeaders = $oHeader->raw;
		$sBody = $oMessage->getRawBody();
		
		$bUseMessageId = (bool) MetaModel::GetModuleSetting('combodo-email-synchro', 'use_message_id_as_uid', false);
		$sUId = $oMessage->uid;
		if ($bUseMessageId)
		{
			$sUId = $oHeader->get("message_id");
		}
		
		return new MessageFromMailbox($sUId, $sRawHeaders, $sBody);
	}
	
	/**
	 * Deletes the message of the given index [0..Count] from the mailbox
	 * @param $index integer The index between zero and count
	 */
	public function DeleteMessage($index)
	{
		$oFolder = $this->oClient->getFolderByPath($this->sMailbox);
		$oMessage = $oFolder->query()->getMessage($index);
		$ret = $oMessage->delete();
		return $ret;
	}
	
	/**
	 * Name of the eMail source
	 */
	public function GetName()
	{
		return $this->sLogin;
	}
	
	/**
	 * Mailbox path of the eMail source
	 */
	public function GetMailbox()
	{
		return $this->sMailbox;
	}
	
	/**
	 * Get the list (with their IDs) of all the messages
	 * @return Array An array of hashes: 'msg_id' => index 'uild' => message identifier
	 */
	public function GetListing()
	{
		$ret = array();
		
		$oFolder = $this->oClient->getFolderByPath($this->sMailbox);
		$aInfo = $oFolder->overview('1:*');
		
		// Workaround for some email servers (like gMail!) where the UID may change between two sessions, so let's use the
		// MessageID as a replacement for the UID.
		// Note that it is possible to receive two times a message with the same MessageID, but since the content of the message
		// will be the same, it's safe to process such messages only once...
		// BEWARE: Make sure that you empty the mailbox before toggling this setting in the config file, since all the messages
		// present in the mailbox at the time of the toggle will be considered as "new" and thus processed again.
		$bUseMessageId = (bool)MetaModel::GetModuleSetting('combodo-email-synchro', 'use_message_id_as_uid', false);
		
		$iNumber = 1;
		foreach($aInfo as $sUid => $aHeaders)
		{
			if ($bUseMessageId)
			{
				$ret[] = array('msg_id' => $iNumber, 'uidl' => $aMessage->message_id);
			}
			else
			{
				$ret[] = array('msg_id' => $iNumber, 'uidl' => $sUid);
			}
			$iNumber++;
		}
		return $ret;
	}
	
	public function Disconnect()
	{
		$this->oClient->expunge();
		$this->oClient->disconnect();
		$this->oClient = null; // Just to be sure...
	}
}