<?php
ini_set("memory_limit","512M");

function mail_utf8($to, $subject = '(No subject)', $message = '', $header = '') {
  $header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
  mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
}

function prepare_description($m, $mfields, $inst, $ifields, $mfieldgrps) {

  $desc = array('email' => '', 'name' => '', 'data' => array() );
  $desc['email'] = $m['fields'][20];
  $desc['name'] = $m['fields'][1].' '.$m['fields'][3];

  if ( empty($m['fields'][10]) && empty($m['fields'][11]) && empty($m['fields'][12]) ) {
	  $m['fields'][10] = $inst[ $m['fields'][17] ]['fields'][10];
	  $m['fields'][11] = $inst[ $m['fields'][17] ]['fields'][11];
  }

  if ( empty($m['fields'][4]) )  { $m['fields'][4]  = $m['fields'][3]; }
  if ( empty($m['fields'][13]) ) { $m['fields'][13] = $inst[ $m['fields'][17] ]['fields'][12]; }
  if ( empty($m['fields'][14]) ) { $m['fields'][14] = $inst[ $m['fields'][17] ]['fields'][13]; }
  if ( empty($m['fields'][15]) ) { $m['fields'][15] = $inst[ $m['fields'][17] ]['fields'][14]; }
  if ( empty($m['fields'][16]) ) { $m['fields'][16] = $inst[ $m['fields'][17] ]['fields'][15]; }
  if ( empty($m['fields'][25]) ) { $m['fields'][25] = ( $inst[ $m['fields'][17] ]['fields'][8] ) ? ( $inst[ $m['fields'][17] ]['fields'][8] ) : ( $inst[ $m['fields'][17] ]['fields'][7] ); }

  foreach( $mfields as $k => $v ) {
	if ( $k == 17 ) {
	  $desc['data'][] = array( 0 => 'Home institution', 1 => $inst[ $m['fields'][17] ]['fields'][1] );
	} else {
	  $desc['data'][] = array( 0 => $mfields[$k]['name_desc'], 1 => ( !empty( $m['fields'][$k] ) ? $m['fields'][$k] : 'N/A' ) );
	}
  }

  return $desc;
}

function send_mail_to_representative($r, $ifields, $mfields) {

	if ( empty( $r ) || empty( $r['rep'] ) || empty( $r['rep']['email'] ) ) { return; }

    $to = trim($r['rep']['email']);
    $from = 'arkhipkin@bnl.gov';
    $subject = 'ePIC PhoneBook Group information - please review';

    $message = 'Dear '.$r['rep']['first_name'].' '.$r['rep']['last_name'].",\n\n"
			.'You are receiving this email as you are the IB representative for '
			.'the ePIC '.$r['inst'][2].' group to provide you with opportunity to'
			.' review and update your group\'s records.'."\n\n"
			.' This is an automatically generated'
			.' email showing the information we have for your group in the ePIC PhoneBook.'
			.' Please provide your corrections, if necessary, within two weeks from today. '
			.'Please do not remove or delete the original information but'."\n"
			.'- correct the fields that are incorrect by adding the proper information after the arrow "->"'."\n"
			.'- reply to this Email with the corrected information'."\n"
			.'If you have no corrections, you do NOT need to reply to this email.'."\n\n";

	// member information
	if (!empty($r['members'])) {
		$message .= '********* Group Member Info *********'."\n";
		$message .= ' Please review the list below carefully.'. "\n\n";

		usort($r['members'], 'mem_cmp');
		foreach($r['members'] as $k => $v) {
				$message .= $v[3].', '.$v[1]."\n";
		}
		$message .= "\n\n";

		$message .= 'If new members have appeared in your group, please send in the'
			.' following information: First Name, Last Name, Gender, Email address, Office phone number.'."\n\n";
	}

	// institution information
	$message .=	'********* Institution Information *********'."\n";
	$inst = $r['inst'];
	$flds = array(
		0 => 1,
		1 => 2,
		2 => 5,
		3 => 4,
		4 => 7,
		5 => 8,
		6 => 10,
		7 => 11,
		8 => 12,
		9 => 13,
		10 => 14,
		11 => 15,
		12 => 16,
		13 => 43,
		14 => 44
	);

	foreach($flds as $k => $v) {
		$message .= $ifields[$v]['name_desc'].' = '.$inst[$v]." -> \n";
		if (!empty($ifields[$v]['hint_full']) ) {
	  	$message .='('.$ifields[$v]['hint_full'].')'."\n";
		}
	  $message .= "\n";
	}

	// associated institutions information
	if (!empty($r['associated_institutions'])) {
		$message .= '******** Associated Institutions Info ********'."\n\n"
		.'Below are records for institutes associated to the '.$r['inst'][2].' institution.'
		.' You are equally responsible to communicate and update those records.'."\n\n";

		foreach($r['associated_institutions'] as $k => $v) {
			$message .= 'o '.$v['inst'][1]."\n";
		}
		$message .= "\n";

		foreach($r['associated_institutions'] as $k => $v) {
			// assoc member details
			usort($v['members'], 'mem_cmp');
			$message .= '*** '.$v['inst'][1].': members ***'."\n\n";
			foreach($v['members'] as $k2 => $v2) {
				$message .= $v2[3].', '.$v2[1]."\n";
			}
			$message .= "\n\n";

			// assoc institution details
			$message .= '*** '.$v['inst'][1].' ***'."\n\n";
			foreach($flds as $k2 => $v2) {
			  $message .= $ifields[$v2]['name_desc'].' = '.$v['inst'][$v2]." -> \n";
			  if ( !empty($ifields[$v2]['hint_full']) ) {
			  	$message .='('.$ifields[$v2]['hint_full'].')'."\n";
			  }
			  $message .= "\n";
			}
		}
	}

	// the end
	$message .= '*****************************************'."\n\n"
    .'We appreciate your efforts to keep our records accurate.'."\n"
	.'ePIC automated phonebook system';

  if (isset($from) and strlen($from)) {
		$additional  = 'From: ' . $from."\r\n";
		$additional .= 'Bcc: arkhipkin@bnl.gov' . "\r\n";
  }

	//$to = 'arkhipkin@bnl.gov'; // DEBUG, comment out when done testing

	if ( !empty($to) ) {
	  mail_utf8($to,$subject,$message,$additional);
	}

	return array('success' => true);
}

