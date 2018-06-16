<?php
class TeamManager {
	
	public function checkTeamByTeamIdAndCompanyId($team_id, $company_id, $db){
		
		$sql = "select teams.id from companies 
						join teams on companies.id = teams.company_id
						where companies.id = :company_id
						and teams.id = :team_id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["team_id" => $team_id, "company_id" => $company_id]);
		$result = $stmt->fetch();
		return $result;		
	}
	
	public function checkTeamByTeamId($team_id, $db){
		$sql = "select t.id from teams t
						where t.id = :team_id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["team_id" => $team_id]);
		$result = $stmt->fetch();
		
		return $result;		
	}
	
	public function checkEventById($event_id, $db){
		$sql = "select t.id from events t
						where t.id = :event_id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["event_id" => $event_id]);
		$result = $stmt->fetch();
		
		return $result;		
	}
	
	public function getTeamsByCompanyId($company_id, $db) {
        $sql = "SELECT t.id, t.name as team_name, s.name as season 
            from teams t
				join seasons s on s.id = t.season_id
				where t.company_id = :company_id";
            
        $stmt = $db->prepare($sql);
		
		$stmt->execute(["company_id" => $company_id]);    

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        return $results;
    }
    
    public function getTeamsByCoachUserId($user_id, $db) {
        $sql = "SELECT t.id, t.name
				from users u
				join coaches c on c.user_id = u.id
				join teams_coaches tc on tc.coach_id = c.id
				join teams t on tc.team_id = t.id
				where u.id = :user_id
				and u.active = 1";
            
        $stmt = $db->prepare($sql);
		
		$stmt->execute(["user_id" => $user_id]);    

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        return $results;
    }
    
    public function getTeamsByAthleteUserId($user_id, $db) {
        $sql = "SELECT t.id, t.name
				from users u
				join athletes a on a.user_id = u.id
				join teams_athletes ta on ta.athlete_id = a.id
				join teams t on ta.team_id = t.id
				where u.id = :user_id
				and u.active = 1";
            
        $stmt = $db->prepare($sql);
		
		$stmt->execute(["user_id" => $user_id]);    

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        return $results;
    }
	
	public function getEventsByCompanyIdAndTeamId($company_id, $team_id, $db) {
        $sql = "SELECT e.id, e.name as event_name, e.event_date, t.name as team_name, et.type 
            from events e
				join teams t on e.team_id = t.id
				join event_types et on et.id = e.event_type_id
				where t.company_id = :company_id
				and e.team_id = :team_id";
            
        $stmt = $db->prepare($sql);
		
		$stmt->execute(["company_id" => $company_id, "team_id"=> $team_id]);    

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        return $results;
    }
    
    public function getAthletesByCompanyIdAndEventId($company_id, $event_id, $db) {

        $sql = "SELECT a.id, a.user_id, u.id, u.email 
				from athletes a
				join users u on a.user_id = u.id
				join events_athletes ea on ea.athlete_id = a.id
				where a.company_id = :company_id
				and ea.event_id = :event_id";
            
        $stmt = $db->prepare($sql);
		
		$stmt->execute(["company_id" => $company_id, "event_id"=> $event_id]);    

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        return $results;
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
	
	public function createAthleteInEvent($event_id, $athlete_id, $db){
		try {		
			$sql = "insert into events_athletes
				(event_id, athlete_id) values
				(:event_id, :athlete_id)";

			$stmt = $db->prepare($sql);
			$result = $stmt->execute(["event_id" => $event_id, "athlete_id" => $athlete_id]);			
		} catch (PDOException $e){
			$errorInfo = $e->errorInfo;
			if($errorInfo[1] == 1062) throw new Exception ("athlete already exists in event", 409);
			else throw new Exception ("MySQL error " . $errorInfo[1]);
		}
		
		
	}
	
	public function createEventInTeam($name, $event_date, $team_id, $event_type_id, $db){
		try {		
			$sql = "insert into events
				(name, event_date, team_id, event_type_id) values
				(:name, :event_date, :team_id, :event_type_id)";

			$stmt = $db->prepare($sql);
			$result = $stmt->execute(["name" => $name, "event_date" => $event_date, "team_id" => $team_id, "event_type_id" => $event_type_id]);			
		} catch (PDOException $e){
			$errorInfo = $e->errorInfo;
			if($errorInfo[1] == 1062) throw new Exception ("event already exists in this date", 409);
			else throw new Exception ("MySQL error " . $errorInfo[1]);
		}
		
		return $db->lastInsertId();
		
		
	}


}
