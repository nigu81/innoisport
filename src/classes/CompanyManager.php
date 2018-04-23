<?php
class CompanyManager {
	
	public function checkCompanyById($id, $db){

		$sql = "SELECT t.id
				from companies t
				where t.id = :id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["id" => $id]);
		$result = $stmt->fetch();


		
		return $result;		
	}


}
