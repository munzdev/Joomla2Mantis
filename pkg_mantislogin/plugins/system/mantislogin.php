<?php
/**
 * File: mantislogin.php
 * Version: 1.0
 * Author: Thomas Munz 
 * Website: https://github.com/munzili/Joomla2Mantis
 * Plugin for automatic mantis login with Joomla.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class plgSystemMantisLogin extends JPlugin 
{
	private static $b_mantis_loaded = false;
	
	function plgSystemMantisLogin(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}
	
	public function onAfterRoute()
	{
		$app    = JFactory::getApplication();			
		$input  = $app->input;
		
		$component = $input->get('option');		
		
		$session = JFactory::getSession();
		
		if($component == "com_users" )
		{
			$task = $input->get('task');
			$mantis = $input->getBool('mantis');
			
			//-- Allow a direct logout link. A token must be generated in order to work correct
			if($task == "user.logout" && $mantis)
			{				
				$token = JSession::getFormToken(false);
				$input->request->set($token, 1);
			}
			
			$login_url = $input->request->get('login_redirect_url', '', 'BASE64');				
			
			if ($login_url) 
			{				
				$session->set('login_redirect_url', $login_url, 'mantislogin');
			}

		}
		else //-- if user for example clicked in mentis on login but does not login and continue browsing in joomla, remove reference to mantis as its not the 'coming from' page		 
		{
			$session->clear('login_redirect_url', 'mantislogin');;
		}
	}	
}