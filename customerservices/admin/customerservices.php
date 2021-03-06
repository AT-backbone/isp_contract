<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Sebastien Di Cintio  <sdicintio@ressource-toi.org>
 * Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
 * Copyright (C) 2005      Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2006-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2011 	   Juanjo Menent		<jmenent@2byte.es>
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
 *      \file       htdocs/admin/ldap.php
 *      \ingroup    ldap
 *      \brief      Page d'administration/configuration du module Ldap
 */
 ini_set('display_errors', '1');

include '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/ldap.class.php';
require_once DOL_DOCUMENT_ROOT.'/customerservices/lib/customerservices.lib.php';

$langs->load("admin");

if (!$user->admin) accessforbidden();

  $action = GETPOST("action");

  $ISP = GETPOST("ISP");

  $rowid = GETPOST("rowid");
  

/*
 * Actions
 */

if ($action == 'setvalue' && $user->admin)
{
	$error=0;

	$db->begin();
	
	if (! dolibarr_set_const($db, 'MAIN_MODULE_CUSTOMERSERVICES_ISPCONFIG',GETPOST("CUSTINFO_ISPCONFIG"),'chaine',0,'',$conf->entity)) $error++;
	//if (! dolibarr_set_const($db, 'MAIN_MODULE_CUSTOMERSERVICES_SOAP_LOCATION',GETPOST("soap_url"),'chaine',0,'',$conf->entity)) $error++;
	//if (! dolibarr_set_const($db, 'MAIN_MODULE_CUSTOMERSERVICES_SOAP_URI',GETPOST("soap_uri"),'chaine',0,'',$conf->entity)) $error++;
	//if (! dolibarr_set_const($db, 'MAIN_MODULE_CUSTOMERSERVICES_ISPCONFIG_USER',GETPOST("soap_user"),'chaine',0,'',$conf->entity)) $error++;
	//if (! dolibarr_set_const($db, 'MAIN_MODULE_CUSTOMERSERVICES_ISPCONFIG_PASS',GETPOST("pass"),'chaine',0,'',$conf->entity)) $error++;
	
	
	if (! $error)
  	{
  		$db->commit();
  		$mesg='<div class="ok">'.$langs->trans("SetupSaved").'</div>';
  	}
  	else
  	{
  		$db->rollback();
		dol_print_error($db);
    }
}
if ($action == 'setISP' && $user->admin)
{
	// SQL for all server enterys UPDATE

	$ac_rowid 		= $_POST['rowid'];
	$ac_name 		= $_POST['name'];
	$ac_soap_url 	= $_POST['soap_url'];
	$ac_soap_uri 	= $_POST['soap_uri'];
	$ac_soap_user 	= $_POST['soap_user'];
	$ac_pass 		= $_POST['pass'];
	$ac_default 	= $_POST['default'];

	$int = 0;
	foreach($ac_rowid as $id){

		$sql_name 		= $ac_name[$int];
		$sql_soap_url  	= $ac_soap_url[$int];
		$sql_soap_uri  	= $ac_soap_uri[$int];
		$sql_soap_user  = $ac_soap_user[$int];
		$sql_pass  		= $ac_pass[$int];
		$sql_default  	= $ac_default[$int];

	// UPDATE
		$sql = 'UPDATE '.MAIN_DB_PREFIX.'custominfo_ISPc_server ';
		$sql.= ' SET  ';
		$sql.= ' name = "'.$sql_name.'",';
		$sql.= ' soap_location = "'.$sql_soap_url.'",';
		$sql.= ' soap_uri = "'.$sql_soap_uri.'",';
		$sql.= ' username = "'.$sql_soap_user.'",';
		$sql.= ' password = "'.$sql_pass.'",';

		$sql.= ' default_server = "'.$sql_default.'"';

		$sql.= ' WHERE rowid = "'.$ac_rowid[$int].'"';

		$resql=$db->query($sql);
		$int++;
	}
	header("Location: ".$_SERVER["PHP_SELF"]."?ISP=1");
	//header bzw. reload
}

