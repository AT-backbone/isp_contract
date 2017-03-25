<?php

class FacExpiredS  extends CommonObject{
	
	var $db;
	public $element='customerservices_FacExpiredS';
//	public $table_element='contrat';
//	public $table_element_line='contratdet';
//	public $fk_element='fk_contrat';

	var $id;
	var $ref;
	var $socid;
	var $societe;		// Objet societe
	var $statut=0;		// 0=Draft,
	var $product;

	var $user_author;
	var $date_creation;
	var $date_validation;

	var $date_contrat;
	var $date_cloture;

	var $commercial_signature_id;
	var $commercial_suivi_id;

	var $note;			// deprecated
	var $note_private;
	var $note_public;

	var $fk_projet;

	var $extraparams=array();

	var $lines=array();

	function FactureExpiredServices($db){
		$this->db = $db;
	}	
	
	function fetch($socid){
		global $user, $conf;
		$this->socid = $socid;
		
		dol_include_once('/contrat/class/contrat.class.php');
		
		$this->id = $socid;
		$this->nbofserviceswait=0;
		$this->nbofservicesopened=0;
		$this->nbofservicesexpired=0;
		$this->nbofservicesclosed=0;
		
		$total_ttc=0;
		$total_vat=0;
		$total_ht=0;

		$now=dol_now();

		$this->lines=array();
		
		
		$sql = "SELECT p.label, p.description as product_desc, p.ref as pref, p.duration,";
		$sql.= " cd.rowid, cd.fk_contrat, cd.statut, cd.description, cd.price_ht, cd.tva_tx, cd.localtax1_tx, cd.localtax2_tx, cd.qty, cd.remise_percent, cd.subprice,";
		$sql.= " c.rowid as cid, p.rowid as pid, c.ref as cref, ";
		$sql.= " cd.total_ht,";
		$sql.= " cd.total_tva,";
		$sql.= " cd.total_localtax1,";
		$sql.= " cd.total_localtax2,";
		$sql.= " cd.total_ttc,";
		$sql.= " cd.info_bits, cd.fk_product,";
		$sql.= " cd.date_ouverture_prevue, cd.date_ouverture,";
		$sql.= " cd.date_fin_validite, cd.date_cloture,";
		$sql.= " cd.fk_user_author,";
		$sql.= " cd.fk_user_ouverture,";
		$sql.= " cd.fk_user_cloture";
		$sql.= " FROM ".MAIN_DB_PREFIX."contrat as c,";
		$sql.= " ".MAIN_DB_PREFIX."societe as s,";
		if (!$user->rights->societe->client->voir && !$socid) $sql .= " ".MAIN_DB_PREFIX."societe_commerciaux as sc,";
		$sql.= " ".MAIN_DB_PREFIX."contratdet as cd";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product as p ON cd.fk_product = p.rowid";
		$sql.= " WHERE c.entity = ".$conf->entity;
		$sql.= " AND c.rowid = cd.fk_contrat";
		$sql.= " AND c.fk_soc = s.rowid";
		if (!$user->rights->societe->client->voir && !$socid) $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
		$sql.= " AND (cd.statut = 4 AND (cd.date_fin_validite < NOW() || cd.date_fin_validite IS NULL))";
		$sql.= " AND s.rowid = ".$socid;
		$sql.= " ORDER by cd.rowid ASC";

		
		//print $sql;
		dol_syslog("contrat/services.php sql=".$sql);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;

			while ($i < $num)
			{
				$objp					= $this->db->fetch_object($result);

				$line				= new ContratLigne($this->db);
				$line->id				= $objp->rowid;
				$line->cref			= $objp->cref;
				$line->pref			= $objp->pref;
				$line->cid			= $objp->cid;
				$line->pid			= $objp->pid;
				$line->fk_contrat		= $objp->fk_contrat;
				$line->fk_product		= $objp->pid;
				$line->desc			= $objp->description;  // Description ligne
				$line->qty			= $objp->qty;
				$line->tva_tx			= $objp->tva_tx;
				$line->localtax1_tx		= $objp->localtax1_tx;
				$line->localtax2_tx		= $objp->localtax2_tx;
				$line->subprice		= $objp->subprice;
				$line->statut			= $objp->statut;
				$line->remise_percent	= $objp->remise_percent;
				$line->price_ht		= $objp->price_ht;
				$line->price			= $objp->price_ht;	// For backward compatibility
				$line->total_ht		= $objp->total_ht;
				$line->total_tva		= $objp->total_tva;
				$line->total_localtax1	= $objp->total_localtax1;
				$line->total_localtax2	= $objp->total_localtax2;
				$line->total_ttc		= $objp->total_ttc;
				$line->fk_product		= $objp->fk_product;
				$line->info_bits		= $objp->info_bits;

				$line->fk_user_author	= $objp->fk_user_author;
				$line->fk_user_ouverture= $objp->fk_user_ouverture;
				$line->fk_user_cloture  = $objp->fk_user_cloture;

				$line->ref				= $objp->ref;
				$line->libelle			= $objp->label;        // Label produit
				$line->label			= $objp->label;        // For backward compatibility
				$line->product_desc		= $objp->product_desc; // Description produit

				$line->description		= $objp->description;

				$line->date_ouverture        = $this->db->jdate($objp->date_ouverture);
				$line->date_ouverture_prevue = empty($line->date_ouverture_prevue)? $line->date_ouverture : $this->db->jdate($objp->date_ouverture_prevue);
				$line->date_fin_validite     = empty($objp->date_fin_validite)? strtotime('-1day',$line->date_ouverture_prevue) : $this->db->jdate($objp->date_fin_validite);
				$line->date_cloture          = $this->db->jdate($objp->date_cloture);
				// For backward compatibility
				$line->date_debut_prevue = $this->db->jdate($objp->date_ouverture_prevue);
				$line->date_debut_reel   = $this->db->jdate($objp->date_ouverture);
				$line->date_fin_prevue   = $this->db->jdate($objp->date_fin_validite);
				$line->date_fin_reel     = $this->db->jdate($objp->date_cloture);
				
				$line->duration		= empty($objp->duration)?'1m':$objp->duration;
				$line->duration_value	= substr($line->duration,0,dol_strlen($line->duration)-1);
				$line->duration_unit	= substr($line->duration,-1);
				
				$dur=array("h"=>("Hour"),"d"=>("Day"),"w"=>("Week"),"m"=>("Month"),"y"=>("Year"));
	              
	               
				$line->new_date_ouverture_prevue    = strtotime('+1day',$line->date_fin_validite);
				$line->new_date_fin_validite = $this->addtime($line->date_fin_validite,$line->duration_value,$line->duration_unit);
				
				while($line->new_date_fin_validite < $now){
				
				//$line->new_date_ouverture_prevue    = strtotime('+1day',$line->new_date_fin_validite);
				$line->new_date_fin_validite = $this->addtime($line->new_date_fin_validite,$line->duration_value,$line->duration_unit);
				
					$line->qty++;
					
					$line->total_ht		+= $objp->total_ht;
					$line->total_tva		+= $objp->total_tva;
					$line->total_ttc		+= $objp->total_ttc;
				
					$total_ttc+=$objp->total_ttc;   // TODO Not saved into database
              			$total_vat+=$objp->total_tva;
             			$total_ht+=$objp->total_ht;	
				}
				$line->date_start = $line->new_date_ouverture_prevue;
				$line->date_end = $line->new_date_fin_validite;
				
				
				$this->lines[]			= $line;
				//dol_syslog("1 ".$line->desc);
				//dol_syslog("2 ".$line->product_desc);

				$total_ttc+=$objp->total_ttc;   // TODO Not saved into database
              		$total_vat+=$objp->total_tva;
             		$total_ht+=$objp->total_ht;

				$i++;
			}
		}
		else
		{
			dol_syslog(get_class($this)."::Fetch Erreur lecture des lignes de contrat non liees aux produits");
			$this->error=$this->db->error();
			return -2;
		}
		
