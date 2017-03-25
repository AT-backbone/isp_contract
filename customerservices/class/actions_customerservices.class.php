<?php
 
class ActionsCustomerServices // extends CommonObject 
{ 
 
    /** Overloading the doActions function : replacing the parent's function with the one below 
     *  @param      parameters  meta datas of the hook (context, etc...) 
     *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...) 
     *  @param      action             current action (if set). Generally create or edit or null 
     *  @return       void 
     */ 
 /*   function doActions($parameters, $object, $action) 
    { 
    		print '<pre>';
        print_r($parameters); 
        echo "\n\naction: ".$action."\n\n"; 
        print_r($object); 
 
        if (in_array('somecontext',explode(':',$parameters['context']))) 
        { 
          // do something only for the context 'somecontext'
        }
        
        
    } 
  */
  
	function createFrom($parameters, $object, $action){
		global $db;
  		$org =  $parameters['objFrom'];
  		if($org->element != 'customerservices_FacExpiredS') return 0;
  		
  		$sql = "DELETE FROM ".MAIN_DB_PREFIX."element_element WHERE sourcetype = 'customerservices_FacExpiredS'";
  		$db->query($sql);
  		
  		foreach($org->lines as $line){
  			$sql = "REPLACE INTO ".MAIN_DB_PREFIX."element_element 
  					SET	sourcetype 	= 'contrat'
  					,	targettype	= 'facture'
  					,	fk_source		= '".$line->cid."'
  					,	fk_target		= '".$object->id."'
  					
  					";	
  			$db->query($sql);
  			
  			$sql = "SELECT rowid, description FROM ".MAIN_DB_PREFIX."facturedet WHERE fk_facture = ".$object->id." AND description LIKE '%<!--cdid=".$line->id."-->%'";
  			print str_replace(explode(' ','< >'),explode(' ','&lt; &gt;'),$sql)."\n\n";
  			$res = $db->query($sql);
  			if($obj = $db->fetch_object($res)){
  				$db->query("UPDATE ".MAIN_DB_PREFIX."facturedet SET description = '".preg_replace("/\<\!\-\-.*?\-\-\>/",'',$obj->description)."' WHERE rowid=".$obj->rowid);	
  				
  				$db->query("REPLACE INTO ".MAIN_DB_PREFIX."element_element 
  					SET	sourcetype 	= 'contratdet'
  					,	targettype	= 'facturedet'
  					,	fk_source		= '".$line->id."'
  					,	fk_target		= '".$obj->rowid."'
  					
  					");
  				
  				print "REPLACE INTO ".MAIN_DB_PREFIX."element_element 
  					SET	sourcetype 	= 'contratdet'
  					,	targettype	= 'facturedet'
  					,	fk_source		= '".$line->id."'
  					,	fk_target		= '".$obj->rowid."'
  					
  					";
  				
  				
  			}
  			
  		}
  		
	}  
	
	
    function doActions($parameters, $object, $action){
   	 	global $db;
		if($action != 'confirm_valid' || GETPOST('confirm','alpha') != 'yes') return 0;
		
		$sql = "SELECT fd.rowid as fdid, fd.date_start, fd.date_end, ee.*, ee.rowid as eeid
			FROM  ".MAIN_DB_PREFIX."facturedet AS fd
			LEFT JOIN ".MAIN_DB_PREFIX."element_element AS ee ON ee.targettype = 'facturedet' AND ee.fk_target = fd.rowid
			WHERE fd.fk_facture = ".$object->id;
      	
      	$res = $db->query($sql);
      //	print '<pre>';
      //	print $sql;
      //	print "\n";
  		while($obj = $db->fetch_object($res)){
  			$usql = "UPDATE ".MAIN_DB_PREFIX."contratdet SET date_ouverture_prevue = '".$obj->date_start."', date_fin_validite = '".$obj->date_end."' WHERE rowid=".$obj->fk_source;
  			//	print $usql;
 				$db->query($usql);
			}
		
    }
    
/* alte Version von Lukas    
    function printSearchForm($parameters, $object){
    		global $langs;
    		  $langs->load("customerservices@customerservices");
    		  echo "hallo";
	       return printSearchForm(DOL_URL_ROOT.'/customerservices/allservices.php', DOL_URL_ROOT.'/customerservices/allservices.php', img_object('','product').' '.$langs->trans("services"), 'member', 'sall');
    	
	}

*/
    function printSearchForm() {
				global $langs, $user, $db, $conf, $hookmanager;
				$error = 0; // Error counter
				$myvalue = 'test'; // A result value
 				$title=img_object('','product').' '.$langs->trans("services");
 				$urlaction="/customerservices/allservices.php";
 				$urlobject="/customerservices/allservices.php";
 				$htmlinputname="sall";
 				$htmlmodesearch="search";

						$ret='';
				    $ret.='<div class="menu_titre">';
				    $ret.='<a class="vsmenu" href="'.$urlobject.'">';
				    $ret.=$title.'</a><br>';

				    $ret.='</div>';
				    $ret.='<form action="'.$urlaction.'" method="post">';
				    $ret.='<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
				    $ret.='<input type="hidden" name="mode" value="search">';
				    $ret.='<input type="hidden" name="mode_search" value="'.$htmlmodesearch.'">';
				    $ret.='<input type="text" class="flat" ';
				    if (! empty($conf->global->MAIN_HTML5_PLACEHOLDER)) $ret.=' placeholder="'.$langs->trans("SearchOf").''.strip_tags($title).'"';
				    else $ret.=' title="'.$langs->trans("SearchOf").''.strip_tags($title).'"';
				    $ret.=' name="'.$htmlinputname.'" size="10" />';
				    $ret.='<input type="submit" class="button" value="'.$langs->trans("Go").'">';
				    $ret.="</form>\n";
						$hookmanager->resPrint.=$ret;
	  					
			
	  }

}
?>