if ($action == 'delete' && $user->admin && $rowid > 0)
{
	// SQL for DELTE
	$sql ="DELETE FROM ".MAIN_DB_PREFIX."custominfo_ISPc_server";
	$sql.=" WHERE rowid = '".$rowid."'";

	$resql=$db->query($sql);
}
if ($action == 'makeNewISP' && $user->admin)
{
	// SQL for INSERT
	$ac_name 		= $_POST['name'];
	$ac_soap_url 	= $_POST['soap_url'];
	$ac_soap_uri 	= $_POST['soap_uri'];
	$ac_soap_user 	= $_POST['soap_user'];
	$ac_pass 		= $_POST['pass'];
	$ac_default 	= $_POST['default'];

	$sql ="INSERT INTO ".MAIN_DB_PREFIX."custominfo_ISPc_server (";
	$sql.=" soap_location ,";
	$sql.=" soap_uri ,";
	$sql.=" username ,";
	$sql.=" password ,";
	$sql.=" name ,";
	$sql.=" default_server";
	$sql.=" ) VALUES (";
	$sql.=" '".$ac_soap_url."',";
	$sql.=" '".$ac_soap_uri."',";
	$sql.=" '".$ac_soap_user."',";
	$sql.=" '".$ac_pass."',";
	$sql.=" '".$ac_name."',";
	$sql.=" '".$ac_default."'";
	$sql.=" );";

	$resql=$db->query($sql);
}


/*
 * View
 */

llxHeader('',$langs->trans("CustomerServiceSetup"),'');

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';

print_fiche_titre($langs->trans("CustomerServiceSetup"),$linkback,'setup');

$head = customerservices_prepare_head();

