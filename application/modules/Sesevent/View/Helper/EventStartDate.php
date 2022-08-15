<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: eventStartDate.php 2020-07-14 00:00:00 SocialEngineSolutions $
 * @author     Jurgen Tonneyck
 */
class Sesevent_View_Helper_EventStartDate extends Sesevent_View_Helper_EventStartEndDates {

	public function eventStartDate($sesevent){
		if($sesevent == null) return;
		$dt = new DateTime($sesevent->starttime);
		$dt->setTimeZone(new DateTimeZone($sesevent->timezone));

		$day = $dt->format("D");
		$date = $dt->format("d");
		$month = $dt->format("M");
		$time = $dt->format("H:i");

		return array(
			"day"=> $day, 
			"date"=> $date, 
			"month"=> $month,
			"time"=> $time
		);
	}
}