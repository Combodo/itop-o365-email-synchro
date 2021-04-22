<?php
/**
 * @copyright   Copyright (C) 20121 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */
namespace Combodo\iTop\Extension\O365EmailSynchro\Controller;


use Combodo\iTop\Application\TwigBase\Controller\Controller;
use Combodo\iTop\Extension\CustomHyperlinks\Service\MenuBuilder;
use Exception;
use IssueLog;
use MetaModel;
use utils;
use Dict;

class O365Controller extends Controller
{
	public function OperationDisplayConfiguration()
	{
		$sTemplate = 'configuration';
		if (Dict::GetUserLanguage() == 'FR FR')
		{
			// TODO : implement something generic with a fallback to English when the translated template does not exist...
			$sTemplate = 'fr.configuration';
		}
		$this->DisplayPage(array('redirect_url' => utils::GetAbsoluteUrlModulePage('itop-o365-email-synchro', 'authorize2.php')), $sTemplate, 'setup');
	}
}