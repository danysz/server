<?php

OC::$CLASSPATH['OCA\Encryption\Crypt'] = 'files_encryption/lib/crypt.php';
OC::$CLASSPATH['OCA\Encryption\Hooks'] = 'files_encryption/hooks/hooks.php';
OC::$CLASSPATH['OCA\Encryption\Util'] = 'files_encryption/lib/util.php';
OC::$CLASSPATH['OCA\Encryption\Keymanager'] = 'files_encryption/lib/keymanager.php';
OC::$CLASSPATH['OCA\Encryption\Stream'] = 'files_encryption/lib/stream.php';
OC::$CLASSPATH['OCA\Encryption\Proxy'] = 'files_encryption/lib/proxy.php';
OC::$CLASSPATH['OCA\Encryption\Session'] = 'files_encryption/lib/session.php';
OC::$CLASSPATH['OCA\Encryption\Capabilities'] = 'files_encryption/lib/capabilities.php';

OC_FileProxy::register( new OCA\Encryption\Proxy() );

// User-related hooks
OCP\Util::connectHook( 'OC_User', 'post_login', 'OCA\Encryption\Hooks', 'login' );
OCP\Util::connectHook( 'OC_User', 'pre_setPassword', 'OCA\Encryption\Hooks', 'setPassphrase' );

// Sharing-related hooks
OCP\Util::connectHook( 'OCP\Share', 'post_shared', 'OCA\Encryption\Hooks', 'postShared' );
OCP\Util::connectHook( 'OCP\Share', 'post_unshare', 'OCA\Encryption\Hooks', 'postUnshare' );
OCP\Util::connectHook( 'OCP\Share', 'post_unshareAll', 'OCA\Encryption\Hooks', 'postUnshareAll' );

// Webdav-related hooks
OCP\Util::connectHook( 'OC_Webdav_Properties', 'update', 'OCA\Encryption\Hooks', 'updateKeyfileFromClient' );

stream_wrapper_register( 'crypt', 'OCA\Encryption\Stream' );
$view = new OC\Files\View('/');
$session = new OCA\Encryption\Session($view);

if ( 
	! $session->getPrivateKey( \OCP\USER::getUser() )
	&& OCP\User::isLoggedIn() 
	&& OCA\Encryption\Crypt::mode() == 'server' 
) {

	// Force the user to log-in again if the encryption key isn't unlocked 
	// (happens when a user is logged in before the encryption app is 
	// enabled)
	OCP\User::logout();
	
	header( "Location: " . OC::$WEBROOT.'/' );
	
	exit();

}

// Register settings scripts
OCP\App::registerAdmin( 'files_encryption', 'settings' );
OCP\App::registerPersonal( 'files_encryption', 'settings-personal' );
