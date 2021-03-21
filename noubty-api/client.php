<?php
/** abdelmoujib and hakim
 *  -------
 *  @file
 *  @copyright Copyright (c) 2errors::noError1errors::unknown abdelmoujib and hakim,*** license, See the LICENSE file for copying permissions.
 *  @brief Various functions, not specific and widely used.
 */

include "connectbd.php";


/**
 * @breif						Returns information about the turn ided by @param code
 * @param string $code			QR code
 * @return int $error			Error code:
 * @return string $error_msg	Error message if the error is a system error
 * @return string $title		Event Title
 * @return int myTurn			The turn's number
 * @return int beforeMe			The number of turns before this turn
 */
function get_turn_info($code){
    $response = array(
		"error" => errors::noError,
		"error_msg" => "no system error",
		"title" => "no result",
		"myTurn" => "no result",
		"beforeMe" => "no result",
	);
	try{
		global $bdd;
		$rqt = $bdd->prepare('SELECT * FROM Turn WHERE id = ?');
		$rqt->execute(array($code));
		$turn = $rqt->fetch();

		if ($turn) {
			$event_id = $turn["service_id"];
			$rqt = $bdd->prepare('SELECT * FROM Service WHERE id = ?');
			$rqt->execute(array($event_id));
			$event = $rqt->fetch();
			if ($event) {
				$response["title"] = $event["title"];
				$response["myTurn"] = $turn["turn"];
				$response["beforeMe"] = (int)$turn["turn"] - (int)$event["current_turn"];
			} else
				$response["error"] = errors::noSuchEvent;
		}else
			$response["error"] = errors::noSuchTurn;
	}catch(Exception $e){
		$response["error"] = errors::unknown;
		$response["error_msg"] = $e->getMessage();
	}
	return json_encode($response);
}
