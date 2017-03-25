<?php
/* Copyright (C) 2012      Lukas Prömer <lukas.proemer@gmail.com>
 

*/

/**
 *   \file       htdocs/societe/note.php
 *   \brief      Tab for notes on third party
 *   \ingroup    societe
 */

require("../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");
require_once(DOL_DOCUMENT_ROOT."/custominfo/class/custominfo.class.php");


$langs->load("products");
$langs->load("suppliers");
$langs->load("bills");
$langs->load("custominfo@custominfo");
$langs->load("companies");
$langs->load("users");
$langs->load("other");
$langs->load("commercial");
$langs->load('orders');
$langs->load("contracts");
$langs->load('deliveries');

$action = GETPOST('action');

$langs->load("companies");

$_id = GETPOST('id','int');
$socid = $_id;
$_from = GETPOST('from');
$_ref = GETPOST('ref');
$_comref = GETPOST('comref');
$_det = GETPOST('det');
$massage_db_u = GETPOST('massage');
$massage_d = GETPOST('massage_d');

if($massage_d != ""){
	$massage_d = "<br>".$massage_d;	
}

if($massage_db_u != ""){
	$massage_db_u = "<br>".$massage_db_u;	
}

$ci = new custominfo($db);

	// Security check
	if ($user->societe_id) $socid=$user->societe_id;

switch($_from){
	case 'thirdparty':
		$result = restrictedArea($user, 'societe', $socid, '&societe');
		$object = new Societe($db);
		if ($socid > 0) $object->fetch($socid);
		
		
		$tree = $ci->fetchInfosBySociete($_id);
	break;
	case 'contract':
		require_once(DOL_DOCUMENT_ROOT.'/core/lib/contract.lib.php');
		require_once(DOL_DOCUMENT_ROOT."/contrat/class/contrat.class.php");
		
		$result=restrictedArea($user,'contrat',$_id);
		$object = new Contrat($db);
		if($_id > 0 || strlen($_comref) > 0) $object->fetch($_id,$_comref);
		$_id = $object->id;
		
		$tree = $ci->fetchInfoByContrat($_id);
	break;
	case 'commande':
		require_once(DOL_DOCUMENT_ROOT."/commande/class/commande.class.php");
		require_once(DOL_DOCUMENT_ROOT."/core/lib/order.lib.php");
		$result = restrictedArea($user, 'commande', $_id,'');
		$object = new Commande($db);
		if($_id > 0 || strlen($_comref) > 0) $object->fetch($_id,$_comref);
		$_id = $object->id;
		
		$tree = $ci->fetchInfosByCommande($_id);
	break;
	

}


/*
 * Actions
 */

if ($action == 'edit' && GETPOST('submit'))
{
    
    $val = $tree[$_GET['ref']];
    $det   = $val->det[$_GET['det']]; 
    $p = new product($db);
    $p->fetch($det->fk_product);
	foreach($det->fields as $ck => $cv){
    		// Product
    		if( ! $ci->lineRight($cv,$user) ) continue;
    		$cusd = new Custominfodet($db);
    		$cusd->fk_user=$user->id;
    		$cusd->fk_societe=$val->fk_soc;
    		$cusd->fk_product=$det->fk_product;
    		$cusd->fk_contratdet=0;
    		$cusd->fk_commandedet=0;
    		$cusd->{'fk_'.$det->type}=$det->id;
    		$cusd->fk_custominfo=$cv->id;
    		switch(trim($cv->rtype)){
    			case 'date':
    				$cusd->value = dol_mktime(0,0,0,GETPOST('customfield_'.$cv->keyname.'month'),GETPOST('customfield_'.$cv->keyname.'day'),GETPOST('customfield_'.$cv->keyname.'year'));
    				
    			break;
    			case 'ispconfig_function':
    			$cusd->value="";
    			break;
    			case 'password':
    				$cusd->value = base64_encode(GETPOST('customfield_'.$cv->keyname));
    			break;
    			default:
    				$cusd->value= GETPOST('customfield_'.$cv->keyname);
    			break;
    		}
    		$cusd->replace($user);
    	}

	header ('LOCATION: custominfodet.php?id='.$_id.'&from='.$_from.'&ref='.$_ref.'&det='.$_det);
}


