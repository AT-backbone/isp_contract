<?php
/* Copyright (C) 2012      Lukas Prömer <lukas.proemer@gmail.com>
 

*/


class Custominfo // extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	//var $element='custominfo';			//!< Id that identify managed objects
	//var $table_element='custominfo';	//!< Name of table without prefix where object is stored

    var $id;
    
	var $fk_product;
	var $type;
	var $name;
	var $value;
     var $det;
    


    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
        $this->det = new Custominfodet($db);
        return 1;
    }


    /**
     *  Create object into database
     *
     *  @param	User	$user        User that create
     *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
     *  @return int      		   	 <0 if KO, Id of created object if OK
     */
    function create($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->fk_product)) $this->fk_product=trim($this->fk_product);
		if (isset($this->type)) $this->type=trim($this->type);
		if (isset($this->name)) $this->name=trim($this->name);
		if (isset($this->value)) $this->value=trim($this->value);

        

		// Check parameters
		// Put here code to add control on parameters values

         // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."custominfo(";
		
		$sql.= "fk_product,";
		$sql.= "type,";
		$sql.= "name,";
		$sql.= "value";

		
        $sql.= ") VALUES (";
        
		$sql.= " ".(! isset($this->fk_product)?'NULL':"'".$this->fk_product."'").",";
		$sql.= " ".(! isset($this->type)?'NULL':"'".$this->db->escape($this->type)."'").",";
		$sql.= " ".(! isset($this->name)?'NULL':"'".$this->db->escape($this->name)."'").",";
		$sql.= " ".(! isset($this->value)?'NULL':"'".$this->db->escape($this->value)."'")."";

        
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."custominfo");

			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action call a trigger.

	            //// Call triggers
	            //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
			}
        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
            return $this->id;
		}
    }


    /**
     *  Load object in memory from database
     *
     *  @param	int		$id    Id object
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch($id)
    {
    	global $langs;
        $sql = "SELECT";
		$sql.= " t.rowid,";
		
		$sql.= " t.fk_product,";
		$sql.= " t.sort,";
		$sql.= " t.type,";
		$sql.= " t.name,";
		$sql.= " t.value";

		
        $sql.= " FROM ".MAIN_DB_PREFIX."custominfo as t";
        $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                
				$this->fk_product = $obj->fk_product;
				$this->sort = $obj->sort;
				$this->type = $obj->type;
				$this->name = $obj->name;
				$this->value = $obj->value;

                
            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
            return -1;
        }
    }


    /**
     *  Update object into database
     *
     *  @param	User	$user        User that modify
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    function update($user=0, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->fk_product)) $this->fk_product=trim($this->fk_product);
		if (isset($this->sort)) $this->sort=trim($this->sort);
		if (isset($this->type)) $this->type=trim($this->type);
		if (isset($this->name)) $this->name=trim($this->name);
		if (isset($this->value)) $this->value=trim($this->value);

        

		// Check parameters
		// Put here code to add control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."custominfo SET";
        
		$sql.= " fk_product=".(isset($this->fk_product)?$this->fk_product:"null").",";
		$sql.= " sort=".(isset($this->sort)?$this->sort:"0").",";
		$sql.= " type=".(isset($this->type)?"'".$this->db->escape($this->type)."'":"null").",";
		$sql.= " name=".(isset($this->name)?"'".$this->db->escape($this->name)."'":"null").",";
		$sql.= " value=".(isset($this->value)?"'".$this->db->escape($this->value)."'":"null")."";

        
        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{
			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action call a trigger.

	            //// Call triggers
	            //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
	    	}
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
    }


 	/**
	 *  Delete object in database
	 *
     *	@param  User	$user        User that delete
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		$this->db->begin();

		if (! $error)
		{
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
		        // want this action call a trigger.

		        //// Call triggers
		        //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
		        //$interface=new Interfaces($this->db);
		        //$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
		        //if ($result < 0) { $error++; $this->errors=$interface->errors; }
		        //// End call triggers
			}
		}

		if (! $error)
		{
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."custominfo";
    		$sql.= " WHERE rowid=".$this->id;

    		dol_syslog(get_class($this)."::delete sql=".$sql);
    		$resql = $this->db->query($sql);
        	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}



	/**
	 *	Load an object from its id and create a new one in database
	 *
	 *	@param	int		$fromid     Id of object to clone
	 * 	@return	int					New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Custominfo($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		$object->statut=0;

		// Clear fields
		// ...

		// Create clone
		$result=$object->create($user);

		// Other options
		if ($result < 0)
		{
			$this->error=$object->error;
			$error++;
		}

		if (! $error)
		{


		}

		// End
		if (! $error)
		{
			$this->db->commit();
			return $object->id;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Initialise object with example values
	 *	Id must be 0 if object instance is a specimen
	 *
	 *	@return	void
	 */
	function initAsSpecimen()
	{
		$this->id=0;
		
		$this->fk_product='';
		$this->type='';
		$this->name='';
		$this->value='';

		
	}

	function fetchLinesByProduct($id)
	{
    	global $langs;
        $sql = "SELECT";
		$sql.= " t.rowid,";
		
		$sql.= " t.fk_product,";
		$sql.= " t.sort,";
		$sql.= " t.type,";
		$sql.= " t.name,";
		$sql.= " t.value";

		
        $sql.= " FROM ".MAIN_DB_PREFIX."custominfo as t";
        $sql.= " WHERE t.fk_product = ".$id;
	   $sql.= " ORDER BY sort ASC, rowid ASC";
    	dol_syslog(get_class($this)."::fetchLinesByProduct sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
	 	  $ret = (Object)'';
	 	  $ret->byname=array();
	 	  $ret->byid=array();
	 	  $ret->bysort=array();
	 	  
	 	  $i=0;
            while ($obj = $this->db->fetch_object($resql))
            {
          	 $tmp = (Object)'';
			// $i = $obj->sort;
                $tmp->id = $obj->rowid;
                
			 $tmp->fk_product = $obj->fk_product;
			 $tmp->sort = $obj->sort;
			 $tmp->type = $obj->type;
			 $tmp->rtype = preg_replace('/^[^\_]+_/','',$obj->type);
			 $tmp->name = $obj->name;
			 $tmp->keyname = hash('crc32',$obj->name);
			 $tmp->value = $obj->value;
			 $tmp->defvalue = $obj->value;
                
                
                $ret->byname[$obj->name] = $tmp;
                $ret->byid[$obj->rowid] = $tmp;
                $ret->bysort[$i] = $tmp;
                
                $i++;
            }
            $ret->count = count($ret->byid);
               
            $this->db->free($resql);

            return $ret;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
            return -1;
        }
	}
	
	function fetchInfoByContrat($id){
		return $this->fetchLinesByBoughtItems($id);
	}
	function fetchInfosByCommande($id){
		return $this->fetchLinesByBoughtItems(0,$id);
	}

	function fetchInfoByContratdet($id){
		$t = $this->fetchLinesByBoughtItems(0,0,0,$id);
		return $t['cond'.$id];
	}
	function fetchInfosByCommandedet($id){
		$t =  $this->fetchLinesByBoughtItems(0,0,0,0,$id);
		return $t['comd'.$id];
	}

	function fetchInfosBySociete($id){
		return $this->fetchLinesByBoughtItems(0,0,$id);
	}

	
	function fetchLinesByBoughtItems($fk_contrat, $fk_commande=0, $fk_societe=0, $fk_contratdet=0, $fk_commandedet=0)
	{
	if( $fk_contrat==0 && $fk_commande==0 && $fk_societe==0 && $fk_contratdet && $fk_commandedet ) return 0;
    	global $user, $langs;
      $tree = array();
      $products = array();
      
      if($fk_contrat > 0 || $fk_contratdet > 0 || $fk_societe > 0){
      	$sql = "SELECT con.rowid, con.ref, con.note_public, cond.rowid as fk_contratdet, cond.fk_product, cond.description, cond.statut, con.fk_soc  FROM ".MAIN_DB_PREFIX."contrat as con ";
      	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."contratdet as cond ON cond.fk_contrat = con.rowid WHERE";
      	$sql.= " cond.rowid > 0 AND cond.fk_product > 0";
      	if($fk_societe > 0) $sql.=" AND con.fk_soc=".$fk_societe;
      	if($fk_contrat > 0) $sql.=" AND con.rowid=".$fk_contrat;
      	if($fk_contratdet > 0) $sql.=" AND cond.rowid=".$fk_contratdet;
      	$sql.= " ORDER BY ref ASC, statut ASC";
      	$res = $this->db->query($sql);
     	if($res)
      	while($obj = $this->db->fetch_object($res)){
      		
      		$c = 'con'.$obj->rowid;
        		if(!isset($tree[$c]))
        			$tree[$c] = (object) '';
        		$tree[$c]->type = 'contrat';
        		$tree[$c]->ts = 'con';
        		$tree[$c]->fk_soc = $obj->fk_soc;
        		$tree[$c]->id = $obj->rowid;
        		$tree[$c]->ref = $obj->ref;
        		$tree[$c]->note = $obj->note_public;
        		if(!isset($tree[$c]->det[$obj->fk_contratdet]))
        			$tree[$c]->det[$obj->fk_contratdet] = (object)'';
        		$tree[$c]->det[$obj->fk_contratdet]->id = $obj->fk_contratdet;
        		$tree[$c]->det[$obj->fk_contratdet]->societe = $tree[$c]->fk_soc;
        		$tree[$c]->det[$obj->fk_contratdet]->type = 'contratdet';
        		$tree[$c]->det[$obj->fk_contratdet]->ts= 'cond';
        		$tree[$c]->det[$obj->fk_contratdet]->desc = $obj->description;
        		$tree[$c]->det[$obj->fk_contratdet]->status = $obj->statut;
        		$tree[$c]->det[$obj->fk_contratdet]->fk_product = $obj->fk_product;
        		$tree[$c]->det[$obj->fk_contratdet]->fk_contrat = $obj->rowid;
        		$tree[$c]->det[$obj->fk_contratdet]->br = $c;
  			$products[] = &$tree[$c]->det[$obj->fk_contratdet];
     		$tree['cond'.$obj->fk_contratdet] = &$tree[$c]->det[$obj->fk_contratdet];
        	}
      }
      if($fk_commande > 0 || $fk_commandedet > 0 || $fk_societe > 0){
      	$sql = "SELECT com.rowid, com.ref, com.note_public, comd.rowid as fk_commandedet, comd.fk_product, comd.description, com.fk_statut as statut, com.fk_soc  FROM ".MAIN_DB_PREFIX."commande as com ";
      	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."commandedet as comd ON comd.fk_commande = com.rowid WHERE";
      	$sql.= " comd.rowid > 0 AND comd.fk_product > 0";
      	if($fk_societe > 0) $sql.=" AND com.fk_soc=".$fk_societe;
      	if($fk_commande > 0) $sql.=" AND com.rowid=".$fk_commande;
      	if($fk_commandedet > 0) $sql.=" AND comd.rowid=".$fk_commandedet;
      	$sql.= " ORDER BY ref ASC";
      	$res = $this->db->query($sql);
      	if($res)
      	while($obj = $this->db->fetch_object($res)){
      		$c = 'com'.$obj->rowid;
        		if(!isset($tree[$c]))
        			$tree[$c] = (object) '';
        		$tree[$c]->type = 'commande';
        		$tree[$c]->ts = 'com';
        		$tree[$c]->fk_soc = $obj->fk_soc;
        		$tree[$c]->id = $obj->rowid;
        		$tree[$c]->ref = $obj->ref;
        		$tree[$c]->note = $obj->note_public;
        		if(!isset($tree[$c]->det[$obj->fk_commandedet]))
        		$tree[$c]->det[$obj->fk_commandedet] = (object)'';
        		$tree[$c]->det[$obj->fk_commandedet]->id = $obj->fk_commandedet;
        		$tree[$c]->det[$obj->fk_commandedet]->societe = $tree[$c]->fk_soc;
        		$tree[$c]->det[$obj->fk_commandedet]->type = 'commandedet';
        		$tree[$c]->det[$obj->fk_commandedet]->ts= 'comd';
        		$tree[$c]->det[$obj->fk_commandedet]->desc = $obj->description;
        		$tree[$c]->det[$obj->fk_commandedet]->status = $obj->statut;
        		$tree[$c]->det[$obj->fk_commandedet]->fk_product = $obj->fk_product;
        		$tree[$c]->det[$obj->fk_commandedet]->fk_commande = $obj->rowid;
        		$tree[$c]->det[$obj->fk_commandedet]->br = $c;
     		$products[] = &$tree[$c]->det[$obj->fk_commandedet];
     		$tree['comd'.$obj->fk_commandedet] = &$tree[$c]->det[$obj->fk_commandedet];
        	}
       }
       foreach($products as $det){
        	 $f = $this->fetchLinesByProduct($det->fk_product);
       	 $det->fields = $f->bysort;
       	 
       	 if(is_array($det->fields))
       	 foreach($det->fields as $key){
       	 	$key->societe = $det->societe;
       	 	
       	 	$sql = "SELECT rowid, value FROM ".MAIN_DB_PREFIX."custominfodet";
		     $sql.= " WHERE fk_".$det->type."=".$det->id;
		     $sql.= " AND fk_custominfo = ".$key->id; 
       	     $sql.= " AND fk_product	  = ".$key->fk_product; 
       		$res = $this->db->query($sql);
       		if($res)
       		if($obj = $this->db->fetch_object($res)){
       			$key->value=$obj->value;
       			$key->fk_custominfodet = $obj->rowid;
       		}
       	
       	}
       }
        
        return $tree;
     }
     

     
	function lineRight($obj, $user){
		//print_r($obj);
		preg_match('/(i_|p_)?(.*?)$/',$obj->type,$t);
		
		// not implemented
		
		return true;
	}
	function lineValue($obj){
		global $langs;
		preg_match('/(i_|p_)?(.*?)$/',$obj->type,$t);
		//print_r($obj);
		switch($t[2]){
			case 'select':
				if($obj->defvalue == $obj->value){
					preg_match('/d\:([^\,]+)/',$obj->defvalue,$o);
					return preg_replace('/^d\:/','',trim($o[1]));
				}else
					return preg_replace('/^d\:/','',trim($obj->value));
			break;
			case 'checkbox':
				if($obj->value==1)
					return img_picto($langs->trans('Yes'),'tick');
				else
					return '';
			break;
			case 'password':
				if($obj->defvalue == $obj->value){
					return $obj->value;
				}else{
					return base64_decode($obj->value);
				}
			break;
			case 'date':
				return dol_print_date($obj->value,'day');
			break;
			default:
				return $obj->value;
			break;
		}
	}
	function LineEditField($obj){
		global $langsm, $form;
		preg_match('/(i_|p_)?(.*?)$/',$obj->type,$t);
		//print_r($obj);
		switch($t[2]){
			case 'checkbox':
				return '<input type="'.$t[2].'" name="customfield_'.$obj->keyname.'" value="1" '.(($obj->value==1)?'checked':'').'>';
			break;
			case 'longtext':
				return '<textarea name="customfield_'.$obj->keyname.'" style="height:80px; width:400px">'.$this->lineValue($obj).'</textarea>';
			break;
			case 'select':
				$sel = '<select name="customfield_'.$obj->keyname.'">';
				$o = explode(',',$obj->defvalue);
				foreach($o as $opt){
					$opt = preg_replace('/^d\:/','',trim($opt));
					$selected = ($opt==$this->lineValue($obj))? ' selected' : '';
					$sel.= '<option name="'.$opt.'"'.$selected.'>'.$opt.'</option>';	
				}
				$sel .= '</select>';
				return $sel;
			break;
			case 'date':
				ob_start();
				 $form->select_date( $obj->value, $prefix='customfield_'.$obj->keyname, $h=0, $m=0, $empty=0, $form_name='', $d=1, $addnowbutton=1, $nooutput=0, $disabled=0, $fullday='');
				$return = ob_get_contents();
				ob_end_clean();
				
				return $return;
			break;
			default:
				return '<input type="'.$t[2].'" name="customfield_'.$obj->keyname.'" value="'.$this->lineValue($obj).'">';
			break;
		}		
	}
}

