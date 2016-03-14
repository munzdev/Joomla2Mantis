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

class plgUserMantisLogin extends JPlugin 
{
	private static $b_mantis_loaded = false;
	
	function plgUserMantisLogin(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}
	
	function onUserBeforeSave($user, $isnew, $new)  
	{
		$str_mentis_path = $this->params->get('mantis_absolutpath');
		if($str_mentis_path  && $isnew) 
		{
			$this->loadMantisCore();
			
			if ( user_is_name_valid($new['username'])) 
			{
				return true;
			} 
			else 
			{
				$message = $this->params->get('mantis_usernameerrormessage', '');
				throw new Exception($message);
				return false;
			}
		}
		return true;
	}
	
	/**
	 * If User made a Profile Update
	 * 
	 * @param unknown $user
	 * @param unknown $isnew
	 * @param unknown $success
	 * @param unknown $msg
	 */
	function onUserAfterSave($user, $isnew, $success, $msg)
	{
		$str_mentis_path = $this->params->get('mantis_absolutpath');
		if($str_mentis_path  && !$isnew && $success)
		{
			$instance = $this->_getUser($user);
		
			// actual Mantis directory and domain for the cookie
			$str_mentis_domain = $this->params->get('mantis_domain');
			
			$this->loadMantisCore();
			
			$t_user_id = user_get_id_by_name(  $user['username'] );
			
			# check for anonymous login
			if(false !== $t_user_id && user_is_anonymous( $t_user_id ) )
			{
				return false;
			}
			
			$success = user_set_email($t_user_id, $user['email']) &&
					   user_set_realname($t_user_id, $user['fullname']);
		}
	}

