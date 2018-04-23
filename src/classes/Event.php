<?php
class Event {
	private $id;
	private $name;
	private $event_date;
	private $team_id; 
	private $event_type_id;
	private $location;
	
	public function getId(){
		return $this->id;
	}

}
