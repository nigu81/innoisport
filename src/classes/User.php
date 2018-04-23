<?php
class User {
	private $id;
	private $username;
	private $email;
	private $active;
	
	function __construct($email){
		$this->email = $email;
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function setId($id){
		$this->id = $id;
	} 
	
	public function setUsername($username){
		$this->username = $username;
	}
		
	public function isCoach($db){
		$sql = "SELECT t.id
				from users t
				join coaches c on t.id = c.user_id
				where c.user_id = :id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["id" => $this->id]);	
		$result = $stmt->fetch();
		if ($result) {
			return true;
		}
		return false;
	}
	
	public function isAdmin($db){
		$sql = "SELECT t.id
				from users t
				join admins c on t.id = c.user_id
				where c.user_id = :id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["id" => $this->id]);	
		$result = $stmt->fetch();
		if ($result) {
			return true;
		}
		return false;
	}


}
