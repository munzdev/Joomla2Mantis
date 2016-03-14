# Joomla2Mantis
A Single-Sign-On Bridge for Joomla 3 and Mantis Bug Tracker

## How to install

### Joomla 3.x Setup
1. Open the pkg_mantislogin/plugins/system/ folder
2. Create a Zip File of those files and save it somehwere as 'system_mantislogin.zip'
3. Open the pkg_mantislogin/plugins/user/ folder
4. Create a Zip File of those files and save it somehwere as 'user_mantislogin.zip' 
5. Go to the Joomla Admin interface and open the Extension Menu
6. Install both files, that we just created by Uploading the files as package
7. Go to the Extension -> Plugins menu in the Admin interface
8. Activate both 'User - Mantis Login' Plugins
9. Open the 'User - Mantis Login' plugin of type 'user'
10. Configure now the Joomla Modul:
	- Mantis Path: Enter the absolute path to the Mantis root directory, Ex: /var/www/manits/ <- ENDING SLASH NEEDED
	- Mantis URL: Enter the absolute URL to the Mantis directory, Ex: http://mantis.website.com/ or https://www.website.com/mantis/ <- ENDING SLASH NEEDED
	- Username check Erorr Message: Enter a error text, if the username, that the visitor entered on signup, doesn't match the Mantis username policy
	- Save the changes

### Mantis 1.2 Setup
1. Upload the folder 'Joomla3Login' to the Mantis 'extensions' directory
2. Log into Mantis as Administrator
3. Go to the Plugins-Management Menu
4. Install the 'Joomla 3.x Login' Plugin
5. Go to the 'Config Report' Menu
6. Open the new Menu 'Joomla 3.x Login Configuration' link
7. Configure now the Mantis Modul:
	- Joomla 3.x Website Url: Enter the absolute Url to the Joomla Website, ex: https://www.website.com/  <- ENDING SLASH NEEDED
	- Link to the Login Page: Enter the reltive link to the Joomla Login Page. Normal this doesn't need any changes and can be keeped as is
	- Link to the Logout Page: Enter the reltive link to the Joomla Logout Page. Normal this doesn't need any changes and can be keeped as is
	- Link to the Profilc Edit Page: Enter the relative link to the Edit Profil Page in Joomla
	- Save the Changes
	
### Additional Config
If Joomla and Mantis are running on different Subdomains, you need an extra config:
1. Open the config_defaults_inc.php file in your Mantis directory
2. Find the Option $g_cookie_domain
3. Change it to the Top-level domain. Ex: If mantis is runnging at manits.website.com, change the variable to: website.com, so cookies can be set properly