class Custominfodet // extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	//var $element='custominfodet';			//!< Id that identify managed objects
	//var $table_element='custominfodet';	//!< Name of table without prefix where object is stored

    var $id;
    
	var $fk_user;
	var $fk_societe;
	var $fk_product;
	var $fk_contratdet;
	var $fk_commandedet;
	var $fk_custominfo;
	var $value;

    


    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
        return 1;
    }


    /**
     *  Create object into database
     *
     *  @param	User	$user        User that create
     *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
     *  @return int      		   	 <0 if KO, Id of created object if OK
     */
    function create($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->fk_user)) $this->fk_user=trim($this->fk_user);
		if (isset($this->fk_societe)) $this->fk_societe=trim($this->fk_societe);
		if (isset($this->fk_product)) $this->fk_product=trim($this->fk_product);
		if (isset($this->fk_contratdet)) $this->fk_contratdet=trim($this->fk_contratdet);
		if (isset($this->fk_commandedet)) $this->fk_commandedet=trim($this->fk_commandedet);
		if (isset($this->fk_custominfo)) $this->name=trim($this->fk_custominfo);
		if (isset($this->value)) $this->value=trim($this->value);

        

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."custominfodet(";
		
		$sql.= "fk_user,";
		$sql.= "fk_societe,";
		$sql.= "fk_product,";
		$sql.= "fk_contratdet,";
		$sql.= "fk_commandedet,";
		$sql.= "fk_custominfo,";
		$sql.= "value";

		
        $sql.= ") VALUES (";
        
		$sql.= " ".(! isset($this->fk_user)?'NULL':"'".$this->fk_user."'").",";
		$sql.= " ".(! isset($this->fk_societe)?'NULL':"'".$this->fk_societe."'").",";
		$sql.= " ".(! isset($this->fk_product)?'NULL':"'".$this->fk_product."'").",";
		$sql.= " ".(! isset($this->fk_contratdet)?'NULL':"'".$this->fk_contratdet."'").",";
		$sql.= " ".(! isset($this->fk_commandedet)?'NULL':"'".$this->fk_commandedet."'").",";
		$sql.= " ".(! isset($this->fk_custominfo)?'NULL':"'".$this->db->escape($this->fk_custominfo)."'").",";
		$sql.= " ".(! isset($this->value)?'NULL':"'".$this->db->escape($this->value)."'")."";

        
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."custominfodet");

			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action call a trigger.

	            //// Call triggers
	            //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
			}
        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
            return $this->id;
		}
    }
    
    /**
     *  replace object into database
     *
     *  @param	User	$user        User that create
     *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
     *  @return int      		   	 <0 if KO, Id of created object if OK
     */
    function replace($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->fk_user)) $this->fk_user=trim($this->fk_user);
		if (isset($this->fk_societe)) $this->fk_societe=trim($this->fk_societe);
		if (isset($this->fk_product)) $this->fk_product=trim($this->fk_product);
		if (isset($this->fk_contratdet)) $this->fk_contratdet=trim($this->fk_contratdet);
		if (isset($this->fk_commandedet)) $this->fk_commandedet=trim($this->fk_commandedet);
		if (isset($this->fk_custominfo)) $this->name=trim($this->fk_custominfo);
		if (isset($this->value)) $this->value=trim($this->value);

        

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "REPLACE INTO ".MAIN_DB_PREFIX."custominfodet(";
		
		$sql.= "fk_user,";
		$sql.= "fk_societe,";
		$sql.= "fk_product,";
		$sql.= "fk_contratdet,";
		$sql.= "fk_commandedet,";
		$sql.= "fk_custominfo,";
		$sql.= "value";

		
        $sql.= ") VALUES (";
        
		$sql.= " ".(! isset($this->fk_user)?'NULL':"'".$this->fk_user."'").",";
		$sql.= " ".(! isset($this->fk_societe)?'NULL':"'".$this->fk_societe."'").",";
		$sql.= " ".(! isset($this->fk_product)?'NULL':"'".$this->fk_product."'").",";
		$sql.= " ".(! isset($this->fk_contratdet)?'NULL':"'".$this->fk_contratdet."'").",";
		$sql.= " ".(! isset($this->fk_commandedet)?'NULL':"'".$this->fk_commandedet."'").",";
		$sql.= " ".(! isset($this->fk_custominfo)?'NULL':"'".$this->fk_custominfo."'").",";
		$sql.= " ".(! isset($this->value)?'NULL':"'".$this->db->escape($this->value)."'")."";

        
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."custominfodet");

			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action call a trigger.

	            //// Call triggers
	            //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
			}
        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
            return $this->id;
		}
    }


    /**
     *  Load object in memory from database
     *
     *  @param	int		$id    Id object
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch($id)
    {
    	global $langs;
        $sql = "SELECT";
		$sql.= " t.rowid,";
		
		$sql.= " t.fk_user,";
		$sql.= " t.fk_societe,";
		$sql.= " t.fk_product,";
		$sql.= " t.fk_contratdet,";
		$sql.= " t.fk_commandedet,";
		$sql.= " t.fk_custominfo,";
		$sql.= " t.value";

		
        $sql.= " FROM ".MAIN_DB_PREFIX."custominfodet as t";
        $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                
				$this->fk_user = $obj->fk_user;
				$this->fk_societe = $obj->fk_societe;
				$this->fk_product = $obj->fk_product;
				$this->fk_contratdet = $obj->fk_contratdet;
				$this->fk_commandedet = $obj->fk_commandedet;
				$this->fk_custominfo = $obj->fk_custominfo;
				$this->value = $obj->value;

                
            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
            return -1;
        }
    }


    /**
     *  Update object into database
     *
     *  @param	User	$user        User that modify
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    function update($user=0, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->fk_user)) $this->fk_user=trim($this->fk_user);
		if (isset($this->fk_societe)) $this->fk_societe=trim($this->fk_societe);
		if (isset($this->fk_product)) $this->fk_product=trim($this->fk_product);
		if (isset($this->fk_contratdet)) $this->fk_contratdet=trim($this->fk_contratdet);
		if (isset($this->fk_commandedet)) $this->fk_commandedet=trim($this->fk_commandedet);
		if (isset($this->fk_custominfo)) $this->fk_custominfo=trim($this->fk_custominfo);
		if (isset($this->value)) $this->value=trim($this->value);

        

		// Check parameters
		// Put here code to add control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."custominfodet SET";
        
		$sql.= " fk_user=".(isset($this->fk_user)?$this->fk_user:"null").",";
		$sql.= " fk_societe=".(isset($this->fk_societe)?$this->fk_societe:"null").",";
		$sql.= " fk_product=".(isset($this->fk_product)?$this->fk_product:"null").",";
		$sql.= " fk_contratdet=".(isset($this->fk_contratdet)?$this->fk_contratdet:"null").",";
		$sql.= " fk_commandedet=".(isset($this->fk_commandedet)?$this->fk_commandedet:"null").",";
		$sql.= " fk_custominfo=".(isset($this->fk_custominfo)?"'".$this->db->escape($this->fk_custominfo)."'":"null").",";
		$sql.= " value=".(isset($this->value)?"'".$this->db->escape($this->value)."'":"null")."";

        
        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{
			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action call a trigger.

	            //// Call triggers
	            //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
	    	}
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
    }


 	/**
	 *  Delete object in database
	 *
     *	@param  User	$user        User that delete
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		$this->db->begin();

		if (! $error)
		{
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
		        // want this action call a trigger.

		        //// Call triggers
		        //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
		        //$interface=new Interfaces($this->db);
		        //$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
		        //if ($result < 0) { $error++; $this->errors=$interface->errors; }
		        //// End call triggers
			}
		}

		if (! $error)
		{
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."custominfodet";
    		$sql.= " WHERE rowid=".$this->id;

    		dol_syslog(get_class($this)."::delete sql=".$sql);
    		$resql = $this->db->query($sql);
        	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}



	/**
	 *	Load an object from its id and create a new one in database
	 *
	 *	@param	int		$fromid     Id of object to clone
	 * 	@return	int					New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Custominfodet($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		$object->statut=0;

		// Clear fields
		// ...

		// Create clone
		$result=$object->create($user);

		// Other options
		if ($result < 0)
		{
			$this->error=$object->error;
			$error++;
		}

		if (! $error)
		{


		}

		// End
		if (! $error)
		{
			$this->db->commit();
			return $object->id;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Initialise object with example values
	 *	Id must be 0 if object instance is a specimen
	 *
	 *	@return	void
	 */
	function initAsSpecimen()
	{
		$this->id=0;
		
		$this->fk_user='';
		$this->fk_societe='';
		$this->fk_product='';
		$this->fk_contratdet='';
		$this->fk_commandedet='';
		$this->fk_custominfo='';
		$this->value='';

		
	}

}
?>
