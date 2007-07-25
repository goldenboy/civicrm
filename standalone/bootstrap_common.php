<?php

// Pull in the settings file
if ( ! @require_once '../civicrm.settings.php' ) {
    // It seems we need to bootstrap this installation, so redirect there
    header("Location: new_install_setup.html");
    exit(0);
}

// Add the packages to the include path
$include_path = ini_get('include_path');
ini_set('include_path', "$civicrm_root:$civicrm_root/packages:$include_path");

// Start the session
session_start();

// Instantiate the config so that the DB connection will fire up
require_once 'CRM/Core/Config.php';
$config &= CRM_Core_Config::singleton( );

?>
