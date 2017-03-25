<?php
/**
    \file       htdocs/societe/domainmanagement.php
    \brief      Fichier permetant d'ajouter des informations technique aux societes
    \version    $Revision: 1 $
		\autor		Guido Schratzer
		\SonderFelder: Gastrokönig
		\ Branchen Info Tab unter Adressen
*/ 
require("../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/product.lib.php");
require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once("./domainmanagement.class.php");

$action = isset($_GET["action"])?$_GET["action"]:$_POST["action"];

$langs->load("companies");
$langs->load("commit");
$langs->load("bills");

// Protection quand utilisateur externe
$socid = isset($_GET["socid"])?$_GET["socid"]:$_POST["socid"];
if (!$socid) $socid=isset($_GET["id"])?$_GET["id"]:$_POST["id"];


function ddate($d){
	if(trim($d) != ''){
		$d = str_replace('/','-',$d);
		$d= date('Y-m-d',strtotime($d));
	}
	return $d;
}
	/// ### ACTIONS #### ///
	if($_POST[act] == 'edit'){
 		if(isset($_POST[submit])){
 			 $domains = new domains($db);
		 	$domains->fetchProduct($_POST[cpid]);
			$domains->id = $domains->id;
			$domains->artikelbezeichnung = trim($_POST[artikelbezeichnung]);
			$domains->betrag = trim($_POST[betrag]);
			$domains->rabatt = trim($_POST[rabatt]);
			$domains->verbis = ddate($_POST[verbis]);
			$domains->vorauszahlung = $_POST[vorauszahlung]?1:0;
			$domains->kuendigungsdatum = ddate($_POST[kuendigungsdatum]);
			$domains->ablaufdatum = ddate($_POST[ablaufdatum]);
			$domains->deleted = $_POST[deleted]?1:0;
		 	if(false){		 	
 				//$_GET[msg] = $domains->verbis;
 				header("location: domainmanagement.php?socid=".$_POST[socid]."&msg=saved&act=values&cpid=".$_POST[cpid]);
 			}else
 				header("location: domainmanagement.php?socid=".$_POST[socid]."&msg=".$domains->errormsg);
 		}else
 			header("location: domainmanagement.php?socid=".$_POST[socid]);
 			 
 }elseif($_POST[act] == 'values'){
 		if(isset($_POST[submit])){
 			 $domains = new domains($db);
		 	 $val = $domains->fetchValues($_POST[cpid]);
			foreach($val as $k){
				$domains->{$k.'_value'} = $_POST[$k];
			}	
	 	if(false){		 	
				//$_GET[msg] = $domains->verbis;
 				header("location: domainmanagement.php?socid=".$_POST[socid]."&msg=saved");
 			}else
 				header("location: domainmanagement.php?socid=".$_POST[socid]."&msg=".$domains->errormsg);
 		}else
 			header("location: domainmanagement.php?socid=".$_POST[socid]);
 			 
 }elseif($_POST[act] == 'new'){
	if(isset($_POST[submit])){
 		$domains = new domains($db);
		$domains->pid = $_POST['pid'];
		$domains->socid= $_POST['socid'];
	 
	 	if(false){		 	
				header("location: domainmanagement.php?socid=".$_POST[socid]."&act=edit&cpid=$cpid");
 			}else
 				header("location: domainmanagement.php?socid=".$_POST[socid]."&msg=".$domains->errormsg);
 		}else
 			header("location: domainmanagement.php?socid=".$_POST[socid]);
 	
}
if($_POST[mode] == 'search'){
	
	$sql = ("SELECT DOLcustomer as socid 
						FROM ".MAIN_DB_PREFIX."dv_customer_products AS cp
						INNER JOIN ".MAIN_DB_PREFIX."dv_customer_products_properties AS cpp
							ON	cpp.cpid = cp.id
								AND	(	kennz = 'DN'
									OR kennz = 'UN'
									OR kennz = 'PB'
								)
						WHERE	LOWER(text) LIKE LOWER('%".$_POST['s-value']."%')
						
						GROUP BY DOLcustomer
					");
	if($res = $db->query($sql)){	
		if($db->num_rows($reS) == 1){
			if($row = $db->fetch_object($res)){
				$socid = $row->socid;
	 			header("location: domainmanagement.php?socid=".$socid."&text=".$_POST['s-value']);

			}	
		}elseif($db->num_rows($reS) > 1){
				$socids= array();
			while($row = $db->fetch_object($res)){
				$socids[] = $row->socid;
			}
				header("Location: ../compta/rechnungsvorschlag.php?listids=".implode('|',$socids));
				exit();
		}
		
		
	}
	
}
 
/// ### ACTIONS END  #### ///

llxHeader();

$html = new Form($db);


if ($socid > 0)
{
	$soc = new Societe($db, $socid);
  $soc->fetch($socid);
  $domains = new domains($db);
	
	$head = societe_prepare_head($soc);
	
	dol_fiche_head($head, 'domainmanagement', $soc->nom);    //modifier le nom de l'onglet actif
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="30%">'.$langs->trans("Name").'</td><td width="70%" colspan="3">';
	print $soc->nom;
	print '</td></tr>';

	print '<tr><td>'.$langs->trans('Prefix').'</td><td colspan="3">'.$soc->prefix_comm.'</td></tr>';

	if ($soc->client)
	{
		print '<tr><td>';
		print $langs->trans('CustomerCode').'</td><td colspan="3">';
		print $soc->code_client;
		if ($soc->check_codeclient() <> 0) print ' '.$langs->trans("WrongCustomerCode");
		print '</td></tr>';
	}
	
	if ($soc->fournisseur)
	{
		print '<tr><td>';
		print $langs->trans('SupplierCode').'</td><td colspan="3">';
		print $soc->code_fournisseur;
		if ($soc->check_codefournisseur() <> 0) print ' '.$langs->trans("WrongSupplierCode");
		print '</td></tr>';
	}

	print "<tr><td valign=\"top\">".$langs->trans('Address')."</td><td colspan=\"3\">".nl2br($soc->adresse)."</td></tr>";

	print '<tr><td>'.$langs->trans('Zip').'</td><td>'.$soc->cp."</td>";
	print '<td>'.$langs->trans('Town').'</td><td>'.$soc->ville."</td></tr>";
	if ($soc->pays) {
		print '<tr><td>'.$langs->trans('Country').'</td><td colspan="3">'.$soc->pays.'</td></tr>';
	}

	print '<tr><td>'.$langs->trans('Phone').'</td><td>'.dol_print_phone($soc->tel,$soc->pays_code).'</td>';
	print '<td>'.$langs->trans('Fax').'</td><td>'.dol_print_phone($soc->fax,$soc->pays_code).'</td></tr>';

	print '<tr><td>'.$langs->trans("Web")."</td><td colspan=\"3\"><a href=\"http://$soc->url\" target=\"_blank\">".$soc->url."</a>&nbsp;</td></tr>";

	// Assujeti ? TVA ou pas
	print '<tr>';
	print '<td nowrap="nowrap">'.$langs->trans('VATIsUsed').'</td><td colspan="3">';
	print yn($soc->tva_assuj);
	print '</td>';
	print '</tr>';

	print '</table><br>';
 	print '<table class="border" width="100%">';

 /// ### FROMS #### ///
 if($_GET[act] == 'edit'){
 	$domains->fetchProduct($_GET[cpid]);
 	print '<tr><td><form action="domainmanagement.php?socid='.$socid.'" method="POST">';
 	print '<table class="border" width="100%">';
 	print '<tr><td><b>ID</b></td><td>'.$domains->id.'</td></tr>';
	print '<tr><td><b>Artikelbezeichnung:</b></td><td><input type="text" value="'.$domains->artikelbezeichnung.'" name="artikelbezeichnung"></td></tr>';
	print '<tr><td><b>verrechnet bis:</b></td><td>';
	$verbis = (strtotime($domains->verbis)>0)? strtotime($domains->verbis):false;
	$kuendigungsdatum = (strtotime($domains->kuendigungsdatum)>0)? strtotime($domains->kuendigungsdatum):false;
	$ablaufdatum = (strtotime($domains->ablaufdatum)>0)? strtotime($domains->ablaufdatum):false;
		
	print $html->select_date($verbis,'verbis','','',1,'','ap');
	print '</td></tr>';
	print '<tr><td><b>Zahlung jährlich im voraus:</b></td><td><input type="checkbox" name="vorauszahlung" '.($domains->vorauszahlung?'checked':'').'></td></tr>';
	print '<tr><td><b>gekündigt am:</b></td><td>';
	print $html->select_date($kuendigungsdatum,'kuendigungsdatum','','',1,'','ap');
	print '</td></tr>';
	print '<tr><td><b>Leistung bis:</b></td><td>';
	print $html->select_date($ablaufdatum,'ablaufdatum','','',1,'','ap');
	print '</td></tr>';	
	print '<tr><td><b>Leistungs-Preis:</b></td><td><input type="text" value="'.$domains->betrag.'" name="betrag"> <i>(Punkt für Komma)</i></td></tr>';
	print '<tr><td><b>Rabatt in Prozent:</b></td><td><input type="text" value="'.$domains->rabatt.'" name="rabatt"> <i>(% von Leistungs-Preis)</i></td></tr>';
	print '<tr><td><b>zum löschen vormerken:</b></td><td><input type="checkbox" name="deleted" '.($domains->deleted?'checked':'').'></td></tr>';
	//print '<tr><td colspan="2" align="center"><input type="submit" name="submit" value="'.$langs->trans('Save').'"> <input type="submit" name="cancel" value="'.$langs->trans('Cancel').'"></td></tr>';

 	print '</table>';
 	print '<input type="hidden" value="'.$_GET[cpid].'" name="cpid">';
 	print '<input type="hidden" value="edit" name="act">';
 	print '<input type="hidden" value="'.$socid.'" name="socid">';

 	print '</form></td></tr>';
 }elseif($_GET[act] == 'values'){
 	$val = $domains->fetchValues($_GET[cpid]);
 	print '<tr><td><form action="domainmanagement.php?socid='.$socid.'" method="POST">';
 	print '<table class="border" width="100%">';
	foreach($val as $k){
		print "<tr><td><b>".$domains->{$k.'_title'}."</b></td><td>";
			switch($k){
				case 'PW': 
						print '<input type="text" name="PW" value="'.$domains->{$k.'_value'}.'">';
				break;
				case 'PB': print '<textarea name="PB" rows="5" style="width:300px">'.$domains->{$k.'_value'}.'</textarea>';
				break;
				default:
				print '<input type="text" name="'.$k.'" value="'.$domains->{$k.'_value'}.'">';
			}
		print "</td></tr>";
	}
	//print '<tr><td colspan="2" align="center"><input type="submit" name="submit" value="'.$langs->trans('Save').'"> <input type="submit" name="cancel" value="'.$langs->trans('Cancel').'"></td></tr>';
 	print '</table>';
 	print '<input type="hidden" value="'.$_GET[cpid].'" name="cpid">';
 	print '<input type="hidden" value="values" name="act">';
 	print '<input type="hidden" value="'.$socid.'" name="socid">';

 	print '</form></td></tr>';
 }elseif($_GET[act] == 'new'){
 	print '<tr><td style="padding:10px;" align="center"><form action="domainmanagement.php?socid='.$socid.'" method="POST">';

$domains->SelectProducts('pid');

	//print '<br><br><input type="submit" name="submit" value="'.$langs->trans('Save').'"> <input type="submit" name="cancel" value="'.$langs->trans('Cancel').'">';

 	print '<input type="hidden" value="new" name="act">';
 	print '<input type="hidden" value="'.$socid.'" name="socid">';
	print "</form></td></tr>"; 
 	
	}
 /// ### FROMS END #### ///

 if(isset($_GET[msg])) print '<tr><td><div style="margin:4px; border:1px solid #fff; background:#eaeaea; width:80%; padding:3px; font-weight:bold;">'.$_GET[msg].'</div></td></tr>';
  
 print '<tr><td style="padding:10px;" align="right"></td></tr>';
 print '<tr><td><table class="liste noborder">'."\n";
 print '<tr class="liste_titre">';
 //print_liste_field_titre($langs->trans("Anr"),"","","",$param,"",$sortfield,$sortorder);

		$sortorder=$_GET['sortorder']?$_GET['sortorder']:'ASC';
		$sortfield=$_GET['sortfield']?$_GET['sortfield']:'artikelbezeichnung';

print '<form method="get" action="'.$_SERVER["PHP_SELF"].'">'."\n";
$param = "&socid=".$socid."&text=".$text."&pid=".$pid."";
 print_liste_field_titre($langs->trans("Artikelbezeichnug"),$_SERVER['PHP_SELF'],"artikelbezeichnung","",$param,"",$sortfield,$sortorder);
 print_liste_field_titre($langs->trans("Domain/Mbits"),$_SERVER['PHP_SELF'],"cpp.text","",$param,"",$sortfield,$sortorder);
 print_liste_field_titre($langs->trans("Billed"),$_SERVER['PHP_SELF'],"cp.verbis","",$param,"",$sortfield,$sortorder);
 print_liste_field_titre($langs->trans("Montant"),$_SERVER['PHP_SELF'],"cp.betrag","",$param,"align=\"right\"",$sortfield,$sortorder);
 print '<td class="liste_titre">&nbsp;</td>';
 print "</tr>\n";
		
print '<tr class="liste_titre">';
print '<td class="liste_titre" align="left">';
$domains->SelectProducts('pid',$_GET['pid'],1);
print '</td><td class="liste_titre" colspan="1" align="left">';
print '<input class="flat" type="text" size="20" name="text" value="'.$_GET[text].'">';
print '</td><td class="liste_titre">'.$langs->trans('gelöschte anzeigen').': <input type="checkbox" name="showdeleted" value="1" onchange="this.form.submit()" '.(($_GET['showdeleted']==1)?'checked':'').'></td>';

print '<td align="right" class="liste_titre">';
print '<input type="hidden" name="socid" value="'.$socid.'">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

print '</td>';
print '<td class="liste_titre" align="right"><input type="image" class="liste_titre" name="button_search" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" alt="'.$langs->trans('Search').'">';
print "</td></tr></form>\n";


$sql = ("SELECT cp.id as cpid, cp.customer, cp.betrag, cp.pid as pid, cp.artikelbezeichnung as artikelbezeichnung, cp.verbis as verbis, cp.kuendigungsdatum as kuendigungsdatum, cp.betrag as betrag, cp.deleted as deleted, cpp.text as domain
FROM ".MAIN_DB_PREFIX."dv_customer_products AS cp
LEFT  JOIN ".MAIN_DB_PREFIX."dv_customer_products_properties AS cpp ON cp.id = cpp.cpid 
AND (cpp.kennz='DN' or cpp.kennz='AB' or cpp.kennz is Null)");
if(!empty($_GET[text]) && trim($_GET[text]) != '')
	$sql.= (" 
	INNER JOIN ".MAIN_DB_PREFIX."dv_customer_products_properties AS cpps ON cp.id = cpps.cpid 
");

$sql.= ("WHERE cp.DOLcustomer=".$socid." ");

if(!empty($_GET[text]) && trim($_GET[text]) != '')
	$sql.= (" AND LOWER(cpps.text) LIKE  LOWER('%".trim($_GET[text])."%') ");
	
if(!empty($_GET[pid]) && $_GET[pid] > 0)
	$sql.= (" AND cp.pid = ".$_GET[pid]);
if($_GET['showdeleted']==1)
	$sql.= (" AND (cp.deleted = 1 AND cp.ablaufdatum < NOW() )");
else
	$sql.= (" AND (cp.deleted != 1 OR cp.ablaufdatum > NOW() )");


$sql.=("
  GROUP BY cp.id ORDER BY $sortfield $sortorder
");
if($res = $db->query($sql))
	while($row = $db->fetch_object( $res ) ){
		if(strtotime($row->ablaufdatum) > time() && $row->deleted==1) $background="#CC99FF";
		elseif($row->deleted==1) $background='#FF6666';
		elseif(strtotime($row->verbis) < time()) $background="#33FFFF";
		else $background="";
			
	
		$verbis = (strtotime($row->verbis)>0)?date("m/Y",strtotime($row->verbis)):'00/0000';
		$betrag = price($row->betrag);
		print ("
			<tr style='background:$background;'>
				".('' /*<td>
					{$row->pid}
				</td>*/)."
				<td>
					{$row->artikelbezeichnung}
				</td>
				<td>
					{$row->domain}
				</td>
				<td>
					{$verbis}
				</td>
				<td align='right'>
					{$betrag}
				</td>
				<td>
					&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"domainmanagement.php?act=edit&socid=$socid&cpid={$row->cpid}\">".$langs->trans('edit')."</a> <a href=\"domainmanagement.php?act=values&socid=$socid&cpid={$row->cpid}\">".$langs->trans('values')."</a>
				</td>
			</tr>
		");
	}
 print "</table>";
}

//print '</td></tr><tr><td style="padding:10px;" align="right"><a href="domainmanagement.php?act=new&socid='.$socid.'">'.$langs->trans('NewProduct').'</a></td></tr>';
print "</table></div>";
$db->close();

llxFooter('$Date: 2006/09/13 18:56:30 $ - $Revision: 1.41 $');


?>