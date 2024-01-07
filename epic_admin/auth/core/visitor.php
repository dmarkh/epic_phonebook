<?php

function Visitor_Login($rusername, $rstatus, &$auth) {
	$status = '';
        if (!empty($rstatus) && $rstatus == AUTH_EXPIRED) {
            $status = '<i>Your session has expired. Please login again!</i>'."\n";
        } else if (!empty($rstatus) && $rstatus == AUTH_IDLED) {
            $status = '<i>You have been idle for too long. Please login again!</i>'."\n";
        } else if (!empty ($rstatus) && $rstatus == AUTH_WRONG_LOGIN) {
            $status = '<i>Wrong login data, please try again</i>'."\n";
        } else if (!empty ($rstatus) && $rstatus == AUTH_SECURITY_BREACH) {
            // $status = '<i>Security problem detected. </i>'."\n";
        }
    echo '
    <HTML> 
    <HEAD> 
    <TITLE>ePIC PHONEBOOK ADMIN CONSOLE</TITLE> 
    
    <BODY style="background-color: #CCC; font-family: verdana, helvetica, arial;display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100vw; height: 100vh;"> 
    '.$status.'
    <form method="post" action="">
    <table class="login_table" style="background-color: #FFF; margin: 10px; padding: 10px; border: 1px solid #000; box-shadow: 5px 5px #999;">
	<th colspan="2">ePIC PHONEBOOK ADMINISTRATION CONSOLE</th>
	<tr>
	    <td>ADMIN</td><td><input type="text" name="username" value="admin"></td>
	</tr>
	<tr><td>PINCODE</td><td><input type="password" name="password" placeholder="admin pincode"></td></tr>
	<tr><td colspan="2"><center><input type="submit" name="LOGIN" value="LOGIN"></center></td></tr>
    </table>
    </form>
    </BODY>
    ';    
}

function Visitor_SuccessfulLoginCallback($username, &$auth) {
    //dblogit('user <b>'.$username.'</b> just logged in', 'INFO');
}

function Visitor_SuccessfulLogoutCallback($username, &$auth) {
    //dblogit('user <b>'.$username.'</b> has logged out', 'INFO');
}

function Visitor_FailedLoginCallback($username, &$auth) {
    //dblogit('user <b>'.$username.'</b> has failed to log in', 'INFO');
	sleep(3);
}

class Visitor {
    
    var $ip = NULL;
    var $login = NULL;
    var $email = NULL;
    var $description = NULL;
    var $certificate = NULL;
    var $sha256 = NULL;
    var $fingerprint = NULL;
    var $admin_access = false;
    var $users = NULL;
    var $admins = NULL;
    
    var $pear_auth = NULL;
    
    var $db = NULL;

    function Visitor() {
			$this->checkAuthorization();
    }
    
    function logout() {
			if (isset($this->pear_auth) && !empty($this->pear_auth)) {
	    	$this->pear_auth->logout();
			}
    }
    
    function isAuthorized() {
			return $this->pear_auth->checkAuth();
    }
    
    function checkAuthorization() {
			$auth_container = new KRB_Auth_Container();
			$this->pear_auth = new Auth($auth_container, NULL, 'Visitor_Login');
			$this->pear_auth->setLoginCallback('Visitor_SuccessfulLoginCallback');
			$this->pear_auth->setLogoutCallback('Visitor_SuccessfulLogoutCallback');
			$this->pear_auth->setFailedLoginCallback('Visitor_FailedLoginCallback');
			$this->pear_auth->start();
    }    

}

