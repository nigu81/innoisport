<?php
class UserManager {
	public function getBasicHttpAuthenticatication($request, $response,$db){
		$username = $request->getHeaderLine('PHP_AUTH_USER');
	    $password = $request->getHeaderLine('PHP_AUTH_PW');
	    $result = "";
	    
		if (empty($username)) throw new Exception("Unauthorized - Authentication needed",401);

		$sql = "SELECT t.id, t.username, t.email
			from users t
			where t.username = :username and t.password = :password and active = 1";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["username" => $username, "password"=>$password]);
		$result = $stmt->fetch();
		
		if (!$result) throw new Exception("Bad credentials",401);
		
		$user = new User($result['email']);
		$user->setId($result['id']);
		$user->setUsername($result['username']);
		return $user;
    }
    
    
	public function checkUserById($id, $db){
		
		$sql = "SELECT t.id
				from users t
				where t.id = :id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["id" => $id]);
		$result = $stmt->fetch();
		
		return $result;		
	}
	
	public function checkAthleteByAthleteId($athlete_id, $db){
		$sql = "select t.id from athletes t
						where t.id = :athlete_id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["athlete_id" => $athlete_id]);
		$result = $stmt->fetch();
		
		return $result;		
	}
	
	// forse da cancellare
	public function isUserAdminById($id, $db){
		$sql = "SELECT t.id
				from users t
				join admins c on t.id = c.user_id
				where c.user_id = :id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["id" => $id]);	
		$result = $stmt->fetch();
		if ($result) {
			return true;
		}
		return false;
	}
	
	public function isUserAdminOfCompanyByIds($user_id, $company_id, $db){
		
		$sql = "SELECT t.id
				from users t
				join admins c on t.id = c.user_id
				where c.user_id = :user_id and c.company_id = :company_id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["user_id" => $user_id, "company_id" => $company_id]);	
		$result = $stmt->fetch();
		if ($result) {
			return true;
		}
		return false;
	}
	
	public function isUserCoachOfTeamByIds($user_id, $team_id, $db){
		
		$sql = "SELECT t.id
				from users t
				join coaches c on t.id = c.user_id
				join teams_coaches tc on c.id = tc.coach_id
				where c.user_id = :user_id and tc.team_id = :team_id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["user_id" => $user_id, "team_id" => $team_id]);	
		$result = $stmt->fetch();
		if ($result) {
			return true;
		}
		return false;
	}
	
	public function isUserSuperAdminOfCompanyByIds($user_id, $company_id, $db){

		$sql = "SELECT t.id
				from users t
				join admins c on t.id = c.user_id
				where c.user_id = :user_id and c.company_id = :company_id and c.superadmin = 1";
		$stmt = $db->prepare($sql);
		
		$results = $stmt->execute(["user_id" => $user_id, "company_id" => $company_id]);
	
		
		$result = $stmt->fetch();
		
		if ($result) {
			return true;
		}
		return false;
	}
	
	public function getUsers($db) {
        $sql = "SELECT t.id, t.username, t.email, t.active
            from users t";
        $stmt = $db->query($sql);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        return $results;
    }
    
    public function getUserById($user_id, $db) {
        $sql = "SELECT t.id, t.username, t.email, t.active
            from users t 
            where t.id = :user_id";
            
        $stmt = $db->prepare($sql);
		
		$stmt->execute(["user_id" => $user_id]);    

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
 
        return $result;
    }
    
    public function getAdminsByIdCompany($company_id, $db) {
        $sql = "SELECT t.id, t.username, t.email, t.active, a.superadmin
                from users t
				join admins a on t.id = a.id
				where a.company_id = :company_id";
            
        $stmt = $db->prepare($sql);
		
		$stmt->execute(["company_id" => $company_id]);    

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        return $results;
    }
    
    public function getAdminByIdAdminAndIdCompany($admin_id, $company_id, $db) {
        $sql = "SELECT t.id, t.username, t.email, t.active, a.superadmin
                from users t
				join admins a on t.id = a.id
				where a.company_id = :company_id
				and a.id = :admin_id";
            
        $stmt = $db->prepare($sql);
		
		$stmt->execute(["company_id" => $company_id, "admin_id" => $admin_id]);    

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
 
        return $result;
    }
	
	public function getAthletesByIdCompany($company_id, $db) {
        $sql = "SELECT u.id, u.username, u.email, u.active
                from users u
				join athletes a on u.id = a.id
				where a.company_id = :company_id";
            
        $stmt = $db->prepare($sql);
		
		$stmt->execute(["company_id" => $company_id]);    

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        return $results;
    }
		
	public function getAthletesByTeamIdAndCompanyId($team_id, $company_id, $db) {
        $sql = "SELECT u.id, u.username, u.email, u.active, ta.team_id
            from users u
				join athletes a on u.id = a.id
				join teams_athletes ta on ta.athlete_id = a.id 
				where a.company_id = :company_id
				and ta.team_id = :team_id";
            
        $stmt = $db->prepare($sql);
		
		$stmt->execute(["company_id" => $company_id, "team_id" => $team_id]);    

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
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
	
	public function getAthletesByCompanyIdAndTeamId($company_id, $team_id, $db) {
        $sql = "SELECT t.id, t.name as team_name, s.name as season 
            from teams t
				join seasons s on s.id = t.season_id
				where t.company_id = :company_id";
            
        $stmt = $db->prepare($sql);
		
		$stmt->execute(["company_id" => $company_id]);    

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        return $results;
    }
	
	public function createAthlete($user_id, $company_id, $db){
		try {		
			$sql = "insert into athletes
				(user_id, company_id) values
				(:user_id, :company_id)";

			$stmt = $db->prepare($sql);
			$result = $stmt->execute(["user_id" => $user_id, "company_id" => $company_id]);			
		} catch (PDOException $e){
			$errorInfo = $e->errorInfo;
			if($errorInfo[1] == 1062) throw new Exception ("athlete already exists", 409);
			else throw new Exception ("MySQL error " . $errorInfo[1]);
		}
		
		return $db->lastInsertId();
		
		
	}
	
		public function createCoach($user_id, $company_id, $db){
		try {		
			$sql = "insert into coaches
				(user_id, company_id) values
				(:user_id, :company_id)";

			$stmt = $db->prepare($sql);
			$result = $stmt->execute(["user_id" => $user_id, "company_id" => $company_id]);			
		} catch (PDOException $e){
			$errorInfo = $e->errorInfo;
			if($errorInfo[1] == 1062) throw new Exception ("coach already exists", 409);
			else throw new Exception ("MySQL error " . $errorInfo[1]);
		}
		
		return $db->lastInsertId();
		
		
	}
	
	public function createAdmin($user_id, $company_id, $superadmin, $db){
		try {		
			$sql = "insert into admins
				(user_id, company_id, superadmin) values
				(:user_id, :company_id, :superadmin)";

			$stmt = $db->prepare($sql);
			$result = $stmt->execute(["user_id" => $user_id, "company_id" => $company_id, "superadmin" => $superadmin]);			
		} catch (PDOException $e){
			$errorInfo = $e->errorInfo;
			if($errorInfo[1] == 1062) throw new Exception ("admin already exists", 409);
			else throw new Exception ("MySQL error " . $errorInfo[1]);
		}
		
		return $db->lastInsertId();
		
		
	}
	
	public function createUser($email, $db){
		try {		
			$sql = "insert into users
				(email) values
				(:email)";

			$stmt = $db->prepare($sql);
			$result = $stmt->execute(["email" => $email]);			
		} catch (PDOException $e){
			$errorInfo = $e->errorInfo;
			if($errorInfo[1] == 1062) throw new Exception ("email already exists", 409);
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

	public function createCoachInTeam($team_id, $coach_id, $db){

		try {		
			$sql = "insert into teams_coaches
				(team_id, coach_id) values
				(:team_id, :coach_id)";

			$stmt = $db->prepare($sql);
			$result = $stmt->execute(["team_id" => $team_id, "coach_id" => $coach_id]);			
		} catch (PDOException $e){
			$errorInfo = $e->errorInfo;
			if($errorInfo[1] == 1062) throw new Exception ("coach already exists in team", 409);
			else throw new Exception ("MySQL error " . $errorInfo[1]);
		}
	}
	
	public function checkAthleteByAthleteIdAndCompanyId($athlete_id, $company_id, $db){
		$sql = "SELECT t.id
				from athletes t
				where t.id = :athlete_id and t.company_id = :company_id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["athlete_id" => $athlete_id, "company_id"=>$company_id]);
		$result = $stmt->fetch();
		
		return $result;		
	}  
	
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
	
	// da eliminare forse	
	public function getAthleteByUserIdAndCompanyId($user_id, $company_id, $db){
		$sql = "SELECT t.id, t.user_id, t.company_id
				from athletes t
				where t.user_id = :user_id and t.company_id = :company_id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["user_id" => $user_id, "company_id"=>$company_id]);
		$result = $stmt->fetch();
		$athlete = new Athlete($user_id, $company_id);
		$athlete->setId($result['id']);
		return $athlete;		
	}    
	
	// da eliminare forse
	public function getTeamByAthleteIdAndTeamId($athlete_id, $team_id, $db){
		$sql = "SELECT t.team_id, t.athlete_id
				from teams_athletes t
				where t.team_id = :team_id and t.athlete_id = :athlete_id";
		$stmt = $db->prepare($sql);
		$results = $stmt->execute(["team_id" => $team_id, "athlete_id"=>$athlete_id]);
		$result = $stmt->fetch();
		$athlete = new Team();
		$athlete->setId($result['team_id']);
		return $athlete;		
	}    
	
}
