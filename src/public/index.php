<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['db']['host']   = "localhost";
$config['db']['user']   = "nigu";
$config['db']['pass']   = "nigu";
$config['db']['dbname'] = "exampleapp";


$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['user_manager'] = function ($c) {
    $user_manager = new UserManager;
    return $user_manager;
};

$container['company_manager'] = function ($c) {
    $company_manager = new CompanyManager;
    return $company_manager;
};

$container['team_manager'] = function ($c) {
    $team_manager = new TeamManager;
    return $team_manager;
};/*

$container['athlete_manager'] = function ($c) {
    $athlete_manager = new AthleteManager;
    return $athlete_manager;
};
*/

/*
$container['coach_manager'] = function ($c) {
    $coach_manager = new CoachManager;
    return $coach_manager;
};
*/
$container['season_manager'] = function ($c) {
    $season_manager = new SeasonManager;
    return $season_manager;
};

$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
		
		//Format of exception to return
        
        return $c->get('response')->withStatus($exception->getCode())
            ->withHeader('Content-Type', 'application/json')
            ->write($exception->getMessage());
    };
};

$app->get('/tickets', function (Request $request, Response $response) {
	$container = $this;
	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$container);
	if ($logged_user){
		if ($logged_user->isCoach($this->db)) {
			echo "Sono un coach";
		};
		$mapper = new TicketMapper($this->db);
        $tickets = $mapper->getTickets();
        return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/json')
                        ->write(json_encode(array("tickets" => $tickets)));
	}

    return $response;
});

// ----------------------------ADMIN ENDPOINTS--------- POST

// add a user
$app->post('/user', function (Request $request, Response $response) {
	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$isAdmin = $this->user_manager->isUserAdminById($logged_user->getId(),$this->db);
	if (!$isAdmin) throw new Exception ("not an admin", 403);
	
    $data = $request->getParsedBody();
    $user_data = [];
    $user_data['email'] = filter_var($data['email'], FILTER_SANITIZE_STRING);
    $user_id = $this->user_manager->createUser($user_data['email'],$this->db);
    
    
    
    //$user = $this->user_manager->getUserFromEmail($user_data['email'], $this->db);
    return $response->getBody()->write("User created with id ".$user_id);
});


// add a user as new athlete in a company
$app->post('/company/{company_id}/user/{user_id}/athlete', function (Request $request, Response $response, $args) {
	
	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$user_exists = $this->user_manager->checkUserById($args['user_id'],$this->db);
    if (!$user_exists) throw new Exception("user not found",404);
	
	$company_exists = $this->company_manager->checkCompanyById($args['company_id'],$this->db);
	if (!$company_exists) throw new Exception("company not found",404);
	
	$isAdminOfCompany = $this->user_manager->isUserAdminOfCompanyByIds($logged_user->getId(), $args['company_id'],$this->db);
	if (!$isAdminOfCompany) throw new Exception ("not an admin of company ".$args['company_id'], 403);
	
	$athlete_id = $this->user_manager->createAthlete($args['user_id'], $args['company_id'],$this->db);
	
	return $response->getBody()->write("Athlete created with id ".$athlete_id);

});

// add a user as new coach in a company
$app->post('/company/{company_id}/user/{user_id}/coach', function (Request $request, Response $response, $args) {
	
	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$user_exists = $this->user_manager->checkUserById($args['user_id'],$this->db);
    if (!$user_exists) throw new Exception("user not found",404);
	
	$company_exists = $this->company_manager->checkCompanyById($args['company_id'],$this->db);
	if (!$company_exists) throw new Exception("company not found",404);
	
	$isAdminOfCompany = $this->user_manager->isUserAdminOfCompanyByIds($logged_user->getId(), $args['company_id'],$this->db);
	if (!$isAdminOfCompany) throw new Exception ("not an admin of company ".$args['company_id'], 403);
	
	$coach_id = $this->user_manager->createCoach($args['user_id'], $args['company_id'],$this->db);
	
	return $response->getBody()->write("Coach created with id ".$coach_id);

});

// add a new admin in a company
$app->post('/company/{company_id}/user/{user_id}/admin', function (Request $request, Response $response, $args) {
	
	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$data = $request->getParsedBody();
	$super_admin = $data['superadmin'];
	
	$user_exists = $this->user_manager->checkUserById($args['user_id'],$this->db);
    if (!$user_exists) throw new Exception("user not found",404);
	
	$company_exists = $this->company_manager->checkCompanyById($args['company_id'],$this->db);
	if (!$company_exists) throw new Exception("company not found",404);
	
	$isSuperAdminOfCompany = $this->user_manager->isUserSuperAdminOfCompanyByIds($logged_user->getId(), $args['company_id'],$this->db);
	if (!$isSuperAdminOfCompany) throw new Exception ("not a super admin of company ".$args['company_id'], 403);

	$admin_id = $this->user_manager->createAdmin($args['user_id'], $args['company_id'], $data['superadmin'], $this->db);
	
	return $response->getBody()->write("Admin created with id ".$admin_id);

});

