<?php
class CoachManager {
	
	public function checkCoachByCoachIdAndCompanyId($coach_id, $company_id, $db){
		
		$sql = "select 	* from companies 
						join coaches on companies.id = coaches.company_id
						where companies.id = :company_id
						and coaches.id = :coach_id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["coach_id" => $coach_id, "company_id" => $company_id]);
		$result = $stmt->fetch();


		
		return $result;		
	}


}
