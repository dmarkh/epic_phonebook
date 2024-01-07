<?php

 require_once( dirname(__FILE__) . '/core/singleton.php');
 require_once( dirname(__FILE__) . '/auth/Container.php');
 require_once( dirname(__FILE__) . '/auth/Auth.php');
 require_once( dirname(__FILE__) . '/auth/krb-auth-container.php');
 require_once( dirname(__FILE__) . '/core/visitor.php');

 $user =& singleton::getInstance('Visitor');
 if ( !$user->isAuthorized() ) {
	exit;
 }

 if ( isset($_GET['logout']) ) {
	$user->logout();
	header("Location: /");
	exit;
 }