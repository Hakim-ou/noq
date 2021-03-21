<?php
/** abdelmoujib and hakim
 *  -------
 *  @file
 *  @copyright Copyright (c) 2errors::noError1errors::unknown abdelmoujib and hakim,*** license, See the LICENSE file for copying permissions.
 *  @brief Various functions, not specific and widely used.
 */




/**
 * @breif verifies the qr code existence and returns the matching event id
 * @param string $code
 * @return int $error eroor code:
 *@return bool $exists 1 if exists 0 else
 *@return int id: event_id (only if the event exists else returns errors::noError)
 */
function verify_event_qr_code($code){
    $response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
		"exists" => 0,
		"event_id" => "no result",
	);
	try{
		global $bdd;
		$rqt = $bdd->prepare('SELECT event_id FROM qr_code WHERE qr_code=?');
		$rqt->execute(array($code));
		$event_id = $rqt->fetch();
		if ($event_id){
			$event_id = $event_id["event_id"];
			$response["exists"] = 1;
			$response["event_id"] = $event_id;
		}else
			$response["error"] = errors::noSuchEvent;
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);
}


//TODO add parser for schedule and description
/**
 * @brief create event
 *
 * @param int $owner_id
 * @param sting $title		 		 			 the service's title
 * @param sting $description		 			 the service's description
 * @param sting $schedule			 			 the service's schedule
 * @param string $owner_code
 * @param string $servent_code
 * @return int $error							 the error code
 * @return int $event_id						 the new event's id
 */
function create_event($owner_id, $title, $description, $schedule, $owner_code, $servent_code){ //TODO MAKE OWNER CODE AND SERVENT CODE RANDOMLY GENERATED AND NOT a parameter of the function
	$response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
		"event_id" => "no result",
	);
	try{
		global $bdd;
		// check if owner exists
		$rqt = $bdd->prepare('SELECT * FROM owner WHERE id=?');
		$rqt->execute(array($owner_id));
		if (!$rqt->fetch()){
			$response["error"] = errors::noSuchOwner;
			return json_encode($response);
		}
		// create event
		$rqt = $bdd->prepare('INSERT INTO event(title, description, schedule, owner_code, servent_code)
								VALUES(?, ?, ?, ?, ?)');
		$rqt->execute(array($title, $description, $schedule, $owner_code, $servent_code));
		$event_id = $bdd->lastInsertId();
		// create owning
		$rqt = $bdd->prepare('INSERT INTO owning(event, owner) VALUES (?, ?)');
		$rqt->execute(array($event_id, $owner_id));
		$response["event_id"] = $event_id;
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);


}


/**
 * @brief configure event. No parameter could be null: when calling, current informations
 *		  about the event are alredy loaded, so if we desire to keep them we shoul give them
 *		  back as parameters.
 *
 * @param int $event_id						 the event's id
 * @param sting $title		 		 			 the service's title
 * @param sting $description		 			 the service's description
 * @param sting $schedule			 			 the service's schedule
 * @param string $owner_code
 * @param string $servent_code
 * @return int $error							 the error code
 */
function configure_event($event_id, $title, $description, $schedule){
	$response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
	);
	try{
		global $bdd;
		// check if event exists
		$rqt = $bdd->prepare('SELECT * FROM event WHERE id=?');
		$rqt->execute(array($event_id));
		if (!$rqt->fetch()){
			$response["error"] = errors::noSuchEvent;
			return json_encode($response);
		}
		// configure it
		$rqt = $bdd->prepare('UPDATE event SET title=?, description=?, schedule=? WHERE id=?');
		$rqt->execute(array($title, $description, $schedule, $event_id));
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);
}


/**
 * @brief delete event, servents, turns, services, owning, 
 * 
 * @param int $event_id
 * @return int $error
 */
function destroy_event($event_id){
	$response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
	);
	try{
		global $bdd;
		// delete from qr_code
		$rqt = $bdd->prepare('DELETE FROM qr_code WHERE event_id=?');
		$rqt->execute(array($event_id));

		// delete from turn
		$rqt = $bdd->prepare('DELETE FROM turn WHERE event_id=?');
		$rqt->execute(array($event_id));

		// delete from owning
		$rqt = $bdd->prepare('DELETE FROM owning WHERE event_id=?');
		$rqt->execute(array($event_id));

		// delete from servent
		$rqt = $bdd->prepare('DELETE FROM servent WHERE event_id=?');
		$rqt->execute(array($event_id));

		// delete from service
		$rqt = $bdd->prepare('DELETE FROM service WHERE event_id=?');
		$rqt->execute(array($event_id));

		// delete from event
		$rqt = $bdd->prepare('DELETE FROM event WHERE id=?');
		$rqt->execute(array($event_id));
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);
}


