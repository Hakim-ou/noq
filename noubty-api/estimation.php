<?php
/** abdelmoujib and hakim
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 abdelmoujib and hakim,*** license, See the LICENSE file for copying permissions.
 *  @brief Various functions, not specific and widely used.
 */


/**
 * @brief   estimate waiting time for a new participant (the one that will be added next)
 *
 * @param int $service_id			the concerned service's id
 * @return time $estimated_time		in mysql time format 
 */
function estimate_for_next($service_id){

    return time();
}


/**
 * @breif estimate time in database for all turns related to this service and modify estimation time
 *
 * @param $service_id
 * @return int $error
 */
function estimate($service_id){

}
