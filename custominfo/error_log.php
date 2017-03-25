<?php
/* Copyright (C) 2013 Guido Schratzer <guido.schratzer@backbone.co.at>
 * Copyright (C) 2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * Licensed under the GNU GPL v3 or higher (See file gpl-3.0.html)
 */

/**
 *       \file       htdocs/dolimail/error_log.php
 *       \ingroup    dolimail
 *       \brief      list Logfile Mails 
 *       \version    $Id: error_log.php,v 1.2 2014/12/20 11:30:05 bbgs Exp $
 *       \author     Guido Schratzer
 */
ini_set('display_errors', '1');

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/dolimail/class/dolimail.class.php';
require_once(DOL_DOCUMENT_ROOT . "/dolimail/class/dolimail.imap.class.php");

$langs->load('other');
$langs->load("dolimail@dolimail");


$action = GETPOST('action');
$confirm	= GETPOST('confirm','alpha');
$socid=GETPOST('socid','int');
$id=GETPOST('id','int');
$mid=GETPOST("mid");

if (!$del_woclimit ) $del_woclimit = $conf->global->DOLIMAIL_MAXDEL_WOCONFIRM;
if (!$del_limit ) $del_limit = $conf->global->DOLIMAIL_MAXDEL;


if (isset($_POST['submitselaction'])) 
{
	if ($_POST['sel_action']=="sel_delete") $action="set_delete";
	if ($_POST['sel_action']=="sel_flagged") $action="set_flagged";
	if ($_POST['sel_action']=="sel_unflagged") $action="set_unflagged";
	if ($_POST['sel_action']=="sel_public") $action="set_public";
	if ($_POST['sel_action']=="sel_privat") $action="set_privat";
}

// Searchfields

$semail=GETPOST("semail");
$smailbox=GETPOST("smailbox");
$serrtype=(strlen(GETPOST("serrtype"))> 8 ? GETPOST("serrtype") : "MAILADR_NOT_FOUND");
$search_user=(GETPOST('search_user')>0 ? GETPOST('search_user','int') : $user->id);


$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');


if ($page == -1) { $page = 0; }


$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortorder) $sortorder="DESC";
if (! $sortfield) $sortfield="e.email_cnt";

$limit = $conf->liste_limit;
$offset = $conf->liste_limit * $page;

