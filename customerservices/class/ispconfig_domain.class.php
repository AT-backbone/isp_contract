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
class ispconfigdomain extends CommonObject
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

			$client = new SoapClient(null, array('location' => $soap_location,
					'uri'      => $soap_uri,
					'trace' => 1,
					'exceptions' => 1));
			
			
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

			$client = new SoapClient(null, array('location' => $soap_location,
					'uri'      => $soap_uri,
					'trace' => 1,
					'exceptions' => 1));
			
			
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
		
		function get_Client_u_id($id){

			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$client = new SoapClient(null, array('location' => $soap_location,
					'uri'      => $soap_uri,
					'trace' => 1,
					'exceptions' => 1));
			
			
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

			$client = new SoapClient(null, array('location' => $soap_location,
					'uri'      => $soap_uri,
					'trace' => 1,
					'exceptions' => 1));
			
			
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

		function make_domain_entery($client_id, $ISPdomain){
			global $conf, $db;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$client = new SoapClient(null, 
				array('location' => $soap_location,
					  'uri'      => $soap_uri,
					  'trace' => 1,
					  'exceptions' => 1
				)
			);


			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				$affected_rows = $client->sites_web_domain_add($session_id, $client_id, $ISPdomain, $readonly = false);
			
				//echo "Web Domain ID: ".$affected_rows."<br>";
			
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				//echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
			return $affected_rows;
		}

		function make_ftp_user($client_id, $proparray2, $ftp_pass, $ftp_user, $domain_id){

			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 

			$client = new SoapClient(null, array('location' => $soap_location,
					'uri'      => $soap_uri,
					'trace' => 1,
					'exceptions' => 1));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}					

				$proparray = $client->sites_web_domain_get($session_id, $domain_id);

				$array['server_id'] = $proparray['server_id'];
				$array['parent_domain_id'] = $proparray['parent_domain_id'];
				$array['username'] = $ftp_user;
				$array['password'] = $ftp_pass;
				$array['quota_size'] = -1;
				$array['active'] = 'y';
				$array['uid'] = $proparray['system_user'];
				$array['gid'] = $proparray['system_group'];
				$array['dir'] = $proparray['document_root'];
				$array['quota_files'] = -1;
				$array['ul_ratio'] = -1;
				$array['dl_ratio'] = -1;
				$array['ul_bandwidth'] = -1;
				$array['dl_bandwidth'] = -1;
			
				$affected_rows = $client->sites_ftp_user_add($session_id, $client_id, $array);
			
				//echo "FTP User ID: ".$affected_rows."<br>";
			
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
			return $affected_rows;
		}

		function show_domain_add_inteface($ISPdomain, $socid, $domain_name, $expert_m=false){
			$ISP_input.= "<tabel>";				
					$ISP_input.= '<tr width="500px" colspan=2><td colspan=2><input type="submit" name="submit" value="Make"></td></tr>';
						foreach($ISPdomain as $label => $data){		
							// ISPdomain_varchar
							if($label == "ISPdomain_varchar"){
								foreach($ISPdomain["ISPdomain_varchar"] as $label2 => $data2){
									if($expert_m == true){$type_t = "text";}else{$type_t = "hidden";}
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="'.$type_t.'" name="domain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									$domain_insert[$label2]=$data2;
								}	
							}

							// ISPdomain_mediumtext
							if($label == "ISPdomain_mediumtext"){
								foreach($ISPdomain["ISPdomain_mediumtext"] as $label2 => $data2){
									if($expert_m == true){$type_t = "text";}else{$type_t = "hidden";}
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="'.$type_t.'" name="domain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									$domain_insert[$label2]=$data2;
								}	
							}

							// ISPdomain_varchar_type
							if($label == "ISPdomain_varchar_type"){
								foreach($ISPdomain["ISPdomain_varchar_type"] as $label2 => $data2){
									if(!$data2){$data2 = 'vhost';}
									if($expert_m == true){$type_t = "text";}else{$type_t = "hidden";}
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="'.$type_t.'" name="domain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									$domain_insert[$label2]=$data2;
								}	
							}

							// ISPdomain_varchar_php
							if($label == "ISPdomain_varchar_php"){
								foreach($ISPdomain["ISPdomain_varchar_php"] as $label2 => $data2){
									if(!$data2){$data2 = 'suphp';}
									if($expert_m == true){$type_t = "text";}else{$type_t = "hidden";}
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="'.$type_t.'" name="domain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									$domain_insert[$label2]=$data2;
								}	
							}

							// ISPdomain_bigint_quota
							if($label == "ISPdomain_bigint_quota"){
								foreach($ISPdomain["ISPdomain_bigint_quota"] as $label2 => $data2){
									if(!$data2){$data2 = '-1';}
									if($expert_m == true){$type_t = "number";}else{$type_t = "hidden";}
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="'.$type_t.'" name="domain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									$domain_insert[$label2]=$data2;
								}	
							}							
							

							//ISPdomain_int_parent
							if($label == "ISPdomain_int_parent"){
								foreach($ISPdomain["ISPdomain_int_parent"] as $label2 => $data2){
									if(!$data2){$data2 = '0';}
									if($expert_m == true){$type_t = "number";}else{$type_t = "hidden";}
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="'.$type_t.'" name="domain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									$domain_insert[$label2]=$data2;
								}	
							}

							//ISPdomain_varchar_vhost_type
							if($label == "ISPdomain_varchar_vhost_type"){
								foreach($ISPdomain["ISPdomain_varchar_vhost_type"] as $label2 => $data2){
									if(!$data2){$data2 = 'name';}
									if($expert_m == true){$type_t = "text";}else{$type_t = "hidden";}
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="'.$type_t.'" name="domain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									$domain_insert[$label2]=$data2;
								}	
							}

							//ISPdomain_varchar_allow
							if($label == "ISPdomain_varchar_allow"){
								foreach($ISPdomain["ISPdomain_varchar_allow"] as $label2 => $data2){
									if(!$data2){$data2 = 'All';}
									if($expert_m == true){$type_t = "text";}else{$type_t = "hidden";}
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="'.$type_t.'" name="domain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									$domain_insert[$label2]=$data2;
								}	
							}

							//ISPdomain_varchar_domain
							if($label == "ISPdomain_varchar_domain"){
								foreach($ISPdomain["ISPdomain_varchar_domain"] as $label2 => $data2){
									if(!$data2){$data2 = $domain_name;}
									if($expert_m == true){$type_t = "text";}else{$type_t = "hidden";}
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="'.$type_t.'" name="domain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									$domain_insert[$label2]=$data2;
								}	
							}

							// ISPdomain_tinyint
							if($label == "ISPdomain_tinyint"){
								foreach($ISPdomain["ISPdomain_tinyint"] as $label2 => $data2){
									if(!$data2){$data2 = 1;}
									if($expert_m == true){$type_t = "number";}else{$type_t = "hidden";}
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="'.$type_t.'" name="domain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									$domain_insert[$label2]=$data2;
								}	
							}

							// ISPdomain_int
							if($label == "ISPdomain_int"){
								foreach($ISPdomain["ISPdomain_int"] as $label2 => $data2){
									if(!$data2){$data2 = 1;}
									if($expert_m == true){$type_t = "number";}else{$type_t = "hidden";}
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="'.$type_t.'" name="domain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									$domain_insert[$label2]=$data2;
								}	
							}								

							//ISPdomain_enum_ny_active
							if($label == "ISPdomain_enum_ny_active"){
								foreach($ISPdomain["ISPdomain_enum_ny_active"] as $label2 => $data2){
									if($expert_m == true){$type_t = "";}else{$type_t = 'style="display:none;"';}	
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td>';
										$ISP_input.= '<select '.$type_t.' name="domain_insert['.$label2.']">';
											$ISP_input.= '<option value="n">No</option>';
											$ISP_input.= '<option value="y" selected="selected">Yes</option>';
										$ISP_input.= '</select>';
									$ISP_input.= '</td></tr>';
									$domain_insert[$label2]="y";
								}	
							}

							// ISPdomain_enum_ny
							if($label == "ISPdomain_enum_ny"){
								foreach($ISPdomain["ISPdomain_enum_ny"] as $label2 => $data2){
									if($expert_m == true){$type_t = "";}else{$type_t = 'style="display:none;"';}	
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td>';
										$ISP_input.= '<select '.$type_t.' name="domain_insert['.$label2.']">';
											$ISP_input.= '<option value="n">No</option>';
											$ISP_input.= '<option value="y">Yes</option>';
										$ISP_input.= '</select>';
									$ISP_input.= '</td></tr>';
									$domain_insert[$label2]="n";
								}	
							}

							// ISPdomain_enum_none_www_stern
							if($label == "ISPdomain_enum_none_www_stern"){
								foreach($ISPdomain["ISPdomain_enum_none_www_stern"] as $label2 => $data2){	
								if($expert_m == true){$type_t = "";}else{$type_t = 'style="display:none;"';}							
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td>';
										$ISP_input.= '<select '.$type_t.' name="domain_insert['.$label2.']">';
											$ISP_input.= '<option value="none">none</option>';
											$ISP_input.= '<option value="www" selected="selected">www</option>';
											$ISP_input.= '<option value="*">*</option>';
										$ISP_input.= '</select>';
									$ISP_input.= '</td></tr>';

									$domain_insert[$label2]="none";
								}	
							}

							// ISPdomain_enum_static_dynamic_ondemand
							if($label == "ISPdomain_enum_static_dynamic_ondemand"){
								foreach($ISPdomain["ISPdomain_enum_static_dynamic_ondemand"] as $label2 => $data2){		
								if($expert_m == true){$type_t = "";}else{$type_t = 'style="display:none;"';}							
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td>';
										$ISP_input.= '<select '.$type_t.' name="domain_insert['.$label2.']">';
											$ISP_input.= '<option value="static">static</option>';
											$ISP_input.= '<option value="dynamic">dynamic</option>';
											$ISP_input.= '<option value="ondemand">ondemand</option>';
										$ISP_input.= '</select>';
									$ISP_input.= '</td></tr>';

									$domain_insert[$label2]="static";
								}									
							}
							
							// ISPdomain_date
							if($label == "ISPdomain_date"){
								foreach($ISPdomain["ISPdomain_date"] as $label2 => $data2){
									if($expert_m == true){$type_t = "date";}else{$type_t = "hidden";}
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="'.$type_t.'" name="domain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
								}	
							}

							// ISPdomain_added_date
							if($label == "ISPdomain_added_date"){
								foreach($ISPdomain["ISPdomain_added_date"] as $label2 => $data2){
									if($expert_m == true){$type_t = "text";}else{$type_t = "hidden";}
									if(!$data2){$data2 = date('y-m-d');}
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="'.$type_t.'" name="domain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
								}	
							}

							// ISPdomain_varchar_added_by
							if($label == "ISPdomain_varchar_added_by"){
								foreach($ISPdomain["ISPdomain_varchar_added_by"] as $label2 => $data2){
									if($expert_m == true){$type_t = "text";}else{$type_t = "hidden";}
									if(!$data2){$data2 = 'remote';}
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="'.$type_t.'" name="domain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
								}	
							}

						} // ´<-- foreach($ISPdomain as $label => $data)

					$ISP_input.= '<tr width="500px" colspan=2><td colspan=2><input type="submit" name="submit" value="Make"></td></tr>';				
			$ISP_input.= "</tabel>";				
			
			if($expert_m == true){
				return $ISP_input;
			}else{
				return $domain_insert;
			}
			
		}

		function label_domain($ISPobject, $intorvar=1){

			//$ISPdomain['domain_id']							= ''; // domain_id 									int(11)
			$ISPdomain_int['sys_userid']						= $ISPobject->client_id; // sys_userid 				int(11)
			$ISPdomain_int['sys_groupid']						= $ISPobject->fk_group_id; // sys_groupid 			int(11)
			$ISPdomain_varchar['sys_perm_user']					= $ISPobject->sys_perm_user; // sys_perm_user 		varchar(5)
			$ISPdomain_varchar['sys_perm_group']				= $ISPobject->sys_perm_group; // sys_perm_group 	varchar(5)
			$ISPdomain_varchar['sys_perm_other']				= ''; // sys_perm_other 							varchar(5)
			$ISPdomain_int['server_id']							= ''; // server_id 									int(11)
			$ISPdomain_varchar['ip_address']					= ''; // ip_address 								varchar(39)
			$ISPdomain_varchar['ipv6_address']					= ''; // ipv6_address 								varchar(255)
			$ISPdomain_varchar_domain['domain']					= ''; // domain 									varchar(255)
			$ISPdomain_varchar_type['type']						= ''; // type 										varchar(32)
			$ISPdomain_int_parent['parent_domain_id']			= ''; // parent_domain_id 							int(11)
			$ISPdomain_varchar_vhost_type['vhost_type']			= ''; // vhost_type 								varchar(32)
			$ISPdomain_varchar['document_root']					= ''; // document_root 								varchar(255)
			$ISPdomain_varchar['web_folder']					= ''; // web_folder 								varchar(100)
			$ISPdomain_varchar['system_user']					= ''; // system_user 								varchar(255)
			$ISPdomain_varchar['system_group']					= ''; // system_group 								varchar(255)
			$ISPdomain_bigint_quota['hd_quota']					= ''; // 	hd_quota 									bigint(20)	
			$ISPdomain_bigint_quota['traffic_quota']			= ''; // 	traffic_quota 								bigint(20)		
			$ISPdomain_enum_ny['cgi']							= ''; // cgi 										enum('n','y')
			$ISPdomain_enum_ny['ssi']							= ''; // ssi 										enum('n','y')
			$ISPdomain_enum_ny['suexec']						= ''; // suexec 									enum('n','y')	
			$ISPdomain_tinyint['errordocs']						= ''; // errordocs 									tinyint(1)	
			$ISPdomain_tinyint['is_subdomainwww']				= ''; // is_subdomainwww 							tinyint(1)			
			$ISPdomain_enum_none_www_stern['subdomain']			= ''; // subdomain 									enum('none','www','*')	
			$ISPdomain_varchar_php['php']						= ''; // php 											varchar(32)
			$ISPdomain_enum_ny['ruby']							= ''; // ruby 										enum('n','y')
			$ISPdomain_enum_ny['python']						= ''; // python 									enum('n','y')	
			$ISPdomain_enum_ny['perl']							= ''; // perl 										enum('n','y')
			$ISPdomain_varchar['redirect_type']					= ''; // redirect_type 								varchar(255)		
			$ISPdomain_varchar['redirect_path']					= ''; // redirect_path 								varchar(255)		
			$ISPdomain_varchar['seo_redirect']					= ''; // seo_redirect 								varchar(255)		
			$ISPdomain_enum_ny['ssl']							= ''; // ssl 										enum('n','y')
			$ISPdomain_varchar['ssl_state']						= ''; // ssl_state 									varchar(255)	
			$ISPdomain_varchar['ssl_locality']					= ''; // ssl_locality 								varchar(255)		
			$ISPdomain_varchar['ssl_organisation']				= ''; // ssl_organisation 							varchar(255)			
			$ISPdomain_varchar['ssl_organisation_unit']			= ''; // ssl_organisation_unit 						varchar(255)				
			$ISPdomain_varchar['ssl_country']					= ''; // ssl_country 								varchar(255)		
			$ISPdomain_varchar['ssl_domain']					= ''; // ssl_domain 								varchar(255)		
			$ISPdomain_mediumtext['ssl_request']				= ''; // ssl_request 								mediumtext		
			$ISPdomain_mediumtext['ssl_cert']					= ''; // ssl_cert 									mediumtext	
			$ISPdomain_mediumtext['ssl_bundle']					= ''; // ssl_bundle 								mediumtext		
			$ISPdomain_mediumtext['ssl_key']					= ''; // ssl_key 									mediumtext	
			$ISPdomain_varchar['ssl_action']					= ''; // ssl_action 								varchar(16)		
			$ISPdomain_varchar['stats_password']				= ''; // stats_password 							varchar(255)			
			$ISPdomain_varchar['stats_type']					= ''; // stats_type 								varchar(255)		
			$ISPdomain_varchar_allow['allow_override']			= ''; // allow_override 							varchar(255)			
			$ISPdomain_mediumtext['apache_directives']			= ''; // apache_directives 							mediumtext			
			$ISPdomain_mediumtext['nginx_directives']			= ''; // nginx_directives 							mediumtext			
			$ISPdomain_enum_ny['php_fpm_use_socket']			= ''; // php_fpm_use_socket 						enum('n','y')				
			$ISPdomain_enum_static_dynamic_ondemand['pm']		= ''; // pm 										enum('static','dynamic','ondemand')
			$ISPdomain_int['pm_max_children']					= ''; // pm_max_children 							int(11)			
			$ISPdomain_int['pm_start_servers']					= ''; // pm_start_servers 							int(11)			
			$ISPdomain_int['pm_min_spare_servers']				= ''; // pm_min_spare_servers 						int(11)				
			$ISPdomain_int['pm_max_spare_servers']				= ''; // pm_max_spare_servers 						int(11)				
			$ISPdomain_int['pm_process_idle_timeout']			= ''; // pm_process_idle_timeout 					int(11)					
			$ISPdomain_int['pm_max_requests']					= ''; // pm_max_requests 							int(11)			
			$ISPdomain_mediumtext['php_open_basedir']			= ''; // php_open_basedir 							mediumtext			
			$ISPdomain_mediumtext['custom_php_ini']				= ''; // custom_php_ini 							mediumtext			
			$ISPdomain_varchar['backup_interval']				= ''; // backup_interval 							varchar(255)			
			$ISPdomain_int['backup_copies']						= ''; // backup_copies 								int(11)		
			$ISPdomain_mediumtext['backup_excludes']			= ''; // backup_excludes 							mediumtext			
			$ISPdomain_enum_ny_active['active']					= ''; // active 									enum('n','y')	
			$ISPdomain_enum_ny['traffic_quota_lock']			= ''; // traffic_quota_lock 						enum('n','y')				
			$ISPdomain_varchar['fastcgi_php_version']			= ''; // fastcgi_php_version 						varchar(255)				
			$ISPdomain_mediumtext['proxy_directives']			= ''; // proxy_directives 							mediumtext			
			$ISPdomain_date['last_quota_notification']			= ''; // last_quota_notification 					date				
			$ISPdomain_mediumtext['rewrite_rules']				= ''; // rewrite_rules 								mediumtext		
			$ISPdomain_added_date['added_date']						= ''; // added_date 								date			
			$ISPdomain_varchar_added_by['added_by']						= ''; // added_by 									varchar(255)	

		// make one array with the specified array-values!
			$ISPdomain['ISPdomain_varchar_domain']					= $ISPdomain_varchar_domain;
			$ISPdomain['ISPdomain_enum_ny_active']					= $ISPdomain_enum_ny_active;			
			$ISPdomain['ISPdomain_varchar']							= $ISPdomain_varchar;
			$ISPdomain['ISPdomain_varchar_type']					= $ISPdomain_varchar_type;	
			$ISPdomain['ISPdomain_int_parent']						= $ISPdomain_int_parent;	
			$ISPdomain['ISPdomain_varchar_vhost_type']				= $ISPdomain_varchar_vhost_type;		
			$ISPdomain['ISPdomain_varchar_allow']					= $ISPdomain_varchar_allow;
			$ISPdomain['ISPdomain_varchar_php']						= $ISPdomain_varchar_php;		
			$ISPdomain['ISPdomain_bigint_quota']					= $ISPdomain_bigint_quota;
			$ISPdomain['ISPdomain_mediumtext']						= $ISPdomain_mediumtext;
			$ISPdomain['ISPdomain_tinyint']							= $ISPdomain_tinyint;
			$ISPdomain['ISPdomain_int']								= $ISPdomain_int;				
			$ISPdomain['ISPdomain_date']							= $ISPdomain_date;	
			$ISPdomain['ISPdomain_added_date']						= $ISPdomain_added_date;	
			$ISPdomain['ISPdomain_enum_ny']							= $ISPdomain_enum_ny;
			$ISPdomain['ISPdomain_enum_none_www_stern']				= $ISPdomain_enum_none_www_stern;
			$ISPdomain['ISPdomain_enum_static_dynamic_ondemand']	= $ISPdomain_enum_static_dynamic_ondemand;	
			$ISPdomain['ISPdomain_varchar_added_by']				= $ISPdomain_varchar_added_by;	
			
			
			//$ISPdomain['ISPdomain_bigint']							= $ISPdomain_bigint;	

			return $ISPdomain;
		}
