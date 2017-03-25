<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	    \file       htdocs/contrat/services.php
 *      \ingroup    contrat
 *		\brief      Page to list services in contracts
 */

ini_set('display_errors', '1');

require ("../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once (DOL_DOCUMENT_ROOT."/contrat/class/contrat.class.php");
require_once (DOL_DOCUMENT_ROOT."/product/class/product.class.php");
require_once (DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
require_once (DOL_DOCUMENT_ROOT."/customerservices/class/ispconfig.class.php");

$langs->load("products");
$langs->load("contracts");
$langs->load("companies");
$langs->load("customerservices@customerservices");

// Security check
$id = GETPOST('id','int');

$socid = GETPOST('socid','int');

// for Expert Modus and other stuff
$all = GETPOST('all','int');
$add = GETPOST('add','int');

$registrieren = GETPOST('registrieren','int');
$groupid = GETPOST('groupid','int');

$serchN = GETPOST('serchN','int');

$f = GETPOST('f','int');

$sync = GETPOST('sync','int');
$syncALL = GETPOST('syncALL','int');

$backup = GETPOST('backup','int');
$backupon = GETPOST('backupon','int');

$synC_B = GETPOST('synC_B');
$synname = GETPOST('synname');

$ISP_server_rowid = GETPOST('ISP_server_rowid','int');
// ---------------------------------------------------

// for casual domain
$domain_add = GETPOST('domain_add','int');
$make = GETPOST('make','int');
$domain_insert = GETPOST('domain_insert');
$domain_name = GETPOST('domain_name');
$expert_m = GETPOST('expert');

// for Subdomain and Alias
$subdomain_add = GETPOST('subdomain_add');
$subdomain_insert = GETPOST('subdomain_insert');
$alias_add = GETPOST('alias_add');
$alias_insert = GETPOST('alias_insert');
$domain_id = GETPOST('domain_id');

// for FTP user
$ftp_u_ex = GETPOST('ftp_u_ex');
$ftp_pass = GETPOST('ftp_p_w');
$ftp_user = GETPOST('ftp_u_n');

// for Database like mysql
$database_add = GETPOST('database_add','int');
$database_user_add = GETPOST('database_user_add','int');

$database_insert = GETPOST('database_insert');
$database_user_insert = GETPOST('database_user_insert');

// for Database user
$database_user_name = GETPOST('database_user_name');
$database_user_pass = GETPOST('database_user_pass');
$database_user_id = GETPOST('database_user_id');
$notback = GETPOST('notback');

$domain_db_id = GETPOST('domain_db_id');

$database_id = GETPOST('database_id');
$deoraktiv = GETPOST('deoraktiv');
$whatdeakt = GETPOST('whatdeakt');

$del = GETPOST('del','int');

if($ISP_server_rowid < 0){$ISP_server_rowid = 0;}

$syncron_t = 'background-color: #D2FAD2;';
$unsyncron_t = 'background-color: #FAD2D2;';
$leer = 'background-color: #EBEBEB;';

$ispconfigsync=new ispconfigsync($db, $conf);
$companystatic=new Societe($db);
$object = new Societe($db);
$form = new Form($db);

if ($socid > 0) $object->fetch($socid);

$ispconfigsync->set_login($ISP_server_rowid); // login

$sync_akzept = 0;



/*
 * SQL and other Sorces
 */
	if($ISP_server_rowid < 1){
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."custominfo_ISPc_server ";
		$sql.= " Where default_server = 'yes' ";
		
		$resql=$db->query($sql);
		$Server_object = $db->fetch_object($resql);
		$ISP_server_rowid = $Server_object->rowid;

		header( "Location: ".$_SERVER["PHP_SELF"]."?ISP_server_rowid=".$ISP_server_rowid."&socid=".$socid);
	}else{
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."custominfo_ISPc_server ";
		$sql.= " Where rowid = '".$ISP_server_rowid."' ";
		
		$resql=$db->query($sql);		
		$Server_object = $db->fetch_object($resql);				
		$ISP_server_Name = $Server_object->name;
	}

	if($del == 1){
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."custominfo_ISPc_info WHERE fk_socid = '".$socid."'";
		$resql=$db->query($sql);
	}

	// SYNC akzept !
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."custominfo_ISPc_info";
	$sql.= " WHERE fk_socid = ".$socid;
	if($ISP_server_rowid){
	$sql.= " AND fk_server_rowid = ".$ISP_server_rowid;
	}

	$resql=$db->query($sql);
	if ($resql){
		$ISPobject = $db->fetch_object($resql);
		if($ISPobject->rowid == ""){
			$sync_akzept = 0;
		}elseif($ISPobject->rowid > 0){
			$sync_akzept = 1;
			$fk_client_id = $ISPobject->client_id;
			$fk_database_user_id = $ISPobject->fk_database_user_id;
			$fk_database_id = $ISPobject->fk_database_id;
			$ispconfigsync->sync_objekt_of_Client($socid, $object, $ISP_server_rowid);
		}
	}
	//###############################################
	if($domain_add){
		if($expert_m == 0){
			if(strlen($ftp_pass) >= 8){
				require_once (DOL_DOCUMENT_ROOT."/customerservices/class/ispconfig_domain.class.php");
				$domain_class = new ispconfigdomain($db, $conf);
				$domain_class->set_login($ISP_server_rowid); // login at domain class
	
				$ISPdomain_object = $domain_class->label_domain($ISPobject);
				$domain_input_tabel = $domain_class->show_domain_add_inteface($ISPdomain_object, $socid, $domain_name, $expert_m); // login at domain class
				$domain_id = $domain_class->make_domain_entery($ISPobject->client_id, $domain_input_tabel);
	
				if($ftp_u_ex == 0){
					$ftp_id = $domain_class->make_ftp_user($ISPobject->client_id, $ftp_array_para, $ftp_pass, $ftp_user, $domain_id);
					//print_r($ftp_id);
				}
				
				header( "Location: ".$_SERVER['HTTP_REFERER']);
				exit;
			}else{
				header( "Location: ".$_SERVER['HTTP_REFERER']."&massage_d=Password to short (8 or more characters needed)");
				exit;
			}
		}
	}
	if($database_user_add == 1 && $database_add == ''){	
		if($make == 1){ // ANLEGEN
			if(strlen($database_user_pass) >= 8){
				require_once (DOL_DOCUMENT_ROOT."/customerservices/class/ispconfig_domain.class.php");
				$database_class = new ispconfigdomain($db, $conf);
				$database_class->set_login($ISP_server_rowid); // login at domain class
	
				$param['server_id'] = 0;
				$param['database_user'] = $database_user_name;
				$param['database_password'] = $database_user_pass;
		
				$database_user_id = $database_class->make_database_user_entery($ISPobject->client_id, $param);
	
				header( "Location: ".$_SERVER['HTTP_REFERER']."&massage=".$database_user_id);				
				exit;		
			}else{
				header( "Location: ".$_SERVER['HTTP_REFERER']."&massage=Password to short (8 or more characters needed)");			
				exit;	
			}	
		}
	}

	if($deoraktiv){
		if($whatdeakt){
			require_once (DOL_DOCUMENT_ROOT."/customerservices/class/ispconfig_domain.class.php");
			$database_class = new ispconfigdomain($db, $conf);
			$database_class->set_login($ISP_server_rowid); // login at domain class
	
			$database_user_id = $database_class->aktivate_deaktivate_database($fk_client_id, $database_id, $deoraktiv, $whatdeakt);
	
			header( "Location: ".$_SERVER['HTTP_REFERER']);
			exit;
		}
	}

	if($sync==2){
		if($ISP_server_rowid == 0){
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."custominfo_ISPc_server ";
			$sql.= " Where default_server = 'yes' ";
			$resql=$db->query($sql);
			$server_obj = $db->fetch_object($resql);
			$ISP_server_rowid = $server_obj->rowid;
			
		}
			
			$ispconfigsync->make_first_entery($socid, $registrieren, $groupid, $ISP_server_rowid);
			$ispconfigsync->SYNC_ALL_ISP_TO_DOLL($socid, $registrieren);
			
			header( "Location: ISPconfig_sync.php?ISP_server_rowid=".$ISP_server_rowid."&socid=".$socid);
		}

	if($syncALL == 1 || $syncALL == 2){
		if($syncALL == 1 && $sync_akzept == 1){	// SYNC to ISP
			$ispconfigsync->SYNC_ALL_DOLL_TO_ISP($socid, $fk_client_id, $ISPobject);	
			header( "Location: ISPconfig_sync.php?ISP_server_rowid=".$ISP_server_rowid."&socid=".$socid);	
		}

		if($syncALL == 2 && $sync_akzept == 1){ // SYNC to DOLL
			$ispconfigsync->SYNC_ALL_ISP_TO_DOLL($socid, $fk_client_id, $ISP_server_rowid);	
			header( "Location: ISPconfig_sync.php?ISP_server_rowid=".$ISP_server_rowid."&socid=".$socid);	
		}
	}

	if($backup != ""){
		if($backupon == 1){
			$ispconfigsync->Make_Backup($socid, $backup);
			header( "Location: ISPconfig_sync.php?ISP_server_rowid=".$ISP_server_rowid."&socid=".$socid);
		}elseif($backupon == 2){
			$ispconfigsync->Del_Backup($socid, $backup);
			header( "Location: ISPconfig_sync.php?ISP_server_rowid=".$ISP_server_rowid."&socid=".$socid);
		}
	}

	if($synC_B != ""){		
		//zur nach Doll
		if($synC_B == "zur" && $sync_akzept == 1){			
			$ispconfigsync->SYNC_LABEL_TO_DOLL($synname, $socid, $fk_client_id, $ISP_server_rowid);
			header( "Location: ISPconfig_sync.php?ISP_server_rowid=".$ISP_server_rowid."&socid=".$socid);
		}		
		//vor nach ISP
		if($synC_B == "vor" && $sync_akzept == 1){
			$ispconfigsync->SYNC_LABEL_TO_ISP($synname, $socid, $fk_client_id, $ISPobject);
			header( "Location: ISPconfig_sync.php?ISP_server_rowid=".$ISP_server_rowid."&socid=".$socid);
		}	
	}


/*
 * View
 */

// <a href="?synname='.$label.'&socid='.$socid.'&synC=zur"><img src="/theme/eldy/img/previous.png" border="0" alt="Zurück" title="Zurück"></a>					<-
// <a href="?synname='.$label.'&socid='.$socid.'&synC=suc"><img src="/theme/eldy/img/search.png" border="0" alt="Suchen" title="Suchen"></a>					-o-
// <a href="?synname='.$label.'&socid='.$socid.'&synC=vor"><img src="/theme/eldy/img/next.png" border="0" alt="Vor" title="Vor"></a>							->

// <img src="/theme/eldy/img/switch_off.png" border="0" alt="switch_off" title="switch_off">		OFF
// <img src="/theme/eldy/img/switch_on.png" border="0" alt="switch_off" title="switch_off">			ON

llxHeader('',$langs->trans("ThirdParty").' - '.$langs->trans("Notes"),$help_url);
$head = societe_prepare_head($object);
dol_fiche_head($head, 'services', $langs->trans("ThirdParty"),0,'company');

//fon index.php
//######################################################################################################################################################################################################
//######################################################################################################################################################################################################

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
		    
		   	if ($conf->global->MAIN_MODULE_CUSTOMERSERVICES_ISPCONFIG=="yes")
		    {
						require_once (DOL_DOCUMENT_ROOT."/customerservices/class/ispconfig.class.php");

						$staticispconfig=new ispconfigsync($db, $conf);
						//$staticispconfig->fetch_SOAP_conf();
						print '<tr><td>';
		        print $langs->trans('ISPFOUND').'</td><td colspan="3">';
		        echo '<a href="ISPconfig_sync.php?socid='.$socid.'" target="_blank">ISP SYNC configurieren</a>';
		        
		        print '</td></tr>';
		    }

//######################################################################################################################################################################################################
//######################################################################################################################################################################################################

		    if(!$domain_add){

			}else{	// domain add? abfrage
				require_once (DOL_DOCUMENT_ROOT."/customerservices/class/ispconfig_domain.class.php");
				$domain_class = new ispconfigdomain($db, $conf);
				$domain_class->set_login($ISP_server_rowid); // login at domain class

				if($make == 1){
					//print_r($domain_insert);
					$domain_id = $domain_class->make_domain_entery($ISPobject->client_id, $domain_insert);
					print '<br>Domain id: '.$domain_id.'<br>';					
				}else{					
					$ISPdomain_object = $domain_class->label_domain($ISPobject);
					$domain_input_tabel = $domain_class->show_domain_add_inteface($ISPdomain_object, $socid, $domain_name, $expert_m); // login at domain class
					
					//print_r($_SERVER);
					print '<form method="post" name="domain_f" action="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&socid='.$socid.'&domain_add=1&make=1&expert='.$expert_m.'">';			
						print '<tr height="20px"><td style="border:none;"></td></tr>';
						print $domain_input_tabel;		

					print '</form>';		
							
				}
			}		

		// Subdomain and Alias
			if($domain_id > 0){
				// $domain_id is the id for the standard parameters
				require_once (DOL_DOCUMENT_ROOT."/customerservices/class/ispconfig_domain.class.php");
				$domain_class = new ispconfigdomain($db, $conf);
				$domain_class->set_login($ISP_server_rowid); // login at domain class

				// SUBDOMAIN
				if($subdomain_add == 1){

					if($make == 1){
						//print_r($subdomain_insert);
						$subdomain_id = $domain_class->make_subdomain_entery($subdomain_insert, $ISPobject->client_id, 'subdomain');
						print '<br>Domain id: '.$subdomain_id.'<br>';					
					}else{					
						$ISPsubdomain_object = $domain_class->label_subdomain($domain_id);
						$subdomain_input_tabel = $domain_class->show_subdomain_add_inteface($ISPsubdomain_object); // login at domain class
						
						//print_r($_SERVER);
						print '<form method="post" name="domain_f" action="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&socid='.$socid.'&subdomain_add=1&make=1&domain_id='.$domain_id.'">';			
							print '<tr height="20px"><td style="border:none;"></td></tr>';
							print $subdomain_input_tabel;		
	
						print '</form>';		
								
					}
				}

				// ALIAS
				if($alias_add == 1){

					if($make == 1){
						//print_r($subdomain_insert);
						$subdomain_id = $domain_class->make_subdomain_entery($subdomain_insert, $ISPobject->client_id, 'alias');
						print '<br>Domain id: '.$subdomain_id.'<br>';					
					}else{					
						$ISPsubdomain_object = $domain_class->label_subdomain($domain_id);
						$subdomain_input_tabel = $domain_class->show_subdomain_add_inteface($ISPsubdomain_object); // login at domain class
						
						//print_r($_SERVER);
						print '<form method="post" name="domain_f" action="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&socid='.$socid.'&alias_add=1&make=1&domain_id='.$domain_id.'">';			
							print '<tr height="20px"><td style="border:none;"></td></tr>';
							print $subdomain_input_tabel;		
	
						print '</form>';		
								
					}
				}
			}
		/**
															DATABASE AND USER
		  */
			if($database_add == 2){
				require_once (DOL_DOCUMENT_ROOT."/customerservices/class/ispconfig_domain.class.php");
				$database_class = new ispconfigdomain($db, $conf);
				$database_class->set_login($ISP_server_rowid); // login at domain class

				// Wenn -->!noch!<-- eine Datenbank angelegt werden soll	

				if($make == 1){ // ANLEGEN
					$database_id = $database_class->make_database_entery($ISPobject->client_id, $database_insert, '1', $domain_db_id);
					print '<br>This Domain id: '.$database_id.'<br>';		

				}else{ // INTERFACE		

					$ISPdatabase_object = $database_class->label_database($ISPobject, $database_user_name, $database_user_pass, $fk_database_user_id, $domain_db_id, $database_user_id);
					$database_input_tabel = $database_class->show_database_add_inteface($ISPdatabase_object, $socid); // login at domain class
					
					print '<form method="post" name="database_f" action="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&socid='.$socid.'&database_add=2&make=1&domain_db_id='.$domain_db_id.'">';			
						print '<tr height="20px"><td style="border:none;"></td></tr>';
						print $database_input_tabel;		
	
					print '</form>';		
							
				}

			}
			if($database_add == 1 || $database_user_add == 1){ 
				require_once (DOL_DOCUMENT_ROOT."/customerservices/class/ispconfig_domain.class.php");
				$database_class = new ispconfigdomain($db, $conf);
				$database_class->set_login($ISP_server_rowid); // login at domain class

				if($database_add == 1 && $database_user_add == 0){ 
					// Wenn eine Datenbank angelegt werden soll	

					if($make == 1){ // ANLEGEN
						$database_id = $database_class->make_database_entery($ISPobject->client_id, $database_insert, "0", $domain_db_id);
						print '<br>Domain id: '.$database_id.'<br>';		

					}else{ // INTERFACE		

						$ISPdatabase_object = $database_class->label_database($ISPobject, $database_user_name, $database_user_pass, $fk_database_user_id, $domain_db_id, $database_user_id);
						$database_input_tabel = $database_class->show_database_add_inteface($ISPdatabase_object, $socid); // login at domain class
						
						print '<form method="post" name="database_f" action="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&socid='.$socid.'&database_add=1&make=1&domain_db_id='.$domain_db_id.'">';			
							print '<tr height="20px"><td style="border:none;"></td></tr>';
							print $database_input_tabel;		
	
						print '</form>';		
								
					}
				}elseif($database_user_add == 1 && $database_add == 0){	
					// Wenn ein Datenbank User angelegt werden soll

					if($make == 1){ // ANLEGEN

						$param['server_id'] = 0;
						$param['database_user'] = $database_user_name;
						$param['database_password'] = $database_user_pass;

						$database_user_id = $database_class->make_database_user_entery($ISPobject->client_id, $param);

						header( "Location: ".$_SERVER['HTTP_REFERER']);
						exit;

						print '<br>Domain id: '.$database_user_id.'<br>';	

					}else{ // INTERFACE			

						$ISPdatabase_user_object = $database_class->label_database_user($ISPobject, $database_user_name, $database_user_pass);
						$database_user_input_tabel = $database_class->show_database_user_add_inteface($ISPdatabase_user_object, $socid); // login at domain class
						
						print '<form method="post" name="database_user_f" action="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&socid='.$socid.'&database_user_add=1&make=1">';			
							print '<tr height="20px"><td style="border:none;"></td></tr>';
							print $database_user_input_tabel;		
	
						print '</form>';		
								
					}
				}
			}

		/**
															DATABASE AND USER
		  */
		print '</table>';

//######################################################################################################################################################################################################
//######################################################################################################################################################################################################


if ($conf->global->MAIN_MODULE_CUSTOMERSERVICES_ISPCONFIG=="yes"){
	if(!$domain_add){	// wenn nicht eine Domain hinzugefügt wird
		if(!$subdomain_add){
			if(!$alias_add){
				if($database_add == 0 && $database_user_add == 0){ // Database..
					if(!$add){	// wenn nicht ein neuer Kunde angelegt werden soll
				
						//$time_start_2 = microtime(true);		
				
						//print_r($id_array);
						//echo '<br>';
				
						//print_r($ispconfigsync->get_funktionen(), 1);
						//echo '<br>';	
				
						$sql = "SELECT * FROM ".MAIN_DB_PREFIX."custominfo_ISPc_server";
				
						$resql=$db->query($sql);
						if ($resql){
							$num = $db->num_rows($resql);
							$i = 0;
							print '<form method="post" action="'.$_SERVER["PHP_SELF"].'?socid='.$socid.'">';
								print '<label for="ISP_server_rowid">ISPconfig Server: </label>';
								print '<select name="ISP_server_rowid" onchange="submit()">';
								while($i < $num){
									$server_object = $db->fetch_object($resql);
									
									print '<option value="'.$server_object->rowid.'" ';
									if($server_object->default_server == "yes" && $ISP_server_rowid == 0){
										print 'selected="selected"';
									}elseif($server_object->rowid == $ISP_server_rowid){
										print 'selected="selected"';
									}
									print '>';
									print $server_object->name;
									print '</option>';
									$i++;
								}						
								print '</select>';
							print '</form>';
						}
						if($sync_akzept == 0){
								print '<script type="text/javascript" language="Javascript">'; 
								print 'alert("Der User ist noch nicht im Module Registriert! Der Such Vorgang kann einen Moment dauern.")';
								print '</script>';
				
								$id_array = $ispconfigsync->get_all_Client();
						}else{
							$id_array[0] = $fk_client_id;
						}
						foreach($id_array as $id){
							
							if($id == $fk_client_id){
								$Client = $ispconfigsync->get_Client_u_id($id);
							}elseif($sync_akzept == 0){
								$Client = $ispconfigsync->get_Client_u_id($id);
							}
				
							if($object->code_client == $Client['customer_no']){
								if($groupid == ""){
										$groupid = $ispconfigsync->get_client_groupid($Client['client_id']);
								}
								//print ' Group id:|'.$groupid.'| <br>';
								if($sync_akzept == 1){					
									$Sites = $ispconfigsync->get_Client_sites(0, $groupid, '');
									
									
									foreach($Sites as $label => $domain){
										$Pages.= '<br><br> Num: '.$label;
										$Pages.= '<br>Domain: '.$domain['domain'];
										$Pages.= '<br>Domain_id: '.$domain['domain_id'];
										$Pages.= '<br>Document_root: '.$domain['document_root'];
										$Pages.= '<br>Active: '.$domain['active'];					
									}
								}
								//print_r($Sites);
								//print '<br><br><h1>User Found!</h1><h2> '.$Client['company_name'].' is a ISP User/Client </h2>';
								//echo '<h2>ID: '.$id.'</h2><br>';				
							
				
							$tabele = "";
							$unsyncron_c=0;
						// #####################################################   INSERT TABELL   ###############################################################################
								foreach($Client as $label => $data){
									$data_wordwraped = $data;
									$data_wordwraped = wordwrap($data_wordwraped, 50, "\n", true);	
									$data_UP = strtoupper($data);
				
									$aus_c = ""; $aus_n = "";
					
									$aus_array = $ispconfigsync->label_switch($label, $object, $ISPobject);
									$aus_array['aus_c_wordwraped'] = $aus_array['aus_c'];
									$aus_array['aus_c_wordwraped'] = wordwrap($aus_array['aus_c_wordwraped'], 50, "\n", true);
									$aus_array['aus_c_UP'] = strtoupper($aus_array['aus_c']);
				
									$tabele.= '<tr style="text-align: left;max-width:550px;">';
					
									// Beschreibung
										$tabele.= '<th style="'; if($aus_array['aus_c'] == "" && $data == ""){$tabele.=$leer;}elseif($aus_array['aus_c_UP'] == $data_UP){$tabele.=$syncron_t;}else{$tabele.=$unsyncron_t; $unsyncron_c++;} $tabele.='">';
											$tabele.= $aus_array['aus_n'];
										$tabele.= '</th>';
					
									// DOLLIABAR TABELLE
										$tabele.= '<th width="500px" style="'; if($aus_array['aus_c'] == "" && $data == ""){$tabele.=$leer;}elseif($aus_array['aus_c_UP'] == $data_UP){$tabele.=$syncron_t;}else{$tabele.=$unsyncron_t; $unsyncron_c++;} $tabele.='">';
											$tabele.= ' '.$aus_array['aus_c_wordwraped'];
										$tabele.= '</th>';
					
									// ISPconfig TABELLE
										$tabele.= '<th width="500px" style="'; if($aus_array['aus_c'] == "" && $data == ""){$tabele.=$leer;}elseif($aus_array['aus_c_UP'] == $data_UP){$tabele.=$syncron_t;}else{$tabele.=$unsyncron_t; $unsyncron_c++;} $tabele.='">';
											$tabele.= ' '.$data_wordwraped;
										$tabele.= '</th>';
					
									// SYNC button	
										$tabele.= '<th style="text-align: center;">';
											if($sync_akzept == 1){				
												if($aus_array['aus_c'] == "" && $data == ""){
													
												}elseif($aus_array['aus_c'] == $data){
													//if($backup == true){
													//	$tabele.='<a href="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&backup='.$label.'&backupon=2&socid='.$socid.'"><img src="/theme/eldy/img/switch_on.png" border="0" alt="Backup_off" title="Backup_off"></a>';
													//	
													//}else{
													//	$tabele.='<a href="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&backup='.$label.'&backupon=1&socid='.$socid.'"><img src="/theme/eldy/img/switch_off.png" border="0" alt="Backup_on" title="Backup_oon"></a>';
													//	
													//}
												}else{
													$tabele.= '<a href="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&synname='.$label.'&socid='.$socid.'&synC_B=zur"><img src="/theme/eldy/img/previous.png" border="0" alt="Sync to DOLL" title="Sync to DOLL"></a>';
													
													$tabele.= '<a href="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&synname='.$label.'&socid='.$socid.'&synC_B=vor"><img src="/theme/eldy/img/next.png" border="0" alt="Sync to ISP" title="Sync to ISP"></a>';
												}
											}				
										$tabele.= '</th>';
									$tabele.= '</tr>';
								}  
							}
// #				####################################################   INSERT TABELL ENDE  ###############################################################################
// -				----------------------------------------------------------------------------------------------------------------------------------------------------------
// #				########################################################## Überprüfungs strategien #######################################################################
							if($Client['customer_no'] == $object->code_client){
								if($sync_akzept == 1){
								}else{
									$richtiger_user_text = '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&sync=2&registrieren='.$Client['client_id'].'&groupid='.$groupid.'&socid='.$socid.'">'.$langs->trans("Richtiger User").'</a>';	
								}	
				
									//print_r($Client);
									//print_r($object);
				
								$tabel_s.=$tabele;
								$gefunden=1;
							}elseif($Client['company_name'] == $object->name){
								if($serchN == 1){
									if($sync_akzept == 1){
									}else{
										$richtiger_user_text = '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&sync=2&registrieren='.$Client['client_id'].'&groupid='.$groupid.'&socid='.$socid.'">'.$langs->trans("Richtiger User").'</a>';	
									}	
								
									$tabel_s.=$tabele;
									$gefunden=1;
								}
							}
							if($gefunden == 1){
								break;				
							}
						}
						//$time_end_2 = microtime(true);
						//$time_2 = $time_end_2 - $time_start_2;
						//print '<br>Time: '.$time_2.'<br>';
					
					// ###############################################     KUNDE NICHT GERFUNDEN     ####################################################################
						if($gefunden == 0){
							echo '<h1>Der Kunde wurde auf ISPconfig nicht gefunden.</h1>';
							if($serchN == 0){
							echo '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&serchN=1&socid='.$socid.'">'.$langs->trans("nach Name suchen").'</a>';
							}
							echo '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&add=1&socid='.$socid.'">'.$langs->trans("Kunde neu anlegen").'</a>';	
				
					// ###################################################################################################################################################
					//																	TABELL 																			 #
					// ###################################################################################################################################################	
						}else{	
							if($sync_akzept == 1){
								if($unsyncron_c > 0){
									$sync_a_z_ISP = '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&syncALL=1&socid='.$socid.'">'.$langs->trans("Syncroniesiere alles zu ISPconfig3").'</a>';	
									$sync_a_z_Dol = '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&syncALL=2&socid='.$socid.'">'.$langs->trans("Syncroniesiere alles zu Dollibarr").'</a>';	
								}
								$del_user_text = '<br><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&del=1&socid='.$socid.'">'.$langs->trans("User aus Module Löschen").'</a><br><br>';	
							}
							print '</tabel>';
		
							//print '<h1>'.$ISP_server_Name.'</h1>';
		
							print '<table id="Vergleich" border="1px" style="border-collapse: collapse;border-color: #000000;">';
								print '<tr><td colspan=4 style="text-align: center;background-color: rgb(159, 161, 186);color: white;"><h1>'.$ISP_server_Name.'</h1></td></tr>';
								print ' <tr style="height: 45px;background-color: rgb(159, 161, 186);color: white;">
											<th>'.$del_user_text.$richtiger_user_text.'</th>
											<th>'.$sync_a_z_Dol.'</th>
											<th>'.$sync_a_z_ISP.'</th>
											<th></th>
									    </tr>';
								print '	<tr style="height: 45px;background-color: rgb(159, 161, 186);color: white;">
											<th style="width: 150px;">Beschreibung</th>
											  <th style="width: 50%;">Dollibarr</th>
											  <th style="width: 50%;">'.$ISP_server_Name.'</th>
											 <th style="width: 65px;">SYNC.</th>
										</tr>';
								print 	$tabel_s; // alle Tabellen forgänge <--
							print '</table>';
						}
					print $Pages;
// #				###############################################################################################################################################################
//																											ERSTELLEN															  #
// #				###############################################################################################################################################################
				
					}else{  // !add <---
						if($f == 1){		
							echo '<h1>Erstellt</h1>';
							$para = $ispconfigsync->make_soc_to_ISP($object, $socid);
							//print_r($para);
							print $para['password'];
							$test_f = $ispconfigsync->add_Client($para);
				
							//echo $test_f;
							//header( "Location: ISPconfig_sync.php?reload=1&ISP_server_rowid=".$ISP_server_rowid."&socid=".$socid);
							print '<a href="ISPconfig_sync.php?reload=1&ISP_server_rowid='.$ISP_server_rowid.'&socid='.$socid.'">Reload</a>';
						}else{
				
					//################################# Kunden überprüfung #################################################
							echo '<h1>Der Kunde wird erstellt.</h1>';
							echo '<h2>Bitte die Daten überprüfen.</h2>';
					
							print '<table id="Vergleich" border="1px" style="text-align:left;">';
								echo '<tr>';
									echo '<th>';
										echo 'Name:';
									echo '</th>';
									echo '<th>';
										echo $object->name;
									echo '</th>';
								echo '</tr>';
								echo '<tr>';
									echo '<th>';
								echo 'Kunden Code:';
									echo '</th>';
									echo '<th>';
										echo $object->code_client;
									echo '</th>';
								echo '</tr>';
								echo '<tr>';
									echo '<th>';
								echo 'Postleitzahl:';
									echo '</th>';
									echo '<th>';
										echo $object->zip;
									echo '</th>';
								echo '</tr>';
								echo '<tr>';
									echo '<th>';
								echo 'Adresse:';
									echo '</th>';
									echo '<th>';
										echo $object->address;
									echo '</th>';
								echo '</tr>';
								echo '<tr>';
									echo '<th>';
								echo 'Stadt:';
									echo '</th>';
									echo '<th>';
										echo $object->town;
									echo '</th>';
								echo '</tr>';
								echo '<tr>';
									echo '<th>';
								echo 'Tel.:';
									echo '</th>';
									echo '<th>';
										echo $object->tel;
									echo '</th>';
								echo '</tr>';
								echo '<tr>';
									echo '<th>';
								echo 'Mobil:';
									echo '</th>';
									echo '<th>';
										echo $object->phone;
									echo '</th>';
								echo '</tr>';
								echo '<tr>';
									echo '<th>';
								echo 'Fax:';
									echo '</th>';
									echo '<th>';
										echo $object->fax;
									echo '</th>';
								echo '</tr>';
								echo '<tr>';
									echo '<th>';
								echo 'Email:';
									echo '</th>';
									echo '<th>';
										echo $object->email;
									echo '</th>';
								echo '</tr>';
							print '</table>';
							echo '<br><br><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?ISP_server_rowid='.$ISP_server_rowid.'&add=1&f=1&socid='.$socid.'">'.$langs->trans("Fertig").'</a>';
				
						//################################# Kunden überprüfung ENDE #################################################
						}
					} // add
				} // database
			} // add alias
		} // add subdomain
	} // domain

} // MAIN Modul abfrage <---

/*
 * ENDE
 */

$db->close();

llxFooter();
?>
