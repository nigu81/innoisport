<?php
class TeamManager {
	
	public function checkTeamByTeamIdAndCompanyId($team_id, $company_id, $db){
		$sql = "select 	* from companies 
						join teams on companies.id = teams.company_id
						where companies.id = :company_id
						and teams.id = :team_id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["team_id" => $team_id, "company_id" => $company_id]);
		$result = $stmt->fetch();
		
		return $result;		
	}
	
	public function createTeam($name, $company_id, $season_id, $db){
		try {		
			$sql = "insert into teams
				(name, company_id, season_id) values
				(:name, :company_id, :season_id)";

			$stmt = $db->prepare($sql);
			$result = $stmt->execute(["name" => $name, "company_id" => $company_id, "season_id" => $season_id]);			
		} catch (PDOException $e){
			$errorInfo = $e->errorInfo;
			if($errorInfo[1] == 1062) throw new Exception ("team already exists", 409);
			else throw new Exception ("MySQL error " . $errorInfo[1]);
		}
		
		return $db->lastInsertId();
			
	}
	
		public function createAthleteInTeam($team_id, $athlete_id, $db){
		try {		
			$sql = "insert into teams_athletes
				(team_id, athlete_id) values
				(:team_id, :athlete_id)";

			$stmt = $db->prepare($sql);
			$result = $stmt->execute(["team_id" => $team_id, "athlete_id" => $athlete_id]);			
		} catch (PDOException $e){
			$errorInfo = $e->errorInfo;
			if($errorInfo[1] == 1062) throw new Exception ("athlete already exists in team", 409);
			else throw new Exception ("MySQL error " . $errorInfo[1]);
		}
		
		
	}


}
