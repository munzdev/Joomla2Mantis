<?php
form_security_validate( 'plugin_Joomla3Login_config_update' );

auth_reauthenticate();

if(!current_user_is_administrator())
{
	access_denied();
}

$f_joomla_website = gpc_get_string( 'joomla_website' );
$f_joomla_login_link = gpc_get_string( 'joomla_login_link' );
$f_joomla_logout_link = gpc_get_string( 'joomla_logout_link' );
$f_joomla_manage_account_link = gpc_get_string( 'joomla_manage_account_link' );

plugin_config_set( 'joomla_website', $f_joomla_website );
plugin_config_set( 'joomla_login_link', $f_joomla_login_link );
plugin_config_set( 'joomla_logout_link', $f_joomla_logout_link );
plugin_config_set( 'joomla_manage_account_link', $f_joomla_manage_account_link );

form_security_purge( 'plugin_Joomla3Login_config_update' );
print_successful_redirect( plugin_page( 'config_page', true ) );