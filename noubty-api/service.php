<?php
/** abdelmoujib and hakim
 *  -------
 *  @file
 *  @copyright Copyright (c) 2errors::noError1errors::unknown abdelmoujib and hakim,*** license, See the LICENSE file for copying permissions.
 *  @brief Various functions, not specific and widely used.
 */


/*
 * @brief add service
 *
 * @param int $event_id							 the corresponding event's id
 * @param string $title		 		 			 the service's title
 * @param string $description		 			 the service's description
 * @param string $schedule			 			 the service's schedule
 * @param string $additional_info				 the service's additional information
 * @return int $error							 the error code
 * @return int $service_id						 the created service's id         I don't know if this is essential for service so I didn't emplement it because we can get the services related to an event elsewher
 */
/*function add_service($event_id, $title, $description, $schedule, $additional_info){
    $response = array(
        "error" => errors::noError,
        "error_msg" => "no error"
    );
    try{
        global $bdd;
        // create service
        if(!event_exists($event_id)) {
            $response["error"] = errors::noSuchEvent;
        }elseif (!check_title($title)){
            $response["error"]= errors::titleNotPermited;
        }elseif (!check_scheduel($schedule)){
            $response["error"]= errors::wrongScheduelSyntax;
        }elseif(!check_additional_info($additional_info)){
            $response["error"]= errors::wrongAdditionalInfoSyntax;
        }
        else{
            $rqt = $bdd->prepare('INSERT INTO service(event_id, title, description, schedule, additional_information)
								VALUES(?, ?, ?, ?, ?)');
            $rqt->execute(array($event_id, $title, $description, $schedule, $additional_info));
        }
    }catch(Exception $e){
        $response["error"] = errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
    return json_encode($response);
}
*/
function check_title($title){// TODO ome titles must be forbiden (racist...) for now the function will always return true if $title is not none
    // must be called in graphical interface and before creating or modifying service/ event
    // i guess this is not the suitibale placement for this function
    return !is_nan($title);

}


function check_additional_info($additional_info){// TODO check that the form of additional info is correct
    return True;
}


function check_scheduel($scheduel){ // TODO checks that the schedual syntax is correct
    return True;

}



/**
 * @brief  delete the given service
 *
 * @param int $service_id			the servise's id
 * @return int $error
 */
