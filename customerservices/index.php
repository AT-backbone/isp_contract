<?php
/* Copyright (C) 2012      Lukas Prömer <lukas.proemer@gmail.com>
 

*/
 ini_set('display_errors', '1');

/**
 *   \file       htdocs/societe/note.php
 *   \brief      Tab for notes on third party
 *   \ingroup    societe
 */
require("../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once (DOL_DOCUMENT_ROOT."/contrat/class/contrat.class.php");
require_once (DOL_DOCUMENT_ROOT."/product/class/product.class.php");
require_once (DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");

$langs->load("products");
$langs->load("suppliers");
$langs->load("bills");
$langs->load("custominfo@custominfo");
$langs->load("companies");
$langs->load("users");
$langs->load("other");
$langs->load("commercial");


$action = GETPOST('action');

$langs->load("companies");

$_id = GETPOST('id','int');
$socid = $_id;

if($user->societe_id) $_id=$socid=$user->societe_id;


$langs->load("products");
$langs->load("contracts");
$langs->load("companies");

$mode = GETPOST("mode");
$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page");
if ($page == -1) { $page = 0 ; }
$limit = $conf->liste_limit;
$offset = $limit * $page ;

if (! $sortfield) $sortfield="c.rowid";
if (! $sortorder) $sortorder="ASC";

$filter=GETPOST("filter");
$search_nom=GETPOST("search_nom");
$search_contract=GETPOST("search_contract");
$search_service=GETPOST("search_service");
$filter_statut=GETPOST("filter_statut");
$statut=isset($_GET["statut"])?$_GET["statut"]:1;


$staticcontrat=new Contrat($db);
$staticcontratligne=new ContratLigne($db);
$companystatic=new Societe($db);

	// Security check
	if ($user->societe_id) $socid=$user->societe_id;

	$result = restrictedArea($user, 'societe', $socid, '&societe');
	$object = new Societe($db);
	if ($socid > 0) $object->fetch($socid);
	
	


/*
 * Actions
 */




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


   		$head = societe_prepare_head($object);
		dol_fiche_head($head, 'services', $langs->trans("ThirdParty"),0,'company');
		
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
		        echo '<a href="ISPconfig_sync.php?socid='.$_id.'">ISP SYNC configurieren</a>';
		        
		        print '</td></tr>';
		    }

		    print '</table>';
    

  /*  print '<pre>';
    print_r($tree);    
    
    print '</pre>';
    
    */
    dol_fiche_end();

	##################
	## tabs Actions ##
	##################
   print '<div class="tabsAction">';

	// Add invoice
	if ($user->societe_id == 0)
	{
	
		if ($conf->facture->enabled)
		{
			if ($user->rights->facture->creer)
			{
				$langs->load("bills");
				if ($object->client != 0) print '<a class="butAction" href="'.DOL_URL_ROOT.'/compta/facture.php?action=create&socid='.$object->id.'&origin=customerservices_FacExpiredS&originid='.$object->id.'">'.$langs->trans("AddBill").'</a>';
				else print '<a class="butActionRefused" title="'.dol_escape_js($langs->trans("ThirdPartyMustBeEditAsCustomer")).'" href="#">'.$langs->trans("AddBill").'</a>';
			}
			else
			{
				print '<a class="butActionRefused" title="'.dol_escape_js($langs->trans("ThirdPartyMustBeEditAsCustomer")).'" href="#">'.$langs->trans("AddBill").'</a>';
			}
		}
	}

print '</div>';



$now=dol_now();	    
	    
	$sql = "SELECT c.rowid as cid, c.ref, c.statut as cstatut,";
	$sql.= " s.rowid as socid, s.nom,";
	$sql.= " cd.rowid, cd.description, cd.statut,";
	$sql.= " p.rowid as pid, p.ref as pref, p.label as label, p.fk_product_type as ptype,";
	if (!$user->rights->societe->client->voir && !$socid) $sql .= " sc.fk_soc, sc.fk_user,";
	$sql.= " cd.date_ouverture_prevue,";
	$sql.= " cd.date_ouverture,";
	$sql.= " cd.date_fin_validite,";
	$sql.= " cd.date_cloture";
	$sql.= " FROM ".MAIN_DB_PREFIX."contrat as c,";
	$sql.= " ".MAIN_DB_PREFIX."societe as s,";
	if (!$user->rights->societe->client->voir && !$socid) $sql .= " ".MAIN_DB_PREFIX."societe_commerciaux as sc,";
	$sql.= " ".MAIN_DB_PREFIX."contratdet as cd";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product as p ON cd.fk_product = p.rowid";
	$sql.= " WHERE c.entity = ".$conf->entity;
	$sql.= " AND c.rowid = cd.fk_contrat";
	$sql.= " AND c.fk_soc = s.rowid";
	if (!$user->rights->societe->client->voir && !$socid) $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	if ($mode == "0") $sql.= " AND cd.statut = 0";
	if ($mode == "4") $sql.= " AND cd.statut = 4";
	if ($mode == "5") $sql.= " AND cd.statut = 5";
	if ($filter == "expired") $sql.= " AND cd.date_fin_validite < '".$db->idate($now)."'";
	if ($search_nom)      $sql.= " AND s.nom like '%".$db->escape($search_nom)."%'";
	if ($search_contract) $sql.= " AND c.rowid = '".$db->escape($search_contract)."'";
	if ($search_service)  $sql.= " AND (p.ref like '%".$db->escape($search_service)."%' OR p.description like '%".$db->escape($search_service)."%' OR cd.description LIKE '%".$db->escape($search_service)."%')";
	if ($filter_statut == 1) $sql.= " AND c.statut = 0";
	if ($filter_statut == 2) $sql.= " AND cd.statut = 0";
	if ($filter_statut == 3) $sql.= " AND (cd.statut = 4 AND cd.date_fin_validite > NOW())";
	if ($filter_statut == 4) $sql.= " AND (cd.statut = 4 AND (cd.date_fin_validite < NOW() || cd.date_fin_validite IS NULL))";
	if ($filter_statut == 5) $sql.= " AND (cd.statut = 5)";
	
	if ($socid > 0)       $sql.= " AND s.rowid = ".$socid;
	$filter_date1=dol_mktime(0,0,0,$_REQUEST['op1month'],$_REQUEST['op1day'],$_REQUEST['op1year']);
	$filter_date2=dol_mktime(0,0,0,$_REQUEST['op2month'],$_REQUEST['op2day'],$_REQUEST['op2year']);
	if (! empty($_REQUEST['filter_op1']) && $_REQUEST['filter_op1'] != -1 && $filter_date1 != '') $sql.= " AND date_ouverture_prevue ".$_REQUEST['filter_op1']." ".$db->idate($filter_date1);
	if (! empty($_REQUEST['filter_op2']) && $_REQUEST['filter_op2'] != -1 && $filter_date2 != '') $sql.= " AND date_fin_validite ".$_REQUEST['filter_op2']." ".$db->idate($filter_date2);
	$sortfielde = $sortfield;
	if($sortfield == 'service'){
		$sortfielde = "ref, description";
	}
	$sql .= $db->order($sortfielde,$sortorder);
	$sql .= $db->plimit($limit + 1, $offset);
	
	//print $sql;
	dol_syslog("customerservices/index.php sql=".$sql);
	$resql=$db->query($sql);
	if ($resql)
	{
		$num = $db->num_rows($resql);
		$i = 0;
	
		$param='&amp;id='.$_id;
		if ($search_contract) $param.='&amp;search_contract='.urlencode($search_contract);
		if ($search_nom)      $param.='&amp;search_nom='.urlencode($search_nom);
		if ($search_service)  $param.='&amp;search_service='.urlencode($search_service);
		if ($mode)            $param.='&amp;mode='.$mode;
		if ($filter)          $param.='&amp;filter='.$filter;
		if (! empty($_REQUEST['filter_op1']) && $_REQUEST['filter_op1'] != -1) $param.='&amp;filter_op1='.urlencode($_REQUEST['filter_op1']);
		if (! empty($_REQUEST['filter_op2']) && $_REQUEST['filter_op2'] != -1) $param.='&amp;filter_op2='.urlencode($_REQUEST['filter_op2']);
		if ($filter_date1 != '') $param.='&amp;op1day='.$_REQUEST['op1day'].'&amp;op1month='.$_REQUEST['op1month'].'&amp;op1year='.$_REQUEST['op1year'];
		if ($filter_date2 != '') $param.='&amp;op2day='.$_REQUEST['op2day'].'&amp;op2month='.$_REQUEST['op2month'].'&amp;op2year='.$_REQUEST['op2year'];
	
		$title=$langs->trans("ListOfServices");
		if ($mode == "0") $title=$langs->trans("ListOfInactiveServices");	// Must use == "0"
		if ($mode == "4" && $filter != "expired") $title=$langs->trans("ListOfRunningServices");
		if ($mode == "4" && $filter == "expired") $title=$langs->trans("ListOfExpiredServices");
		if ($mode == "5") $title=$langs->trans("ListOfClosedServices");
		print_barre_liste($title, $page, "index.php", $param, $sortfield, $sortorder,'',$num);
	
		print '<table class="liste" width="100%">';
	
		print '<tr class="liste_titre">';
		print_liste_field_titre($langs->trans("Contract"),"index.php", "c.rowid",$param,"","",$sortfield,$sortorder);
		print_liste_field_titre($langs->trans("Service"),"index.php", "p.ref,cd.description",$param,"","",$sortfield,$sortorder);
		print '<td></td>';
	
		print '<td></td>';
		
		// Date debut
		print_liste_field_titre($langs->trans("DateStartRealShort"),"index.php", "cd.date_ouverture",$param,'',' align="center"',$sortfield,$sortorder);
		print_liste_field_titre($langs->trans("DateStartPlannedShort"),"index.php", "cd.date_ouverture_prevue",$param,'',' align="center"',$sortfield,$sortorder);
	
		// Date fin
		print_liste_field_titre($langs->trans("DateEndPlannedShort"),"index.php", "cd.date_fin_validite",$param,'',' align="center"',$sortfield,$sortorder);
		print_liste_field_titre($langs->trans("Status"),"index.php", "cd.statut,c.statut",$param,"","align=\"right\"",$sortfield,$sortorder);
		
		print '<td></td>';
		print "</tr>\n";
	
		print '<form method="POST" action="index.php?id='.$_id.'">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	
		print '<tr class="liste_titre">';
		print '<td class="liste_titre">';
		print '<input type="hidden" name="filter" value="'.$filter.'">';
		print '<input type="hidden" name="id" value="'.$_id.'">';
		print '<input type="hidden" name="mode" value="'.$mode.'">';
		print '<input type="text" class="flat" size="3" name="search_contract" value="'.dol_escape_htmltag($search_contract).'">';
		print '</td>';
		// Service label
		print '<td class="liste_titre">';
		print '<input type="text" class="flat" size="18" name="search_service" value="'.dol_escape_htmltag($search_service).'">';
		print '</td>';
		print '<td></td>';
		print '<td></td>';
		
		print '<td></td>';
		
		print '<td class="liste_titre" align="center">';
		$arrayofoperators=array('<'=>'<','>'=>'>');
		print $form->selectarray('filter_op1',$arrayofoperators,$_REQUEST['filter_op1'],1);
		print ' ';
		$filter_date1=dol_mktime(0,0,0,$_REQUEST['op1month'],$_REQUEST['op1day'],$_REQUEST['op1year']);
		print $form->select_date($filter_date1,'op1',0,0,1);
		print '</td>';
		print '<td class="liste_titre" align="center">';
		$arrayofoperators=array('<'=>'<','>'=>'>');
		print $form->selectarray('filter_op2',$arrayofoperators,$_REQUEST['filter_op2'],1);
		print ' ';
		$filter_date2=dol_mktime(0,0,0,$_REQUEST['op2month'],$_REQUEST['op2day'],$_REQUEST['op2year']);
		print $form->select_date($filter_date2,'op2',0,0,1);
		print '</td>';
		print '<td class="liste_titre" align="right">';
		
		$arrayofoperators=array(1=>$langs->trans('ContractStatusDraft'),2=>$langs->trans('ServiceStatusInitial'),3=>$langs->trans('ServiceStatusNotLateShort'),4=>$langs->trans('ServiceStatusLateShort'),5=>$langs->trans('ServiceStatusClosed') );
		print $form->selectarray('filter_statut',$arrayofoperators,$_REQUEST['filter_statut'],1);
		print '</td>';
		
		print '<td class="liste_titre" align="right"><input class="liste_titre" type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
		print "</td>";
		print "</tr>\n";
		print '</form>';
	
		$contractstatic=new Contrat($db);
		$productstatic=new Product($db);
	
		$var=True;
		while ($i < min($num,$limit))
		{
			$obj = $db->fetch_object($resql);
			$var=!$var;
			print "<tr ".$bc[$var].">";
			print '<td>';
			$contractstatic->id=$obj->cid;
			$contractstatic->ref=$obj->ref?$obj->ref:$obj->cid;
			print $contractstatic->getNomUrl(1,16);
			
			
			print '</td>';

			print '<td>';
			// Service
			if ($obj->pid)
			{
				$productstatic->id=$obj->pid;
				$productstatic->type=$obj->ptype;
				$productstatic->ref=$obj->pref;
				$text = $productstatic->getNomUrl(1,'',20);
			
				print $form->textwithtooltip($text,$obj->label, 1, 0,'','', 3);
			}
			print '</td>';
			
			print '<td>';
			if ($obj->description && $conf->global->PRODUIT_DESC_IN_LIST) print ''.preg_replace('~<br>.*~s','',dol_nl2br($obj->description));
			
			print '</td>';
				
				print '<td>';
			if($conf->global->MAIN_MODULE_CUSTOMINFO){
				require_once(DOL_DOCUMENT_ROOT.'/custominfo/class/custominfo.class.php');
				$custominfostatic = new Custominfo($db);
				$res = $custominfostatic->fetchInfoByContratdet($obj->rowid);
				$desc = '<table class="nobordernopadding" >';
				foreach($res->fields as $f){
					$desc.='<tr>';
					$desc.='<td><b>'.$f->name.':</b>&nbsp;</td>';
					$desc.='<td>'.$f->value.'</td>';
					$desc.='</tr>';
				}
				$desc .= '</table>';
				
				print $form->textwithtooltip('<a href="'.DOL_URL_ROOT.'/custominfo/custominfodet.php?id='.$obj->socid.'&from=thirdparty&ref='.$res->br.'&det='.$obj->rowid.'">'.$langs->trans('CUSTOMINFO').' '.img_picto($langs->trans('info'),'info').'</a>',$desc, 1, 0,'','',3);
			}
				print '</td>';
	
			// Start date
		
			print '<td align="center">'.($obj->date_ouverture?dol_print_date($db->jdate($obj->date_ouverture)):'&nbsp;').'</td>';
			print '<td align="center">'.($obj->date_ouverture?dol_print_date($db->jdate($obj->date_ouverture_prevue)):'&nbsp;').'</td>';
			
			// Date fin
			print '<td align="center">'.($obj->date_fin_validite?dol_print_date($db->jdate($obj->date_fin_validite)):'&nbsp;');
		
			// Icone warning
			if ($obj->date_fin_validite && $db->jdate($obj->date_fin_validite) < ($now - $conf->contrat->services->expires->warning_delay) && $obj->statut < 5) print img_warning($langs->trans("Late"));
			else print '&nbsp;&nbsp;&nbsp;&nbsp;';
			print '</td>';
			print '<td align="right" nowrap="nowrap">';
			if ($obj->cstatut == 0)	// If contract is draft, we say line is also draft
			{
				print $contractstatic->LibStatut(0,5,((!empty($obj->date_fin_validite) && $db->jdate($obj->date_fin_validite) < $now) || empty($obj->date_fin_validite))?1:0);
				
			}
			else
			{
				print $staticcontratligne->LibStatut($obj->statut,5,((!empty($obj->date_fin_validite) && $db->jdate($obj->date_fin_validite) < $now) || empty($obj->date_fin_validite))?1:0);
				
			}
			print '</td>';
			print '<td></td>';
			print "</tr>\n";
			$i++;
		}
		$db->free($resql);
	
		print "</table><br><br><br>";
	
	}
	else
	{
		dol_print_error($db);
	}
	

}




dol_htmloutput_errors('',$errors);


/*
 * Buttons
 */



llxFooter();

$db->close();

?>
