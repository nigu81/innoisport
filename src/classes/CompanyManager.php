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
	
	public function getCompaniesByIdUser($user_id, $db) {
        $sql = "SELECT c.id, c.name, a.superadmin
                from users u 
				join admins a on a.user_id = u.id
				join companies c on a.company_id = c.id  
				where a.user_id = :user_id
				and u.active = 1";
            
        $stmt = $db->prepare($sql);
		
		$stmt->execute(["user_id" => $user_id]);    

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        return $results;
    }


}
