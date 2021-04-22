<?php
/**
 * Localized data
 *
 * @copyright   Copyright (C) 2021 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

Dict::Add('FR FR', 'French', 'Français', array(
	// Dictionary entries go here
	'Class:MailInboxO365' => 'Boîte Mail Office 365',
	'Class:MailInboxO365+' => 'Configuration pour accéder aux Mails d\'une boîte mail Office 365',
	'Class:MailInboxO365/Attribute:azure_authorization' => 'Etat d\'Autorisation',
	'Class:MailInboxO365/Attribute:azure_client_id' => 'ID d\'application (client)',
	'Class:MailInboxO365/Attribute:azure_client_id+' => 'ID de l\'Application dans Azure Active Directory',
	'Class:MailInboxO365/Attribute:azure_tenant_id' => 'ID de l\'annuaire (locataire)',
	'Class:MailInboxO365/Attribute:azure_tenant_id+' => 'ID de l\'annuaire dans Azure Active Directory',
	'Class:MailInboxO365/Attribute:azure_secret' => 'Secret Client',
	'Class:MailInboxO365/Attribute:azure_secret+' => 'Secret client généré par Azure',
	'itop-o365-email-synchro/Operation:DisplayConfiguration/Title' => 'Configuration Azure AD pour Office 365',
	'O365:AzureAuthorization:Ok' => 'Ok',
	'O365:AzureAuthorization:KO' => 'Non Autorisé',
	'O365:AzureAuthorization:ClickToInit' => '%1$s Cliquez <a href="%2$s">ici</a> pour terminer le processus d\'autorisation',
	'O365:AzureAuthorization:ClickToRenew' => '%1$s Cliquez <a href="%2$s">ici</a> pour renouveler l\'autorisation',
	'O365:AzureAuthorization:ClickForMoreInfo' => 'Cliquez <a href="%1$s" target="_blank">ici</a> pour des informations à propos de la configuration Azure AD',
));
