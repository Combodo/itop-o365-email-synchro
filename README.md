# Office 365 eMail Synchro

**Experimental** adapter module to connect Mail to Ticket Automation with Office 365 using IMAP + oAuth

## Configuration
The configuration is a 3 step process:
  - Create a new Application in [Azure Active Directory](https://aad.portal.azure.com/#blade/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/RegisteredApps). Use the RedirectURI provided by iTop
  - Create a new Office 365 Mailbox in iTop with the Application (client) ID, Directory (tenant) ID, a client secret
  - Perform the interactive oAuth authorization from the iTop Office 365 Mailbox link.

  