if(!$ISP){
	dol_fiche_head($head, 'custserv', $langs->trans("services"));
	
	$var=true;
	$form=new Form($db);
	
	
	print '<form method="post" action="'.$_SERVER["PHP_SELF"].'?action=setvalue">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Parameter").'</td>';
	print '<td>'.$langs->trans("Value").'</td>';
	print '<td>'.$langs->trans("Example").'</td>';
	print "</tr>\n";
	
	// ISP-Config ?
	$var=!$var;
	print '<tr '.$bc[$var].'><td>'.$langs->trans("ISPC").'</td><td>';
	print $form->selectyesno('CUSTINFO_ISPCONFIG',$conf->global->MAIN_MODULE_CUSTOMERSERVICES_ISPCONFIG);
	
	
	print '</td><td>&nbsp;</td></tr>';
	
}elseif($action != 'new'){
// #################################################################################  SERVER #######################################################################################################
	dol_fiche_head($head, 'ISPcServer', $langs->trans("server"));
	
	$var=true;
	$form=new Form($db);

	print '<center><input type="submit" class="button" value="'.$langs->trans("Modify").'"><a href="'.$_SERVER["PHP_SELF"].'?action=new&ISP=1" class="button">'.$langs->trans("New").'</a></center>';

	print '<form method="post" action="'.$_SERVER["PHP_SELF"].'?action=setISP&ISP=1">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Parameter").'</td>';
	print '<td>'.$langs->trans("Value").'</td>';
	print '<td>'.$langs->trans("Example").'</td>';
	print "</tr>\n";

	if ($conf->global->MAIN_MODULE_CUSTOMERSERVICES_ISPCONFIG=="yes")
	{
		// URL zum Server
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."custominfo_ISPc_server";

		$resql=$db->query($sql);
		if ($resql){
			$num = $db->num_rows($resql);
			$i = 0;

			while($i < $num){
				$object = $db->fetch_object($resql);
				$online = false;
				// Login for TEST if online

					$context = stream_context_create([
					    'ssl' => [
					        // set some SSL/TLS specific options
					        'verify_peer' => false,
					        'verify_peer_name' => false,
					        'allow_self_signed' => true
					    ]
					]);
					$client = new SoapClient(null, array(
						'location' => $object->soap_location,
						'uri'      => $object->soap_uri,
						'trace' => 1,
						'exceptions' => 1,
						'stream_context' => $context
					));

					try {
						if($session_id = $client->login($object->username, $object->password)) {
							if($session_id){
								$online = true;
							}
						}else{
							$online = false;
						}
						if($client->logout($session_id)) {
							
						}else{
							$online = false;
						}
					} catch (SoapFault $e) {}

				if($online == true){
					$var=!$var;
					print '<tr '.$bc[$var].'><td>'.$langs->trans("Status").'</td><td>';
					print '<div style="color:#00C800">Online</div>';				
					print '</td><td>&nbsp;</td></tr>';
				}elseif($online == false){
					$var=!$var;
					print '<tr '.$bc[$var].'><td>'.$langs->trans("Status").'</td><td>';
					print '<div style="color:#ff0000">Offline</div>';				
					print '</td><td>&nbsp;</td></tr>';
				}

				$var=!$var;
				print '<tr '.$bc[$var].'><td>';
				print $langs->trans("ISPC_SOAPNAME").'</td><td>';
				print '<input type="hidden" name="rowid['.$i.']" value="'.$object->rowid.'">';
				print '<input size="55" type="text" name="name['.$i.']" value="'.$object->name.'">';
				print '</td><td>'.$langs->trans("ISPC_SOAPPATHExample").'</td></tr>';

				$var=!$var;
				print '<tr '.$bc[$var].'><td>';
				print $langs->trans("ISPC_SOAPPATH").'</td><td>';
				print '<input size="55" type="text" name="soap_url['.$i.']" value="'.$object->soap_location.'">';
				print '</td><td>'.$langs->trans("ISPC_SOAPPATHExample").'</td></tr>';
				
				// URI zum Server
				$var=!$var;
				print '<tr '.$bc[$var].'><td>';
				print $langs->trans("ISPC_SOAPURI").'</td><td>';
				print '<input size="55" type="text" name="soap_uri['.$i.']" value="'.$object->soap_uri.'">';
				print '</td><td>'.$langs->trans("ISPC_SOAPURIExample").'</td></tr>';
				
				
				// Remote User
				$var=!$var;
				print '<tr '.$bc[$var].'><td>'.$langs->trans("ISPC_SOAPUSER").'</td><td>';
				print '<input size="25" type="text" name="soap_user['.$i.']" value="'.$object->username.'">';
				print '</td><td>'.$langs->trans("ISPC_SOAPUSERExample").'</td></tr>';
				
				// Pass
				$var=!$var;
				print '<tr '.$bc[$var].'><td>'.$langs->trans("Password").'</td><td>';
		
				if (! empty($object->password))
				{
					print '<input size="25" type="password" name="pass['.$i.']" value="'.$object->password.'">';// je le met en visible pour test
				}
				else
				{
					print '<input size="25" type="text" name="pass['.$i.']" value="'.$object->password.'">';
				}

				print '</td><td>secret</td></tr>';

				$var=!$var;
				print '<tr '.$bc[$var].'><td>'.$langs->trans("ISPC_default").'</td><td>';
				if($object->default_server == "yes"){
					print ' <select class="flat" id="default" name="default['.$i.']">
								<option value="yes" selected="selected">Ja</option>
								<option value="no">Nein</option>
							</select>';
				}else{
					print ' <select class="flat" id="default" name="default['.$i.']">
								<option value="yes">Ja</option>
								<option value="no" selected="selected">Nein</option>
							</select>';
				}
				//print $form->selectyesno('default',$object->default);
				print ' <a href="'.$_SERVER["PHP_SELF"].'?action=delete&rowid='.$object->rowid.'&ISP=1" class="button">delete</a>';
				print '</td><td>&nbsp;</td></tr>';

				// abstand
				print '<tr '.$bc[$var].'></tr>';

				$i++;
			}
		}
	}
}elseif ($action == 'new' && $user->admin){
	//SQL for new
	
	dol_fiche_head($head, 'ISPcServer', $langs->trans("server"));
	
	$var=true;
	$form=new Form($db);

	print '<center><input type="submit" class="button" value="'.$langs->trans("Modify").'"><a href="'.$_SERVER["PHP_SELF"].'?action=new&ISP=1" class="button">'.$langs->trans("New").'</a></center>';

	print '<form method="post" action="'.$_SERVER["PHP_SELF"].'?action=makeNewISP&ISP=1">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Parameter").'</td>';
	print '<td>'.$langs->trans("Value").'</td>';
	print '<td>'.$langs->trans("Example").'</td>';
	print "</tr>\n";

	if ($conf->global->MAIN_MODULE_CUSTOMERSERVICES_ISPCONFIG=="yes")
	{
		$var=!$var;
		print '<tr '.$bc[$var].'><td>';
		print $langs->trans("ISPC_SOAPNAME").'</td><td>';
		print '<input size="55" type="text" name="name" value="">';
		print '</td><td>'.$langs->trans("ISPC_SOAPPATHExample").'</td></tr>';

		$var=!$var;
		print '<tr '.$bc[$var].'><td>';
		print $langs->trans("ISPC_SOAPPATH").'</td><td>';
		print '<input size="55" type="text" name="soap_url" value="">';
		print '</td><td>'.$langs->trans("ISPC_SOAPPATHExample").'</td></tr>';
		
		// URI zum Server
		$var=!$var;
		print '<tr '.$bc[$var].'><td>';
		print $langs->trans("ISPC_SOAPURI").'</td><td>';
		print '<input size="55" type="text" name="soap_uri" value="">';
		print '</td><td>'.$langs->trans("ISPC_SOAPURIExample").'</td></tr>';
		
		
		// Remote User
		$var=!$var;
		print '<tr '.$bc[$var].'><td>'.$langs->trans("ISPC_SOAPUSER").'</td><td>';
		print '<input size="25" type="text" name="soap_user" value="">';
		print '</td><td>'.$langs->trans("ISPC_SOAPUSERExample").'</td></tr>';
		
		// Pass
		$var=!$var;
		print '<tr '.$bc[$var].'><td>'.$langs->trans("Password").'</td><td>';
		
		if (! empty($object->password))
		{
			print '<input size="25" type="password" name="pass" value="">';// je le met en visible pour test
		}
		else
		{
			print '<input size="25" type="text" name="pass" value="">';
		}

		print '</td><td>secret</td></tr>';

		$var=!$var;
		print '<tr '.$bc[$var].'><td>'.$langs->trans("ISPC_default").'</td><td>';
		if($object->default_server == "yes"){
			print ' <select class="flat" id="default" name="default">
						<option value="yes" selected="selected">Ja</option>
						<option value="no">Nein</option>
					</select>';
		}else{
			print ' <select class="flat" id="default" name="default">
						<option value="yes">Ja</option>
						<option value="no" selected="selected">Nein</option>
					</select>';
		}
		print '</td><td>&nbsp;</td></tr>';

		// abstand
		print '<tr '.$bc[$var].'></tr>';
	}
}
/*
// Version
$var=!$var;
print '<tr '.$bc[$var].'><td>'.$langs->trans("Version").'</td><td>';
$arraylist=array();
$arraylist['3']='Version 3';
$arraylist['2']='Version 2';
print $form->selectarray('LDAP_SERVER_PROTOCOLVERSION',$arraylist,$conf->global->LDAP_SERVER_PROTOCOLVERSION);
print '</td><td>'.$langs->trans("LDAPServerProtocolVersion").'</td></tr>';
*/