// add a new season in a company
$app->post('/company/{company_id}/season', function (Request $request, Response $response, $args) {
	
	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$data = $request->getParsedBody();
	$season_name = $data['name'];
	
	$company_exists = $this->company_manager->checkCompanyById($args['company_id'],$this->db);
	if (!$company_exists) throw new Exception("company not found",404);
	
	$isSuperAdminOfCompany = $this->user_manager->isUserSuperAdminOfCompanyByIds($logged_user->getId(), $args['company_id'],$this->db);
	if (!$isSuperAdminOfCompany) throw new Exception ("not a super admin of company ".$args['company_id'], 403);

	$season_id = $this->season_manager->createSeason($data['name'], $args['company_id'], $this->db);
	
	return $response->getBody()->write("Season created with id ".$season_id);

});

// add a new team in a company in a season
$app->post('/company/{company_id}/team', function (Request $request, Response $response, $args) {
	
	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$data = $request->getParsedBody();
	$season_name = $data['name'];
	$season_name = $data['season_id'];

	$company_exists = $this->company_manager->checkCompanyById($args['company_id'],$this->db);
	if (!$company_exists) throw new Exception("company not found",404);
	
	$season_exists = $this->season_manager->checkSeasonBySeasonIdAndCompanyId($data['season_id'], $args['company_id'], $this->db);
	if (!$season_exists) throw new Exception("season not found",404);

	$isSuperAdminOfCompany = $this->user_manager->isUserSuperAdminOfCompanyByIds($logged_user->getId(), $args['company_id'],$this->db);
	if (!$isSuperAdminOfCompany) throw new Exception ("not a super admin of company ".$args['company_id'], 403);

	$team_id = $this->team_manager->createTeam($data['name'], $args['company_id'], $data['season_id'], $this->db);
	
	return $response->getBody()->write("Team created with id ".$team_id);

});

// add a new athlete in team in a company
$app->post('/company/{company_id}/team/{team_id}/athlete/{athlete_id}', function (Request $request, Response $response, $args) {
	
	// TODO endpoint troppo lungo, meglio mettere tutto nel body
	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$company_exists = $this->company_manager->checkCompanyById($args['company_id'],$this->db);
	if (!$company_exists) throw new Exception("company not found",404);
	
	$isAdminOfCompany = $this->user_manager->isUserAdminOfCompanyByIds($logged_user->getId(), $args['company_id'],$this->db);
	if (!$isAdminOfCompany) throw new Exception ("not an admin of company ".$args['company_id'], 403);
	
	$team_exists = $this->team_manager->checkTeamByTeamIdAndCompanyId($args['team_id'], $args['company_id'],$this->db);
	if (!$team_exists) throw new Exception("team not found",404);
	
	$athlete_exists = $this->user_manager->checkAthleteByAthleteIdAndCompanyId($args['athlete_id'], $args['company_id'],$this->db);
	if (!$athlete_exists) throw new Exception("athlete not found",404);
	
	//$this->user_manager->createAthleteInTeam($args['team_id'], $args['athlete_id'],$this->db);
	$this->team_manager->createAthleteInTeam($args['team_id'], $args['athlete_id'],$this->db);
	
	return $response->getBody()->write("Athlete ".$args['athlete_id']." added in team ".$args['team_id']);

});

// add a new coach in team in a company
$app->post('/company/{company_id}/team/{team_id}/coach/{coach_id}', function (Request $request, Response $response, $args) {
	
	// TODO endpoint troppo lungo, meglio mettere tutto nel body
	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$company_exists = $this->company_manager->checkCompanyById($args['company_id'],$this->db);
	if (!$company_exists) throw new Exception("company not found",404);
	
	$isAdminOfCompany = $this->user_manager->isUserAdminOfCompanyByIds($logged_user->getId(), $args['company_id'],$this->db);
	if (!$isAdminOfCompany) throw new Exception ("not an admin of company ".$args['company_id'], 403);
	
	$team_exists = $this->team_manager->checkTeamByTeamIdAndCompanyId($args['team_id'], $args['company_id'],$this->db);
	if (!$team_exists) throw new Exception("team not found",404);
	
	$coach_exists = $this->user_manager->checkCoachByCoachIdAndCompanyId($args['coach_id'], $args['company_id'],$this->db);
	if (!$coach_exists) throw new Exception("coach not found",404);
	
	$this->user_manager->createCoachInTeam($args['team_id'], $args['coach_id'],$this->db);
	
	return $response->getBody()->write("Coach ".$args['coach_id']." added in team ".$args['team_id']);

});


