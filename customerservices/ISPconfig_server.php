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
require_once (DOL_DOCUMENT_ROOT."/contrat/class/contrat.class.php");
require_once (DOL_DOCUMENT_ROOT."/product/class/product.class.php");
require_once (DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
require_once (DOL_DOCUMENT_ROOT."/customerservices/class/ispconfig.class.php");

$langs->load("products");
$langs->load("contracts");
$langs->load("companies");
$langs->load("customerservices@customerservices");

// Security check
$add = GETPOST('add','int');
$rowid = GETPOST('rowid');

$soap_location = GETPOST('soap_location');
$soap_uri = GETPOST('soap_uri');
$username = GETPOST('username');
$password = GETPOST('password');

$new = GETPOST('new');
$sumbit = GETPOST('sumbit');


$ispconfigsync=new ispconfigsync($db, $conf);
$companystatic=new Societe($db);
$object = new Societe($db);
$form = new Form($db);


/*
 * SQL and other Sorces
 */

if($sumbit == "Save"){
	//SQL for update

}
if($sumbit == "add"){
	//SQL for insert

}
if($submit == "delete"){
	//SQL for delete

}

/*
 * View
 */


	$soap_location_text =  $langs->trans("location of index.php in ISPconfig remote");
	$soap_uri_text 		=  $langs->trans("Location of the remote folder in ISPconfig");
	$username_text 		=  $langs->trans("Username of the remote user in ISPconfig");
	$password_text 		=  $langs->trans("Password of the remote user in ISPconfig");



llxHeader();
if ($conf->global->MAIN_MODULE_CUSTOMERSERVICES_ISPCONFIG=="yes"){
	
	if($add == 1){

	}else{

	// ####################  VISO ####################
			print '<br>';
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."costomerinfo_ISPc_server";

			$resql=$db->query($sql);
			if ($resql){
				$num = $db->num_rows($resql);
				$i = 0;

				while($i < $num){
					$object = $db->fetch_object($resql);

					print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
					print '<input type="hidden" name="rowid" value="'.$object->rowid.'">';
					print '<input type="hidden" name="edit" value="edit">';

						
						print '<br>Name: '.$object->name;
						print '<br>soap_location: '.$object->soap_location;
						print '<br>soap_uri: '.$object->soap_uri;
						print '<br>username: '.$object->username;
						print '<br>password: '.$object->password;
						print '<br>';

					print '<input type="submit" name="sumbit" value="edit">';
					print '<input type="submit" name="sumbit" value="delete">';
					print '</form>';
					$i++;
				}
				if($new){

				}else{
					print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';	
					print '<input type="submit" name="new" value="New">';
					print '</form>';
				}
			}
		if($sumbit == "edit"){
	// ####################  Edit ####################
			print 'Edit: <br>';

			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."costomerinfo_ISPc_server";
			$sql.= " WHERE rowid = ".$rowid;

			$resql=$db->query($sql);
			if ($resql){
				$ISPserver = $db->fetch_object($resql);

				print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
				print '<input type="hidden" name="rowid" value="'.$object->rowid.'">';
				print '<input type="hidden" name="renew" value="renew">';

				print '<br><input style="width: 400px;" type="text" name="name" value="'.$ISPserver->name.'">';

				print '<br><input style="width: 400px;" type="text" name="soap_location" value="'.$ISPserver->soap_location.'">';
				print '<br><input style="width: 400px;" type="text" name="soap_uri" value="'.$ISPserver->soap_uri.'">';
				print '<br><input style="width: 400px;" type="text" name="username" value="'.$ISPserver->username.'">';
				print '<br><input style="width: 400px;" type="password" name="password" value="'.$ISPserver->password.'">';


				print '<br><br><input type="submit" name="sumbit" value="Save">';
				print '</form>';
			}
		}elseif(!$num){
			
		}
	}

} // MAIN Modul abfrage <---

/*
 * ENDE
 */

$db->close();

llxFooter();
?>
