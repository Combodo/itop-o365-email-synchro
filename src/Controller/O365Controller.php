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

class O365Controller extends Controller
{
	public function OperationDisplayConfiguration()
	{	
		$this->DisplayPage(array('redirect_url' => utils::GetAbsoluteUrlModulePage('itop-o365-email-synchro', 'authorize2.php')), 'configuration', 'setup');
	}
}