/*
 * Actions
 */
		$dolimail=new DoliMail($db);
		if ($action=='delmail' && $id>0)
		{
			$mailboxconfig = new IMAP($db);
			$dolimail_err = new DoliMailErrorLog($db);
			if ($dolimail_err->fetch($id)==1)
			{
				$mailboxconfig->fetch($dolimail_err->mbox);
				
				$mbox = imap_open($mailboxconfig->get_connector_url(). $dolimail_err->cache_key, $mailboxconfig->mailbox_imap_login, $mailboxconfig->mailbox_imap_password);
		
			
				$searchstring.=' FROM "'.$dolimail_err->email.'"';
				if ($mailsearch=@imap_search($mbox, trim($searchstring), SE_UID))
				{
					$numdel=sizeof($mailsearch);
					if ($numdel <= $del_woclimit || $confirm == 'yes')
					{
						$messages = implode(',',array_slice($mailsearch,0,$del_limit));
						$delmsg=min($numdel,$del_limit);
						if (imap_delete($mbox,$messages,FT_UID)==true) 
						{	
							setEventMessage($langs->trans("MailRemoveSuccessfully",$delmsg));
							$dolimail_err->errortype="MAILS_DELETED";
							$dolimail_err->email_cnt=$delmsg;
							$delres=$dolimail_err->delete($user);
							
						}
						else setEventMessage($langs->trans("MailRemoveError"),'warnings');
					}
					else 
					{
						// Wenn LöschLimit ohne Confirm überschritten
						$action="set_delete";
					}
				}
				imap_close($mbox,CL_EXPUNGE);  //CL_EXPUNGE ist für richtiges Löschen des emails
				if ($delres) 
    		{
	    		$objp = $db->fetch_object($resql);
	    		header("Location: ".$_SERVER["PHP_SELF"]);
	    		exit;
				}
			}
		}
		elseif ($action=='flagmail' && $confirm == 'yes')
    {
			 		$resd=$dolimail->change_prop($user, "flagged", 1);
					if ($resd>0) setEventMessage($langs->trans($action."Successfully",$resd),'mesgs');
					elseif ($resd==0) setEventMessage($langs->trans($action."Error".$resd),'warnings');
					else setEventMessage($langs->trans($action."Error".$resd),'errors');
    }
    elseif ($action=='unflagmail' && $confirm == 'yes')
    {
			 		$resd=$dolimail->change_prop($user, "flagged", 0);
					if ($resd>0) setEventMessage($langs->trans($action."Successfully",$resd),'mesgs');
					elseif ($resd==0) setEventMessage($langs->trans($action."Error".$resd),'warnings');
					else setEventMessage($langs->trans($action."Error".$resd),'errors');
    }
		elseif ($action=='publicmail' && $confirm == 'yes')
    {
			 		$resd=$dolimail->change_prop($user, "public", 1);
					if ($resd>0) setEventMessage($langs->trans($action."Successfully",$resd),'mesgs');
					elseif ($resd==0) setEventMessage($langs->trans($action."Error".$resd),'warnings');
					else setEventMessage($langs->trans($action."Error".$resd),'errors');
    }
	  elseif ($action=='privatmail' && $confirm == 'yes')
    {
    	 		$resd=$dolimail->change_prop($user, "public", 0);
					if ($resd>0) setEventMessage($langs->trans($action."Successfully",$resd),'mesgs');
					elseif ($resd==0) setEventMessage($langs->trans($action."Error".$resd),'warnings');
					else setEventMessage($langs->trans($action."Error".$resd),'errors');
    }
    elseif (GETPOST('action')=='setprop' && $mid>0)
		{
			$dolimail->id=$mid;
			if ($_GET['public']=='0') 
			{ 
				$res=$dolimail->set_prop($user, "public", 1);
				$proptype="Public";
			}	
			elseif ($_GET['public']=='1') 
			{
				$res=$dolimail->set_prop($user, "public", 0);
				$proptype="Public";
			}
			elseif ($_GET['flag']=='0') 
			{
				$res=$dolimail->set_prop($user, "flagged", 1);
				$proptype="Flagged";
			}
			elseif ($_GET['flag']=='1') 
			{
				$res=$dolimail->set_prop($user, "flagged", 0);
				$proptype="Flagged";
			}
			
			if ($res < 1) setEventMessage($langs->trans("Set".$proptype."PropError"),'errors');
			else setEventMessage($langs->trans("Set".$proptype."PropSuccess"),'mesgs');
		}
    
    
if (isset($_POST["button_removefilter_x"]))
{
	$semail="";
	$serrtype="";
	$search_user="";
}


/*
 * View
 */


