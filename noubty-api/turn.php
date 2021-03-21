<?php

/** abdelmoujib and hakim
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 abdelmoujib and hakim,*** license, See the LICENSE file for copying permissions.
 *  @brief Various functions, not specific and widely used.
 */


include "qr_code.php";
include "estimation.php";


/**
 * @brief Get last turn
 *
 * @param int $service_id
 * @return int $error				error code  0: succesfull |
 *											    4: uknown error
 * @return int $last				the last turn
 */
function last_turn($service_id){
	$response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
		"last_turn" => 0,
	);
	try{
		global $bdd;
		$rqt = $bdd->prepare('SELECT MAX(turn) AS last FROM Turn WHERE service_id=?');
		$rqt->execute(array($service_id));
		$last = $rqt->fetch();
		if ($last && $last["last"])
			$response["last_turn"] = $last["last"];
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);
}

/*
/**
 * @breif Add new turn to the service. Verify first that the service is open, if the service is closed its an error because we
 *		  are already trying to take a turn.
 *
 * @param int $service_id			the	service in which the turn is taken
 * @param int $participant_id		participant who is taking turn
 * @param $additional_information
 * @return int $error				error code  0: succesfull | 1: unexsisting service |
 *											    2: closed service | 4: uknown error
 */
/*function take_turn($service_id, $participant_id, $additional_information ){// TODO treat case already token
	$response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
	);
	try{
		global $bdd;
		$rqt = $bdd->prepare('SELECT state FROM service WHERE id=?');
		$rqt->execute(array($service_id));
		$info = $rqt->fetch();
		if (!$info)
			$response["error"] = errors::noSuchService;
		else if (!$info["state"])
			$response["error"] = errors::closedService;
		else{
			$turn_number = json_decode(last_turn($service_id));
            if ($turn_number->error == errors::unknown){
				$response["error"] = $turn_number->error;
				$response["error_msg"] = $turn_number->error_msg;
			}else{
                if ($turn_number->last_turn == null){
                    $turn_number=1;
                }else {
                    $turn_number = $turn_number->last_turn + 1;
                }
                $in_place = false;
                $turn_qr_code = generate_qr_code();
                $message = "not your turn yet";
                $estimation = estimate_for_next($service_id);
                $estimation_time = date("Y-m-d h:i:sa");
                $validation = false;
                // insert in turn
                $rqt = $bdd->prepare('INSERT INTO turn(service_id, participant_id, turn_number, additional_information,
									turn_qr_code, message, time)
								 	VALUES(?, ?, ?, ?, ?, ?, CURRENT_TIME)');
                $rqt->execute(array( $service_id, $participant_id, $turn_number, $additional_information,
                    $turn_qr_code, $message));
            }
		}
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);
}
*/

/**
 * @param int $turn_id the turn to save as in_place
 * @param bool $in_place true to change to in place  false to change to not in place
 * @return int error
 */
function in_place($turn_id, $in_place){
	$response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
	);
	try{
		global $bdd;
		$rqt = $bdd->prepare('UPDATE turn SET in_place=? WHERE id=?');
		$rqt->execute(array($in_place, $turn_id));
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);
}


/**
 * @brief cancel the turn and aplly the specified algorithm in the activity diagrame
 * @param int $turn_id the turn to cancel
 * @return int error
 */
function cancel_turn($turn_id){
	$response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
	);
	try{
		global $bdd;
		$rqt = $bdd->prepare('SELECT * FROM turn where id=?');
		$rqt->execute(array($turn_id));
		$turn_info = $rqt->fetch();
		// get service limit stability
		$rqt = $bdd->prepare('SELECT limit_stability FROM turn where id=?');
		$rqt->execute(array($turn_info["service_id"]));
		$limit_stability = $rqt->fetch()["limit_stability"];
		// apply the canceling algorithm
		$current_turn = get_current_turn($turn_info["servent_id"]);
		$canceled_turn = $turn_info["turn_number"];
		while ($canceled_turn - $current_turn < $limit_stability){
			$next = next_in_place($canceled_turn);
			$rqt = $bdd->prepare('SELECT turn_number FROM turn WHERE id=?');
			$rqt->execute(array($next));
			$turn = $rqt->fetch()["turn_number"];
			$rqt = $bdd->prepare('UPDATE turn SET turn_number=? WHERE id=?');
			$rqt->execute(array($canceled_turn, $next));
			$canceled_turn = $turn;
		}
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);
}