	function onUserLogin($user, $options = array())
	{		
		jimport('joomla.user.helper');

		$instance = $this->_getUser($user, $options);

		// If _getUser returned an error, then pass it back.
		if (JError::isError($instance)) {
			return false;
		}

		// If the user is blocked, redirect with an error
		if ($instance->get('block') == 1) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('JERROR_NOLOGIN_BLOCKED'));
			return false;
		}
		
		$success = true;

		// If we are logged in to administrator back end, just exit
		$app = JFactory::getApplication();
		if ($app->isAdmin()) {
			return ($success);
		}
		
		// actual Mantis directory and domain for the cookie
		$str_mentis_domain = $this->params->get('mantis_domain');
		
		$this->loadMantisCore();	
		
		$f_secure_session = gpc_get_bool( 'secure_session', false );
		
		gpc_set_cookie( config_get_global( 'cookie_prefix' ) . '_secure_session', $f_secure_session ? '1' : '0' );
		
		$t_user_id = user_get_id_by_name(  $user['username'] );
		
		# check for anonymous login
		if(false !== $t_user_id && user_is_anonymous( $t_user_id ) )
		{
			return false;
		}
		
		if (false === $t_user_id) //-- new User ? Create a Mantis account
		{
			$t_seed = $user['email'] . $user['username'];
			
			# Create random password
			$t_password = auth_generate_random_password( $t_seed );
			
			$success = user_create( $user['username'], $t_password, $user['email'],  null, false, true, $user['fullname']);
		}
		else //-- existing user - update mantis account
		{
			$success = user_set_email($t_user_id, $user['email']) && 
					   user_set_realname($t_user_id, $user['fullname']);
		}
		
		//-- something went wrong, stop it
		if(!$success)
			return $success;				
		
		# ok, we're good to login now
		# increment login count
		user_increment_login_count( $t_user_id );
		
		user_reset_failed_login_count_to_zero( $t_user_id );
		user_reset_lost_password_in_progress_count_to_zero( $t_user_id );
		
		# set the cookies
		auth_set_cookies( $t_user_id);
		auth_set_tokens( $t_user_id );
						
		session_set( 'secure_session', $f_secure_session );	
				
		return $success;
	}

	function onUserLogout($user, $options = array())
	{
		$success = true;

		// If we are logged in to administrator back end, just exit
		$app = JFactory::getApplication();
		if ($app->isAdmin()) {
			return ($success);
		}
		
		$this->loadMantisCore();		
			
		//-- call mantis logout api
		auth_logout();

		return $success;
	}
	
	function onUserAfterLogin()
	{
		// Get the return url from the request and validate that it is internal.
		$app    = JFactory::getApplication();
		$session = JFactory::getSession();
		
		$url = $session->get('login_redirect_url', '', 'mantislogin');		
		
		if($url)
		{
			$session->clear('login_redirect_url', 'mantislogin');
			
			$this->handleReturnUrl($url);
		}	
	}
	
	function onUserAfterLogout()
	{	
		// Get the return url from the request and validate that it is internal.
		$app    = JFactory::getApplication();
		$input  = $app->input;		
		
		$logout_url = $input->request->get('logout_redirect_url', '', 'BASE64');
		
		if($logout_url)
			$this->handleReturnUrl($logout_url);		
	}
	
	protected function handleReturnUrl($return)
	{		
		$app    = JFactory::getApplication();
		
		$return = base64_decode($return);
		
		$this->loadMantisCore();
		
		$g_cookie_path			= $GLOBALS['g_cookie_path'];
		$g_cookie_domain		= $GLOBALS['g_cookie_domain'];
		
		$str_mantis_url = $g_cookie_domain . $g_cookie_path;
		
		$return_url_path = '';
		if ($return) 
		{
			$url_informations = parse_url($return);
			$return_url_path = $url_informations['host'] . $url_informations['path'];
		}
		
		if($return)
		{
			// Redirect the user.
			$app->redirect(JRoute::_($return, false));
		}
	}
	
	protected function loadMantisCore()
	{
		//-- if mantis core was allread loaded
		if(self::$b_mantis_loaded)
			return;
		
		//-- set mantis load to true
		self::$b_mantis_loaded = true;
		
		// actual Mantis directory and domain for the cookie
		$str_mentis_path = $this->params->get('mantis_absolutpath');
		
		// load Mantis config
		require_once $str_mentis_path . 'core' . DIRECTORY_SEPARATOR . 'constant_inc.php';
		require_once $str_mentis_path . 'config_defaults_inc.php';
		
		//-- Database
		$g_queries_array = array();
		$g_db_connected = false;
		$g_db_param_count = 0;
		
		$a_vars = get_defined_vars();
		
		foreach ($a_vars as $str_key => $value)
		{
			if(!array_key_exists($str_key, $GLOBALS))
				$GLOBALS[$str_key] = $value;
		}			
		
		// load Mantis core, allow us to use its API
		require_once $str_mentis_path . 'core.php';
		
		require_once 'access_api.php';
		require_once 'print_api.php';
		require_once 'project_api.php';
		require_once 'user_api.php';
		require_once 'authentication_api.php';
	}

	/**
	 * This method will return a user object
	 *
	 * If options['autoregister'] is true, if the user doesn't exist yet he will be created
	 *
	 * @param	array	$user		Holds the user data.
	 * @param	array	$options	Array holding options (remember, autoregister, group).
	 *
	 * @return	object	A JUser object
	 * @since	1.5
	 */	
	protected function &_getUser($user, $options = array())
	{
		$instance = JUser::getInstance();
		if ($id = intval(JUserHelper::getUserId($user['username'])))  {
			$instance->load($id);
			return $instance;
		}

		//TODO : move this out of the plugin
		jimport('joomla.application.component.helper');
		$config	= JComponentHelper::getParams('com_users');
		// Default to Registered.
		$defaultUserGroup = $config->get('new_usertype', 2);

		$acl = JFactory::getACL();

		$instance->set('id'			, 0);
		$instance->set('name'			, $user['fullname']);
		$instance->set('username'		, $user['username']);
		$instance->set('password_clear'	, $user['password_clear']);
		$instance->set('email'			, $user['email']);	// Result should contain an email (check)
		$instance->set('usertype'		, 'deprecated');
		$instance->set('groups'		, array($defaultUserGroup));

		//If autoregister is set let's register the user
		$autoregister = isset($options['autoregister']) ? $options['autoregister'] :  $this->params->get('autoregister', 1);

		if ($autoregister) {
			if (!$instance->save()) {
				return JError::raiseWarning('SOME_ERROR_CODE', $instance->getError());
			}
		}
		else {
			// No existing user and autoregister off, this is a temporary user.
			$instance->set('tmp_user', true);
		}

		return $instance;
	}
	
}