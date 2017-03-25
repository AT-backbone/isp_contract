<?php
/* Copyright (C) 2012      Lukas Prömer <lukas.proemer@gmail.com>
 

*/


/**
 *  \file       htdocs/product/fournisseurs.php
 *  \ingroup    product
 *  \brief      Page of tab suppliers for products
 */

require("../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/product.lib.php");
require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");
require_once(DOL_DOCUMENT_ROOT."/custominfo/class/custominfo.class.php");

$langs->load("products");
$langs->load("suppliers");
$langs->load("bills");
$langs->load("custominfo@custominfo");


$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'int');
$action=GETPOST('action', 'alpha');
$socid=GETPOST('socid', 'int');
$error=0; $mesg = '';
$mesg	= GETPOST('mesg');

// Security check
/*
$fieldvalue = (! empty($id) ? $id : (! empty($ref) ? $ref : ''));
$fieldtype = (! empty($ref) ? 'ref' : 'rowid');

if ($user->societe_id) $socid=$user->societe_id;
$result=restrictedArea($user,'produit|service&fournisseur',$fieldvalue,'product&product','','',$fieldtype);
*/

/*
 * Actions
 */
 
 
$object = new Product($db);


if ($id > 0)
{
	$result = $object->fetch($id);

}


if ($_POST["cancel"] == $langs->trans("Cancel"))
{
	$action = '';
	Header("Location: custominfo.php?id=".$_GET["id"]);
	exit;
}
if($_POST['action'] == 'resort'){
	$resort=GETPOST('resort', 'alpha');
	/*
		$resort == 5++ // id 5 moves down
		$resort == 5-- // id 5 moves up
		$resort == 5=2 // id 5 moves on position 2
	
	*/
	$ci = new custominfo($db);
	$l = $ci->fetchLinesByProduct($object->id);	
	$e=0;
	$regex = "/^(\d+)([\+\-]{2}|[\=]{1})+(\d+)?$/x";  
	if(!preg_match ($regex, $resort, $o)) {
		header("Location: custominfo.php?id=".$id);
		exit();
	}
	$ref = $o[1];
	$r = $o[2];
	$n = $o[3];
	$k=0;
	$ta = array();
	foreach($l->byid as $lid => $v){
		//$v = explode(':',$v);	
		$e=$k;
		if($lid==$ref){
			if($r=='++'){
				$e = $k+15;	
			}elseif($r=='--'){
				$e = $k-15;	
			}elseif($r='='){
				$e=($n*10)-5;
			}	
		}
		
		$ta[$e] = $lid;
		
		$k=$k+10;
	}
	ksort($ta);
	$e=0;
	foreach($ta as $lid){
		$ci = new custominfo($db);
		$ci->fetch($lid);
		$ci->sort = $e;
		$ci->update($user);
		$e++;
	}	
	header("Location: custominfo.php?id=".$id);
		
}
if($_POST['action'] == 'create' || $_POST['action'] == 'edit'){
	
	if(empty($_POST['name'])){ $error++; $mesg='<div class="error">'.$langs->trans('custominfoErrorNoTagName').'</div>'; }
	
	$ci = new custominfo($db);
	if($action == 'edit')
		$ci->fetch($ref);
	
	$l = $ci->fetchLinesByProduct($object->id);	
	if(
		isset($l->byname[trim($_POST['name'])])
	&&   (	$_POST['action'] == 'create'	
		||	(
				$_POST['action'] == 'edit'
			&&	trim($_POST['name']) != $ci->name
			)
		)
	){ $error++; $mesg='<div class="error">'.$langs->trans('custominfoErrorDuplicateTagName').'</div>'; };


	$ci->fk_product=$object->id;
	$ci->name=$_POST['name'];
	$ci->type=$_POST['type'];
	$ci->value=$_POST['value'];
	$ci->sort=$_POST['sort'];
	

	if($ci->type == 'select' || $ci->type == 'p_select'){
		if(empty($_POST['value'])){ $error++; $mesg='<div class="error">'.$langs->trans('custominfoErrorNoValue').'</div>'; }
		
		$ci->value = preg_replace('/\s{2,}/',' ',$ci->value);
		$ci->value = explode(',',$ci->value);
		foreach($ci->value as $v) $v=trim($v);
		$ci->value = implode(', ',$ci->value);
	}
	if($ci->type == 'checkbox' || $ci->type == 'p_checkbox'){
		$ci->value = (strtoupper(trim($ci->value))=='YES' || trim($ci->value)=='1')? '1' : '0';
	}
	
	if(!$error){
		if($action != 'edit')
			$e = $ci->create($user);
		if($e < 0){
			print '<pre>';
			print_r($db);
		}
		$e = $ci->update($user);
		if($e < 0){
			print '<pre>';
			print_r($db);
			exit();
			die();
		$mesg='<div class="error">'.$langs->trans("custominfoErrorUpdating",$cat->label).'</div>';
		}else
		$mesg='<div class="ok">'.$langs->trans("custominfoSuccessful",$cat->label).'</div>';
		header("Location: custominfo.php?id=".$id."&mesg=".$mesg);
	}
	

		
}
if($_GET['action'] == 'confirm_delete'){
	if($_GET['confirm'] == 'yes'){
		$ci = new custominfo($db);
		$ci->fetch($ref);
		if($ci->delete($user))
			$mesg='<div class="ok">'.$langs->trans("custominfoDeleteSuccessful",$cat->label).'</div>';
		
	}else
		$mesg='';
	header("Location: custominfo.php?id=".$id."&mesg=".$mesg);
}


