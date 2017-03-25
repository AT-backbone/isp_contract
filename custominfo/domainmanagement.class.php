<?php

class domains{
	
	function domains($db){
		$this->db = $db;
		$this->ddb = $this->db;
	}
	
	function fetchDomainsFromCustomer($CustomID){
		$sql = ("SELECT cp.id as cpid, cp.customer, cp.betrag, cp.pid as pid, cp.artikelbezeichnung as artikelbezeichnung, cp.verbis as verbis, cp.kuendigungsdatum as kuendigungsdatum, cp.betrag as betrag, cp.deleted as deleted, cpp.text as domain
FROM ".MAIN_DB_PREFIX."dv_customer_products AS cp
LEFT  JOIN ".MAIN_DB_PREFIX."dv_customer_products_properties AS cpp ON cp.id = cpp.cpid 
WHERE cp.DOLcustomer=".$CustomID." And (cpp.kennz='DN' or cpp.kennz='AB' or cpp.kennz is Null)
ORDER BY deleted ASC, cpp.text ASC
");

		if($res = $this->ddb->query($sql) ){
			$list = array();
		//print_r($res);
			while($list[] = $this->db->fetch_object( $res ) );
			return $list;
		}
		return false;

	}
	function fetchProduct($cpid){
		$sql = ("SELECT * FROM ".MAIN_DB_PREFIX."dv_customer_products 
WHERE id=".$cpid." 
");

		if($res = $this->ddb->query($sql) ){
			if($obj = $this->db->fetch_object( $res )){
				$this->id = $obj->id;
				$this->artikelbezeichnung = $obj->artikelbezeichnung;
				$this->verbis = $obj->verbis;
				$this->vorauszahlung = $obj->vorauszahlung;
				$this->kuendigungsdatum = $obj->kuendigungsdatum;
				$this->ablaufdatum = $obj->ablaufdatum;
				$this->deleted = $obj->deleted;
				$this->betrag = $obj->betrag;
				$this->rabatt = $obj->rabatt;
				
							
			}
		}
	}
	function updateProduct($cpid){
		$sql = ("UPDATE ".MAIN_DB_PREFIX."dv_customer_products 
			SET		artikelbezeichnung	=	'{$this->artikelbezeichnung}',
						verbis							=	'{$this->verbis}',
						vorauszahlung				=	'{$this->vorauszahlung}',
						kuendigungsdatum		=	'{$this->kuendigungsdatum}',
						ablaufdatum					=	'{$this->ablaufdatum}',
						deleted							=	'{$this->deleted}',			
						betrag							=	'{$this->betrag}',			
						rabatt							=	'{$this->rabatt}'			
			WHERE id=".$cpid." 
		");
	//	print($sql);

		if($res = $this->ddb->query($sql) ){
			return true;
		}else{
			$this->errormsg = $sql;
			return false;
		}
	}
	function getValuesFields($cpid){
				$sql = ("SELECT pid FROM ".MAIN_DB_PREFIX."dv_customer_products
				WHERE id=".$cpid."");
		if($res = $this->ddb->query($sql) ){
			$v  = $this->db->fetch_object( $res );
			$pid=$v->pid;
		}else return -1;				
		
		$sql = ("
			SELECT a.techinfo 
			FROM ".MAIN_DB_PREFIX."dv_t_articles as a
			LEFT JOIN ".MAIN_DB_PREFIX."dv_t_articles_to_products as ap ON ap.aid = a.aid
			WHERE ap.pid = $pid
		");
		if($res = $this->ddb->query($sql) ){
			$v = $this->db->fetch_object( $res );
			return explode('|', $v->techinfo);
		}else return array();		
	}
	function fetchValues($cpid){
		$vals = $this->getValuesFields($cpid);
		$list=array();
		foreach($vals as $v){
			$sql = ("SELECT cpp.text, cpp.zahl, cpp.md5, tap.bezeichnung, tap.type
				FROM ".MAIN_DB_PREFIX."dv_t_articles_properties as tap
				LEFT JOIN ".MAIN_DB_PREFIX."dv_customer_products_properties as cpp 
					ON 	tap.kennz = cpp.kennz
					AND cpp.cpid=".$cpid."
				WHERE tap.kennz = '$v'
			");
			if ($v != 'TI')
			if($res = $this->ddb->query($sql) ){
				$obj = $this->db->fetch_object( $res ) ;
				$this->{$v.'_value'} = $obj->{$obj->type};	
				$this->{$v.'_type'} = $obj->type;
				$this->{$v.'_title'} =$obj->bezeichnung;
				
			}
			
		}

		return $vals;	

		return false;

	}
	function updateValues($cpid){
		$vals = $this->getValuesFields($cpid);
		foreach($vals as $v){
			if ($v == 'TI') continue;
			$sql = ("
				SELECT * FROM ".MAIN_DB_PREFIX."dv_customer_products_properties
				WHERE cpid=".$cpid." AND kennz = '$v' LIMIT 0,1
			");
			$res = $this->ddb->query($sql);
				if($this->db->num_rows($res) == 0){
					$sql = ("
						INSERT INTO ".MAIN_DB_PREFIX."dv_customer_products_properties
							SET cpid=".$cpid.", kennz = '$v', cuser='{$user->id}'
					");
					$this->db->query($sql);
			}
			$sql = ("
				UPDATE ".MAIN_DB_PREFIX."dv_customer_products_properties
					SET ".($this->{$v.'_type'})." = '".($this->{$v.'_value'})."'				
				WHERE cpid=".$cpid." AND kennz = '$v'
			");
			if($this->ddb->query($sql) ){
			}else{
				$this->error++;
			}
		}
		if($this->error == 0) return true;
		else return false; 
	}
	function switchKennz($kennz){
		switch(strtoupper($kennz)){
			case 'AC': return 'AC - Daten Leitung';
			case 'DA': return 'DA - Daten Volumen';
			case 'DO': return 'DO - Domain';
			case 'HO': return 'HO - Hosting';
			case 'MA': return 'MA - Mail';
			case 'SK': return 'SK - Zertifikat';
			case 'VE': return 'VE - Mail Alt';	
		}
	}
	function SelectProducts($name,$val=-1,$null=0){
		$sql = ("
			SELECT tp.pid, tp.beschreibungkurz, ta.kennz FROM ".MAIN_DB_PREFIX."dv_t_products as tp
				LEFT JOIN ".MAIN_DB_PREFIX."dv_t_articles_to_products as atp ON atp.pid = tp.pid
				LEFT JOIN ".MAIN_DB_PREFIX."dv_t_articles as ta ON atp.aid = ta.aid
				WHERE tp.del = 0 GROUP BY tp.pid ORDER BY ta.kennz ASC, tp.beschreibungkurz ASC
		");
		print '<select name="'.$name.'">';
		if($null) print '<option value="-1"></option>';
			if($res = $this->ddb->query($sql) )
				$lkennz = '';
				while($obj = $this->db->fetch_object( $res ) ){
					if($lkennt != $obj->kennz && $lkennt != '') print '</optgroup>';
					if($lkennt != $obj->kennz) print '<optgroup label="'.$this->switchKennz($obj->kennz).'">';
						print '<option value="'.$obj->pid.'"'.(($obj->pid==$val)?' selected':'').'>'.$obj->beschreibungkurz.'</option>';
					$lkennt = $obj->kennz;
				}
		print '</optgroup></select>';
		
		
	}
	function newProduct(){
		global $user;
		$sql = ("
			SELECT * FROM ".MAIN_DB_PREFIX."dv_t_products
				WHERE pid = ".$this->pid."
		");
		if($res = $this->ddb->query($sql) ){			
			if($obj = $this->db->fetch_object( $res ) ){	
					
	$sql = ("SELECT price as preis FROM ".MAIN_DB_PRODUCT.MAIN_DB_PREFIX."product WHERE bbv_pid = ".$this->pid);
					if($res2 = $this->ddb->query($sql) ){
						if($ob = $this->db->fetch_object( $res2 ) ){
							$obj->preis = $ob->preis;
						}
					}
					
					$sql = ("
						INSERT INTO ".MAIN_DB_PREFIX."dv_customer_products
							SET pid								 	=	{$this->pid},
									DOLcustomer				 	= {$this->socid},
									artikelbezeichnung 	= '{$obj->beschreibungkurz}',
									kaufdatum          	= NOW(),
									betrag 							= {$obj->preis},
									cuser 							= {$user->id},
									verbis 							= '',
									kuendigungsdatum		= '',
									ablaufdatum					= ''
										
					");
					if($res = $this->ddb->query($sql)){
						return $this->db->last_insert_id($res);
						
					}else{
						$this->errormsg = $sql;
						return false;
					}
					
				
			
			}
		}else{
			$this->errormsg = $sql;
			return false;
		}
	
	}
	

}
class domain{
	function domain($obj){
				$this->cpid = $obj->cpid;
				$this->customer = $obj->customer;
				$this->pid = $obj->pid;
				$this->artikelbezeichnung = $obj->artikelbezeichnung;
				$this->verbis = $obj->verbis;
				$this->kuendigungsdatum = $obj->kuendigungsdatum;
				$this->betrag = $obj->betrag;
				$this->deleted = $obj->deleted;
				$this->domain = $obj->domain;
				
	}	
}
	
	
	


?>