function mailr_send_handler($params) {

  $mem = file_get_contents('http://phonebook.sdcc.bnl.gov/epic/service/?q=/members/list/status:active/details:full&rnd='.time(0));
  $mem = json_decode($mem, true);

  $mfields = file_get_contents('http://phonebook.sdcc.bnl.gov/epic/service/?q=/service/list/object:fields/type:members&rnd='.time(0));
  $mfields = json_decode($mfields, true);

  unset($mfields[84]); unset($mfields[85]); unset($mfields[86]); unset($mfields[87]);
  unset($mfields[40]); unset($mfields[41]); unset($mfields[42]); unset($mfields[43]);
  unset($mfields[44]); unset($mfields[45]); unset($mfields[46]);

  $mfieldgrps = file_get_contents('http://phonebook.sdcc.bnl.gov/epic/service/?q=/service/list/object:fieldgroups/type:members&rnd='.time(0));
  $mfieldgrps = json_decode($mfieldgrps, true);

  $inst = file_get_contents('http://phonebook.sdcc.bnl.gov/epic/service/?q=/institutions/list/status:active/details:full&rnd='.time(0));
  $inst = json_decode($inst, true);

  $ifields = file_get_contents('http://phonebook.sdcc.bnl.gov/epic/service/?q=/service/list/object:fields/type:institutions&rnd='.time(0));
  $ifields = json_decode($ifields, true);

  // select active members who did not leave yet
  $memlist = array();
  foreach( $mem as $k => $v ) {
    $inst_id = ( $inst[ $mem[$k]['fields'][17] ]['fields'][45] ? $inst[ $mem[$k]['fields'][17] ]['fields'][45] : $mem[$k]['fields'][17] );
	if ( empty($v['fields'][17]) ) {
		//echo 'no institution assigned'."\n";
		unset($mem[$k]);
		continue;
	} 
	if ( empty($inst[$inst_id]) ) {
		//echo 'no institution exists'."\n";
		unset($mem[$k]);
		continue;
	}
	if ( !empty($inst[$inst_id]['fields'][42]) && ($inst[$inst_id]['fields'][42] != '0000-00-00 00:00:00') ) {
		//echo 'left star: '.print_r($inst[$inst_id]['fields'][42], true)."\n";
		unset($mem[$k]);
		continue;
	}
	if ( !empty( $v['fields'][85] )
		  && $v['fields'][85] != '0000-00-00 00:00:00'
      	  && (
			  ( time(0) - strtotime($v['fields'][85]) ) > 1
			)
		)
	{
		//echo 'left star over 6 months ago'."\n";
        unset($mem[$k]);
		continue;
    }
  }

  // filter institutions, remove ones without representative
  foreach($inst as $k => $v) {
	if ( !empty($v['fields'][45]) ) { continue; } // parent exists, representative is not needed
	if ( !empty($v['fields'][42]) && $v['fields'][42] != '0000-00-00 00:00:00' ) {
		unset($inst[$k]);
		continue;
	}
	if ( empty( $v['fields'][9] ) || $v['fields'][9] == "0" || $v['fields'][9] == 0 || $v['fields'][9] == "" ) {
		// echo 'no representative!'."\n";
		// print_r($inst[$k]);
		unset($inst[$k]);
		continue;
	}
	if ( empty( $mem[$v['fields'][9]] ) ) {
		// echo 'representative left STAR, id: '.$v['fields'][9]."\n";
		// print_r($inst[$k]);
		unset($inst[$k]);
		continue;
	}
  }

  $rep = array(); // by inst id
  foreach($inst as $k => $v) {
		if ( !empty($v['fields'][45]) ) {
			if ( empty($rep[ $v['fields'][45] ]) ) {
				echo 'adding dep array'."\n";
				$rep[$v['fields'][45]] = array();
				$rep[$v['fields'][45]]['associated_institutions'] = array();
			}
			$rep[$v['fields'][45]]['associated_institutions'][$k] = array(
				'inst' => $v['fields'],
				'members' => array()
			);
		} else {
			$rep[$k] = array();
			$tmp = $mem[ $v['fields'][9] ]['fields'];
			$rep[$k]['rep'] = array( 'first_name' => $tmp[1], 'last_name' => $tmp[3], 'email' => $tmp[20] );
			$rep[$k]['inst'] = $v['fields'];
			$rep[$k]['members'] = array();
			$rep[$k]['associated_institutions'] = array();
		}
  }

  foreach($mem as $k => $v) {
		$orig_id = intval($v['fields'][17]);
    $inst_id = ( $inst[ $v['fields'][17] ]['fields'][45] ? $inst[ $v['fields'][17] ]['fields'][45] : $v['fields'][17] );

	if ( !isset( $rep[$inst_id] ) ) { continue; } // no representative?

	if ( $inst_id != $orig_id ) { // associated member
		$rep[$inst_id]['associated_institutions'][$orig_id]['members'][$k] = $v['fields'];
	} else {
		$rep[$inst_id]['members'][$k] = $v['fields'];
	}
	// extra institutions:
	if ( !empty($v['fields'][89]) ) {
		$tmpinst = explode( ',', trim( $v['fields'][89] ) ); // ids of institutions
		$tmpinststr = array();
		foreach( $tmpinst as $tmpk => $tmpv ) { $tmpinststr[] = $inst[ intval($tmpv) ]['fields'][1]; }
		$tmpinststr = implode(', ', $tmpinststr); // text names of institutions
		// 1. notify representative of $inst_id about extra institutions
		// 1.1
		$rep[$inst_id]['extra-members'][$k] = array( 'flds' => $v['fields'], 'extra-institutions' => $tmpinststr );

		// 2. notify representatives of extra institutions about $orig_id
		// 2.1 convert extra ids into $inst_id
		foreach( $tmpinst as $tmpk => $tmpv ) {
			$tmpinst[$tmpk] = $inst[ $tmpv ]['fields'][45] ? $inst[ $tmpv ]['fields'][45] : $tmpv;
		}
		// 2.2 assign claim-member to reps
		foreach( $tmpinst as $tmpk => $tmpv ) {
			$rep[$tmpv]['claim-members'][$k] = array( 'flds' => $v['fields'], 'orig-institution' => $inst[$orig_id]['fields'][1] );
		}
	}
  }

  foreach( $rep as $k => $v ) {
		if ($k == 148) { // DEBUG, comment out when done testing. ePIC => BNL, id = 6
			send_mail_to_representative($v, $ifields, $mfields);
		}
  }

  return json_encode(array('success' => true));
  exit;
}

function mem_cmp($a, $b)
{
    return strcmp($a[3], $b[3]);
}