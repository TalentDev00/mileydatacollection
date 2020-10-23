<?php

/*============================================================================
jpl 11/02/09
universalJPLPackageSettings.php

	It is this file that must be customized to a particular application
	environment in order for the universalJPLPackage to work.

============================================================================*/


//============================================================================
//============================================================================
// Database Configuration
	define('DB_TYPE',		'MYSQL');	// valid values: 'MYSQL', 'MSSQL'
	define('DB_NAME',		'miley');
	define('DB_USERNAME',	'miley');
	define('DB_PASSWORD',	'VjV2eu7Hbn');
	define('DB_HOST',		'127.0.0.1');
//============================================================================


//============================================================================
//============================================================================
// ApplicationRootDirectory: Path to the application root.  Handy when in
//	where the application root is varies from production to dev (example:
//	www.livefeed.com --> "/" is the application root
//	www.trueedgedevelopment.com/projects/livefeed/ --> "/projects/livefeed/"
	define('JPL_applicationRootDirectory', '/miley/');
	define('JPL_applicationRootServerName', '162.243.131.72');

	define('JPL_logFileDirectory', '\\..\\logs\\'); // must be relative to admin\includes
	define('JPL_applicationFileRoot', realpath(dirname(__FILE__) . '/../..') . '/');
	define('JPL_SSLIsAvailable', false);
	define('JPL_EmailSendingIsAvailable', false);
//============================================================================


//============================================================================
//============================================================================
// Installation instance management:
	define('JPL_isDevInstallation', false);
	define('JPL_isLocalInstallation', false);
	define('JPL_usePackedJavaScript', false);
//============================================================================

//============================================================================
//============================================================================
// Debug Configuration
	define('JPL_debuggerEmail', "evan@seehausenconsulting.com");
	define('JPL_revealBadSQL', true);
	define('JPL_emailBadSQL', false);
	define('JPL_revealErrorMessages', true);
	define('JPL_emailErrorMessages', false);
//============================================================================

//============================================================================
//============================================================================
// Google Analytics:
	define('JPL_GoogleAnalyticsCode', false); // e.g. "UA-1218379-13"
//============================================================================


//============================================================================
//============================================================================
// Set timezone:
	date_default_timezone_set('America/Denver');
//============================================================================

?>
