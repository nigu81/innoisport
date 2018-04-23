<?php
class Season {
	private $id;
	private $name;
	private $company_id; // dovrebbe essere un oggetto Company
	
	function __construct($name, $company_id){ // i parametri dovrebbero essere oggetti
		$this->name = $name;
		$this->company_id = $company_id;
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function setId($id){
		$this->id = $id;
	} 
	
	public function setName($name){
		$this->name = $name;
	}
	
	public function setCompany($company_id){
		$this->company_id = $company_id;
	}

}
