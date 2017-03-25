<?php

require("../main.inc.php");
require_once (DOL_DOCUMENT_ROOT."/customerservices/class/ispconfig.class.php");

$ispconfigsync=new ispconfigsync($db, $conf);

$ispconfigsync->set_login(8); // login

//print '<pre>';
//	$ispconfigsync->get_funktionen();
//print '<pre>';

print '<pre>';

$ispconfigsync=new ispconfigsync($db, $conf);

$ispconfigsync->set_login(8); // login

	$soap_location = $ispconfigsync->fk_soap_location;
	$soap_uri = $ispconfigsync->fk_soap_uri;
	$username = $ispconfigsync->fk_username;
	$password = $ispconfigsync->fk_password; 

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
			
		$record_record['server_get_all'][] = $client->server_get_all($session_id);
		$record_record['server_ip_get'][] = $client->server_ip_get($session_id);
		$record_record['server_get_app_version'][] = $client->server_get_app_version($session_id);
		$record_record['server_get_functions'][] = $client->server_get_functions($session_id);
		$record_record['mail_domain_get'][] = $client->mail_domain_get($session_id);
		$record_record['mail_aliasdomain_get'][] = $client->mail_aliasdomain_get($session_id);
		$record_record['mail_user_get'][] = $client->mail_user_get($session_id);
		$record_record['mail_alias_get'][] = $client->mail_alias_get($session_id);
		$record_record['mail_forward_get'][] = $client->mail_forward_get($session_id);
		$record_record['mail_catchall_get'][] = $client->mail_catchall_get($session_id);
		$record_record['mail_transport_get'][] = $client->mail_transport_get($session_id);
		$record_record['mail_relay_recipient_get'][] = $client->mail_relay_recipient_get($session_id);
		$record_record['mail_spamfilter_whitelist_get'][] = $client->mail_spamfilter_whitelist_get($session_id);
		$record_record['mail_spamfilter_blacklist_get'][] = $client->mail_spamfilter_blacklist_get($session_id);
		$record_record['mail_spamfilter_user_get'][] = $client->mail_spamfilter_user_get($session_id);
		$record_record['mail_policy_get'][] = $client->mail_policy_get($session_id);
		$record_record['mail_fetchmail_get'][] = $client->mail_fetchmail_get($session_id);
		$record_record['mail_whitelist_get'][] = $client->mail_whitelist_get($session_id);
		$record_record['mail_blacklist_get'][] = $client->mail_blacklist_get($session_id);
		$record_record['mail_filter_get'][] = $client->mail_filter_get($session_id);
		$record_record['client_get_all'][] = $client->client_get_all($session_id); // clients id

		foreach($client->client_get_all($session_id) as $key => $user_id){
			$record_record['client_get'][$user_id] = $client->client_get($session_id, $user_id);
			$record_record['sites_database_get_all_by_user'][$user_id] = $client->sites_database_get_all_by_user($session_id, $user_id);
			$record_record['openvz_vm_get_by_client'][$user_id] = $client->openvz_vm_get_by_client($session_id, $user_id);
		}

		$record_record['sites_cron_get'][] = $client->sites_cron_get($session_id);
		$record_record['sites_database_get'][] = $client->sites_database_get($session_id);
		$record_record['sites_database_user_get'][] = $client->sites_database_user_get($session_id);
		$record_record['sites_ftp_user_get'][] = $client->sites_ftp_user_get($session_id);
		$record_record['sites_shell_user_get'][] = $client->sites_shell_user_get($session_id);
		$record_record['sites_web_domain_get'][] = $client->sites_web_domain_get($session_id);
		$record_record['sites_web_vhost_subdomain_get'][] = $client->sites_web_vhost_subdomain_get($session_id);
		$record_record['sites_web_aliasdomain_get'][] = $client->sites_web_aliasdomain_get($session_id);
		$record_record['sites_web_subdomain_get'][] = $client->sites_web_subdomain_get($session_id);
		$record_record['sites_web_folder_get'][] = $client->sites_web_folder_get($session_id);
		$record_record['sites_web_folder_user_get'][] = $client->sites_web_folder_user_get($session_id);
		$record_record['domains_domain_get'][] = $client->domains_domain_get($session_id);
		$record_record['dns_zone_get'][] = $client->dns_zone_get($session_id);
		$record_record['dns_aaaa_get'][] = $client->dns_aaaa_get($session_id);
		$record_record['dns_a_get'][] = $client->dns_a_get($session_id);
		$record_record['dns_alias_get'][] = $client->dns_alias_get($session_id);
		$record_record['dns_cname_get'][] = $client->dns_cname_get($session_id);
		$record_record['dns_hinfo_get'][] = $client->dns_hinfo_get($session_id);
		$record_record['dns_mx_get'][] = $client->dns_mx_get($session_id);
		$record_record['dns_ns_get'][] = $client->dns_ns_get($session_id);
		$record_record['dns_ptr_get'][] = $client->dns_ptr_get($session_id);
		$record_record['dns_rp_get'][] = $client->dns_rp_get($session_id);
		$record_record['dns_srv_get'][] = $client->dns_srv_get($session_id);
		$record_record['dns_txt_get'][] = $client->dns_txt_get($session_id);
		$record_record['openvz_ostemplate_get'][] = $client->openvz_ostemplate_get($session_id);
		$record_record['openvz_template_get'][] = $client->openvz_template_get($session_id);
		$record_record['openvz_ip_get'][] = $client->openvz_ip_get($session_id);
		//$record_record['unk'][] = $client->openvz_get_free_ip($session_id);
		$record_record['openvz_vm_get'][] = $client->openvz_vm_get($session_id);
		
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

print '</pre>';

?>