	   $this->nbofservices=count($this->lines);
	   
        $this->total_ttc = price2num($total_ttc);   // TODO For the moment value is false as value is not stored in database for line linked to products
        $this->total_tva = price2num($total_vat);   // TODO For the moment value is false as value is not stored in database for line linked to products
        $this->total_ht = price2num($total_ht);     // TODO For the moment value is false as value is not stored in database for line linked to products


//print '<pre>';
//print_r($this);
	   return $this->lines;
	
	}
	function addTime($time, $dur_value, $dur_unit){
		$dur=array("h"=>("Hour"),"d"=>("Day"),"w"=>("Week"),"m"=>("Month"),"y"=>("Year"));
	     switch($dur_unit){
	     	case 'w':
	     		$dur_value = $dur_value*7;
			case 'd':
				$ntime = strtotime('+'.$dur_value.'day',$time);
			break;	
			case 'm':
				if(date('j',$time) == date('t',$time)
				 || ( (  date('n',$time) + $dur_value == 2 
				 		|| date('n',$time) + $dur_value - (floor((date('n',$time) + $dur_value)/12)*12) == 2 )
					 && date('j',$time) > 28
					)
				)
					$ntime = strtotime('last day of +'.$dur_value.'month',$time);
				else
					$ntime = strtotime('+'.$dur_value.'month',$time);
				
			break;
			case 'y':
				if( date('n',$time) == 2 && date('j',$time) >= 28)
					$ntime = strtotime('last day of +'.$dur_value.'year',$time);
				else
					$ntime = strtotime('+'.$dur_value.'year',$time);
			break;
			
		}
		
		return $ntime;
	}
	
	function getNomUrl($withpicto=0,$maxlength=0)
	{
		global $langs;

		$result='';

		$lien = '<a href="'.DOL_URL_ROOT.'/customerservices/index.php?id='.$this->id.'&filter_statut=4">';
		$lienfin='</a>';

		$picto='company';
		
		if(!is_object($this->thirdparty))
		$this->fetch_thirdparty();

		$label=$langs->trans("ShowCompany").': '.$this->thirdparty->nom;

		if ($withpicto) $result.=($lien.img_object($label,$picto).$lienfin);
		if ($withpicto && $withpicto != 2) $result.=' ';
		if ($withpicto != 2) $result.=$lien.($maxlength?dol_trunc($this->thirdparty->nom,$maxlength):$this->thirdparty->nom).$lienfin;
		return $result;
	}

	function printOriginLinesList(){
		global $langs;
		
		$now=dol_now();
		
		print '<table class="liste" width="100%">';
	
		print '<tr class="liste_titre">';
		print_liste_field_titre($langs->trans("Contract"));
		print_liste_field_titre($langs->trans("Service"));
		// Date debut
		print_liste_field_titre($langs->trans("DateStartRealShort"));
		// Date fin
		//print_liste_field_titre($langs->trans("DateEndPlannedShort"));
		
		print_liste_field_titre($langs->trans("Status"));
		
		print_liste_field_titre($langs->trans("Duration"));
		print_liste_field_titre($langs->trans("DateStartPlannedShort"));
		print_liste_field_titre($langs->trans("DateEndPlannedShort"));
		print_liste_field_titre($langs->trans("VAT"));
		print_liste_field_titre($langs->trans("PriceUHT"));
		print_liste_field_titre($langs->trans("Qty"));
		print_liste_field_titre($langs->trans("ReductionShort"));
		print_liste_field_titre($langs->trans("TotalHTShort"));
		
		print "</tr>\n";
	
		
	
		$contractstatic=new Contrat($this->db);
		$productstatic=new Product($this->db);
		
		$i=0;
		$var=True;
		$num = count($this->lines);
		while ($i < $num)
		{
			$obj = $this->lines[$i];
			
			$var=!$var;
			print "<tr ".$bc[$var].">";
			print '<td>';
			$contractstatic->id=$obj->cid;
			$contractstatic->ref=$obj->cref?$obj->cref:$obj->cid;
			print $contractstatic->getNomUrl(1,16);
			print '</td>';
	
			// Service
			print '<td>';
			if ($obj->pid)
			{
				$productstatic->id=$obj->pid;
				$productstatic->type=$obj->ptype;
				$productstatic->ref=$obj->pref;
				print $productstatic->getNomUrl(1,'',20);
	            print $obj->label?' - '.dol_trunc($obj->label,16):'';
	            if ($obj->description && $conf->global->PRODUIT_DESC_IN_LIST) print '<br>'.dol_nl2br($obj->description);
			}
			else
			{
				if ($obj->type == 0) print img_object($obj->description,'product').dol_trunc($obj->description,20);
				if ($obj->type == 1) print img_object($obj->description,'service').dol_trunc($obj->description,20);
			}
			print '</td>';
	
		
			// Start date
			
			print '<td align="center">'.($obj->date_ouverture?dol_print_date(($obj->date_ouverture)):'&nbsp;').'</td>';
			// Date fin
			//print '<td align="center">'.($obj->date_fin_validite?dol_print_date(($obj->date_fin_validite)):'&nbsp;');
		
			
			// Icone warning
			//if ($obj->date_fin_validite && ($obj->date_fin_validite) < ($now - $conf->contrat->services->expires->warning_delay) && $obj->statut < 5) print img_warning($langs->trans("Late"));
			//else print '&nbsp;&nbsp;&nbsp;&nbsp;';
			print '</td>';
			print '<td align="right" nowrap="nowrap">';
		
			print $obj->LibStatut($obj->statut,5,($obj->date_fin_validite && ($obj->date_fin_validite) < $now)?1:0);
			
			
			
			print '</td>';
			print '<td>'.$obj->duration_value.'&nbsp;';
			      if ($obj->duration_value > 1)
	                {
	                    $dur=array("h"=>$langs->trans("Hours"),"d"=>$langs->trans("Days"),"w"=>$langs->trans("Weeks"),"m"=>$langs->trans("Months"),"y"=>$langs->trans("Years"));
	                }
	                else if ($obj->duration_value > 0)
	                {
	                    $dur=array("h"=>$langs->trans("Hour"),"d"=>$langs->trans("Day"),"w"=>$langs->trans("Week"),"m"=>$langs->trans("Month"),"y"=>$langs->trans("Year"));
	                }
	                print $langs->trans($dur[$obj->duration_unit])."&nbsp;";

			
			print '</td>';
			
			print '<td align="center">'.($obj->new_date_ouverture_prevue?dol_print_date(($obj->new_date_ouverture_prevue)):'&nbsp;');
		     print '</td>';
			
			print '<td align="center">'.($obj->new_date_fin_validite?dol_print_date(($obj->new_date_fin_validite)):'&nbsp;');
		     print '</td>';
			
			print '<td>'.vatrate($obj->tva_tx, true).'</td>';
			print '<td>'.price($obj->subprice).'</td>';
			print '<td>'.$obj->qty.'</td>';
			print '<td>'.((($obj->info_bits & 2) != 2) ? vatrate($obj->remise_percent, true) : '&nbsp;').'</td>';
			print '<td>'.price($obj->total_ht).'</td>';
			
			print "</tr>\n";
			$i++;
		}
		print '</table>';
	

	}
	
	
}










?>	