// ----------------------------ADMIN ENDPOINTS--------- GET

// forse da eliminare, non mi sermbra utile
$app->get('/users', function (Request $request, Response $response, $args) {

	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$isAdmin = $this->user_manager->isUserAdminById($logged_user->getId(),$this->db);
	if (!$isAdmin) throw new Exception ("not an admin", 403);
	
	$users = $this->user_manager->getUsers($this->db);
    return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/json')
                        ->write(json_encode($users));

});

// da aggiungere get user by email
$app->get('/user/{user_id}', function (Request $request, Response $response, $args) {

	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$isAdmin = $this->user_manager->isUserAdminById($logged_user->getId(),$this->db);
	if (!$isAdmin) throw new Exception ("not an admin", 403);
	
	$user = $this->user_manager->getUserById($args['user_id'], $this->db);
	
    return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/json')
                        ->write(json_encode($user));

});

$app->get('/company/{company_id}/admins', function (Request $request, Response $response, $args) {

	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$company_exists = $this->company_manager->checkCompanyById($args['company_id'],$this->db);
	if (!$company_exists) throw new Exception("company not found",404);
	
	$isAdminOfCompany = $this->user_manager->isUserAdminOfCompanyByIds($logged_user->getId(), $args['company_id'],$this->db);
	if (!$isAdminOfCompany) throw new Exception ("not an admin of company ".$args['company_id'], 403);
	
	$admins = $this->user_manager->getAdminsByIdCompany($args['company_id'], $this->db);
	
    return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/json')
                        ->write(json_encode($admins));

});


$app->get('/company/{company_id}/admin/{admin_id}', function (Request $request, Response $response, $args) {

	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$company_exists = $this->company_manager->checkCompanyById($args['company_id'],$this->db);
	if (!$company_exists) throw new Exception("company not found",404);
	
	$isAdminOfCompany = $this->user_manager->isUserAdminOfCompanyByIds($logged_user->getId(), $args['company_id'],$this->db);
	if (!$isAdminOfCompany) throw new Exception ("not an admin of company ".$args['company_id'], 403);
	
	$admin = $this->user_manager->getAdminByIdAdminAndIdCompany($args['admin_id'], $args['company_id'], $this->db);
	
    return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/json')
                        ->write(json_encode($admin));

});

$app->get('/company/{company_id}/athletes', function (Request $request, Response $response, $args) {

	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$company_exists = $this->company_manager->checkCompanyById($args['company_id'],$this->db);
	if (!$company_exists) throw new Exception("company not found",404);
	
	$isAdminOfCompany = $this->user_manager->isUserAdminOfCompanyByIds($logged_user->getId(), $args['company_id'],$this->db);
	if (!$isAdminOfCompany) throw new Exception ("not an admin of company ".$args['company_id'], 403);
	
	$athletes = $this->user_manager->getAthletesByIdCompany($args['company_id'], $this->db);
	
    return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/json')
                        ->write(json_encode($athletes));

});

$app->get('/company/{company_id}/athlete/{athlete_id}', function (Request $request, Response $response, $args) {

	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$company_exists = $this->company_manager->checkCompanyById($args['company_id'],$this->db);
	if (!$company_exists) throw new Exception("company not found",404);
	
	$isAdminOfCompany = $this->user_manager->isUserAdminOfCompanyByIds($logged_user->getId(), $args['company_id'],$this->db);
	if (!$isAdminOfCompany) throw new Exception ("not an admin of company ".$args['company_id'], 403);
	
	$athlete = $this->user_manager->getAthleteByAthleteIdAndCompanyId($args['athlete_id'], $args['company_id'], $this->db);
	
    return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/json')
                        ->write(json_encode($athlete));

});

$app->get('/company/{company_id}/teams', function (Request $request, Response $response, $args) {

	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$company_exists = $this->company_manager->checkCompanyById($args['company_id'],$this->db);
	if (!$company_exists) throw new Exception("company not found",404);
	
	$isAdminOfCompany = $this->user_manager->isUserAdminOfCompanyByIds($logged_user->getId(), $args['company_id'],$this->db);
	if (!$isAdminOfCompany) throw new Exception ("not an admin of company ".$args['company_id'], 403);
	
	$teams = $this->team_manager->getTeamsByCompanyId($args['company_id'], $this->db);
	
    return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/json')
                        ->write(json_encode($teams));

});


