<?php

if(defined('_JEXEC')) return;

class Joomla3LoginPlugin extends MantisPlugin 
{
	function register() 
	{
		$this->name = 'Joomla 3.x Login';    # Proper name of plugin
		$this->description = 'Single-Sign-On Authentication for Joomla 3.x users';    # Short description of the plugin
		$this->page = '';           # Default plugin page

		$this->version = '1.0';     # Plugin version string
		$this->requires = array(    # Plugin dependencies, array of basename => version pairs
			'MantisCore' => '1.2.0',  #   Should always depend on an appropriate version of MantisBT
		);

		$this->author = 'Thomas Munz';         # Author/team name
		$this->contact = 'thomas.munz@sanhist-planspiel.at';        # Author/team e-mail address
		$this->url = 'www.sanhist-planspiel.at';            # Support webpage
	}
	
	function config()
	{
		return array(
			'joomla_website' => 'http://',
			'joomla_login_link' => 'index.php?option=com_users&view=login&login_redirect_url=[RETURN]',
			'joomla_logout_link' => 'index.php?option=com_users&task=user.logout&mantis=1&logout_redirect_url=[RETURN]',
			'joomla_manage_account_link' => '',
		);
	}	
	

	function hooks()
	{
		return array('EVENT_PLUGIN_INIT' => 'check_page',
				     'EVENT_MENU_MANAGE_CONFIG' => 'display_manage_menu_item');
	}	
	
	/**
	 * Checks if current Pages needs a redirect. Needs to be called after all Plugins are inited, so everything works ok
	 * @see MantisPlugin::init()
	 */	
	function check_page()
	{
		global $g_path;
		
		$str_file = basename($_SERVER['SCRIPT_NAME']);		
		
		if($this->begins_with($str_file, "login") ||
		   $this->begins_with($str_file, "signup") ||
		   $this->begins_with($str_file, "lost") )
		{
			$this->loadFiles();
			
			$str_website_url = plugin_config_get('joomla_website');
			$str_login_link = plugin_config_get('joomla_login_link');
			
			$str_login_link = str_replace("[RETURN]", base64_encode($g_path . "index.php"), $str_login_link);
			
			print_header_redirect($str_website_url . $str_login_link, true, false, true );
		}
		else if ($this->begins_with($str_file, "logout")) 
		{
			$this->loadFiles();
			
			$str_website_url = plugin_config_get('joomla_website');
			$str_logout_link = plugin_config_get('joomla_logout_link');
			
			$str_logout_link = str_replace("[RETURN]", base64_encode($g_path . "index.php"), $str_logout_link);
			
			print_header_redirect($str_website_url . $str_logout_link, true, false, true );
		}		
		else if ($this->begins_with($str_file, "account_"))
		{
			$this->loadFiles();
			
			$str_website_url = plugin_config_get('joomla_website');
			$str_manage_account_link = plugin_config_get('joomla_manage_account_link');
			
			print_header_redirect($str_website_url . $str_manage_account_link, true, false, true );
		}		
	}
	
	private function loadFiles()
	{
		require_once 'access_api.php';
		require_once 'print_api.php';
		require_once 'project_api.php';
		require_once 'user_api.php';
		require_once 'authentication_api.php';
	}
	
	function display_manage_menu_item()
	{
		if ( access_has_global_level ( ADMINISTRATOR ) ) 
		{
			$str_file = basename($_SERVER['SCRIPT_NAME']);
			
			if ($this->begins_with($str_file, "plugin"))
			{				
				$f_page= gpc_get_string( 'page' );
				
				if($this->begins_with($f_page, 'Joomla3Login'))
				{
					return array("Joomla 3.x Login " . plugin_lang_get("configuration"));
				}
				else 
				{
					return array("<a href='" . plugin_page("config_page") .  "'>Joomla 3.x Login " . plugin_lang_get("configuration") . "</a>");
				}				
			}
			else
			{
				return array("<a href='" . plugin_page("config_page") .  "'>Joomla 3.x Login " . plugin_lang_get("configuration") . "</a>");
			}			
		}
		
		return array();
	}
	
	private function begins_with($haystack, $needle) 
	{
		return strpos($haystack, $needle) === 0;
	}
}