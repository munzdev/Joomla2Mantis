<?php 

auth_reauthenticate();

if(!current_user_is_administrator())
{
	access_denied();
}

html_page_top( "Joomla 3.x Login " . plugin_lang_get( 'configuration' ) );

print_manage_menu( 'adm_permissions_report.php' );
print_manage_config_menu( 'config_page.php' );

$t_joomla_website = plugin_config_get( 'joomla_website' );
$t_joomla_login_link = plugin_config_get( 'joomla_login_link' );
$t_joomla_logout_link = plugin_config_get( 'joomla_logout_link' );
$t_joomla_manage_account_link = plugin_config_get( 'joomla_manage_account_link' );
?>

<br/>

<form action="<?php echo plugin_page( 'config_update' ) ?>" method="post">
<?php echo form_security_field( 'plugin_Joomla3Login_config_update' ) ?>
<table class="width60" align="center">
<tbody>
<tr>
    <td class="form-title">Joomla 3.x Login <?php echo plugin_lang_get( 'configuration' ) ?></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
    <td class="category"><?php echo plugin_lang_get( 'joomla_website' ) ?></td>
    <td><input name="joomla_website" value="<?php echo string_attribute( $t_joomla_website ) ?>"/></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
    <td class="category"><?php echo plugin_lang_get( 'joomla_login_link' ) ?></td>
    <td><input name="joomla_login_link" value="<?php echo string_attribute( $t_joomla_login_link ) ?>"/></td>
    <td><?php echo plugin_lang_get( 'return_var_text' ) ?></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
    <td class="category"><?php echo plugin_lang_get( 'joomla_logout_link' ) ?></td>
    <td><input name="joomla_logout_link" value="<?php echo string_attribute( $t_joomla_logout_link ) ?>"/></td>
    <td><?php echo plugin_lang_get( 'return_var_text' ) ?></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
    <td class="category"><?php echo plugin_lang_get( 'joomla_manage_account_link' ) ?></td>
    <td><input name="joomla_manage_account_link" value="<?php echo string_attribute( $t_joomla_manage_account_link ) ?>"/></td>
</tr>


<tr>
    <td class="center" rowspan="2"><input type="submit"/></td>
</tr>
</tbody>
</table>
</form>

<?php

html_page_bottom();