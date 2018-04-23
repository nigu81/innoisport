<?php
class Team {
	private $id;
	private $name;
	private $company_id; // dovrebbe essere un oggetto Company
	private $season_id;
	
	//function __construct(){ // soluzione momentanea in attesa di fare un repository come si deve
	//	$this->user_id = $user_id;
	//	$this->company_id = $company_id;
	//}
	
	public function getId(){
		return $this->id;
	}
	
	public function setId($id){
		$this->id = $id;
	} 

}