/*
 *	View
 */

if ($conf->global->MAIN_DIRECTEDITMODE && $user->rights->societe->creer) $action='edit';

$form = new Form($db);

$help_url='EN:Module_Third_Parties|FR:Module_Tiers|ES:Empresas';
llxHeader('',$langs->trans("ThirdParty").' - '.$langs->trans("Notes"),$help_url);

if ($_id > 0)
{
    /*
     * Affichage onglets
     */
    if ($conf->notification->enabled) $langs->load("mails");


switch($_from){
	#### Thirdpary ###
	case 'thirdparty':
		$fk_socid = $_id;
   		$head = societe_prepare_head($object);
		dol_fiche_head($head, 'custominfo', $langs->trans("ThirdParty"),0,'company');
		
		   print '<table class="border" width="100%">';

		    print '<tr><td width="20%">'.$langs->trans('ThirdPartyName').'</td>';
		    print '<td colspan="3">';
		    print $form->showrefnav($object,'id','',($user->societe_id?0:1),'rowid','nom','','&from='.$_from);
		    print '</td></tr>';
		
		    if (! empty($conf->global->SOCIETE_USEPREFIX))  // Old not used prefix field
		    {
		        print '<tr><td>'.$langs->trans('Prefix').'</td><td colspan="3">'.$object->prefix_comm.'</td></tr>';
		    }
		
		    if ($object->client)
		    {
		        print '<tr><td>';
		        print $langs->trans('CustomerCode').'</td><td colspan="3">';
		        print $object->code_client;
		        if ($object->check_codeclient() <> 0) print ' <font class="error">('.$langs->trans("WrongCustomerCode").')</font>';
		        print '</td></tr>';
		    }
		
		    if ($object->fournisseur)
		    {
		        print '<tr><td>';
		        print $langs->trans('SupplierCode').'</td><td colspan="3">';
		        print $object->code_fournisseur;
		        if ($object->check_codefournisseur() <> 0) print ' <font class="error">('.$langs->trans("WrongSupplierCode").')</font>';
		        print '</td></tr>';
		    }
		    print '</table>';
    
	break;
	
	#### Contract ###
	case 'contract':
		
		$head = contract_prepare_head($object);
		dol_fiche_head($head, 'custominfo', $langs->trans("Contract"), 0, 'contract');
    		$object->fetch_thirdparty();

			print '<table class="border" width="100%">';
	
			// Reference du contrat
			print '<tr><td width="25%">'.$langs->trans("Ref").'</td><td colspan="3">';
			print $object->ref;
			print "</td></tr>";
	
			// Customer
			print "<tr><td>".$langs->trans("Customer")."</td>";
	        print '<td colspan="3">'.$object->thirdparty->getNomUrl(1).'</td></tr>';
	
			// Ligne info remises tiers
		    print '<tr><td>'.$langs->trans('Discount').'</td><td>';
			if ($object->thirdparty->remise_client) print $langs->trans("CompanyHasRelativeDiscount",$object->thirdparty->remise_client);
			else print $langs->trans("CompanyHasNoRelativeDiscount");
			$absolute_discount=$object->thirdparty->getAvailableDiscounts();
			print '. ';
			if ($absolute_discount) print $langs->trans("CompanyHasAbsoluteDiscount",$absolute_discount,$langs->trans("Currency".$conf->currency));
			else print $langs->trans("CompanyHasNoAbsoluteDiscount");
			print '.';
			print '</td></tr>';
	
			print "</table>";
			$fk_socid = $object->thirdparty->id;
	break;
	
	#### Commande ###
	case 'commande':
		$head = commande_prepare_head($object);
		dol_fiche_head($head, 'custominfo', $langs->trans("CustomerOrder"), 0, 'order');
		
			print '<table class="border" width="100%">';
	
			// Ref
			print '<tr><td width="18%">'.$langs->trans("Ref").'</td><td colspan="3">';

			print $form->showrefnav($object,'comref','',1,'ref','ref','','&from='.$_from);		
			print "</td></tr>";
	
			// Ref commande client
			print '<tr><td>';
	        print '<table class="nobordernopadding" width="100%"><tr><td nowrap>';
			print $langs->trans('RefCustomer').'</td><td align="left">';
	        print '</td>';
	        print '</tr></table>';
	        print '</td><td colspan="3">';
			print $object->ref_client;
			print '</td>';
			print '</tr>';
	
			// Customer
			if (is_null($object->client))	$object->fetch_thirdparty();
	
			print "<tr><td>".$langs->trans("Company")."</td>";
			print '<td colspan="3">'.$object->client->getNomUrl(1).'</td></tr>';
		
			print '</table>';
	break;
	

}
  
	
    print '<br><table width="100%">';
    print '<tr>';
     print '<td width="50%" valign="top">';
	if($action == 'edit'){
		print '<form action="custominfodet.php?id='.$_id.'&from='.$_from.'&ref='.$_ref.'&det='.$_det.'" method="POST">';
		print '<input type="hidden" name="action" value="edit">';
		
		
	}

	  if(isset($_GET['ref']) && isset($_GET['det'])){
	    print '<table width="100%" class="border">';
	    		$val = $tree[$_GET['ref']];
			$det   = $val->det[$_GET['det']]; 
			$p = new product($db);
			$p->fetch($det->fk_product);
	    		// vertrag / order
	    		print '<tr>';
	    			print '<td width="140">'.$langs->trans('Contract').'/'.$langs->trans('Order').'</td>';
	    			print '<td><a href="'.DOL_URL_ROOT.'/'.$val->type.'/fiche.php?id='.$val->id.'">'.img_picto('',$val->type=='commande'?'object_order':'object_contract').' '.$val->ref.'</a></td>';
	    		print '</tr>';
	    		// Product
	    		print '<tr>';
	    			print '<td>'.$langs->trans('Product').'/'.$langs->trans('Service').'</td>';
	    			print '<td><a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$p->id.'">'.img_picto('',$p->type?'object_service':'object_product').' '.$p->ref.'</a> - '.$p->label.'';
						if(strlen(preg_replace("/^\s+|\s+$|\<[^\>]+\>/s",'',$det->desc)) > 3)	print '</br><i>'.preg_replace("/^\s+|\s+$|\<[^\>]+\>/s",'',$det->desc).'</i>';
				print '</td>';
	    		print '</tr>';
	    		print '<tr>';
	    			print '<td></td>';
	    			print '<td></td>';
	    		print '</tr>';
	    		
	    		
	    		foreach($det->fields as $ck => $cv){
		    		// Product
		    		
			    		if($ci->lineRight($cv,$user)){
			    			if ($cv->type != 'ispconfig_function')
		    				{
				    		print '<tr>';
				    			print '<td>'.$cv->name.'</td>';
				    			if($action == 'edit')
				    				print '<td>'.$ci->LineEditField($cv).'</td>';
				    			else
				    				print '<td>'.$ci->LineValue($cv).'</td>';
				    				
				    			print '</tr>';
				    			if ($cv->name=="Webserver")	{
				    				$ispfunction[0]['server']=$cv->value; 
				    				$ispfunction[0]['server_aid']=$ck;
				    			}
				    			elseif ($cv->name=="Domainname")	{
				    				$ispfunction[0]['domain']=$cv->value; 
				    				$ispfunction[0]['domain_aid']=$ck;
				    			}
				    			elseif ($cv->name=="Benutzername")	{
				    				$ispfunction[0]['user']=$cv->value; 
				    				$ispfunction[0]['user_aid']=$ck;
				    			}
				    			elseif ($cv->name=="Passwort")	{
				    				$ispfunction[0]['pass']=$ci->LineValue($cv); 
				    				$ispfunction[0]['pass_aid']=$ck;
				    			}
				    			
			    			} 
			    			else
			    			{
			    				$x=sizeof($ispfunction);
			    				if (!$ispfunction[$x]['funcId']) $x=$x-1;
			    				$ispfunction[$x]['function']=$cv->name; 
			    				$ispfunction[$x]['function_aid']=$ck; 
			    				
			    				// somit steht der Webserver immer auf 0 
			    				// die erste Funktionen auf 0 falls mehrere dann jeweils 1 höher
			    			} 
			    			
		    			}
		    	}
//####################################################################################     ISPconfig sync       ########################################################################################
		  if ($conf->global->MAIN_MODULE_CUSTOMERSERVICES_ISPCONFIG=="yes"){  	
		  			require_once (DOL_DOCUMENT_ROOT."/customerservices/class/ispconfig.class.php");
		  			$ispconfigsync=new ispconfigsync($db, $conf);		  			

		  			if($ispfunction[0]['function'] == "sites"){
						$seit_status = 'N/A'; // standart setzen

		  				$ang_server = $ispfunction[0]['server']; // angegebener Server
						
		  				// Server akzept !
						$sql = "SELECT * FROM ".MAIN_DB_PREFIX."custominfo_ISPc_server";
	
						$resql=$db->query($sql);
						if ($resql){
							$num = $db->num_rows($resql);
							$i = 0;
							$server_akzept = 0;
							while($i < $num){
								$server_object = $db->fetch_object($resql);
	
								if($ang_server == $server_object->name){ // ist der Server Registriert?
									$server_akzept = 1;
									$server_rowid = $server_object->rowid;
								}
								$i++;
							}						
						}
	
						if($server_akzept == 0){ // wenn es den Server nicht giebt/registriert ist
							$Pages = 'Webserver nicht <a href="../customerservices/admin/customerservices.php?ISP=1" target="_blank" style="color:#ff0000">Registriert!</a>';
						}else{ // server akzept true
	
							// Sync akzept !/User registriert?
							$sql = "SELECT * FROM ".MAIN_DB_PREFIX."custominfo_ISPc_info";
							$sql.= " WHERE fk_socid = ".$fk_socid;
							$sql.= " AND fk_server_rowid = ".$server_rowid;
						
							$resql=$db->query($sql);
							if ($resql){
								$ISPobject = $db->fetch_object($resql);
								if($ISPobject->rowid == ""){   // user ist nicht registriert!
									$sync_akzept = 0;
								}elseif($ISPobject->rowid > 0){  // user ist registriert!
									$sync_akzept = 1;
									$groupid = $ISPobject->fk_group_id;
									$fk_client_id = $ISPobject->client_id;
									$ftp_user = $ISPobject->username;
									$ISPserver = $ISPobject->fk_server_rowid;
									$database_user = $ISPobject->fk_database_user_id;
									$database_ids = $ISPobject->fk_database_id;
								}
							}

							if($database_ids){
								$database_ids = explode(", ", $database_ids); 	// mach ein array mit jeder DB id
							}
	
							$ispconfigsync->set_login($ISPserver); // login

							//print $ispfunction[0]['user'];
							$ftp_arr = $ispconfigsync->get_ftp_user($ispfunction[0]['user']); // spezial recht muss dem Isp remote user übergeben werden 'sites_ftp_user_server_get'
							$ftp_arr_n = count($ftp_arr);
							if($ftp_arr_n > 1){
								$ftp_tf = 1;
							}else{
								$ftp_tf = 0;
								$ftp_get = '&ftp_u_n='.$ispfunction[0]['user'];
								$ftp_get.= '&ftp_p_w='.$ispfunction[0]['pass'];
							}
							//print $ftp_get;

							if($sync_akzept == 1){
								// Soap Call (Sites einlesen von ISPConfig mit GroupID)
								$Sites = $ispconfigsync->get_Client_sites(0, $groupid, '');
										
								print '<p>';
								foreach($Sites as $label => $domain){
									
									
									$domain_fields = $ispconfigsync->get_domain_fields($domain['domain_id']);
									
									if(strtolower($ispfunction[0]['domain']) == $domain['domain']){  // ist die domain auf ISPconfig?																
										
										if($domain['active'] == "y"){
											$seit_status = '<div style="color:#00C800">Aktiv</div>';
										}else{
											$seit_status = '<div style="color:#ff0000">Inaktiv</div>';
										}
										// $Pages.= 'Num: '.$label;
										$Pages.= 'Domain: '.$domain['domain'];
										$Pages.= '<br>Site_ID: '.$domain['domain_id'];
										$Pages.= '<br>Site_Dir: '.$domain['document_root'];
										$Pages.= '<br>Site_Type: '.$domain_fields['type'];
										$domain_akzept = 1;	// Domain gefunden!		

										$subdomain_add_akz=1; 	// Subdomain erlaubt
										$alias_add_akz=1; 	    // Alias erlaubt
										$right_domain_id = $domain['domain_id'];
									}

									if($domain_fields['type'] == 'subdomain'){
										$Pages2_s.= '<br>&emsp;Subdomain:&emsp;'.$domain_fields['domain'];	// Subdomain							
									}elseif($domain_fields['type'] == 'alias'){
										$Pages2_a.= '<br>&emsp;Alias:&emsp;'.$domain_fields['domain'];	// ALias
									}
														
								}
								if($Pages2_s != "") $br = "<br>";
								$Pages.= $br.$Pages2_s.'<br>'.$Pages2_a; // intigrate Alias and Subdomain at last in the Output

								$database_arr = $ispconfigsync->get_all_database_by_user($fk_client_id);

								if($domain_akzept == 0){
									// Domain nicht auf ISP gefunden
									$Pages = 'Seite/domain nicht auf ISP gefunden <a href="../customerservices/ISPconfig_sync.php?ISP_server_rowid='.$server_rowid.'&socid='.$object->thirdparty->id.'&domain_add=1&domain_name='.$ispfunction[0]['domain'].'&ftp_u_ex='.$ftp_tf.$ftp_get.'" style="color:#ff0000">Erstellen?</a>';
									$Pages.= ' oder: <a href="../customerservices/ISPconfig_sync.php?ISP_server_rowid='.$server_rowid.'&socid='.$object->thirdparty->id.'&domain_add=1&domain_name='.$ispfunction[0]['domain'].'&expert=1&ftp_u_ex='.$ftp_tf.'" target="_blank" style="color:#ff0000">(Experten Modus)</a>';
									$Pages.= $massage_d;
								}//doman akzept								

								if($database_arr[0]['database_user_id'] > 1){
									$database_user = $database_arr[0]['database_user_id'];
								}
								$database_user_num = $database_user;
								if($database_user < 1){
									$DataBPage.= 'Database User:<a href="../customerservices/ISPconfig_sync.php?ISP_server_rowid='.$server_rowid.'&socid='.$object->thirdparty->id.'&database_user_add=1&database_user_name='.$ispfunction[0]["user"].'&database_user_pass='.$ispfunction[0]["pass"].'&make=1" style="color:#ff0000">
												  			Anlegen
												  			</a>'.$massage_db_u;
								}

								if($database_arr[0]['database_id'] < 1 && $database_user > 0){
									$DataBPage.= 'Database:<a href="../customerservices/ISPconfig_sync.php?ISP_server_rowid='.$server_rowid.'&socid='.$object->thirdparty->id.'&database_add=1&database_user_name='.$ispfunction[0]["user"].'&database_user_pass='.$ispfunction[0]["pass"].'&domain_db_id='.$domain['domain_id'].'" target="_blank">
															Anlegen
														   </a>';
								}

							}else{
								// User nicht registriert
								$Pages = 'User nicht <a href="../customerservices/ISPconfig_sync.php?ISP_server_rowid='.$server_rowid.'&socid='.$object->thirdparty->id.'" target="_blank" style="color:#ff0000">Registriert!</a>';
							}//sync akzept
							
						}// server akzept
						print '<tr>';
							print '<td>';
							print 'ISP-Config Daten:';
							print '</td>';
							print '<td>';
							print $seit_status;
							print '</td>';
						print '</tr>';
					}// function abfrage
			
			  print '<tr>';
				print '<td>&nbsp;</td>';
				print '<td>';
					print $Pages;  // error oder Domain daten angeben!
				print '</td>';
			  print '</tr>';						

			if($database_arr[0]['database_id'] < 1 || $database_user < 1){

			 	print '<tr>';
					print '<td>Datenbank:</td>';
					print '<td>';
						print $DataBPage;  // error oder Domain daten angeben!
	
						foreach($database_arr as $label => $data){		
							if($data['database_id']){			
								$database_data = $ispconfigsync->get_Database($data['database_id']);			
							
								// DATABASE
								print '<br>Database Name: '.$database_data['database_name'];
								if($database_data['active'] == 'y'){
									print '<br>Status: <span style="color:#00C800">Aktiv</span>';
								}else{
									print '<br>Status: <span style="color:#ff0000">Inaktiv</span>';
								}
								
								print '<br>Type: '.$database_data['type'];
								print '<br>';
							}
						}
					print '</td>';
			 	print '</tr>';
			}else{					
					
				$database_user = $ispconfigsync->get_Database_user($database_arr[0]['database_user_id']);
				print '<tr>';
					print '<td>Datenbank:</td>';
					print '<td>';
					// USER
						print 'User: '.$database_user['database_user'].'<br>';					
					foreach($database_arr as $label => $data){						
						$database_data = $ispconfigsync->get_Database($data['database_id']);			
					
						// DATABASE 
						print '<br>Database Name: '.$database_data['database_name'];
						//STATUS:
						if($database_data['active'] == 'y'){
							print '<br>Status: <span style="color:#00C800">Aktiv</span>';
							print '<a href="../customerservices/ISPconfig_sync.php?ISP_server_rowid='.$server_rowid.'&socid='.$object->thirdparty->id.'&database_id='.$data['database_id'].'&deoraktiv=dea&whatdeakt=status">
									<img src="/theme/eldy/img/edit.png" border="0" alt="Projekt setzen" title="Deaktivieren">
								   </a>';
						}else{
							print '<br>Status: <span style="color:#ff0000">Inaktiv</span>';
							print '<a href="../customerservices/ISPconfig_sync.php?ISP_server_rowid='.$server_rowid.'&socid='.$object->thirdparty->id.'&database_id='.$data['database_id'].'&deoraktiv=akt&whatdeakt=status">
									<img src="/theme/eldy/img/edit.png" border="0" alt="Projekt setzen" title="Aktivieren">
								   </a>';
						}
						// REMOTE ACCESS:
						if($database_data['remote_access'] == 'y'){
							print '<br>Remote: <span style="color:#00C800">Aktiv</span>';
							print '<a href="../customerservices/ISPconfig_sync.php?ISP_server_rowid='.$server_rowid.'&socid='.$object->thirdparty->id.'&database_id='.$data['database_id'].'&deoraktiv=dea&whatdeakt=remote">
									<img src="/theme/eldy/img/edit.png" border="0" alt="Projekt setzen" title="Deaktivieren">
								   </a>';
						}else{
							print '<br>Remote: <span style="color:#ff0000">Inaktiv</span>';
							print '<a href="../customerservices/ISPconfig_sync.php?ISP_server_rowid='.$server_rowid.'&socid='.$object->thirdparty->id.'&database_id='.$data['database_id'].'&deoraktiv=akt&whatdeakt=remote">
									<img src="/theme/eldy/img/edit.png" border="0" alt="Projekt setzen" title="Aktivieren">
								   </a>';
						}
						print '<br>Type: '.$database_data['type'];
						print '<br>';						
					}

					print '</td>';
				 print '</tr>';
			}
			
			if($sync_akzept == 1 &&	$domain_akzept == 1 && $database_user_num > 0){
				$database_add_akz = 1;
			}
			
			
		  }
//####################################################################################     ISPconfig sync  Ende   ########################################################################################
	    
	    
	    print '</table>';
    
    
    print '<div class="tabsAction">';
    if ($user->rights->societe->creer)
    {		
    		if($database_add_akz == 1){
    			print '<a class="butAction" href="../customerservices/ISPconfig_sync.php?ISP_server_rowid='.$server_rowid.'&socid='.$object->thirdparty->id.'&database_add=2&database_user_name='.$ispfunction[0]["user"].'&database_user_pass='.$ispfunction[0]["pass"].'&domain_db_id='.$domain['domain_id'].'&database_user_id='.$database_arr[0]['database_user_id'].'" target="_blank">'.$langs->trans("Add Database").'</a>';
    		}
    		if($subdomain_add_akz == 1){
    			print '<a class="butAction" href="../customerservices/ISPconfig_sync.php?ISP_server_rowid='.$server_rowid.'&socid='.$object->thirdparty->id.'&subdomain_add=1&domain_id='.$right_domain_id.'" target="_blank">'.$langs->trans("Add Subdomain").'</a>';
    		}
    		if($alias_add_akz == 1){
    			print '<a class="butAction" href="../customerservices/ISPconfig_sync.php?ISP_server_rowid='.$server_rowid.'&socid='.$object->thirdparty->id.'&alias_add=1&domain_id='.$right_domain_id.'" target="_blank">'.$langs->trans("Add Alias").'</a>';
    		}

    		if($action != 'edit')
        		print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$_id.'&from='.$_from.'&ref='.$_ref.'&det='.$_det.'&action=edit">'.$langs->trans("Modify").'</a>';
    		
    		if($action == 'edit'){
    			print '<input type="submit" class="butAction" name="submit" value="'.$langs->trans("Modify").'">';
    			print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$_id.'&from='.$_from.'&ref='.$_ref.'&det='.$_det.'">'.$langs->trans("Cancel").'</a>';
    			
    		}
    }
    print '</div>';

}

	
	if($action == 'edit'){
		print '</form>';	
	}
	
    print '</td>';
    print '<td width="50%" valign="top">';
	    print '<table width="100%" class="noborder">';
	  	
			foreach($tree as $key => $val){
				if($val->type=='contrat' && !$conlabel){
					if($_from == 'thirdparty'){
						 print '<tr class="liste_titre">';
					    	  print '<th class="liste_titre" valign="middle" colspan="4">'.$langs->trans("ListOfContracts").'</th>';
						print '</tr>';
					}
					print '<tr class="liste_titre">';
					    	  print '<th class="liste_titre" valign="middle">'.$langs->trans("Contract").'</th>';
					    	  print '<th class="liste_titre" valign="middle">'.$langs->trans("Product").'/'.$langs->trans("Service").'</th>';
					    	  print '<th class="liste_titre" valign="middle" align="right" colspan="2">'.$langs->trans("").' </th>';
					print '</tr>';
					$conlabel = true;
				}elseif($val->type=='commande' && !$comlabel){
					if($conlabel) print '</table><br><table width="100%" class="noborder">';
					if($_from == 'thirdparty'){
						 print '<tr class="liste_titre">';
					    	  print '<th class="liste_titre" valign="middle" colspan="5">'.$langs->trans("ListOfOrders").'</th>';
						print '</tr>';
					}
					print '<tr class="liste_titre">';
				
				    	  print '<th class="liste_titre" valign="middle">'.$langs->trans("Order").'</th>';
				    	  print '<th class="liste_titre" valign="middle">'.$langs->trans("Product").'/'.$langs->trans("Service").'</th>';
				    	  print '<th class="liste_titre" valign="middle" align="right" colspan="2">'.$langs->trans("").' </th>';
				
					print '</tr>';
					$comlabel = true;
				}
				$c=0;
				if(is_array($val->det))
				foreach($val->det as $k => $det){
				$var=!$var;
					$p = new product($db);
					$p->fetch($det->fk_product);
					print "<tr ".$bc[$var].">";
						if($c==0)	print '<td width="110" valign="top"><a href="'.DOL_URL_ROOT.'/'.$val->type.'/fiche.php?id='.$val->id.'">'.img_picto('',$val->type=='commande'?'object_order':'object_contract').' '.$val->ref.'</a></td>';
						else 	print '<td></td>';
						print '<td><a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$p->id.'">'.img_picto('',$p->type?'object_service':'object_product').' '.$p->ref.'</a> - '.$p->label.'';
						if(strlen(preg_replace("/^\s+|\s+$|\<[^\>]+\>/s",'',$det->desc)) > 3)	print '</br><i>'.preg_replace("/^\s+|\s+$|\<[^\>]+\>/s",'',$det->desc).'</i>';
						
						print '</td>';
						print '<td width=1>'.img_picto('','statut'.$det->status).'</td>';
						print '<td width=1><a href="'.DOL_URL_ROOT.'/custominfo/custominfodet.php?id='.$_id.'&from='.$_from.'&ref='.$key.'&det='.$k.'">'.img_picto('','view').'</a></td>';
					print '</tr>';
					 
					$c++;
				}
			}			
						
						
						
				
	    	  
	    	print '</tr>';
	    print '</table>';
    print '</td>';
  
  
    print '</tr>';
    print '</table>';
  /*  print '<pre>';
    print_r($tree);    
    
    print '</pre>';
    
    */
    dol_fiche_end();
}

dol_htmloutput_errors('',$errors);


/*
 * Buttons
 */



llxFooter();

$db->close();

?>