$form=new Form($db);
$formother = new FormOther($db);
$title=$langs->trans("Title_Maillog");
	
        $sql = "SELECT e.rowid, e.user_id, e.entity, e.mbox, e.cache_key, e.email, e.email_cnt, e.last_mail, e.error_log, e.fk_user_modif, e.fk_soc, e.fk_contact, e.errortype";
				$sql.= ", t.mailbox_imap_label";
				$sql.= " FROM ".MAIN_DB_PREFIX."usermailboxerrors as e";
				$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."usermailboxconfig as t on (e.mbox = t.rowid)";
				$sql.= ' WHERE e.entity IN ('.getEntity('societe', 1).')';
				if ($search_user > 0) $sql .= ' AND e.user_id = ' . $search_user;
				
				if (!$user->rights->dolimail->readall) $sql.= ' AND e.user_id = '.$user->id;
				
				if ($semail) $sql .= natural_search('e.email', $semail);
				if ($smailbox) $sql .= natural_search('t.mailbox_imap_login', $smailbox);
				if ($serrtype) $sql .= natural_search('e.errortype', $serrtype);
				
					$nbtotalofrecords = 0;
					if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
					{
						$result = $db->query($sql);
						$nbtotalofrecords = $db->num_rows($result);
					}
				  $sql.= $db->order($sortfield,$sortorder);
			    $sql.= $db->plimit($limit + 1, $offset);
					dol_syslog("dolimail:list.php: sql=".$sql);	
   
    $resql = $db->query($sql);
    if ($resql)
    {
    	$num = $db->num_rows($resql);

    	$i = 0;
    	$helpurl='';

    	llxHeader('',$title,$helpurl,'');
    	
?>
<script type="text/javascript">
$(document).ready(function() {
	$("#checkall").click(function() {
		$(".checkformerge").attr('checked', true);
	});
	$("#checknone").click(function() {
		$(".checkformerge").attr('checked', false);
	});
});
</script>
<?php
			$param="&amp;semail=".$semail."&amp;smailbox=".$smailbox;
    	if ($search_user > 0) $param.='&search_user='.$search_user;	
    	if ($serrtype) $param.='&amp;serrtype='.$serrtype	;
    	
			 
			
			if ($user->rights->dolimail->read)
	    {
	        	
	       	if ($action == 'set_delete' && $id > 0)
	      	{
	            	print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$id.$param,$langs->trans("DeleteMails"),$langs->trans("ConfirmDeleteMails"),"delmail",'',0,1);
	      	}
	        elseif ($action == 'set_flagged' && sizeof($_POST['toGenerate']) > 0)
	        {
	        	$resd=$dolimail->set_selpoint($user, '', 1); //undelete Trash
						if ($resd=1) setEventMessage($langs->trans("FlagMailsPreparation"),'mesgs');
						else setEventMessage($langs->trans("FlagMailsPreparationError".$resd),'errors');
	        	
	        	$dolimail = new DoliMail($db);
	        	$dolimail->set_selpoint($user, $_POST['toGenerate']);
	        		
	        		print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$id."&toGenerate=".$vars,$langs->trans("PropMails"),$langs->trans("ConfirmFlagMails"),"flagmail",'',0,1);
	        }
	        elseif ($action == 'set_unflagged' && sizeof($_POST['toGenerate']) > 0)
	        {
	        	$resd=$dolimail->set_selpoint($user, '', 1); //undelete Trash
						if ($resd=1) setEventMessage($langs->trans("FlagMailsPreparation"),'mesgs');
						else setEventMessage($langs->trans("FlagMailsPreparationError".$resd),'errors');
	        	
	        	$dolimail = new DoliMail($db);
	        	$dolimail->set_selpoint($user, $_POST['toGenerate']);
	        		
	        		print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$id."&toGenerate=".$vars,$langs->trans("PropMails"),$langs->trans("ConfirmUnFlagMails"),"unflagmail",'',0,1);
	        }
	        elseif ($action == 'set_public' && sizeof($_POST['toGenerate']) > 0)
	        {
	        	$resd=$dolimail->set_selpoint($user, '', 1); //undelete Trash
						if ($resd=1) setEventMessage($langs->trans("FlagMailsPreparation"),'mesgs');
						else setEventMessage($langs->trans("FlagMailsPreparationError".$resd),'errors');
	        	
	        	$dolimail = new DoliMail($db);
	        	$dolimail->set_selpoint($user, $_POST['toGenerate']);
	        		
	        		print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$id."&toGenerate=".$vars,$langs->trans("PropMails"),$langs->trans("ConfirmPublicMails"),"publicmail",'',0,1);
	        }
	    }

	
			print_barre_liste($langs->trans("Title_Maillog"), $page, $_SERVER["PHP_SELF"], $param, $sortfield,$sortorder,'',$num,$nbtotalofrecords);

  		print '<form action="'.$_SERVER["PHP_SELF"].'" method="post" name="formulaire">';
  		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
  		print '<input type="hidden" name="action" value="list">';
  		print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
  		print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
  		print '<table class="liste" width="100%">';

  		// Filter on categories
  	 	$moreforfilter='';
  	 	$colspan=11;
  	 	
  	 	// If the user can view prospects other than his'
			if ($user->rights->dolimail->readall)
			{
			    $moreforfilter.=$langs->trans('User'). ': ';
			    $moreforfilter.=$form->select_dolusers($search_user,'search_user',1);
			}
  	 	

  		if ($moreforfilter)
  		{
  			print '<tr class="liste_titre">';
  			print '<td class="liste_titre" colspan="'.$colspan.'">';
  		    print $moreforfilter;
  		    print '</td></tr>';
  		}

  		// Lignes des titres
  		
  		
  		print '<tr class="liste_titre">';
  		print_liste_field_titre($langs->trans("DolimaillFrom"), $_SERVER["PHP_SELF"], "e.email",$param,"","",$sortfield,$sortorder);
  		
  		print_liste_field_titre($langs->trans("DoliMailIMAP"), $_SERVER["PHP_SELF"], "t.mailbox_imap_label",$param,"","",$sortfield,$sortorder);
  		if ($user->rights->dolimail->readall) print_liste_field_titre($langs->trans("User"), $_SERVER["PHP_SELF"], "t.user_id",$param,"","",$sortfield,$sortorder);
  		print_liste_field_titre($langs->trans("DolimaillFrequency"), $_SERVER["PHP_SELF"], "e.email_cnt",$param,"",'align="center"',$sortfield,$sortorder);
  		print_liste_field_titre($langs->trans("DolimaillLastError"), $_SERVER["PHP_SELF"], "e.last_mail",$param,"",'align="center"',$sortfield,$sortorder);
  		print_liste_field_titre($langs->trans("DolimaillErrorType"), $_SERVER["PHP_SELF"], "e.errortype",$param,"",'align="center"',$sortfield,$sortorder);
	 		print_liste_field_titre($langs->trans("DolimaillFlagged"), $_SERVER["PHP_SELF"], "t.f_direction",$param,"",'align="center"',$sortfield,$sortorder);
  		print_liste_field_titre($langs->trans("Attachments"), $_SERVER["PHP_SELF"], "t.attachments",$param,"",'align="center"',$sortfield,$sortorder);
      print '<td width="1%">&nbsp;</td>';
      print '<td>'; 
      if ($conf->use_javascript_ajax) print '<a href="#" id="checkall">'.$langs->trans("All").'</a> / <a href="#" id="checknone">'.$langs->trans("None").'</a>';
  		print '</td>';
  		print "</tr>\n";
  		

  		// Lignes des champs de filtre
  		print '<tr class="liste_titre">';
  		print '<td class="liste_titre" align="left">';
  		print '<input class="flat" type="text" name="semail" size="8" value="'.$semail.'">';
  		print '</td>';
  		print '<td class="liste_titre" align="left">';
  		print '<input class="flat" type="text" name="smailbox" size="12" value="'.$smailbox.'">';
  		print '</td>';
  		print '<td class="liste_titre" align="left">';
  		print '</td>';
  		if ($user->rights->dolimail->readall)
  		{
  		print '<td class="liste_titre">';
  		print '&nbsp;';
  		print '</td>';

  	  }
  		print '<td align="center">';
  		
  		
  		
      print '</td >';

      print '<td align="center">';
      $filter_selection['MAILS_DELETED']=$langs->trans("MAILS_DELETED");      
			$filter_selection['MAILADR_NOT_FOUND']=$langs->trans("MAILADR_NOT_FOUND");    
			
			print $form->selectarray("serrtype", $filter_selection, $serrtype, 1);
		
			//print '<input class="flat" type="text" name="serrtype" size="12" value="'.$serrtype.'">';
      print '</td>';

			print '<td class="liste_titre">';
  		print '&nbsp;';
  		print '</td>';

			print '<td class="liste_titre">';
  		print '&nbsp;';
  		print '</td>';

  		print '<td class="liste_titre">';
  		print '&nbsp;';
  		print '</td>';

  		print '<td class="liste_titre" align="right">';
  		print '<input type="image" class="liste_titre" name="button_search" src="'.img_picto($langs->trans("Search"),'search.png','','',1).'" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
  		print '<input type="image" class="liste_titre" name="button_removefilter" src="'.img_picto($langs->trans("Search"),'searchclear.png','','',1).'" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
  		print '</td>';
  		
  		print '</tr>';


  		
  		$var=true;
  		while ($objp = $db->fetch_object($resql))
  		{
  			
  			$var=!$var;
  			 // Subject
  		  
        print '<td class="nowrap">';
        
        
        print '<a href="' . dol_buildpath('/dolimail/imap.php', 1) . '?smailfrom=' . $objp->email  . '&mid='.$objp->mbox.'">' . trim(utf8_encode($objp->email)) . '</a></td>';
  			
  			print '<td>' . $objp->mailbox_imap_label . '</td>';
				
				if ($user->rights->dolimail->readall)
        {
        	print '<td>';
        	$mailowner = new User($db);
					$mailowner->fetch($objp->user_id);
        	print $mailowner->getNomUrl(1);
        	print '</td>';
        }
        
        print '      <td style="text-align:left;width: 115px;">' . $objp->email_cnt . '</td>';
				print '      <td style="text-align:left;width: 115px;">' . dol_print_date($db->jdate($objp->last_mail),"dayhour") . '</td>';
				print '      <td style="text-align:left;width: 115px;">' . $langs->trans($objp->errortype) . '</td>';
				
				print '      <td align="left">';
        if ($objp->f_public == 1) print '<a href="'.$_SERVER["PHP_SELF"].'?action=setprop&public='.$objp->f_public.'&mid='.$objp->message_id.$param.'">'.img_picto($langs->trans('MailPublic'.$objp->f_public), 'mail-share@dolimail');
        elseif ($objp->f_public == 0) print '<a href="'.$_SERVER["PHP_SELF"].'?action=setprop&public='.$objp->f_public.'&mid='.$objp->message_id.$param.'">'.img_picto($langs->trans('MailPublic'.$objp->f_public), 'mail-lock@dolimail');
        
        if ($objp->f_flagged == 1) print '<a href="'.$_SERVER["PHP_SELF"].'?action=setprop&flag='.$objp->f_flagged.'&mid='.$objp->message_id.$param.'">'.img_picto($langs->trans('MailFlagged'.$objp->f_flagged), 'flagged@dolimail').'</a>';
        elseif ($objp->f_flagged == 0) print '<a href="'.$_SERVER["PHP_SELF"].'?action=setprop&flag='.$objp->f_flagged.'&mid='.$objp->message_id.$param.'">'.img_picto($langs->trans('MailFlagged'.$objp->f_flagged), 'unflagged@dolimail').'</a>';
        	
        if ($objp->f_answered == 1) print img_picto($langs->trans('MailAnswered'), 'answered@dolimail');
        if ($objp->f_recent == 1) print img_picto($langs->trans('MailRecent'), 'mail-info@dolimail');
        if ($objp->f_draft == 1) print img_picto($langs->trans('MailDraft'), 'mail3.gif@dolimail');
        
        
        
        
        //if ($objp->f_directionsattachments)
        print '</td>';
				print '      <td align="center">';
        $form->textwithtooltip("Mail Info",$objp->error_log,2,1,(strlen($objp->error_log) > 8)?img_picto(nl2br($objp->error_log), 'info'):'','',3);
        print img_picto(nl2br($objp->error_log), 'info');
        print '</td>';
        print '<td>';
        print '<form name="link_' . $i . '" method="POST">';
        print '<table><tr><td>';
        print '</td><td>';
        print '&nbsp;';
        print '</td>';

        print '<td align="center" colspan=2>';
        
        
        
        if ($objp->errortype != "MAILS_DELETED" && isValidEMail($objp->email) && strlen($objp->email) > 3 && $objp->user_id == $user->id)
        {
        	 print '<a href="'.$_SERVER["PHP_SELF"].'?action=delmail&id='.$objp->rowid.$param.'">';
        	 print img_picto($langs->trans('delmail'), 'delete');
        	 print '</a>';
        }
        
        
        $content    = $_POST['field_content'];
        $name        = $_POST['field_name'];
        $mail        = isset($objp->email) ? $objp->email:'';
        $ip        = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR']:'';
        print '</td>';
				
      	

        print '</tr></table>';
        print '</form>';
        	// Checkbox
				print '<td align="center">';
				print '<input id="cb'.$objp->message_id.'" class="flat checkformerge" type="checkbox" name="toGenerate[]" value="'.$objp->message_id.'">';
				print '</td>' ;
      
        print '</td>';
        
        print '</tr>';
  			$i++;
  		}


			$param="&amp;semail=".$semail."&amp;smailbox=".$smailbox;
    	if ($search_user > 0) $param.='&search_user='.$search_user;	
    	if (isset($serrtype)) $param.='&amp;serrtype='.$serrtype	;
    	
    	
  	
  		if ($num > $conf->liste_limit || $page > 0) print_barre_liste($langs->trans("Title_Maillog"), $page, $_SERVER["PHP_SELF"], $param, $sortfield,$sortorder,'',$num,$nbtotalofrecords);

  		$db->free($resql);

  		print "</table>";
  		// Button
  		 
       //print '<input type="submit" class="button" name="deletesel" id="deletesel" value="'.$langs->trans("DeleteSelection").'">';
       $selection=array(
       '-1'=>'',
       'sel_delete'=>$langs->trans('SelDelete'),
       'sel_public'=>$langs->trans('SelPublic'),
       'sel_privat'=>$langs->trans('SelPrivate'),
       'sel_flagged'=>$langs->trans('SelFlagged'),
       'sel_unflagged'=>$langs->trans('SelUnFlagged')
       );
       
       if ($_POST['sel_action']=="sel_delete") $action="set_delete";
       if ($_POST['sel_action']=="sel_public") $action="set_public";
       if ($_POST['sel_action']=="sel_privat") $action="set_privat";
       if ($_POST['sel_action']=="sel_flagged") $action="set_flagged";
       if ($_POST['sel_action']=="sel_unflagged") $action="set_unflagged";
       print $langs->trans("SelectionTitle");
       print "&nbsp;";
       print $form->selectarray('sel_action', $selection,'-1');
       print "&nbsp;";
       print '<input type="submit" class="button" name="submitselaction" id="submitselaction" value="'.$langs->trans("SubmitSelection").'">';
  		print '</form>';
    	
    }
    else
    {
    	print $sql;
    	dol_print_error($db);
    }

