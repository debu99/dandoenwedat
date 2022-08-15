<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: EventStartEndDates.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_View_Helper_EventStartEndDates extends Engine_View_Helper_Locale {
	public function eventStartEndDates($sesevent,$defaultParams = array()) {
		if(!count($defaultParams)){
			$defaultParams['starttime'] = true;
			$defaultParams['endtime'] = true;
			$defaultParams['timezone'] = true;
		}
		if(!$sesevent)
			return 'No Dates Available';
		if(!$sesevent->endtime || !$sesevent->starttime)
		    return "";
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		//size full,medium,long,short
		$timeformate = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.datetimeformate', 'medium');
		if($defaultParams['starttime']){
		 	$starttimeFull = $this->changeEventDateTime($sesevent->starttime,array('timezone'=>$sesevent->timezone,'size'=>'full'));
			$starttime = $this->changeEventDateTime($sesevent->starttime,array('timezone'=>$sesevent->timezone,'size'=>'small'));
		}
		$timeStr = '';
		if(isset($defaultParams['isPrint'])){
			$sepratorFull= '<br> - ';
			$sepratorHalf = '<br> - ';
			$lineBreak = '<br>';
		}else if(isset($defaultParams['isBreak'])){
			$sepratorHalf = '-';
			$lineBreak = '';
			$sepratorFull = '<br><i class="fa fa-caret-right sesbasic_text_light"></i>';	
		}else if(isset($defaultParams['isSesapi'])){
      $sepratorHalf = "ENDDATE";
      $lineBreak = '';
      $sepratorFull = "ENDDATE";
    }else{
			$sepratorHalf = '-';
			$lineBreak = '';
			$sepratorFull = '<i class="fa fa-caret-right sesbasic_text_light"></i>';	
		}
		if($defaultParams['endtime']){
			$endtimeFull = $this->changeEventDateTime($sesevent->endtime,array('timezone'=>$sesevent->timezone,'size'=>'full'));
			$endtime = $this->changeEventDateTime($sesevent->endtime,array('timezone'=>$sesevent->timezone,'size'=>$timeformate));
		}
		if(date('Y-m-d',strtotime($sesevent->endtime)) == date('Y-m-d',strtotime($sesevent->starttime))){
			$timeStr = '<span><span title="'.$view->translate("Start Time & End Time").$starttimeFull.'">'.$starttime.'</span> '.$sepratorHalf.' '.$endtime.' ('.$sesevent->timezone.')</span>';
		}else{
		if($defaultParams['starttime'])
				$timeStr = '<span><span title="'.$view->translate("Start Time: ").$starttimeFull.'">'.$starttime.'</span>';
		if($defaultParams['endtime'])
			$timeStr .= '<span title="'.$view->translate("End Time: ").$endtimeFull.'">'.$sepratorFull.$endtime.$lineBreak.' ('.$sesevent->timezone.')</span></span>';
		}
		return $timeStr;
	}
	public function changeEventDateTime($date, $options = array()){
		$options = array_merge(array(
			'locale' => $this->getLocale(),
			'size' => 'long',
			'type' => 'datetime',
			'timezone' => Zend_Registry::get('timezone'),
		), $options);    
		$date = $this->_checkDateTime($date, $options);
		if( !$date ) {
			return false;
		}
		if( empty($options['format']) ) {
			if( substr($options['locale']->__toString(), 0, 2) == 'en' && 
				$options['size'] == 'long' && 
				$options['type'] == 'datetime' ) {
				$options['format'] = 'd MMM y H:mm';
			} else if ($options['size'] == "small" ) 
				$options['format'] = 'd MMM  H:mm';
			else {
				$options['format'] = 'd MMM y H:mm';
			}
		}
		// Hack for weird usage of L instead of M in Zend_Locale
		$options['format'] = str_replace('L', 'M', $options['format']);
			//replace seconds string
		$options['format'] = str_replace(':ss', '', $options['format']);
		$str = $date->toString($options['format'], $options['locale']);
		$str = $this->convertNumerals($str, $options['locale']);
		return $str;
  	
	}
}