function delete_service($service_id){

    $response = array(
        "error" => errors::noError,
        "error_msg" => "no error"
    );
    try {
        global $bdd;
        if(!service_exists($service_id)) {
            $response["error"] = errors::noSuchService;
        }else {
            $rqt = $bdd->prepare('DELETE FROM service WHERE id = ?');
            $rqt->execute(array($service_id));
        }
    }catch(Exception $e){
        $response["error"] = errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
    return json_encode($response);
}


/**
 * @brief configurate the given service
 *
 * @param int $service_id						 the servise's id
 * @param string $title		 		 			 the service's title
 * @param string $description		 			 the service's description
 * @param string $schedule			 			 the service's schedule
 * @param string $additional_info				 the service's additional information
 * @return int $error
 */
function configurate_service($service_id, $title, $description, $schedule, $additional_info){
    $response = array(
        "error" => errors::noError,
        "error_msg" => "no error"
    );
    try {
        global $bdd;
        if(!service_exists($service_id)) {
            $response["error"] = errors::noSuchService;
        }elseif (!check_title($title)){
            $response["error"]= errors::titleNotPermited;
        }elseif (!check_scheduel($schedule)){
            $response["error"]= errors::wrongScheduelSyntax;
        }elseif(!check_additional_info($additional_info)){
            $response["error"]= errors::wrongAdditionalInfoSyntax;
        }else {
            $rqt = $bdd->prepare('UPDATE service set title=?, description=?, scheduele=?, additional_information=? WHERE id = ?');
            $rqt->execute(array($title, $description, $schedule, $additional_info, $service_id));
        }
    }catch(Exception $e){
        $response["error"] = errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
    return json_encode($response);

}


/**
 * @brief  tels if the service (turn taking) is actually actif
 *
 * @param int $service_id						 the servise's id
 * @return int $error
 * @return bool $actif							 true if the service is actif
 */
function is_actif($service_id){

    $response = array(
        "error" => errors::noError,
        "error_msg" => "no error",
        "state"=> False
    );
    try {
        global $bdd;
        if(!service_exists($service_id)) {
            $response["error"] = errors::noSuchService;
        }else {
            $rqt = $bdd->prepare('SELECT state FROM service WHERE id = ?');
            $rqt->execute(array($service_id));
            $response["state"] = $rqt->fetch()["state"];
        }
    }catch(Exception $e){
        $response["error"] = errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
    return json_encode($response);

}


/**
 * @brief activate / disactivate turn taking for the given service
 *
 * @param int $service_id						 the servise's id
 * @param bool $actif							 true if the service is actif
 * @return int $error
 */
function set_state($service_id, $actif){
    $response = array(
        "error" => errors::noError,
        "error_msg" => "no error"
    );
    try {
        global $bdd;
        if(!service_exists($service_id)) {
            $response["error"] = errors::noSuchService;
        }else {
            $rqt = $bdd->prepare('UPDATE service SET state = ? WHERE id = ?');
            $rqt->execute(array($service_id, $actif));
        }
    }catch(Exception $e){
        $response["error"] = errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
    return json_encode($response);
}


//TODO add parser for schedule and description

/**
 * @brief find the list of participants that are waiting for their turn
 *
 * @param int $service_id					the service to get participant list for
 * @return int[] $participants				the lists of participants'ids
 */
function get_participant_list($service_id){
    $response = array(
        "error" => errors::noError,
        "error_msg" => "no error",
        "participants" => array()
    );
    try {
        global $bdd;
        if(!service_exists($service_id)) {
            $response["error"] = errors::noSuchService;
        }else {
            $rqt = $bdd->prepare('SELECT id FROM turn WHERE service_id=?');
            $rqt->execute(array($service_id));
            $service = $rqt->fetch()["id"];
            while ($service) {
                array_push($response["participants"], $service);
                $service = $rqt->fetch()["id"];
            }
        }
    }catch(Exception $e){
        $response["error"] = errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
    return json_encode($response);
}

/*
/**
 * @param int $service_id
 * @return int $turn the			current turn for this service
 */
/*
function get_current_turn($service_id){

    $response = array(
        "error" => errors::noError,
        "error_msg" => "no error",
        "turn"=> 0
    );
    try {
        global $bdd;
        if(!service_exists($service_id)) {
            $response["error"] = errors::noSuchService;
        }else {
            $rqt = $bdd->prepare('SELECT turn FROM service WHERE id = ?');
            $rqt->execute(array($service_id));
            $response["turn"] = $rqt->fetch()["turn"];
        }
    }catch(Exception $e){
        $response["error"] = errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
    return json_encode($response);
}
*/

/*
 * @brief increment the current turn and turn_time and store message in database
 * @param int $service_id the service to pass turn for
 * @param $message
 * @return void error the error code
 */
/*function next_turn($service_id, $message){

    $response = array(
        "error" => errors::noError,
        "error_msg" => "no error",
    );
    try {
        if(!service_exists($service_id)) {
            $response["error"] = errors::noSuchService;
        }else {
            $turn = json_decode(get_current_turn($service_id))["turn"];
            $turn += 1;
            global $bdd;
            $rqt = $bdd->prepare('UPDATE service SET turn=?, message=?, turn_time = now() WHERE id = ?');
            $rqt->execute(array($turn, $message, $service_id));
        }
    }catch(Exception $e){
        $response["error"] = errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
    return json_encode($response);
}
*/
/**
 * @param $service_id
 * @param $qr_code
 * @return int  error
 * @return bool right true if the qr code correspond to the current  turn and update state in database and update time turn
 */
function verify_participant_qr_code($service_id, $qr_code){

    $response = array(
        "error" => errors::noError,
        "error_msg" => "no error",
        "correct"=>False,
        "message"=> False
    );
    try {
        $turn = json_decode(get_current_turn($service_id))["turn"];
        global $bdd;
        $rqt = $bdd->prepare('SELECT  id, message FROM turn WHERE turn_number = ? AND service_id=? AND turn_qr_code=?  ');
        $rqt->execute(array($turn,  $service_id, $qr_code));
        $correct = $rqt->fetch();
        if($correct){
            $response["correct"]=True;
            $response["message"]= $correct["message"];
            $rqt = $bdd->prepare('UPDATE turn SET validation=? WHERE id =?  ');
            $rqt->execute(array(True, $correct["id"]));
            $rqt = $bdd->prepare('UPDATE service SET turn_time = now() WHERE id =?  ');
            $rqt->execute(array($service_id));
        }
    }catch(Exception $e){
        $response["error"] = errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
    return json_encode($response);
}




/**
 * @brief  returns the turn_id corresponding to the first parrticipant in place that comes after the canceled turn,
 *		   returns the next turn's id if no participant is in place
 *
 * @param int $canceled_turn		the canceled turn's turn number
 * @return void $error				the error code
 * @return int $next				the next_in_place's turn_id
 */
function next_in_place($canceled_turn){

    $response = array(
        "error" => errors::noError,
        "error_msg" => "no error",
        "next" => 0
    );
    try {
        global $bdd;
        $rqt = $bdd->prepare('SELECT service_id,turn FROM turn WHERE id=?');
        $rqt->execute(array($canceled_turn));
        $answer = $rqt->fetch();
        if($answer) {
            $service_id = $answer["service_id"];
            $turn = $answer["turn"];
            if (!service_exists($service_id)) {
                $response["error"] = errors::noSuchService;
            } else {
                $rqt = $bdd->prepare('SELECT id FROM TURN WHERE service_id=? AND in_place=true AND turn > ? ORDER BY turn ASC ');
                $rqt->execute(array( $service_id, $turn));
                $answer =$rqt->fetch();
                if($answer){
                    $response["next"]=$answer["id"];
                }else{
                    $rqt = $bdd->prepare('SELECT id FROM TURN WHERE service_id=? AND turn = ? ');
                    $rqt->execute(array( $service_id, $turn+1));
                    $answer =$rqt->fetch();
                    if(!$answer){
                        errors::noMoreTurns;
                    }else{
                        $response["next"] = $answer["id"];
                    }
                }
            }
        }else{
            $response["error"] = errors::noSuchTurn;
        }
    }catch(Exception $e){
        $response["error"] = errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
}

/***************************************************new version ****************************************/

/**
 * @brief increment the current turn and turn_time and store message in database
 * @param int $service_id the service to pass turn for
 * @param $message
 * @return string error the error code
 */
function next_turn($service_id)
{
    $response = array(
        "error" => errors::noError,
        "error_msg" => "no error"
    );
    try {
        if (!service_exists($service_id)) {
            $response["error"] = errors::noSuchService;
        } else {
            $turn = json_decode(get_current_turn($service_id))["current_turn"];
            $turn += 1;
            global $bdd;
            $rqt = $bdd->prepare('UPDATE Service SET current_turn=? WHERE id = ?');
            $rqt->execute(array($turn, $service_id));
        }
    } catch (Exception $e) {
        $response["error"] = errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
    return json_encode($response);
}

/**
 * @brief add service
 *
 * @param int $service_id						 the service id randomly generated before asking
 * @param string $title		 		 			 the service's title
 * @return int $error							 the error code
 * @return int $service_id						 the created service's id         I don't know if this is essential for service so I didn't emplement it because we can get the services related to an event elsewher
 */
function add_service($service_id, $title){
    $response = array(
        "error" => errors::noError,
        "error_msg" => "no error"
    );
    try{
        global $bdd;
        // create service
        if (!check_title($title)){
            $response["error"]= errors::titleNotPermited;
        }elseif (service_exists($service_id)){
            $response["error"]= errors::serviceAlreadyExists;
        }
        else{
            $rqt = $bdd->prepare('INSERT INTO Service(id, title,current_turn)
								VALUES(?, ?, 0)');
            $rqt->execute(array($service_id, $title));
        }
    }catch(Exception $e){
        $response["error"] = errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
    return json_encode($response);
}

function service_exists($service_id){
    global $bdd;
    $rqt = $bdd->prepare('SELECT * FROM Service WHERE id = ?');
    $rqt->execute(array($service_id));
    $service_exists = $rqt->fetch();
    if ($service_exists) {
        return true;
    }
    return false;
}


/**
 * @param int $service_id
 * @return int $turn the			current turn for this service
 */

function get_current_turn($service_id){

    $response = array(
        "error" => errors::noError,
        "error_msg" => "no error",
        "current_turn"=> 0
    );
    try {
        global $bdd;
        if(!service_exists($service_id)) {
            $response["error"] = errors::noSuchService;
        }else {
            $rqt = $bdd->prepare('SELECT current_turn FROM Service WHERE id = ?');
            $rqt->execute(array($service_id));
            $response["current_turn"] = $rqt->fetch()["current_turn"];
        }
    }catch(Exception $e){
        $response["error"] = errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
    return json_encode($response);
}

function get_title($service_id){

    $response = array(
        "error" => errors::noError,
        "error_msg" => "no error",
        "title"=> "titre"
    );
    try {
        global $bdd;
        if(!service_exists($service_id)) {
            $response["error"] = errors::noSuchService;
        }else {
            $rqt = $bdd->prepare('SELECT title FROM Service WHERE id = ?');
            $rqt->execute(array($service_id));
            $response["title"] = $rqt->fetch()["title"];
        }
    }catch(Exception $e){
        $response["error"] = errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
    return json_encode($response);
}