$app->get('/company/{company_id}/team/{team_id}/athletes', function (Request $request, Response $response, $args) {

	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$company_exists = $this->company_manager->checkCompanyById($args['company_id'],$this->db);
	if (!$company_exists) throw new Exception("company not found",404);
	
	$isAdminOfCompany = $this->user_manager->isUserAdminOfCompanyByIds($logged_user->getId(), $args['company_id'],$this->db);
	if (!$isAdminOfCompany) throw new Exception ("not an admin of company ".$args['company_id'], 403);
	
	$team_exists = $this->team_manager->checkTeamByTeamIdAndCompanyId($args['team_id'], $args['company_id'],$this->db);
	if (!$team_exists) throw new Exception("team not found",404);
	
	$athletes = $this->user_manager->getAthletesByTeamIdAndCompanyId($args['team_id'], $args['company_id'], $this->db);
	
    return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/json')
                        ->write(json_encode($athletes));

});

$app->get('/company/{company_id}/team/{team_id}/events', function (Request $request, Response $response, $args) {

	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$company_exists = $this->company_manager->checkCompanyById($args['company_id'],$this->db);
	if (!$company_exists) throw new Exception("company not found",404);
	
	$isAdminOfCompany = $this->user_manager->isUserAdminOfCompanyByIds($logged_user->getId(), $args['company_id'],$this->db);
	if (!$isAdminOfCompany) throw new Exception ("not an admin of company ".$args['company_id'], 403);
	
	$team_exists = $this->team_manager->checkTeamByTeamIdAndCompanyId($args['team_id'], $args['company_id'],$this->db);
	if (!$team_exists) throw new Exception("team not found",404);
	
	$events = $this->team_manager->getEventsByCompanyIdAndTeamId($args['company_id'], $args['team_id'], $this->db);
	
    return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/json')
                        ->write(json_encode($events));

});

$app->get('/company/{company_id}/event/{event_id}/athletes', function (Request $request, Response $response, $args) {

	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$company_exists = $this->company_manager->checkCompanyById($args['company_id'],$this->db);
	if (!$company_exists) throw new Exception("company not found",404);
	
	$isAdminOfCompany = $this->user_manager->isUserAdminOfCompanyByIds($logged_user->getId(), $args['company_id'],$this->db);
	if (!$isAdminOfCompany) throw new Exception ("not an admin of company ".$args['company_id'], 403);
	
	$event_exists = $this->team_manager->checkEventById($args['event_id'], $this->db);
	if (!$event_exists) throw new Exception("event not found",404);
	
	$events = $this->team_manager->getAthletesByCompanyIdAndEventId($args['company_id'], $args['event_id'], $this->db);
	
    return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/json')
                        ->write(json_encode($events));

});

// ----------------------------COACH ENDPOINTS

// add a new event for a team in a company
$app->post('/team/{team_id}/event', function (Request $request, Response $response, $args) {
	
	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$data = $request->getParsedBody();
	
	$team_exists = $this->team_manager->checkTeamByTeamId($args['team_id'],$this->db);
	if (!$team_exists) throw new Exception("team not found",404);
	
	$isCoachOfTeam = $this->user_manager->isUserCoachOfTeamByIds($logged_user->getId(), $args['team_id'],$this->db);
	if (!$isCoachOfTeam) throw new Exception ("not a coach of team ".$args['company_id'], 403);
	
	$event_id = $this->team_manager->createEventInTeam($data['name'], $data['event_date'], $args['team_id'],  $data['event_type_id'], $this->db);
	
	return $response->getBody()->write("Event ".$event_id." added in team ".$args['team_id']);

});

// add a new athlete in an event of a team
$app->post('/team/{team_id}/event/{event_id}/athlete/{athlete_id}', function (Request $request, Response $response, $args) {
	
	$logged_user = $this->user_manager->getBasicHttpAuthenticatication($request,$response,$this->db);
	
	$team_exists = $this->team_manager->checkTeamByTeamId($args['team_id'],$this->db);
	if (!$team_exists) throw new Exception("team not found",404);
	
	$athlete_exists = $this->user_manager->checkAthleteByAthleteId($args['athlete_id'],$this->db);
	if (!$athlete_exists) throw new Exception("athlete not found",404);
	
	$event_exists = $this->team_manager->checkEventById($args['event_id'],$this->db);
	if (!$event_exists) throw new Exception("event not found",404);
	
	$isCoachOfTeam = $this->user_manager->isUserCoachOfTeamByIds($logged_user->getId(), $args['team_id'],$this->db);
	if (!$isCoachOfTeam) throw new Exception ("not a coach of team ".$args['company_id'], 403);
		
	$this->team_manager->createAthleteInEvent($args['event_id'], $args['athlete_id'],$this->db);
	
	return $response->getBody()->write("Athlete ".$args['athlete_id']." added in event ".$args['event_id']);

});


$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});
$app->run();