/**
 * @brief get list of event services
 *
 * @param int $event_id			 the event's id
 * @return int[] $services	 services list of services's ids
 */
function get_services($event_id){
	$response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
		"services" => array(),
	);
	try{
		global $bdd;
		$rqt = $bdd->prepare('SELECT id FROM service WHERE event_id=?');
		$rqt->execute(array($event_id));
		$service = $rqt->fetch();
		if (!$service)
			$response["error"] = errors::noSuchEvent;
		else{
			while ($service){
				array_push($response["services"], $service);
				$service = $rqt->fetch()["id"];
			}
		}
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);
}


/**
 * @brief add an owning to the owning table
 *
 * @param int $event_id  the event's id
 * @param int $owner_id  the owner's id
 * @return int $error
 */
function add_owner($event_id, $owner_id){
	$response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
	);
	try{
		global $bdd;
		// check if event exists
		$rqt = $bdd->prepare('SELECT * FROM event WHERE id=?');
		$rqt->execute(array($event_id));
		if (!$rqt->fetch()){
			$response["error"] = errors::noSuchEvent;
			return json_encode($response);
		}
		// check if owner exists
		$rqt = $bdd->prepare('SELECT * FROM owner WHERE id=?');
		$rqt->execute(array($owner_id));
		if (!$rqt->fetch()){
			$response["error"] = errors::noSuchOwner;
			return json_encode($response);
		}
		// insert owning
		$rqt = $bdd->prepare('INSERT INTO owning(event_id, owner_id) VALUES (?, ?)');
		$rqt->execute(array($event_id, $owner_id));
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);

}


/**
 * @brief add a servent to the servent table
 *
 * @param int $event_id		the event's id
 * @param int $servent_id   the servent's id
 * @param strin $name		the servent's pseudo
 * @return int $error
 */
function add_servent($event_id, $servent_id, $name){
	$response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
	);
	try{
		global $bdd;
		// check if event exists
		$rqt = $bdd->prepare('SELECT * FROM event WHERE id=?');
		$rqt->execute(array($event_id));
		if (!$rqt->fetch()){
			$response["error"] = errors::noSuchEvent;
			return json_encode($response);
		}
		// adding servent
		$rqt = $bdd->prepare('INSERT INTO servent(event_id, servent_id, name) VALUES (?, ?, ?)');
		$rqt->execute(array($event_id, $servent_id, $name));
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);

}


/**
 * @brief delete serving from the servent table
 *
 * @param int $event_id     the event's id
 * @param int $servent_id  the owner's id
 * @return int $error
 */
function delete_servent($event_id, $servent_id){
	$response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
	);
	try{
		global $bdd;
		$rqt = $bdd->prepare('DELETE FROM servent WHERE event_id=? AND servent_id=?');
		$rqt->execute(array($event_id, $servent_id));
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);

}


function event_exists($event_id){
    global $bdd;
    $rqt = $bdd->prepare('SELECT * FROM event WHERE id = ?');
    $rqt->execute(array($event_id));
    $event_exists= $rqt->fetch();
    if(!$event_exists){
        return False;
    }
    return True;
}

/**
 * @breif this function is used to verify that the owner qr code is correct and change owner QR code in data base to a new random one
 * @breif this function is ment to be called before add owner
 * @param $event_id
 * @param $code
 */
function  verify_owner_code($event_id, $code){ //TODO
    return False;
}


/**
 * @param int $event_id
 * @return json error 0, 1 noSuch, 4 | error_msg | info:
 */
function get_event_info($event_id){
    $response= array("error"=>errors::noError,
        "error_msg"=>"no error",
        "info"=> 0
        );
    try {
        global $bdd;
        $rqt = $bdd->prepare('SELECT id, title, description, schedule FROM event WHERE  id = ?');
        $rqt->execute(array($event_id));
        $event_exists = $rqt->fetch();
        if ($event_exists) {
            $response["info"] = $event_exists;
        } else {
            $response["error"] = errors::noSuchEvent;
        }
    }catch (Exception $e){
        $response["error"]=errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
    return json_encode($response);
}