/*
 * view
 */

$form = new Form($db);


if ($object->id)
{
			llxHeader("","",$langs->trans("CardProduct".$object->type));
		
			dol_htmloutput_mesg($mesg);	
			/*
			 *  En mode visu
			*/
			$head=product_prepare_head($object, $user);
			$titre=$langs->trans("CardProduct".$object->type);
			$picto=($object->type==1?'service':'product');
			dol_fiche_head($head, 'custominfo', $titre, 0, $picto);
			
			/*
			 * Confirmation de la suppression de photo
			*/
			if ($_GET['action'] == 'delete')
			{
				$ret=$form->form_confirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&ref='.$_GET["ref"], $langs->trans('DeleteCustomField'), $langs->trans('ConfirmCustomField'), 'confirm_delete', '', 0, 1);
				if ($ret == 'html') print '<br>';
			}
			
			//print($mesg);
			
			print '<table class="border" width="100%">';
			
			// Reference
			print '<tr>';
			print '<td width="15%">'.$langs->trans("Ref").'</td><td colspan="2">';
			print $form->showrefnav($object,'ref','',1,'ref');
			print '</td>';
			print '</tr>';
			
			// Libelle
			print '<tr><td>'.$langs->trans("Label").'</td><td colspan="2">'.$object->libelle.'</td>';
			print '</tr>';
			
			// Status (to sell)
			print '<tr><td>'.$langs->trans("Status").' ('.$langs->trans("Sell").')</td><td>';
			print $object->getLibStatut(2,0);
			print '</td></tr>';
			
			// Status (to buy)
			print '<tr><td>'.$langs->trans("Status").' ('.$langs->trans("Buy").')</td><td>';
			print $object->getLibStatut(2,1);
			print '</td></tr>';
			
			print "</table>\n";
			
			
			print "</div>\n";
			
			
			
			/* ************************************************************************** */
			/*                                                                            */
			/* Barre d'action                                                             */
			/*                                                                            */
			/* ************************************************************************** */
			
			print "\n<div class=\"tabsAction\">\n";
			print '<a class="butAction" href="?id='.$id.'&action=create">'.$langs->trans('AddCustomInfo').'</a>';


			print "\n</div>\n";
			
			if($action == 'edit' || $action == 'create'){
				
				print '<form method="POST" action="custominfo.php?id='.$object->id.'">';
				if($action == 'edit'){
					print '<input type="hidden" name="action" value="edit">';
					print '<input type="hidden" name="ref" value="'.$ref.'">';
					
					$ci = new custominfo($db);
					$ci->fetch($ref);
					
					print_fiche_titre($langs->trans('custominfoEditLine'),'','');
					
				}else{
					print '<input type="hidden" name="action" value="create">';
					$ci = new custominfo($db);
					$ci->name = $_POST['name'];
					$ci->type = $_POST['type'];
					$ci->value = $_POST['value'];
					$l = $ci->fetchLinesByProduct($object->id);
					$ci->sort=($l->count > 0)?$l->count:0;
					print_fiche_titre($langs->trans('custominfoCreateLine'),'','');
				}				
				print '<input type="hidden" name="sort" value="'.$ci->sort.'">';
				print '<table class="noborder" width="100%">';
				print '<tr class="liste_titre">';
					print '<td class="liste_titre">'.$langs->trans("Name").'</td>';
					print '<td class="liste_titre">'.$langs->trans("CustominfoType").'</td>';
					print '<td class="liste_titre">'.$langs->trans("CustominfoDefaultValue").'</td>';
					print '<td class="liste_titre" colspan="2"></td>';
				print '</tr>';
				print '<tr>';
					print '<td width="10"><input type="text" name="name" value="'.$ci->name.'"></td>';
					$array = array(
						'text' => $langs->trans('CustominfoTypeText'),
						'select' => $langs->trans('CustominfoTypeSelect'),
						'checkbox' => $langs->trans('CustominfoTypeCheckbox'),
						'number' => $langs->trans('CustominfoTypeNumber'),
						'longtext' => $langs->trans('CustominfoTypeLongText'),
						'password' => $langs->trans('CustominfoTypePassword'),
						'date' => $langs->trans('CustominfoTypeDate'),
						/*,
						'-1" disabled="' => '',
						'-2" style="font-weight:bold" disabled="' => $langs->trans('CustominfoTypePrivate'),
						'p_text' => $langs->trans('CustominfoTypePrivateText'),
						'p_select' => $langs->trans('CustominfoTypePrivateSelect'),
						'p_checkbox' => $langs->trans('CustominfoTypePrivateCheckbox'),
						'p_longtext' => $langs->trans('CustominfoTypePrivateLongText'),
						'p_password' => $langs->trans('CustominfoTypePrivatePassword'),
						'-3" disabled="' => '',
						'-4"  style="font-weight:bold" disabled="' => $langs->trans('CustominfoTypeIntern'),
						'i_text' => $langs->trans('CustominfoTypeInternText'),
						'i_select' => $langs->trans('CustominfoTypeInternSelect'),
						'i_checkbox' => $langs->trans('CustominfoTypeInternCheckbox'),
						'i_longtext' => $langs->trans('CustominfoTypeInternLongText'),
						'i_password' => $langs->trans('CustominfoTypeInternPassword')*/
						
					);
					if ($conf->global->MAIN_MODULE_CUSTOMERSERVICES_ISPCONFIG=="yes")
					{
				  $array['ispconfig_function'] = $langs->trans('CustominfoTypeCustomInfoFunction');
				  }
					print '<td width="10">'.$form->selectarray('type', $array, $ci->type,0,0,0).'</td>';
					print '<td><input type="text" size="75" name="value" value="'.$ci->value.'"></td>';
						
					print '<td width="10"><input class="button" type="submit" name="submit" value="'.$langs->trans('Save').'"></td>';
					print '<td width="10"><input class="button" type="submit" name="cancel" value="'.$langs->trans('Cancel').'"></td>';
					
				print '</tr>';
				print '</table>';
				print '<p>'.$langs->trans('CustominfoCreatingInformations').'</p>';
				
				print '</form>';
			
			}
			
			print "<br />\n";
			
			print_fiche_titre($langs->trans('custominfoLines'),'','');
			$ci = new custominfo($db);
			$l = $ci->fetchLinesByProduct($object->id);
			$l = $l->bysort;
			print '<form method="POST" action="custominfo.php?id='.$object->id.'">';
			print '<input type="hidden" name="action" value="resort">';
			print '<table class="noborder" width="100%">';
				print '<tr class="liste_titre">';
					print '<td class="liste_titre">'.$langs->trans("Name").'</td>';
					print '<td class="liste_titre">'.$langs->trans("CustominfoType").'</td>';
					print '<td class="liste_titre">'.$langs->trans("CustominfoDefaultValue").'</td>';
					print '<td class="liste_titre" colspan="3"></td>';
				print '</tr>';
				if(is_array($l))
				foreach($l as $key => $val){
					$var=!$var;
					print "<tr ".$bc[$var].">";
						print '<td>'.$val->name.'</td>';
						print '<td>'.$val->type.'</td>';
						print '<td>'.$val->value.'</td>';
						print '<td width="10">';
						
						if($key != 0)
						$up = $l[$key]->id.'--';
						
						if(isset($l[$key+1]))
						$down = $l[$key]->id.'++';
						
						if($key==0) print img_picto('1uparrow_selected', '1uparrow_selected');
						else{ 
							print '<button name="resort" value="'.$up.'" style="background:transparent; border:0 none; margin:0; padding:0; cursor:pointer;">';
							print img_picto('moveUp', '1uparrow');
							print '</button>';
						}
						print'<br />';
					
						if(!isset($l[$key+1])) print img_picto('1downarrow_selected', '1downarrow_selected');
						else{ 
							print '<button name="resort" value="'.$down.'" style="background:transparent; border:0 none; margin:0; padding:0; cursor:pointer;">';
							print img_picto('moveDown', '1downarrow');
							print '</button>';
						}
						
						print '</td>';
						
						print '<td width="10">';
							print '<a href="?id='.$object->id.'&action=edit&ref='.$val->id.'">';
							print img_picto('edit', 'edit');
							print '</a>';
						print '</td>';
						
						print '<td width="10">';
							print '<a href="?id='.$object->id.'&action=delete&ref='.$val->id.'">';
							print img_picto('delete', 'delete');
							print '</a>';
						print '</td>';
						
						
					print "</tr>";
					
				}
			
			
			print "</table>\n";
			print '</form>';
			
			

			
			

		
	
}
else
{
	print $langs->trans("ErrorUnknown");
}


// End of page
llxFooter();
$db->close();
?>
