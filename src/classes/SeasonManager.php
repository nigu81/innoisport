<?php
class SeasonManager {

	public function createSeason($name, $company_id, $db){
		try {		
			$sql = "insert into seasons
				(name, company_id) values
				(:name, :company_id)";

			$stmt = $db->prepare($sql);
			$result = $stmt->execute(["name" => $name, "company_id" => $company_id]);			
		} catch (PDOException $e){
			$errorInfo = $e->errorInfo;
			if($errorInfo[1] == 1062) throw new Exception ("season already exists", 409);
			else throw new Exception ("MySQL error " . $errorInfo[1]);
		}
		
		return $db->lastInsertId();
			
	}
	
	public function checkSeasonBySeasonIdAndCompanyId($season_id, $company_id, $db){

		$sql = "SELECT t.id
				from seasons t
				where t.id = :season_id and t.company_id = :company_id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["season_id" => $season_id, "company_id" => $company_id]);
		$result = $stmt->fetch();
		
		return $result;		
	}
}