/**
 * @brief delete the turn from database
 * @param int $turn_id		 the turn to remove frome databsae
 * @return int $error
 */
function delete_turn($turn_id){
	$response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
	);
	try{
		global $bdd;
		$rqt = $bdd->prepare('DELETE FROM Turn WHERE id=?');
		$rqt->execute(array($turn_id));
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);
}


/**
 * @brief
 * @param int $turn_id		 the turn which informations should be get
 *PO/
function get_turn_additional_information($turn_id){

}


/**
 * @param int $turn_id		 turn to validate
 */
function validate_turn($turn_id){
	$response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
	);
	try{
		global $bdd;
		$rqt = $bdd->prepare('UPDATE Turn SET validation=? WHERE id=?');
		$rqt->execute(array(true, $turn_id));
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);
}


/**
 * @param int $turn_id
 * @return time $estimation				 the estimated time for the turn
 * @return time $estimation_time		 the time the estimation has been executed
 */
function get_estimation($turn_id){
	$response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
		"estimation" => 0,
		"estimation_time" => 0,
	);
	try{
		global $bdd;
		$rqt = $bdd->prapare('SELECT estimation, estimation_time FROM turn WHERE id=?');
		$rqt->execute(array($turn_id));
		$turn_info = $rqt->fetch();
		if ($turn_info == false)
			$response["error"] = errors::noSuchTurn;
		else{
			$response["estimation"] = $turn_info["estimation"];
			$response["estimation_time"] = $turn_info["estimation_time"];
		}
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);
}

/******************************************************** new app * **********************************************/

/**
 * @breif Add new turn to the service. Verify first that the service is open, if the service is closed its an error because we
 *		  are already trying to take a turn.
 *
 * @param int $service_id			the	service in which the turn is taken
 * @param int $participant_id		participant who is taking turn
 * @param $additional_information
 * @return int $error				error code  0: succesfull | 1: unexsisting service |
 *											    2: closed service | 4: uknown error
 */
function take_turn($id,$service_id ){// TODO treat case already token //add id to param
    $response = array(
        "error" => errors::noError,
        "error_msg" => "no system error",
    );
    try{
        global $bdd;
        $rqt = $bdd->prepare('SELECT * FROM Service WHERE id=?');
        $rqt->execute(array($service_id));
        $info = $rqt->fetch();
        if (!$info)
            $response["error"] = errors::noSuchService;
//        else if (!$info["state"])
//            $response["error"] = errors::closedService;
        else{
            $turn_number = json_decode(last_turn($service_id));
            if ($turn_number->error == errors::unknown){
                $response["error"] = $turn_number->error;
                $response["error_msg"] = $turn_number->error_msg;
            }else{
                if ($turn_number->last_turn == null){
                    $turn_number=1;
                }else {
                    $turn_number = $turn_number->last_turn + 1;
                }
                $rqt = $bdd->prepare('INSERT INTO Turn(id, service_id, turn)
								 	VALUES(?, ?, ?)');
                $rqt->execute(array( $id,$service_id, $turn_number));
            }
        }
    }catch(Exception $e){
        $response["error"] = errors::unknown;
        $response["error_msg"] = $e->getMessage();
    }
    return json_encode($response);
}

function turn_exists($id){
    global $bdd;
    $existsrqt = $bdd->prepare('SELECT * FROM Turn WHERE id=?');
    $existsrqt->execute(array($id));
    $exists = $existsrqt->fetch();
    if (!$exists)
        return false;
    return true;

}