// Last Connections

    $sql = "SELECT c.imap_host, c.imap_user, c.StartTime, c.EndTime, c.LastFolder, c.Function, c.Param, c.info";
		$sql.= " FROM ".MAIN_DB_PREFIX."usermailboxconnections as c";
		$sql.= " ORDER BY c.rowid desc";
		$sql.= $db->plimit(20, 0);
		$resql = $db->query($sql);
		print '<table class="liste" width="100%">';

    if ($resql)
    {
    	$var=true;
  		while ($objp = $db->fetch_object($resql))
  		{
  			
  			$var=!$var;
  			 // Subject
  		  
        print '<td class="nowrap">';
        print $objp->imap_user;
 				print '</td>';
  			
  			print '<td>' . $objp->imap_host . '</td>';
				
				
        print '      <td style="text-align:left;width: 115px;">' . dol_print_date($db->jdate($objp->StartTime),"dayhour") . '</td>';
        print '      <td style="text-align:left;width: 115px;">';
        if ($objp->EndTime) print dol_print_date($db->jdate($objp->EndTime),"dayhour");
        print '</td>';
				
				print '<td>' . $objp->Param . '</td>';
				
				
        
        
        print '</tr>';
  			$i++;
  		}

    	
		}
		print '</table>';
llxFooter();
$db->close();
