# iTop module : Office 365 eMail Synchro

## About

This is an **Experimental** adapter module to connect Mail to ticket automation extension for [iTop](https://github.com/Combodo/iTop) with Office 365 using IMAP + oAuth.

For more information about this module have a look at the corresponding [extension documentation](https://store.itophub.io/en_US/products/combodo-mail-to-ticket-automation).


## Configuration
The configuration is a 3 step process:
- Create a new Application in [Azure Active Directory](https://aad.portal.azure.com/#blade/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/RegisteredApps). Use the RedirectURI provided by iTop
- Create a new Office 365 Mailbox in iTop with the Application (client) ID, Directory (tenant) ID, a client secret
- Perform the interactive oAuth authorization from the iTop Office 365 Mailbox link.


## Download

Release packages can be found on the [iTop Hub Store](https://store.itophub.io/en_US/taxons/all-extensions). This is the best way to get a
running package as those contains all the needed modules and stable code.

When downloading directly from GitHub (by cloning or downloading as zip) you will get potentially unstable code, and you will miss
additional modules.

## About Us

This iTop module development is sponsored, led and supported by [Combodo](https://www.combodo.com).