/**
																					SUBDOMAIN AND ALIAS
  */

	
	function make_subdomain_entery($subdomain_insert, $client_id, $type){
		global $conf;
		$soap_location = $this->fk_soap_location;
		$soap_uri = $this->fk_soap_uri;
		$username = $this->fk_username;
		$password = $this->fk_password; 	

		if($type==""){$type="subdomain";}
	
		$client = new SoapClient(null, array('location' => $soap_location,
				'uri'      => $soap_uri,
				'trace' => 1,
				'exceptions' => 1));
		
		
		try {
			if($session_id = $client->login($username, $password)) {
				//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
			}
			
			$domain_record = $client->sites_web_domain_get($session_id, $subdomain_insert['domain_id']);

			$domain_record['domain'] = $subdomain_insert['domain'];
			$domain_record['type'] = $type;
			$domain_record['domain_id'] = "";
			$domain_record['parent_domain_id'] = $subdomain_insert['domain_id'];

			//die(print_r($domain_record));

			if($type == "subdomain"){
				$domain_record['subdomain'] = "www";
				$subdomain_id = $client->sites_web_subdomain_add($session_id, $client_id, $domain_record);
			}elseif($type == "alias"){
				$subdomain_id = $client->sites_web_aliasdomain_add($session_id, $client_id, $domain_record);
			}
		
			if($client->logout($session_id)) {
				//echo 'Logged out.<br />';
			}


		} catch (SoapFault $e) {
			echo $client->__getLastResponse();
			echo('SOAP Error: '.$e->getMessage());
		}
		return $subdomain_id;
	}

	function label_subdomain($domain_id){
				
		$ISPdatbase_int['domain_id']	= $domain_id;							// server_id  (int(11))
		$ISPdatbase_varchar['domain']	= $database_user_name;	// database_user  (varchar(64))
		

	// make one array with the specified array-values!
		$ISPdatbase['ISPdatbase_varchar']		= $ISPdatbase_varchar;
		$ISPdatbase['ISPdatbase_int']			= $ISPdatbase_int;

		return $ISPdatbase;	
		
	}

	function show_subdomain_add_inteface($ISPsubdomain_object){
		//subdomain_insert
			$ISP_input.= "<tabel>";				
					$ISP_input.= '<tr width="500px" colspan=2><td colspan=2><input type="submit" name="submit" value="Make"></td></tr>';
						foreach($ISPsubdomain_object as $label => $data){									

							// ISPdatbase_varchar
							if($label == "ISPdatbase_varchar"){
								foreach($ISPsubdomain_object["ISPdatbase_varchar"] as $label2 => $data2){										
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="text" name="subdomain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
								}	
							}

							// ISPdatbase_int
							if($label == "ISPdatbase_int"){
								foreach($ISPsubdomain_object["ISPdatbase_int"] as $label2 => $data2){										
									$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="hidden" name="subdomain_insert['.$label2.']" value="'.$data2.'"></td></tr>';
								}	
							}
							
						} // <-- foreach($ISPsubdomain_object as $label => $data)
	
					$ISP_input.= '<tr width="500px" colspan=2><td colspan=2><input type="submit" name="submit" value="Make"></td></tr>';				
			$ISP_input.= "</tabel>";				

		return $ISP_input;		
	}