/*
// Serveur secondaire
$var=!$var;
print '<tr '.$bc[$var].'><td>';
print $langs->trans("LDAPSecondaryServer").'</td><td>';
print '<input size="25" type="text" name="slave" value="'.$conf->global->LDAP_SERVER_HOST_SLAVE.'">';
print '</td><td>'.$langs->trans("LDAPServerExample").'</td></tr>';

/*
// Port
$var=!$var;
print '<tr '.$bc[$var].'><td>'.$langs->trans("LDAPServerPort").'</td><td>';
if (! empty($conf->global->LDAP_SERVER_PORT))
{
  print '<input size="25" type="text" name="port" value="'.$conf->global->LDAP_SERVER_PORT.'">';
}
else
{
  print '<input size="25" type="text" name="port" value="389">';
}
print '</td><td>'.$langs->trans("LDAPServerPortExample").'</td></tr>';


// DNserver
$var=!$var;
print '<tr '.$bc[$var].'><td>'.$langs->trans("LDAPServerDn").'</td><td>';
print '<input size="25" type="text" name="dn" value="'.$conf->global->LDAP_SERVER_DN.'">';
print '</td><td>'.$langs->trans("LDAPServerDnExample").'</td></tr>';
*/

print '</table>';

print '<center><input type="submit" class="button" value="'.$langs->trans("Modify").'"><a href="'.$_SERVER["PHP_SELF"].'?action=new&ISP=1" class="button">'.$langs->trans("New").'</a></center>';

print '</form>';

print '</div>';

print '<br>';




dol_htmloutput_mesg($mesg);

$db->close();

llxFooter();
?>
