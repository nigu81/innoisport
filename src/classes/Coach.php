<?php
class Coach {
	private $id;
	private $user_id; //dovrebbe essere un oggetto User
	private $company_id; // dovrebbe essere un oggetto Company
	
	function __construct($user_id, $company_id){ // i parametri dovrebbero essere oggetti
		$this->user_id = $user_id;
		$this->company_id = $company_id;
	}
	
	public function getId(){
		return $this->id;
	}
	
	/*public function setId($id){
		$this->id = $id;
	} */

}