/**
            ######################################################################### Database and Database User Anlegen ##################################################################
  */
// #######################+++ANLEGEN ENDE+++#########################
	// SOAP Aktion

	/*
	 *		get_Database_user($database_user_id) & get_Database($dabase_id)	ist in ispconfig.class.php und nicht hier !!!
	 */

	// make Database
	// return ID
		function make_database_entery($client_id, $params, $nextdb = 0, $website_id=0){
			global $conf, $db;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 
	
			$client = new SoapClient(null, array('location' => $soap_location,
					'uri'      => $soap_uri,
					'trace' => 1,
					'exceptions' => 1));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
				$params['parent_domain_id'] = $website_id;

				$database_id = $client->sites_database_add($session_id, $client_id, $params);
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}


			} catch (SoapFault $e) {
				//echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}

				$sql = "SELECT * FROM ".MAIN_DB_PREFIX."custominfo_ISPc_info ";
				$sql.= " WHERE client_id=".$client_id;
				$resql=$db->query($sql);
				$object = $db->fetch_object($resql);
				$existing_ids = $object->fk_database_id;

			if(!$nextdb){
				$sql = "UPDATE ".MAIN_DB_PREFIX."custominfo_ISPc_info SET ";
				$sql.= " fk_database_id = ".$database_id;
				$sql.= " WHERE client_id=".$client_id;
			}elseif($nextdb == 1){
				$sql = "UPDATE ".MAIN_DB_PREFIX."custominfo_ISPc_info SET ";
				$sql.= " fk_database_id = '".$existing_ids.', '.$database_id."'";
				$sql.= " WHERE client_id=".$client_id;
			}
				$resql=$db->query($sql);

			return $database_id;
		}
	
	// make USER
	// return ID
		function make_database_user_entery($client_id, $params){
			global $conf, $db;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 
	
			$client = new SoapClient(null, array('location' => $soap_location,
					'uri'      => $soap_uri,
					'trace' => 1,
					'exceptions' => 1));


			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}
			
				$database_user_id = $client->sites_database_user_add($session_id, $client_id, $params);			// DERWEIL DEATKTIV
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				//echo $client->__getLastResponse();
				$error = 'SOAP Error: '.$e->getMessage();
				return $error;
			}


				$sql = "UPDATE ".MAIN_DB_PREFIX."custominfo_ISPc_info SET ";
				$sql.= " fk_database_user_id = ".$database_user_id;
				$sql.= " WHERE client_id=".$client_id;

				$resql=$db->query($sql);
			
			return $database_user_id;
		}

