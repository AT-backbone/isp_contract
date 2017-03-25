<?php

/**
 *       \file       
 *       \brief      ispconfig Sync Iport/Export
 *       \version    $Id: ispconfig.class.php,v 2.9 2014/010/06 14:17:05 bbgs Exp $
 *       \author     Niklas Spanring / Guido Schratzer
 */
//require(DOL_DOCUMENT_ROOT."main.inc.php");

/**
 *	Put here description of your class
 */
class ispconfigsync extends CommonObject
{
	var $conf;
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)

	var $fk_soap_location;
	var $fk_soap_uri;
	var $fk_username;
	var $fk_password;


		function Test_ausgabe()
		{	
			global $conf;
			echo '<br>';
			echo $this->fk_soap_location;
			echo '<br>';
			echo $this->fk_soap_uri;
			echo '<br>';
			echo $this->fk_username;
			echo '<br>';
			echo $this->fk_password; 
			echo '<br>';
		}
		function set_login($rowid="")
		{	
			global $conf, $db;

			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."custominfo_ISPc_server ";

			if($rowid > 0){
				$sql.= " Where rowid = ".$rowid." ";
			}else{
				$sql.= " Where default_server = 'yes' ";
			}

			$resql=$db->query($sql);
			$object = $db->fetch_object($resql);

			$this->fk_soap_location = $object->soap_location;
			$this->fk_soap_uri 		= $object->soap_uri;
			$this->fk_username 		= $object->username;
			$this->fk_password 		= $object->password;
			//echo $object->soap_location;

		}
		function get_funktionen(){
			
			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo '<br>Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				//* Set the function parameters.
			
				$record_record = $client->get_function_list($session_id);
				
				echo "<br>";
				print_r($record_record);
				echo "<br>";
			
				if($client->logout($session_id)) {
					//echo '<br>Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
		}
		function get_client_groupid($client_id){
			
			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo '<br>Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				//* Set the function parameters.
			
				$record_record = $client->client_get_groupid($session_id, $client_id);
				
				//echo "<br>";
				//print_r($record_record);
				//echo "<br>";
			
				if($client->logout($session_id)) {
					//echo '<br>Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}

			return $record_record;
		}
		
		// Output: Info
		function get_all_Client() {

			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 		

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				$record_record = $client->client_get_all($session_id);
				
				//echo '<br>';
				//print_r($record_record);
				//echo '<br>';
				
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				//echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
			return $record_record;
			
		}
		function get_Client_u_id($id){

			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				//* Set the function parameters.
				$var_client_id = $id;
			
				$client_record = $client->client_get($session_id, $var_client_id);
			
				//print_r($client_record);
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}

			return $client_record;
		}

		// Imput: 	Name
		// Output: 	Info
		function get_Client_by_name($name="")
		{
			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				//* Set the function parameters.
				$username = $name;
			
				$record_record = $client->client_get_by_username($session_id, $username);
				print_r($record_record);
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
		}

		function sync_objekt_of_Client($socid, $object, $server){
			global $db;

				$sql = "UPDATE ".MAIN_DB_PREFIX."custominfo_ISPc_info ";
				$sql.= " SET ";

				$sql.= $sql = " fk_company_name =  '".$object->name."', ";
				$sql.= $sql = " fk_customer_no =  '".$object->code_client."', ";
				$sql.= $sql = " fk_zip =  '".$object->zip."', ";
				$sql.= $sql = " fk_street =  '".$object->address."', ";
				$sql.= $sql = " fk_city =  '".$object->town."', ";
				$sql.= $sql = " fk_telephone =  '".$object->tel."', ";
				$sql.= $sql = " fk_mobile =  '".$object->phone."', ";
				$sql.= $sql = " fk_fax =  '".$object->fax."', ";
				$sql.= $sql = " fk_email =  '".$object->email."' ";

				$sql.= " WHERE  fk_socid = ".$fk_socid;
				if($server){
				$sql.= " AND fk_server_rowid = ".$server;
				}
				//echo($sql);
				$resql=$db->query($sql);

		}

		// Input:	SYS ID
		// Ouput:	User ID
		function get_client_id_ISP($sys_userid)
		{
			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				$client_record = $client->client_get_id($session_id, $sys_userid);
			
				print_r($client_record);
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
		}

		//
		function get_Client_sites($sys_userid, $sys_groupid, $type){

			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				$record_record = $client->client_get_sites_by_user($session_id, $sys_userid, $sys_groupid, $type);
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
			return $record_record;
		}

		function get_domain_fields($domain_id){
			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}

			
				$domain_record = $client->sites_web_domain_get($session_id, $domain_id);
			
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}

			return $domain_record;
		}

		function get_ftp_user($ftp_user_name){

			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 
			
			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				//* Set the function parameters.
			
				$ftp_user_record = $client->sites_ftp_user_server_get($session_id, $ftp_user_name);
			
				//print_r($ftp_user_record);
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}

			return $ftp_user_record;

		}

		// Imput: 	Array mit angaben echo eingetragen werden sollen , $test_for_exist = 1 wenn ausgiebig getestet werden soll befohr gesepichert wird
		// Output: 	Info
		// Func: 	SYNC to Dollibar
		function SYNC_ALL_ISP_TO_DOLL($fk_socid, $client_id, $server)
		{
			global $conf, $db;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				//* Set the function parameters.
				$var_client_id = $client_id;
			
				$client_record = $client->client_get($session_id, $var_client_id);

				$sql = "UPDATE ".MAIN_DB_PREFIX."custominfo_ISPc_info ";
				$sql.= " SET ";
				foreach($client_record as $label => $data){
					$sql.= $this->set_soc_doll_ISP_label($label, $data);
				}
				$sql = substr($sql, 0, -2);
				$sql.= " WHERE  fk_socid = ".$fk_socid." AND client_id = ".$client_id;
				if($server){
				$sql.= " AND fk_server_rowid = ".$server;
				}
				//echo($sql);
				$resql=$db->query($sql);
				
				//print_r($client_record);
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
		}

		// Imput: 	label und socid wie auch client id
		// Output: 	Info
		// Func: 	SYNC to Dollibar
		function SYNC_LABEL_TO_DOLL($label, $fk_socid, $client_id, $server)
		{
			global $conf, $db;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				//* Set the function parameters.
				$var_client_id = $client_id;
			
				$client_record = $client->client_get($session_id, $var_client_id);

				$sql = "UPDATE ".MAIN_DB_PREFIX."custominfo_ISPc_info ";
				$sql.= " SET ";
				foreach($client_record as $label_ISP => $data){
					$sql.= $this->set_soc_doll_ISP_label($label_ISP, $data);					
				}
				$sql = substr($sql, 0, -2);
				$sql.= " WHERE  fk_socid = ".$fk_socid." AND client_id = ".$client_id;
				if($server){
				$sql.= " AND fk_server_rowid = ".$server;
				}
				
				//echo($sql);
				$resql=$db->query($sql);
				
				//print_r($client_record);
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
		}

		function set_soc_doll_ISP_label($label, $data){
			switch ($label) {
			
           	case 'company_name':
           	    $sql = " fk_".$label." =  '".$data."', ";
           	    break;

           	case 'customer_no':
           	    $sql = " fk_".$label." =  '".$data."', ";
           	    break;

           	case 'street':
           	    $sql = " fk_".$label." =  '".$data."', ";
           	    break;

           	case 'zip':
           	    $sql = " fk_".$label." =  '".$data."', ";
           	    break;

           	case 'city':
           	   	$sql = " fk_".$label." =  '".$data."', ";
           	    break;

           	case 'telephone':
           	    $sql = " fk_".$label." =  '".$data."', ";
           	    break;

           	case 'mobile':
           	    $sql = " fk_".$label." =  '".$data."', ";
           	    break;

           	case 'fax':
           	    $sql = " fk_".$label." =  '".$data."', ";
           	    break;

           	case 'email':
           	    $sql = " fk_".$label." =  '".$data."', ";
           	    break;

           	default:
           		$sql = " ".$label." =  '".$data."', ";
           		break;
           }
           return $sql;
		}

		function make_soc_doll_ISP_label($label, $data){
			switch ($label) {
			
           	case 'fk_company_name':
           	    $or_label = substr($label, 3);
           	    break;

           	case 'fk_customer_no':
           	    $or_label = substr($label, 3);
           	    break;

           	case 'fk_street':
           	    $or_label = substr($label, 3);
           	    break;

           	case 'fk_zip':
           	    $or_label = substr($label, 3);
           	    break;

           	case 'fk_city':
           	   	$or_label = substr($label, 3);
           	    break;

           	case 'fk_telephone':
           	    $or_label = substr($label, 3);
           	    break;

           	case 'fk_mobile':
           	    $or_label = substr($label, 3);
           	    break;

           	case 'fk_fax':
           	    $or_label = substr($label, 3);
           	    break;

           	case 'fk_email':
           	    $or_label = substr($label, 3);
           	    break;

           	default:
           		$or_label = $label;
           		break;
           }
           return $or_label;
		}
		// Imput: 	Array mit angaben echo eingetragen werden sollen , $test_for_exist = 1 wenn ausgiebig getestet werden soll befohr gesepichert wird
		// Output: 	Info
		// Func: 	SYNC to Dollibar
		function SYNC_ALL_DOLL_TO_ISP($socid, $client_id, $ISPobject)
		{
			global $conf, $db;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}

				$reseller_id = 1;
			
				//* Get the client record
				$client_record = $client->client_get($session_id, $client_id);
			
				foreach($ISPobject as $label => $data){
					$label = $this->make_soc_doll_ISP_label($label, $data);
					$client_record[$label] = $data;
				}
			
				$affected_rows = $client->client_update($session_id, $client_id, $reseller_id, $client_record);

				//print_r($client_record);
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
		}

		// Imput: 	label und socid wie auch client id
		// Output: 	Info
		// Func: 	SYNC to Dollibar
		function SYNC_LABEL_TO_ISP($label, $fk_socid, $client_id, $ISPobject)
		{
			global $conf, $db;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}

				$reseller_id = 1;
			
				//* Get the client record
				$client_record = $client->client_get($session_id, $client_id);
			
				foreach($ISPobject as $label_DOL => $data){
					$label_DOL = $this->make_soc_doll_ISP_label($label_DOL, $data);
					if($label == $label_DOL){
						$client_record[$label] = $data;
					}					
				}
			
				$affected_rows = $client->client_update($session_id, $client_id, $reseller_id, $client_record);

				//print_r($client_record);
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
		}

		// Imput: 	Array mit angaben echo eingetragen werden sollen , $test_for_exist = 1 wenn ausgiebig getestet werden soll befohr gesepichert wird
		// Output: 	Info
		// Func: 	make a Client on ISPconfig3
		function add_Client($prop_arry="")
		{
			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 
			
			$TEST = $this->get_Client_by_name($prop_arry['username']);
			if($TEST != ""){
				return -1;
			}

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				//* Set the function parameters.
				$reseller_id = 0; // this id has to be 0 if the client shall not be assigned to admin or if the client is a reseller
			
				$affected_rows = $client->client_add($session_id, $reseller_id, $prop_arry);
			
				echo "Client: ".$affected_rows."<br>";
			
			
				if($client->logout($session_id)) {
					echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
			
		}

		/*
		 *  Input: 		client id, reseller id, Array mit allen angaben echo verändert werden soll
		 *  Output: 	info
		 *  func:  		Syncronisiert alle daten des users zu ISPconfig
		 *  EXPORT
		 */
		function Sync_ISP_Client_Export($client_id = "", $reseller_id = "", $params = "")
		{
			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}		
			
				//* Get the client record
				$params = $client->client_get($session_id, $client_id);
			
				//* Change parameters
																									// BEISPIEL DATEN!!!
				$params['company_name'] 			= $prop_arry['company_name'];					//	=> 'awesomecompany'
				$params['contact_name'] 			= $prop_arry['contact_name'];					//	=> 'name'
				$params['customer_no'] 				= $prop_arry['customer_no'];					//	=> '1'
				$params['vat_id'] 					= $prop_arry['vat_id'];						 	//	=> '1'
				$params['street'] 					= $prop_arry['street'];						 	//	=> 'fleetstreet'
				$params['zip'] 						= $prop_arry['zip'];						 	//	=> '21337'
				$params['city'] 					= $prop_arry['city'];						 	//	=> 'london'
				$params['state'] 					= $prop_arry['state'];						 	//	=> 'bavaria'
				$params['country'] 					= $prop_arry['country'];						//	=> 'GB'
				$params['telephone'] 				= $prop_arry['telephone'];						//	=> '123456789'
				$params['mobile'] 					= $prop_arry['mobile'];							//	=> '987654321'
				$params['fax'] 						= $prop_arry['fax'];						 	//	=> '546718293'
				$params['email'] 					= $prop_arry['email'];						 	//	=> 'e@mail.int'
				$params['internet'] 				= $prop_arry['internet'];						//	=> ''
				$params['icq'] 						= $prop_arry['icq'];						 	//	=> '111111111'
				$params['notes'] 					= $prop_arry['notes'];						 	//	=> 'awesome'
				$params['default_mailserver'] 		= $prop_arry['default_mailserver'];				//	=> 1
				$params['limit_maildomain'] 		= $prop_arry['limit_maildomain'];				//	=> -1
				$params['limit_mailbox'] 			= $prop_arry['limit_mailbox'];					//	=> -1
				$params['limit_mailalias'] 			= $prop_arry['limit_mailalias'];				//	=> -1
				$params['limit_mailaliasdomain'] 	= $prop_arry['limit_mailaliasdomain'];			//	=> -1
				$params['limit_mailforward'] 		= $prop_arry['limit_mailforward'];				//	=> -1
				$params['limit_mailcatchall'] 		= $prop_arry['limit_mailcatchall'];				//	=> -1
				$params['limit_mailrouting'] 		= $prop_arry['limit_mailrouting'];				//	=> 0
				$params['limit_mailfilter'] 		= $prop_arry['limit_mailfilter'];				//	=> -1
				$params['limit_fetchmail'] 			= $prop_arry['limit_fetchmail'];				//	=> -1
				$params['limit_mailquota'] 			= $prop_arry['limit_mailquota'];				//	=> -1
				$params['limit_spamfilter_wblist'] 	= $prop_arry['limit_spamfilter_wblist'];		//	=> 0
				$params['limit_spamfilter_user'] 	= $prop_arry['limit_spamfilter_user'];			//	=> 0
				$params['limit_spamfilter_policy'] 	= $prop_arry['limit_spamfilter_policy'];		//	=> 1
				$params['default_webserver'] 		= $prop_arry['default_webserver'];				//	=> 1
				$params['limit_web_ip'] 			= $prop_arry['limit_web_ip'];					//	=> ''
				$params['limit_web_domain'] 		= $prop_arry['limit_web_domain'];				//	=> -1
				$params['limit_web_quota'] 			= $prop_arry['limit_web_quota'];				//	=> -1
				$params['web_php_options'] 			= $prop_arry['web_php_options'];				//	=> 'no,fast-cgi,cgi,mod,suphp'
				$params['limit_web_subdomain'] 		= $prop_arry['limit_web_subdomain'];			//	=> -1
				$params['limit_web_aliasdomain'] 	= $prop_arry['limit_web_aliasdomain'];			//	=> -1
				$params['limit_ftp_user'] 			= $prop_arry['limit_ftp_user'];					//	=> -1
				$params['limit_shell_user'] 		= $prop_arry['limit_shell_user'];				//	=> 0
				$params['ssh_chroot'] 				= $prop_arry['ssh_chroot'];						//	=> 'no,jailkit,ssh-chroot'
				$params['limit_webdav_user'] 		= $prop_arry['limit_webdav_user'];				//	=> 0
				$params['default_dnsserver'] 		= $prop_arry['default_dnsserver'];				//	=> 1
				$params['limit_dns_zone'] 			= $prop_arry['limit_dns_zone'];					//	=> -1
				$params['limit_dns_slave_zone'] 	= $prop_arry['limit_dns_slave_zone'];			//	=> -1
				$params['limit_dns_record'] 		= $prop_arry['limit_dns_record'];				//	=> -1
				$params['default_dbserver'] 		= $prop_arry['default_dbserver'];				//	=> 1
				$params['limit_database'] 			= $prop_arry['limit_database'];					//	=> -1
				$params['limit_cron'] 				= $prop_arry['limit_cron'];						//	=> 0
				$params['limit_cron_type']			= $prop_arry['limit_cron_type'];				//	=> 'url'
				$params['limit_cron_frequency'] 	= $prop_arry['limit_cron_frequency'];			//	=> 5
				$params['limit_traffic_quota'] 		= $prop_arry['limit_traffic_quota'];			//	=> -1
				$params['limit_client'] 			= $prop_arry['limit_client'];					//	=> 0, // If this value is > 0, then the client is a reseller
				$params['parent_client_id'] 		= $prop_arry['parent_client_id'];				//	=> 0
				$params['username'] 				= $prop_arry['username'];						//	=> 'guy3'
				$params['password']					= $prop_arry['password'];						//	=> '' wenn nicht geändert werden soll
				$params['language'] 				= $prop_arry['language'];						//	=> 'en'
				$params['usertheme'] 				= $prop_arry['usertheme'];						//	=> 'default'
				$params['template_master'] 			= $prop_arry['template_master'];				//	=> 0
				$params['template_additional'] 		= $prop_arry['template_additional'];			//	=> ''
				$params['created_at'] 				= $prop_arry['created_at'];						//	=> 0
			
			
				$affected_rows = $client->client_update($session_id, $c_id, $reseller_id, $params);
			
				echo "Number of records that have been changed in the database: ".$affected_rows."<br>";
			
				if($client->logout($session_id)) {
					echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
		}


		// Input: 	name, client id
		// Output: 	info
		// func:  	Syncronisiert alle daten des users zu Dolibar
		// IMPORT

		function Sync_DOL_Client_Import($name = "", $client_id_ISP = "")
		{
			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				//* Set the function parameters.
				$username = $name;
				if($name){
					$record_record = $client->client_get_by_username($session_id, $username);
				}else{
					$record_record = $client->client_get($session_id, $client_id_ISP);
				}

				/*
				 *	FUNCTION echo echo eingelesenen DATEN in DOLIBAR intigriert / updatet
				 *
				 *	ID oder name
				 */
			
				print_r($record_record);
			
				if($client->logout($session_id)) {
					echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
		}

		// Input:	ID
		// Output:	info
		function Del_Client_ISP($id)
		{
			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
					
				//* Delete client
				$affected_rows = $client->client_delete($session_id, $id);
			
				echo "Number of records that have been deleted: ".$affected_rows."<br>";
			
				if($client->logout($session_id)) {
					echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
		}

		// Input:	ID
		// Output:	info
		function Del_Client_allofit_ISP($client_id)
		{
			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}			
			
				//* Delete client
				$affected_rows = $client->client_delete_everything($session_id, $client_id);
			
				//echo "Client ".$client_id." has been deleted.<br>";
				//print_r($affected_rows);
			
				if($client->logout($session_id)) {
					echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
		}
		function fetch_contact($socid){
			global $db;
			$sql = "SELECT name, firstname 
 					FROM llx_socpeople as c
 					WHERE rowid = ".$socid."'";
 			$resql=$db->query($sql);
			if ($resql){
				$contact = $db->fetch_object($resql);
				$contact_name = $contact->name.' '.$contact->firstname;
			}
			return $contact_name;
		}
		function make_soc_to_ISP($prop_arry, $socid){
				require_once DOL_DOCUMENT_ROOT .'/core/lib/security2.lib.php'; 

				$con_name = $this->fetch_contact($socid);

				$user = str_replace(' ','',$prop_arry->name);
				$name = $prop_arry->name;

				$params['company_name'] = $name;	
			
				if($con_name == ""){
					$params['contact_name'] = $name;
				}else{
					$params['contact_name'] = $con_name;
				}
				$params['customer_no'] 	= $prop_arry->code_client;


				$params['street'] 		= $prop_arry->address;	
				$params['zip'] 			= $prop_arry->zip;			
				$params['city'] 		= $prop_arry->town;			

				$params['country'] 		= $prop_arry->country_code;	
				$params['telephone'] 	= $prop_arry->tel;		
				$params['mobile'] 		= $prop_arry->phone;		
				$params['fax'] 			= $prop_arry->fax;			
				$params['email'] 		= $prop_arry->email;			

				$params['username'] 	= $prop_arry->code_client;	

				$params['password']		= getRandomPassword(false);	// generirtes Passwort --> function auf doll speichern

				
			// -------------------------------------------------------------------
				$params['vat_id']					= '0';
				$params['state']					= '';
				$params['internet']					= '';
				$params['icq']						= '';
				$params['notes']					= '';
				$params['default_mailserver']		= '0';
				$params['limit_maildomain']			= '0';
				$params['limit_mailbox']			= '0';
				$params['limit_mailalias']			= '0';
				$params['limit_mailaliasdomain']	= '0';
				$params['limit_mailforward']		= '0';
				$params['limit_mailcatchall']		= '0';
				$params['limit_mailrouting']		= '0';
				$params['limit_mailfilter']			= '0';
				$params['limit_fetchmail']			= '0';
				$params['limit_mailquota']			= '0';
				$params['limit_spamfilter_wblist']	= '0';
				$params['limit_spamfilter_user']	= '0';
				$params['limit_spamfilter_policy']	= '0';
				$params['default_webserver']		= '0';
				$params['limit_web_ip']				= '';
				$params['limit_web_domain']			= '0';
				$params['limit_web_quota']			= '0';
				$params['web_php_options']			= 'no';
				$params['limit_web_subdomain']		= '0';
				$params['limit_web_aliasdomain']	= '0';
				$params['limit_ftp_user']			= '0';
				$params['limit_shell_user']			= '0';
				$params['ssh_chroot']				= 'no,ssh-chroot';
				$params['limit_webdav_user']		= '0';
				$params['default_dnsserver']		= '0';
				$params['limit_dns_zone']			= '0';
				$params['limit_dns_slave_zone']		= '0';
				$params['limit_dns_record']			= '0';
				$params['default_dbserver']			= '0';
				$params['limit_database']			= '0';
				$params['limit_cron']				= '0';
				$params['limit_cron_type']			= 'url';
				$params['limit_cron_frequency']		= '5';
				$params['limit_traffic_quota']		= '0';
				$params['limit_client']				= '0';
				$params['parent_client_id']			= '0';
				$params['language']					= 'en';
				$params['usertheme']				= 'default';
				$params['template_master']			= '1';
				$params['template_additional']		= '';
				$params['created_at']				= '0';
			
				return $params;
		}

		// Input: 	Name , fetched socid, ISP fetchet id
		// Output:  Name;
		function label_switch($label, $object, $ISPobject){

				switch ($label) {		
                    case 'company_name':
                   		if($ISPobject->fk_company_name != ""){
                        	$aus_c = $ISPobject->fk_company_name;
                        	$aus_n = "Name:";
                        }else{
                        	$aus_c = $object->name;
                        	$aus_n = "Name:";
                    	}
                        break;

                    case 'customer_no':
                    	if($ISPobject->fk_customer_no != ""){
                        	$aus_c = $ISPobject->fk_customer_no;
                        	$aus_n = "Kunden Nummer:";
                        }else{
                        	$aus_c = $object->code_client;
                        	$aus_n = "Kunden Nummer:";
                    	}
                        break;

                    case 'zip':
                    	if($ISPobject->fk_zip != ""){
                    		$aus_c = $ISPobject->fk_zip;
                        	$aus_n = "Postleitzahl:";
                    	}else{
                        	$aus_c = $object->zip;
                        	$aus_n = "Postleitzahl:";
                    	}
                        break;

                    case 'street':
                    	if($ISPobject->fk_street != ""){
                        	$aus_c = $ISPobject->fk_street;
                        	$aus_n = "Address:";
                        }else{
                        	$aus_c = $object->address;
                        	$aus_n = "Address:";
                    	}
                        break;

                    case 'city':
                    	if($ISPobject->fk_city != ""){
                       		$aus_c = $ISPobject->fk_city;
                       		$aus_n = "Stadt:";
                        }else{
                        	$aus_c = $object->town;
                        	$aus_n = "Stadt:";
                    	}
                        break;

                    case 'telephone':
                    	if($ISPobject->fk_telephone != ""){
                       		$aus_c = $ISPobject->fk_telephone;
                       		$aus_n = "Telephone:";
                        }else{
                        	$aus_c = $object->tel;
                        	$aus_n = "Telephone:";
                    	}
                        break;

                    case 'mobile':
                    	if($ISPobject->fk_mobile != ""){
                        	$aus_c = $ISPobject->fk_mobile;
                        	$aus_n = "Phone:";
                        }else{
                        	$aus_c = $object->phone;
                        	$aus_n = "Phone:";
                    	}
                        break;

                    case 'fax':
                    	if($ISPobject->fk_fax != ""){
                        	$aus_c = $ISPobject->fk_fax;
                        	$aus_n = "Fax:";
                        }else{
                        	$aus_c = $object->fax;
                        	$aus_n = "Fax:";
                    	}
                        break;

                    case 'email':
                    	if($ISPobject->fk_email != ""){
                        	$aus_c = $ISPobject->fk_email;
                        	$aus_n = "Email:";
                        }else{
                        	$aus_c = $object->email;
                        	$aus_n = "Email:";
                    	}
                        break;
                // -------------------- BEGIN ISO OBJECKT !! ----------------------------
                    case 'username':
                        $aus_c = $ISPobject->username;
                        $aus_n = "Username:";
                        break;
                        
                    case 'password':
                        $aus_c = $ISPobject->password;
                        $aus_n = "Password:";
                        break;

                    case 'vat_id':
                        $aus_c = $ISPobject->vat_id;
                        $aus_n = "Vat_id:";
                        break;

                    case 'state':
                        $aus_c = $ISPobject->state;
                        $aus_n = "State:";
                        break;
                        
                    case 'internet':
                        $aus_c = $ISPobject->internet;
                        $aus_n = "Internet:";
                        break;

                    case 'icq':
                        $aus_c = $ISPobject->icq;
                        $aus_n = "Icq:";
                        break;

                    case 'notes':
                        $aus_c = $ISPobject->notes;
                        $aus_n = "Notes:";
                        break;

                    case 'default_mailserver':
                        $aus_c = $ISPobject->default_mailserver;
                        $aus_n = "Default mailserver:";
                        break;

                    case 'limit_maildomain':
                        $aus_c = $ISPobject->limit_maildomain;
                        $aus_n = "Limit maildomain:";
                        break;

                    case 'limit_mailbox':
                        $aus_c = $ISPobject->limit_mailbox;
                        $aus_n = "Limit mailbox:";
                        break;

                    case 'limit_mailalias':
                        $aus_c = $ISPobject->limit_mailalias;
                        $aus_n = "Limit mailalias:";
                        break;

                    case 'limit_mailaliasdomain':
                        $aus_c = $ISPobject->limit_mailaliasdomain;
                        $aus_n = "Limit mailaliasdomain:";
                        break;

                    case 'limit_mailforward':
                        $aus_c = $ISPobject->limit_mailforward;
                        $aus_n = "Limit mailforward:";
                        break;

                    case 'limit_mailcatchall':
                        $aus_c = $ISPobject->limit_mailcatchall;
                        $aus_n = "Limit mailcatchall:";
                        break;

                    case 'limit_mailrouting':
                        $aus_c = $ISPobject->limit_mailrouting;
                        $aus_n = "Limit mailrouting:";
                        break;
                        
                    case 'limit_mailfilter':
                        $aus_c = $ISPobject->limit_mailfilter;
                        $aus_n = "Limit mailfilter:";
                        break;

                    case 'limit_fetchmail':
                        $aus_c = $ISPobject->limit_fetchmail;
                        $aus_n = "Limit fetchmail:";
                        break;

                    case 'limit_mailquota':
                        $aus_c = $ISPobject->limit_mailquota;
                        $aus_n = "Limit mailquota:";
                        break;

                    case 'limit_spamfilter_wblist':
                        $aus_c = $ISPobject->limit_spamfilter_wblist;
                        $aus_n = "Limit spamfilter wblist:";
                        break;

                    case 'limit_spamfilter_user':
                        $aus_c = $ISPobject->limit_spamfilter_user;
                        $aus_n = "Limit spamfilter user:";
                        break;

                    case 'limit_spamfilter_policy':
                        $aus_c = $ISPobject->limit_spamfilter_policy;
                        $aus_n = "Limit spamfilter mpolicy:";
                        break;

                    case 'default_webserver':
                        $aus_c = $ISPobject->default_webserver;
                        $aus_n = "Default webserver:";
                        break;

                    case 'limit_web_ip':
                        $aus_c = $ISPobject->limit_web_ip;
                        $aus_n = "Limit web ip:";
                        break;

                    case 'limit_web_domain':
                        $aus_c = $ISPobject->limit_web_domain;
                        $aus_n = "Limit web domain:";
                        break;

                    case 'limit_web_quota':
                        $aus_c = $ISPobject->limit_web_quota;
                        $aus_n = "Limit web quota:";
                        break;

                    case 'web_php_options':
                        $aus_c = $ISPobject->web_php_options;
                        $aus_n = "Web php options:";
                        break;
                        
                    case 'limit_web_subdomain':
                        $aus_c = $ISPobject->limit_web_subdomain;
                        $aus_n = "Limit web subdomain:";
                        break;

                    case 'limit_web_aliasdomain':
                        $aus_c = $ISPobject->limit_web_aliasdomain;
                        $aus_n = "Limit web aliasdomain:";
                        break;

                    case 'limit_ftp_user':
                        $aus_c = $ISPobject->limit_ftp_user;
                        $aus_n = "Limit ftp user:";
                        break;

                    case 'limit_shell_user':
                        $aus_c = $ISPobject->limit_shell_user;
                        $aus_n = "Limit shell user:";
                        break;

                    case 'ssh_chroot':
                        $aus_c = $ISPobject->ssh_chroot;
                        $aus_n = "ssh chroot:";
                        break;

                    case 'limit_webdav_user':
                        $aus_c = $ISPobject->limit_webdav_user;
                        $aus_n = "Limit webdav user:";
                        break;

                    case 'default_dnsserver':
                        $aus_c = $ISPobject->default_dnsserver;
                        $aus_n = "Default dnsserver:";
                        break;

                    case 'limit_dns_zone':
                        $aus_c = $ISPobject->limit_dns_zone;
                        $aus_n = "Limit dns zone:";
                        break;

                    case 'limit_dns_slave_zone':
                        $aus_c = $ISPobject->limit_dns_slave_zone;
                        $aus_n = "Limit dns slave zone:";
                        break;

                    case 'limit_dns_record':
                        $aus_c = $ISPobject->limit_dns_record;
                        $aus_n = "Limit dns record:";
                        break;

                    case 'default_dbserver':
                        $aus_c = $ISPobject->default_dbserver;
                        $aus_n = "Default dbserver:";
                        break;
                        
                    case 'limit_database':
                        $aus_c = $ISPobject->limit_database;
                        $aus_n = "Limit database:";
                        break;

                    case 'limit_cron':
                        $aus_c = $ISPobject->limit_cron;
                        $aus_n = "Limit cron:";
                        break;

                    case 'limit_cron_type':
                        $aus_c = $ISPobject->limit_cron_type;
                        $aus_n = "Limit cron type:";
                        break;

                    case 'limit_cron_frequency':
                        $aus_c = $ISPobject->limit_cron_frequency;
                        $aus_n = "Limit cron frequency:";
                        break;

                    case 'limit_traffic_quota':
                        $aus_c = $ISPobject->limit_traffic_quota;
                        $aus_n = "Limit traffic quota:";
                        break;

                    case 'limit_client':
                        $aus_c = $ISPobject->limit_client;
                        $aus_n = "Limit client:";
                        break;

                    case 'parent_client_id':
                        $aus_c = $ISPobject->parent_client_id;
                        $aus_n = "Parent client id:";
                        break;

                    case 'language':
                        $aus_c = $ISPobject->language;
                        $aus_n = "Language:";
                        break;

                    case 'usertheme':
                        $aus_c = $ISPobject->usertheme;
                        $aus_n = "Usertheme:";
                        break;

                    case 'template_master':
                        $aus_c = $ISPobject->template_master;
                        $aus_n = "Template master:";
                        break;

                    case 'template_additional':
                        $aus_c = $ISPobject->template_additional;
                        $aus_n = "Template additional:";
                        break;

                    case 'created_at':
                        $aus_c = $ISPobject->created_at;
                        $aus_n = "Created at:";
                        break;

                    case 'client_id':
                        $aus_c = $ISPobject->client_id;
                        $aus_n = "Client id:";
                        break;
                
                    case 'sys_userid':
                        $aus_c = $ISPobject->sys_userid;
                        $aus_n = "Sys userid:";
                        break;
                
                    case 'sys_groupid':
                        $aus_c = $ISPobject->sys_groupid;
                        $aus_n = "Sys groupid:";
                        break;
                
                    case 'sys_perm_user':
                        $aus_c = $ISPobject->sys_perm_user;
                        $aus_n = "Sys perm user:";
                        break;
                
                    case 'sys_perm_group':
                        $aus_c = $ISPobject->sys_perm_group;
                        $aus_n = "Sys perm group:";
                        break;
                
                    case 'sys_perm_other':
                        $aus_c = $ISPobject->sys_perm_other;
                        $aus_n = "Sys perm other:";
                        break;
                
                    case 'company_id':
                        $aus_c = $ISPobject->company_id;
                        $aus_n = "Company id:";
                        break;
                
                    case 'gender':
                        $aus_c = $ISPobject->gender;
                        $aus_n = "Gender:";
                        break;
                
                    case 'contact_name':
                        $aus_c = $ISPobject->contact_name;
                        $aus_n = "Contact name:";
                        break;   
                
                    case 'country':
                        $aus_c = $ISPobject->country;
                        $aus_n = "country:";
                        break;   
                
                    case 'bank_account_owner':
                        $aus_c = $ISPobject->bank_account_owner;
                        $aus_n = "Bank account owner:";
                        break;
                
                    case 'bank_account_number':
                        $aus_c = $ISPobject->bank_account_number;
                        $aus_n = "Bank account number:";
                        break;
                
                    case 'bank_code':
                        $aus_c = $ISPobject->bank_code;
                        $aus_n = "Bank code:";
                        break; 
                
                    case 'bank_name':
                        $aus_c = $ISPobject->bank_name;
                        $aus_n = "Bank name:";
                        break;
                
                    case 'bank_account_iban':
                        $aus_c = $ISPobject->bank_account_iban;
                        $aus_n = "Bank account iban:";
                        break;
                
                    case 'bank_account_swift':
                        $aus_c = $ISPobject->bank_account_swift;
                        $aus_n = "Bank account swift:";
                        break; 
                
                    case 'paypal_email':
                        $aus_c = $ISPobject->paypal_email;
                        $aus_n = "Paypal email:";
                        break;
                
                    case 'mail_servers':
                        $aus_c = $ISPobject->mail_servers;
                        $aus_n = "Mail servers:";
                        break;
                
                    case 'web_servers':
                        $aus_c = $ISPobject->web_servers;
                        $aus_n = "Web servers:";
                        break; 
                
                    case 'limit_cgi':
                        $aus_c = $ISPobject->limit_cgi;
                        $aus_n = "Limit cgi:";
                        break;
                
                    case 'limit_ssi':
                        $aus_c = $ISPobject->limit_ssi;
                        $aus_n = "Limit ssi:";
                        break;
                
                    case 'limit_perl':
                        $aus_c = $ISPobject->limit_perl;
                        $aus_n = "Limit perl:";
                        break; 
                
                    case 'limit_ruby':
                        $aus_c = $ISPobject->limit_ruby;
                        $aus_n = "Limit ruby:";
                        break;
                
                    case 'limit_python':
                        $aus_c = $ISPobject->limit_python;
                        $aus_n = "Limit python:";
                        break;
                
                    case 'force_suexec':
                        $aus_c = $ISPobject->force_suexec;
                        $aus_n = "Force suexec:";
                        break; 
                
                    case 'limit_hterror':
                        $aus_c = $ISPobject->limit_hterror;
                        $aus_n = "Limit hterror:";
                        break;
                
                    case 'limit_wildcard':
                        $aus_c = $ISPobject->limit_wildcard;
                        $aus_n = "Limit wildcard:";
                        break;
                
                    case 'limit_ssl':
                        $aus_c = $ISPobject->limit_ssl;
                        $aus_n = "Limit ssl:";
                        break; 
                
                    case 'limit_backup':
                        $aus_c = $ISPobject->limit_backup;
                        $aus_n = "Limit backup:";
                        break;
                
                    case 'limit_aps':
                        $aus_c = $ISPobject->limit_aps;
                        $aus_n = "Limit aps:";
                        break;
                
                    case 'db_servers':
                        $aus_c = $ISPobject->db_servers;
                        $aus_n = "DB servers:";
                        break; 
                
                    case 'default_slave_dnsserver':
                        $aus_c = $ISPobject->default_slave_dnsserver;
                        $aus_n = "Default slave dnsserver:";
                        break;
                
                    case 'dns_servers':
                        $aus_c = $ISPobject->dns_servers;
                        $aus_n = "Dns servers:";
                        break;
                
                    case 'limit_database_quota':
                        $aus_c = $ISPobject->limit_database_quota;
                        $aus_n = "Limit database quota:";
                        break; 
                
                    case 'limit_domainmodule':
                        $aus_c = $ISPobject->limit_domainmodule;
                        $aus_n = "Limit domainmodule:";
                        break;
                
                    case 'limit_mailmailinglist':
                        $aus_c = $ISPobject->limit_mailmailinglist;
                        $aus_n = "Limit mailmailinglist:";
                        break;
                
                    case 'limit_openvz_vm':
                        $aus_c = $ISPobject->limit_openvz_vm;
                        $aus_n = "Limit openvz vm:";
                        break; 
                
                    case 'limit_openvz_vm_template_id':
                        $aus_c = $ISPobject->limit_openvz_vm_template_id;
                        $aus_n = "Limit openvz vm template id:";
                        break;
                
                    case 'parent_client_id':
                        $aus_c = $ISPobject->parent_client_id;
                        $aus_n = "Parent client id:";
                        break;
                
                    case 'locked':
                        $aus_c = $ISPobject->locked;
                        $aus_n = "Locked:";
                        break; 
                
                    case 'canceled':
                        $aus_c = $ISPobject->canceled;
                        $aus_n = "Canceled:";
                        break;
                
                    case 'can_use_api':
                        $aus_c = $ISPobject->can_use_api;
                        $aus_n = "Can use api:";
                        break;
                
                    case 'tmp_data':
                        $aus_c = $ISPobject->tmp_data;
                        $aus_n = "Tmp data:";
                        break; 
                
                    case 'id_rsa':
                        $aus_c = $ISPobject->id_rsa;
                        $aus_n = "ID rsa:";
                        break;
                
                    case 'ssh_rsa':
                        $aus_c = $ISPobject->ssh_rsa;
                        $aus_n = "ssh rsa:";
                        break;
                
                    case 'customer_no_template':
                        $aus_c = $ISPobject->customer_no_template;
                        $aus_n = "Customer no template:";
                        break; 
                
                    case 'customer_no_start':
                        $aus_c = $ISPobject->customer_no_start;
                        $aus_n = "Customer no start:";
                        break;
                
                    case 'customer_no_counter':
                        $aus_c = $ISPobject->customer_no_counter;
                        $aus_n = "Customer no counter:";
                        break; 
                
                    case 'added_date':
                        $aus_c = $ISPobject->added_date;
                        $aus_n = "Added date:";
                        break;
                
                    case 'added_by':
                        $aus_c = $ISPobject->added_by;
                        $aus_n = "Added by:";
                        break;

				    default:
				    	$aus_c = '-';
				    	$aus_n = 'Unknown';
				    	break;
				}

				$aus_array['aus_c'] = $aus_c;
				$aus_array['aus_n'] = $aus_n;

				return $aus_array;

		}

		function make_first_entery($socid, $client_id, $groupid, $server){

			global $db;

			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."custominfo_ISPc_info";
			$sql.= " WHERE fk_socid = ".$socid;
			if($server){
			$sql.= " AND fk_server_rowid = ".$server;
			}

			$resql=$db->query($sql);
			if ($resql){
				$ISPobject = $db->fetch_object($resql);
				if($ISPobject->rowid == ""){
					$sync_akzept = 0;
				}elseif($ISPobject->rowid > 0){
					$sync_akzept = 1;
				}
			}
			if($sync_akzept == 0){

				$sql ="INSERT INTO `".MAIN_DB_PREFIX."custominfo_ISPc_info` (";
				$sql.="`rowid`, `fk_server_rowid`, `fk_socid`, `fk_group_id`,`client_id`, `sys_userid`, `sys_groupid`, `sys_perm_user`, `sys_perm_group`, `sys_perm_other`, `company_id`, `gender`, `contact_name`, `vat_id`, `state`, `country`, `internet`, `icq`, `notes`, ";
				$sql.="`bank_account_owner`, `bank_account_number`, `bank_code`, `bank_name`, `bank_account_iban`, `bank_account_swift`, `paypal_email`, `default_mailserver`, `mail_servers`, `limit_maildomain`, ";
				$sql.="`limit_mailbox`, `limit_mailalias`, `limit_mailaliasdomain`, `limit_mailforward`, `limit_mailcatchall`, `limit_mailrouting`, `limit_mailfilter`, `limit_fetchmail`, `limit_mailquota`, ";
				$sql.="`limit_spamfilter_wblist`, `limit_spamfilter_user`, `limit_spamfilter_policy`, `default_webserver`, `web_servers`, `limit_web_ip`";
				$sql.=") ";
				$sql.="VALUES ";
				$sql.="(";
				$sql.="NULL, ";
				$sql.="'".$server."', ";
				$sql.="'".$socid."', ";
				$sql.="'".$groupid."', ";
				$sql.="'".$client_id."', ";
				$sql.="'0', ";
				$sql.="'0', ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="'', ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="'', ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="'1', ";
				$sql.="NULL, ";
				$sql.="'-1', ";
				$sql.="'-1', ";
				$sql.="'-1', ";
				$sql.="'-1', ";
				$sql.="'-1', ";
				$sql.="'-1', ";
				$sql.="'0', ";
				$sql.="'-1', ";
				$sql.="'-1', ";
				$sql.="'-1', ";
				$sql.="'0', ";
				$sql.="'0', ";
				$sql.="'0', ";
				$sql.="'1', ";
				$sql.="NULL, ";
				$sql.="''";
				$sql.=");";

				$resql=$db->query($sql);
				return $sql;

			} // <-- if

			
		}

/**
									DATABASE und DATABASE USER
  */


		function get_Database_user($database_user_id){

			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 
			
			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				$database_record = $client->sites_database_user_get($session_id, $database_user_id);
						
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				//echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
			return $database_record;
		}

		function get_Database($database_id){

			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 
			
			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				$database_record = $client->sites_database_get($session_id, $database_id);			
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				//echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
			return $database_record;
		}

		function get_all_database_by_user($client_id){

			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$context = stream_context_create([
			    'ssl' => [
			        // set some SSL/TLS specific options
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    ]
			]);
			$client = new SoapClient(null, array(
				'location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1,
				'stream_context' => $context
			));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				$record_record = $client->sites_database_get_all_by_user($session_id, $client_id);
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}

			return $record_record;
		}
	}
?>		