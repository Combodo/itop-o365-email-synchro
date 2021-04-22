<?php
require_once(APPROOT.'application/application.inc.php');
require_once(APPROOT.'/application/startup.inc.php');
require_once(APPROOT.'/application/user.preferences.class.inc.php');

require_once(APPROOT.'/application/loginwebpage.class.inc.php');
LoginWebPage::DoLoginEx(null, true); // Only admins below this point

$oP = new NiceWebPage('Azure configuration');

$oP->add('<h1>Azure configuration</h1>');

$oP->p('Redirect URL: <input type="text" readonly size="150" value="'.utils::GetAbsoluteUrlModulePage('itop-o365-email-synchro', 'authorize2.php').'"></input>');

$oP->output();