// #######################---ANLEGEN ENDE---#########################
// Interface

	// DATABASE	##########################################################################################################        DATABASE	
		// Datenbank Inteface
		function show_database_add_inteface($ISPdatabase, $socid){
			//database_insert
				$ISP_input.= "<tabel>";				
						$ISP_input.= '<tr width="500px" colspan=2><td colspan=2><input type="submit" name="submit" value="Make"></td></tr>';
							foreach($ISPdatabase as $label => $data){									

								// ISPdatbase_varchar_name
								if($label == "ISPdatbase_varchar_name"){
									foreach($ISPdatabase["ISPdatbase_varchar_name"] as $label2 => $data2){										
										$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="text" name="database_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									}	
								}

								// ISPdatbase_varchar_charset
								if($label == "ISPdatbase_varchar_charset"){
									foreach($ISPdatabase["ISPdatbase_varchar_charset"] as $label2 => $data2){										
										$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="text" name="database_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									}	
								}

								// ISPdatbase_varchar_type
								if($label == "ISPdatbase_varchar_type"){
									foreach($ISPdatabase["ISPdatbase_varchar_type"] as $label2 => $data2){										
										$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="text" name="database_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									}	
								}

								//ISPdatbase_enum_ny_remote_access
								if($label == "ISPdatbase_enum_ny_remote_access"){
									foreach($ISPdatabase["ISPdatbase_enum_ny_remote_access"] as $label2 => $data2){
										if($data2 == "y"){$selecty = 'selected="selected"';}elseif($data2 == "n"){$selectn = 'selected="selected"';}
										$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td>';
											$ISP_input.= '<select name="database_insert['.$label2.']">';
												$ISP_input.= '<option '.$selectn.' value="n">No</option>';
												$ISP_input.= '<option '.$selecty.' value="y">Yes</option>';
											$ISP_input.= '</select>';
										$ISP_input.= '</td></tr>';

										$selecty = "";
										$selectn = "";
									}	
								}
						//###################### Darunter nicht mehr sichtabar ######################

								// ISPdomain_varchar
								if($label == "ISPdatbase_int"){
									foreach($ISPdatabase["ISPdatbase_int"] as $label2 => $data2){										
										$ISP_input.= '<tr width="500px" style="display:none;"><td>'.$label2.': </td><td><input type="number" name="database_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									}	
								}
	
								//ISPdatbase_varchar
								if($label == "ISPdatbase_varchar"){
									foreach($ISPdatabase["ISPdatbase_varchar"] as $label2 => $data2){
										$ISP_input.= '<tr width="500px" style="display:none;"><td>'.$label2.': </td><td><input type="text" name="database_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									}	
								}

								//ISPdatbase_text
								if($label == "ISPdatbase_text"){
									foreach($ISPdatabase["ISPdatbase_text"] as $label2 => $data2){
										$ISP_input.= '<tr width="500px" style="display:none;"><td>'.$label2.': </td><td><input type="text" name="database_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									}	
								}

								//ISPdatbase_enum_ny
								if($label == "ISPdatbase_enum_ny"){
									foreach($ISPdatabase["ISPdatbase_enum_ny"] as $label2 => $data2){
										if($data2 == "y"){$selecty = 'selected="selected"';}elseif($data2 == "n"){$selectn = 'selected="selected"';}
										$ISP_input.= '<tr width="500px" style="display:none;"><td>'.$label2.': </td><td>';
											$ISP_input.= '<select name="database_insert['.$label2.']">';
												$ISP_input.= '<option '.$selectn.' value="n">No</option>';
												$ISP_input.= '<option '.$selecty.' value="y">Yes</option>';
											$ISP_input.= '</select>';
										$ISP_input.= '</td></tr>';

										$selecty = "";
										$selectn = "";
									}	
								}
	
							} // <-- foreach($ISPdatabase as $label => $data)
	
						$ISP_input.= '<tr width="500px" colspan=2><td colspan=2><input type="submit" name="submit" value="Make"></td></tr>';				
				$ISP_input.= "</tabel>";				

			return $ISP_input;		
		}

		// Datenbank Inteface
		function label_database($ISPobject, $database_name, $database_user_pass, $fk_database_user_id, $website_id, $database_user_id){			
				
			$database_name = $database_name."_db";

			$ISPdatbase_varchar_name['database_name']			= $database_name;		// database_name  		(varchar(64))	+
			$ISPdatbase_varchar['database_password']			= $database_user_pass;	// database_password  	(varchar(64))				
			$ISPdatbase_varchar_charset['database_charset']		= 'utf8';				// database_charset  	(varchar(64))	+			
			$ISPdatbase_varchar_type['type']					= 'mysql';				// type  				(varchar(16))	+		
			$ISPdatbase_varchar['backup_interval']				= 'none';				// backup_interval  	(varchar(255))	
			$ISPdatbase_text['remote_ips']						= '';					// remote_ips  			(text)									
			$ISPdatbase_int['server_id']						= '1';					// server_id 			(int(11))	

			if($fk_database_user_id < 1) $fk_database_user_id = $database_user_id;
			$ISPdatbase_int['database_user_id']					= $fk_database_user_id;	// database_user_id  	(int(11))	
						
			$ISPdatbase_int['database_ro_user_id']				= '0';					// database_ro_user_id  (int(11))
			$ISPdatbase_int['backup_copies']					= '1';					// backup_copies  		(int(11))		
			$ISPdatbase_int['website_id']						= $website_id;			// website_id  			(int(11))	  				
			$ISPdatbase_enum_ny_remote_access['remote_access']	= 'n';					// remote_access  		(enum('n','y'))				
			$ISPdatbase_enum_ny['active']						= 'y';					// active  				(enum('n','y'))

		// make one array with the specified array-values!
			$ISPdatbase['ISPdatbase_varchar_name'] 			= $ISPdatbase_varchar_name;
			$ISPdatbase['ISPdatbase_varchar_charset'] 		= $ISPdatbase_varchar_charset;
			$ISPdatbase['ISPdatbase_varchar_type'] 			= $ISPdatbase_varchar_type;
			$ISPdatbase['ISPdatbase_enum_ny_remote_access'] = $ISPdatbase_enum_ny_remote_access;
			
			$ISPdatbase['ISPdatbase_varchar']				= $ISPdatbase_varchar;			
			$ISPdatbase['ISPdatbase_int']					= $ISPdatbase_int;
			$ISPdatbase['ISPdatbase_enum_ny']				= $ISPdatbase_enum_ny;
			$ISPdatbase['ISPdatbase_text']					= $ISPdatbase_text;

			return $ISPdatbase;	
		}

	//USER	##########################################################################################################        USER	
		// Datenbank User Inteface
		function show_database_user_add_inteface($ISPdatabase, $socid){
			//database_user_insert
				$ISP_input.= "<tabel>";				
						$ISP_input.= '<tr width="500px" colspan=2><td colspan=2><input type="submit" name="submit" value="Make"></td></tr>';
							foreach($ISPdatabase as $label => $data){	
	
								// ISPdomain_varchar
								if($label == "ISPdatbase_int"){
									foreach($ISPdatabase["ISPdatbase_int"] as $label2 => $data2){										
										$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="number" name="database_user_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									}	
								}
	
								//ISPdatbase_varchar
								if($label == "ISPdatbase_varchar"){
									foreach($ISPdatabase["ISPdatbase_varchar"] as $label2 => $data2){
										$ISP_input.= '<tr width="500px"><td>'.$label2.': </td><td><input type="text" name="database_user_insert['.$label2.']" value="'.$data2.'"></td></tr>';
									}	
								}
	
							} // <-- foreach($ISPdatabase as $label => $data)
	
						$ISP_input.= '<tr width="500px" colspan=2><td colspan=2><input type="submit" name="submit" value="Make"></td></tr>';				
				$ISP_input.= "</tabel>";				

			return $ISP_input;		
		}

		// Datenbank User Inteface
		function label_database_user($ISPobject, $database_user_name, $database_user_pass){
					
			$ISPdatbase_int['server_id']					= '0';							// server_id  (int(11))
			$ISPdatbase_varchar['database_user']			= $database_user_name;	// database_user  (varchar(64))
			$ISPdatbase_varchar['database_password']		= $database_user_pass;			// database_password  (varchar(64))

		// make one array with the specified array-values!
			$ISPdatbase['ISPdatbase_varchar']		= $ISPdatbase_varchar;
			$ISPdatbase['ISPdatbase_int']			= $ISPdatbase_int;

			return $ISPdatbase;	
			
		}
	
		function aktivate_deaktivate_database($client_id, $database_id, $deoraktiv, $whatdeakt){
			global $conf;
			$soap_location = $this->fk_soap_location;
			$soap_uri = $this->fk_soap_uri;
			$username = $this->fk_username;
			$password = $this->fk_password; 
	
			$client = new SoapClient(null, array('location' => $soap_location,
					'uri'      => $soap_uri,
					'trace' => 1,
					'exceptions' => 1));
			
			
			try {
				if($session_id = $client->login($username, $password)) {
					//echo 'Logged successfull. Session ID:'.$session_id.'<br />';
				}		
				

				if($deoraktiv == 'akt'){$data = "y";}
				if($deoraktiv == 'dea'){$data = "n";}
				if($whatdeakt == 'status'){$label = 'active';}
				if($whatdeakt == 'remote'){$label = 'remote_access';}
			
				
				//* Get the database record
				$database_record = $client->sites_database_get($session_id, $database_id);
			
				//* Change the status to inactive or somthing else				
				$database_record[$label] = $data;

				$affected_rows = $client->sites_database_update($session_id, $client_id, $database_id, $database_record);
			
				//echo "Number of records that have been changed in the database: ".$affected_rows."<br>";
			
				if($client->logout($session_id)) {
					//echo 'Logged out.<br />';
				}
			
			
			} catch (SoapFault $e) {
				echo $client->__getLastResponse();
				echo('SOAP Error: '.$e->getMessage());
			}
		}
/**
             ######################################################################### Database and Database User Fertig ##################################################################
  */
	
	}
?>		