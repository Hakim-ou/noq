<?php

//echo "Welcome to noQ.com";

/** abdelmoujib and hakim
 *  -------
 *  @file
 *  @copyright Copyright (c) abdelmoujib and hakim,*** license, See the LICENSE file for copying permissions.
 *  @brief Various functions, not specific and widely used.
 *		   coding methodologie:
 *				0-100 no privileges
 *		   		100-200 server privileges
 *		   		>= 200 owner privileges
 *		   		0-20 event.php
 *		   		20-30 turn.php
 *		   		30-50 service.php
*/


session_start();
$_SESSION["id"]=1;//TODO DELETE NEXT THIS LINE  FOR TEST


include "connectdb.php";
include "errors.php";
include "event.php";
include "service.php";
include "turn.php";
include "client.php";


/**
 * 3 session variables exists
 * connected the session state
 * id the identified person id
 */
// TODO verify again pprevileges
/*$preveleges=False;
if(isset($_SESSION["connected"]) and $_SESSION["connected"] == True  ){
    $preveleges=True;
}
$json = file_get_contents('php://input');
$post = json_decode($json);
if($post["function>=100){ //server privileges
    if(isset($post["turn_id)) {
        $rqt = $bdd->prepare("SELECT service_id FROM turn WHERE id=?");
        $rqt->execute(array($post["turn_id));
        $event = $rqt->fetch();
        $sservice_id= event["service_id"];
    }
    if(isset($post["service_id) ) {
        $sservice_id =$post["service_id;
    }
    if(isset($sservice_id)) {
        $rqt = $bdd->prepare("SELECT event_id FROM service WHERE id=?");
        $rqt->execute(array($post["service_id));
        $event = $rqt->fetch();
        if ($_SESSION["type"] > 1) {
            $rqt = $bdd->prepare("SELECT * FROM owning WHERE event_id=? AND owner_id=?");
            $rqt->execute(array($event["event_id"], $_SESSION["id"]));
            $answer = $rqt->fetch();
        } else {
            $rqt = $bdd->prepare("SELECT * FROM servent WHERE event_id=? AND servent_id=?");
            $rqt->execute(array($event["event_id"], $_SESSION["id"]));
            $answer = $rqt->fetch();
        }
        if (!$answer) {

            $preveleges = False;
        }
    }
}elseif($post["function>=200){//owner privileges
    if(isset($post["event_id)){
        $rqt = $bdd->prepare("SELECT * FROM owning WHERE event_id=? AND owner_id=?" );
        $rqt->execute(array($post["event_id,$_SESSION["id"]));
        $answer = $rqt->fetch();
        if(!$answer){
            $preveleges = False;
        }
    }
    if(isset($post["service_id)) {
        $rqt = $bdd->prepare("SELECT event_id FROM service WHERE id=?");
        $rqt->execute(array($post["service_id));
        $answer = $rqt->fetch();
        $rqt = $bdd->prepare("SELECT * FROM owning WHERE event_id=? AND owner_id=?");
        $rqt->execute(array($answer["event_id"], $_SESSION["id"]));
        $answer = $rqt->fetch();
        if (!$answer) {
            $preveleges = False;
        }
    }
    //TODO also manage turn_id
}else{
    if(isset($post["turn_id)){
        $rqt = $bdd->prepare("SELECT * FROM event WHERE id=? AND participant_id=?" );
        $rqt->execute(array($post["turn_id,$_SESSION["id"]));
        $answer = $rqt->fetch();
        if(!$answer){
            $preveleges = False;
        }
    }
}
*/


$preveleges = True; //TODO get rid of this line
if($preveleges) {
    $json = file_get_contents('php://input');
    $post= json_decode($json,true);
    if (isset($post["function"])) {
        switch ($post["function"]) {
            case 0:
                if (!empty($post["code"])) {
                    echo verify_event_qr_code($post["code"]);
                }
                break;
            case 201:
                echo create_event($_SESSION["id"], $post["title"], $post["description"],$post["schedule"],NAN, NAN );
                break;
            case 202:
                echo configure_event($post["event_id,"],$post["title"], $post["description"], $post["schedule"]);
                break;
            case 203:
                echo destroy_event($post["event_id"]);
                break;
            case 4:
                echo get_services($post["event_id"]);
                break;
            case 205:
                if(verify_owner_code($post["event"], $post["code"])) {
                    echo add_owner($post["id"], $post["event"]);
                }
                break;
            case 106:
                if(verify_servent_code($post["event"], $post["code"])) {
                    echo add_servent($post["id"], $post["event"], $post["name"]);
                }
                break;
            case 207:
                echo delete_servent($post["event_id"], $post["servent_id"]);
                break;
            case 8:
                echo get_event_info($post["event_id"]);
                break;
            case 9:
                echo get_turn_info($post["code"]);
                break;
            case 20:
                echo last_turn($post["service_id"]);
                break;
            case 21://TODO maybe add qr code as a parameter to verify before taking turn so to make sure that when shared a person can't take turn
                echo take_turn($post["turn_id"], $post["service_id"]);
                break;
            case 22:
                echo in_place($post["turn_id"],$post["in_place"]);
                break;
            case 23:
                echo cancel_turn($post["turn_id"]);
                break;
            case 24:
                echo delete_turn($post["turn_id"]);
                break;
          //  case 25
                //     echo get_turn_additional_information($post["turn_id"]);
                //break;
            case 126:
                echo validate_turn($post["turn_id"]);
                break;
            case 27:
                echo get_estimation($post["turn_id"]);
                break;
            case 230:
                echo add_service($post["service_id"], $post["title"]);
                break;
            case 231:
                echo delete_service($post["service_id"]);
                break;
            case 232:
                echo configurate_service($post["service_id"], $post["title"], $post["description"], $post["schedule"], $post["additional_information"]);
                break;
            case 33:
                echo is_actif($post["service_id"]);
                break;
            case 234:
                echo set_state($post["service_id"], $post["actif"]);
                break;
            case 135:
                echo get_participant_list($post["service_id"]);
                break;
            case 136:
                echo get_current_turn($post["service_id"]);
                break;
            case 137:
                echo next_turn($post["service_id"]);
                break;
            case 138:
                echo verify_participant_qr_code($post["service_id"], $post["code"]);
                break;
            case 40:
                if(service_exists($post["service_id"])==true){
                    echo "{\"exists\" :1}";
                }else{
                    echo "{\"exists\" : 0}";
                }
                break;
            case 41:
                echo get_title($post["service_id"]);
                break;
            case 42:
                if(turn_exists($post["turn_id"])==true){
                    echo "{\"exists\" :1}";
                }else{
                    echo "{\"exists\" : 0}";
                }
                break;
            default:
                $response =array("error"=> errors::noSuchCommand);
                echo json_encode($response);


        }
    }
}

