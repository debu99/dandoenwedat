<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Api_Core extends Core_Api_Abstract {

  public function getFileUrl($image) {
    
    $table = Engine_Api::_()->getDbTable('files', 'core');
    $result = $table->select()
                ->from($table->info('name'), 'storage_file_id')
                ->where('storage_path =?', $image)
                ->query()
                ->fetchColumn();
    if(!empty($result)) {
      $storage = Engine_Api::_()->getItem('storage_file', $result);
      return $storage->map();
    } else {
      return $image;
    }
  }
  
  /* get other module compatibility code as per module name given */
  public function getPluginItem($moduleName) {
		//initialize module item array
    $moduleType = array();
    $filePath =  APPLICATION_PATH . "/application/modules/" . ucfirst($moduleName) . "/settings/manifest.php";
		//check file exists or not
    if (is_file($filePath)) {
			//now include the file
      $manafestFile = include $filePath;
			$resultsArray =  Engine_Api::_()->getDbtable('integrateothermodules', 'sesevent')->getResults(array('module_name'=>$moduleName));
      if (is_array($manafestFile) && isset($manafestFile['items'])) {
        foreach ($manafestFile['items'] as $item)
          if (!in_array($item, $resultsArray))
            $moduleType[$item] = $item.' ';
      }
    }
    return $moduleType;
  }
  

  public function getWidgetPageId($widgetId) {

    $db = Engine_Db_Table::getDefaultAdapter();
    $params = $db->select()
            ->from('engine4_core_content', 'page_id')
            ->where('`content_id` = ?', $widgetId)
            ->query()
            ->fetchColumn();
    return json_decode($params, true);
  }
  
  public function checkPrivacySetting($id) {

    $item = Engine_Api::_()->getItem('sesevent_event', $id);
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();

    if ($viewerId)
      $level_id = $viewer->level_id;
    else
      $level_id = 5;

    $levels = $item->levels;
    $member_level = explode(",",$item->levels); //json_decode($levels);

    if (!empty($member_level) && !empty($item->levels)) {
      if (!in_array($level_id, $member_level))
        return false;
    } else
      return true;


    if ($viewerId) {
      $network_table = Engine_Api::_()->getDbtable('membership', 'network');
      $network_select = $network_table->select('resource_id')->where('user_id = ?', $viewerId);
      $network_id_query = $network_table->fetchAll($network_select);
      $network_id_query_count = count($network_id_query);
      $network_id_array = array();
      for ($i = 0; $i < $network_id_query_count; $i++) {
        $network_id_array[$i] = $network_id_query[$i]['resource_id'];
      }

      if (!empty($network_id_array)) {
        if(!empty($item->networks)) {
        $networks = explode(",",$item->networks); //json_decode($item->networks);

        if (!empty($networks)) {
          if (!array_intersect($network_id_array, $networks))
            return false;
        } else
          return true;
        } else
            return true;
      }
    }
    return true;
  }

	public function getCustomFieldMapData($event) {
    if ($event) {
      $db = Engine_Db_Table::getDefaultAdapter();
      return $db->query("SELECT GROUP_CONCAT(value) AS `valuesMeta`,IFNULL(TRIM(TRAILING ', ' FROM GROUP_CONCAT(DISTINCT(engine4_sesevent_event_fields_options.label) SEPARATOR ', ')),engine4_sesevent_event_fields_values.value) AS `value`, `engine4_sesevent_event_fields_meta`.`label`, `engine4_sesevent_event_fields_meta`.`type` FROM `engine4_sesevent_event_fields_values` LEFT JOIN `engine4_sesevent_event_fields_meta` ON engine4_sesevent_event_fields_meta.field_id = engine4_sesevent_event_fields_values.field_id LEFT JOIN `engine4_sesevent_event_fields_options` ON engine4_sesevent_event_fields_values.value = engine4_sesevent_event_fields_options.option_id AND (`engine4_sesevent_event_fields_meta`.`type` = 'multi_checkbox' OR `engine4_sesevent_event_fields_meta`.`type` ='multiselect' OR `engine4_sesevent_event_fields_meta`.`type` = 'radio'  OR `engine4_sesevent_event_fields_meta`.`type` = 'select') WHERE (engine4_sesevent_event_fields_values.item_id = ".$event->event_id.") AND (engine4_sesevent_event_fields_values.field_id != 1) GROUP BY `engine4_sesevent_event_fields_meta`.`field_id`,`engine4_sesevent_event_fields_options`.`field_id`")->fetchAll();
    }
    return array();
  }
  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
  */
  public function getHref($albumId = '', $slug = '') {
    if (is_numeric($albumId)) {
      $slug = $this->getSlug(Engine_Api::_()->getItem('sesevent_album', $albumId)->getTitle());
    }
    $params = array_merge(array(
        'route' => 'sesevent_specific_album',
        'reset' => true,
        'album_id' => $albumId,
        'slug' => $slug,
    ));
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
                    ->assemble($params, $route, $reset);
  }
    /**
   * Gets a url slug for this item, based on it's title
   *
   * @return string The slug
   */
  public function getSlug($str = null, $maxstrlen = 245) {
    if (null === $str) {
      $str = $this->getTitle();
    }
    if (strlen($str) > $maxstrlen) {
      $str = Engine_String::substr($str, 0, $maxstrlen);
    }
    $search = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
    $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
    $str = str_replace($search, $replace, $str);
    $str = preg_replace('/([a-z])([A-Z])/', '$1 $2', $str);
    $str = strtolower($str);
    $str = preg_replace('/[^a-z0-9-]+/i', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    $str = trim($str, '-');
    if (!$str) {
      $str = '-';
    }
    return $str;
  }
  public function dateFormat($date = null,$changetimezone = '',$object = '',$formate = 'M d, Y h:m A') {
		if($changetimezone != '' && $date){
			$date = strtotime($date);
			$oldTz = date_default_timezone_get();
			date_default_timezone_set($object->timezone);
			if($formate == '')
				$dateChange = date('Y-m-d h:i:s',$date);
			else{
				$dateChange = date('M d, Y h:i A',$date);
			}
			date_default_timezone_set($oldTz);
			return $dateChange.' ('.$object->timezone.')';
		}
    if($date){
      return date('M d, Y h:i A', strtotime($date));
    }
  }
	function generateQrCode($data = '',$filename = ''){
		$size = 9;
		$quality = 'H';
		if(!$data)
			return false;
		include_once "qrcode/qrlib.php";
		$PNG_TEMP_DIR = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public/sesevent_qrcode/';
		//ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);
		$time = md5(time()).rand(0,10000);
		if($filename == '')
			$filenamePng = $PNG_TEMP_DIR.'qrcode_'.$time.'.png';
		else
			$filenamePng = $PNG_TEMP_DIR.$filename;
    QRcode::png($data, $filenamePng, $quality, $size, 2);
		return ( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] .Zend_Registry::get('StaticBaseUrl') .'/public/sesevent_qrcode/'.$filename;
	}
  function tagCloudItemCore($fetchtype = '', $params = array()) {
    $tableTagmap = Engine_Api::_()->getDbtable('tagMaps', 'core');
    $tableTagName = $tableTagmap->info('name');
    $tableTag = Engine_Api::_()->getDbtable('tags', 'core');
    $tableMainTagName = $tableTag->info('name');
    $selecttagged_photo = $tableTagmap->select()
            ->from($tableTagName)
            ->setIntegrityCheck(false)
            ->where('tag_type =?', 'core_tag')
            ->joinLeft($tableMainTagName, $tableMainTagName . '.tag_id=' . $tableTagName . '.tag_id', array('text'))
            ->group($tableTagName . '.tag_id');
    if (isset($params['type']))
      $selecttagged_photo->where('resource_type =?', $params['type']);
    $selecttagged_photo->columns(array('itemCount' => ("COUNT($tableTagName.tagmap_id)")));
    if ($fetchtype == '')
      return Zend_Paginator::factory($selecttagged_photo);
    else
      return $tableTagmap->fetchAll($selecttagged_photo);
  }
	//get google calander link
	function getGoogleCalendarLink($event){
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		return '<a href="http://www.google.com/calendar/event?
							action=TEMPLATE
							&text='.urlencode($event->getTitle()).'
							&dates='.(date('Ymd\\THi00\\Z',strtotime($event->starttime))).'/'.(date('Ymd\\THi00\\Z',strtotime($event->endtime))).'
							&details='.urlencode($event->getDescription()).'
							&location='.$event->location.',
              &trp=false
							&sprop=name:",
							target="_blank" title="'.$view->translate("Add to Google Calendar").'"><i class="sesevent_icon_google"></i>'.$view->translate("Google Calender").'</a>';
	}
	//get yahoo calander link
	function getYahooLink($event){
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		$https = _ENGINE_SSL ? 'https://' : 'http://';
    $duration = strtotime($event->endtime) - strtotime($event->starttime);
    $hours = floor($duration/3600);
    $minutes = floor(($duration%3600)/60);
    $duration = (strlen($hours) < 2 ? '0'.$hours : $hours).(strlen($minutes) < 2 ? '0'.$minutes : $minutes);
		return '<a href="http://calendar.yahoo.com/?v=60&
							ST=' .date('Ymd\THis\Z',strtotime($event->starttime)) . '&
							DUR='.$duration.'&
							title='. urlencode($event->getTitle()).'&
							view=d&type=20&
							DESC='.urlencode($event->getDescription()).'&
							URL='.$https.$_SERVER['HTTP_HOST'].$event->getHref().'&
							in_loc=' . $event->location.'"
							target="_blank" title="'.$view->translate("Add to Yahoo Calendar").'"><i class="sesevent_icon_yahoo"></i>'.$view->translate("Yahoo Calendar").'</a>';
	}
	//get MSN calander link
	public function getMSNlink($event){
    $starttime = $event->starttime;
    $endtime = $event->endtime;

    $oldTz = date_default_timezone_get();
    date_default_timezone_set('UTC');
    $starttime = date('Y-m-d H:i:s',$starttime);
    $endtime = date('Y-m-d H:i:s',$endtime);

    $dateStart = date("Ymd",strtotime($starttime));
    $dateEnd = date("Ymd",strtotime($endtime));
    $dateStartTime = date("His",strtotime($starttime));
    $dateEndTime = date("His",strtotime($endtime));
    date_default_timezone_set($oldTz);

		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		return '<a href="http://calendar.live.com/calendar/calendar.aspx?&
							dtstart=' . ($dateStart.'T'.($dateStartTime)). '&
							dtend=' .$dateEnd.'T'.($dateEndTime) . '&
							summary=' . urlencode($event->getTitle()) . '&
							rru=addevent&
							description='.urlencode($event->getDescription()).'&location=' . urlencode($event->location).'"
							target="_blank" title="'.$view->translate("Add to MSN Calendar").'"><i class="sesevent_icon_msn"></i>'.$view->translate("MSN Calendar").'</a>';
	}

	//remove incomplete ticket order
	public function removeIncompleteTicketOrder($viewerId = ''){
		if($viewerId){
			$order = Engine_Api::_()->getDbtable('orders', 'sesevent');
			$orderTableName = $order->info('name');
			$select = $order->select()
											->from($orderTableName, "order_id")
											->where('state =?', 'incomplete')
											->where($orderTableName . '.owner_id =?', $viewerId);
			$orderId = $select->query()->fetchColumn();
			if($orderId){
				 $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
				 $dbGetInsert->query('DELETE FROM engine4_sesevent_orderticketdetails WHERE order_id = '.$orderId);
				 $dbGetInsert->query('DELETE FROM engine4_sesevent_ordertickets WHERE order_id = '.$orderId);
				 $dbGetInsert->query('DELETE FROM engine4_sesevent_orders WHERE order_id = '.$orderId);
			}
		}
	}
  //get supported currencies
  public function getSupportedCurrency(){
    if(!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])){
      return Engine_Api::_()->sesmultiplecurrency()->getSupportedCurrency();
    }else{
      return array();
    }
  }
	public function isMultiCurrencyAvailable(){
		if(!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])){
      return Engine_Api::_()->sesmultiplecurrency()->isMultiCurrencyAvailable();
    }else{
      return false;
    }
	}
  public function getCurrencySymbolValue($price, $currency = '', $change_rate = '') {
    if(!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])){
      return Engine_Api::_()->sesmultiplecurrency()->getCurrencySymbolValue($price,$currency,$change_rate);
    }else{
      return false;
    }
  }
  //return price with symbol and change rate param for payment history.
  public function getCurrencyPrice($price = 0, $givenSymbol = '', $change_rate = '') {
		$settings = Engine_Api::_()->getApi('settings', 'core');
    $precisionValue = $settings->getSetting('sesmultiplecurrency.precision', 2);
    $defaultParams['precision'] = $precisionValue;
    if(!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])){
      return Engine_Api::_()->sesmultiplecurrency()->getCurrencyPrice($price, $givenSymbol, $change_rate);
    }else{
      return Zend_Registry::get('Zend_View')->locale()->toCurrency($price, $givenSymbol, $defaultParams);
    }
	}
  public function getCurrentCurrency(){
		$settings = Engine_Api::_()->getApi('settings', 'core');
    if(!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])){
      return Engine_Api::_()->sesmultiplecurrency()->getCurrentCurrency();
    }else{
      return $settings->getSetting('payment.currency', 'USD');
    }
  }
  public function defaultCurrency(){
    if(!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])){
      return Engine_Api::_()->sesmultiplecurrency()->defaultCurrency();
    }else{
      $settings = Engine_Api::_()->getApi('settings', 'core');
      return $settings->getSetting('payment.currency', 'USD');
    }
  }
  public function getCurrenctDateTimeAccordingToTimezone($timezone = '') {
    if (!$timezone)
      return date('Y-m-d H:i:s');
    //conver time zone accrding to event
    $oldTz = date_default_timezone_get();
    date_default_timezone_set($timezone);
    $changeTime = date('Y-m-d H:i:s');
    date_default_timezone_set($oldTz);
    return $changeTime;
  }
  public function purchaseTicketCount($event_id, $ticketId) {
    $orderTicket = Engine_Api::_()->getDbtable('orderTickets', 'sesevent');
    $orderTicketTableName = $orderTicket->info('name');
    $select = $orderTicket->select()
            ->from($orderTicketTableName, "SUM(quantity)")
            ->setIntegrityCheck(false)
            ->where("CASE WHEN state = 'incomplete' THEN creation_date > DATE_SUB(now(), INTERVAL 15 MINUTE) ELSE TRUE  END ")
            ->where("CASE WHEN state = 'pending' THEN creation_date > DATE_SUB(now(), INTERVAL 15 MINUTE) ELSE TRUE END ")
            ->where('state !=?', 'cancelled')
            ->where('state !=?', 'failed')
            ->where('state !=?', 'refund')
            ->where($orderTicketTableName . '.ticket_id =?', $ticketId)
            ->where($orderTicketTableName . '.event_id =?', $event_id)
            ->group($orderTicketTableName . '.ticket_id');
    return $select->query()->fetchColumn();
  }

  public function purchaseTicketByUserCount($user, $ticketId){
    $orderTicket = Engine_Api::_()->getDbtable('orderTickets', 'sesevent');
    $userId = $user->getIdentity();
    $orderTicketTableName = $orderTicket->info('name');
    $select = $orderTicket->select()
            ->from($orderTicketTableName, "SUM(quantity)")
            ->setIntegrityCheck(false)
            ->where("CASE WHEN state = 'incomplete' THEN creation_date > DATE_SUB(now(), INTERVAL 15 MINUTE) ELSE TRUE  END ")
            ->where("CASE WHEN state = 'pending' THEN creation_date > DATE_SUB(now(), INTERVAL 15 MINUTE) ELSE TRUE END ")
            ->where('state !=?', 'cancelled')
            ->where('state !=?', 'failed')
            ->where('state !=?', 'refund')
            ->where($orderTicketTableName . '.ticket_id =?', $ticketId)
            ->where($orderTicketTableName . '.owner_id =?', $userId)
            ->group($orderTicketTableName . '.ticket_id');
    return $select->query()->fetchColumn();

  }
  public function getIdentityWidget($name, $type, $corePages) {
    $widgetTable = Engine_Api::_()->getDbTable('content', 'core');
    $widgetPages = Engine_Api::_()->getDbTable('pages', 'core')->info('name');
    $identity = $widgetTable->select()
            ->setIntegrityCheck(false)
            ->from($widgetTable, 'content_id')
            ->where($widgetTable->info('name') . '.type = ?', $type)
            ->where($widgetTable->info('name') . '.name = ?', $name)
            ->where($widgetPages . '.name = ?', $corePages)
            ->joinLeft($widgetPages, $widgetPages . '.page_id = ' . $widgetTable->info('name') . '.page_id')
            ->query()
            ->fetchColumn();
    return $identity;
  }
  //Get Event like status
  public function getLikeStatusEvent($event_id = '', $moduleName = '') {
    if ($moduleName == '')
      $moduleName = 'sesevent_event';
    if ($event_id != '') {
      $userId = Engine_Api::_()->user()->getViewer()->getIdentity();
      if ($userId == 0)
        return false;
      $coreLikeTable = Engine_Api::_()->getDbtable('likes', 'core');
      $total_likes = $coreLikeTable->select()
              ->from($coreLikeTable->info('name'), new Zend_Db_Expr('COUNT(like_id) as like_count'))
              ->where('resource_type =?', $moduleName)
              ->where('poster_id =?', $userId)
              ->where('poster_type =?', 'user')
              ->where('	resource_id =?', $event_id)
              ->query()
              ->fetchColumn();
      if ($total_likes > 0)
        return true;
      else
        return false;
    }
    return false;
  }
	function getRandonRagistrationCode($length){
			$az = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$azr = rand(0, 51);
			$azs = substr($az, $azr, 10);
			$stamp = hash('sha256', time());
			$mt = hash('sha256', mt_rand(5, 20));
			$alpha = hash('sha256', $azs);
			$hash = str_shuffle($stamp . $mt . $alpha);
			return ucfirst(substr($hash, $azr, $length));
	}
	//randon ticket id generator
	public function generateTicketCode($length = 8,$tableName = 'orders')
	{
			$code = $this->getRandonRagistrationCode($length);
			$checkRegistrationNumber = 1;
			do {
			 	$checkRegistrationNumber =  Engine_Api::_()->getDbtable($tableName, 'sesevent')->checkRegistrationNumber($code);
				if($checkRegistrationNumber){
					$code = $this->getRandonRagistrationCode($length);
				}
			} while ($checkRegistrationNumber != 0);
			return $code;
	}
  public function getColumnName($value) {
		switch ($value) {
			case 'recently created':
			$optionKey = 'creation_date ASC';
			break;
					case 'most viewed':
			$optionKey = 'view_count DESC';
			break;
					case 'most liked':
			$optionKey = 'like_count DESC';
			break;
					case 'most commented':
			$optionKey = 'comment_count DESC';
			break;
					case 'most rated':
			$optionKey = 'rating DESC';
			break;
		  case 'most favourite':
			$optionKey = 'favourite_count DESC';
      break;
      case 'starttime':
			  $optionKey = 'starttime DESC';
			break;
			default:
			$optionKey = $value;
		};
    return $optionKey;
  }
    //get album photo
  function getAlbumPhoto($albumId = '', $photoId = '', $limit = 4) {
    if ($albumId != '') {
      $albums = Engine_Api::_()->getItemTable('sesevent_album');
      $albumTableName = $albums->info('name');
      $photos = Engine_Api::_()->getItemTable('sesevent_photo');
      $photoTableName = $photos->info('name');
      $select = $photos->select()
              ->from($photoTableName)
              ->limit($limit)
              ->where($albumTableName . '.album_id = ?', $albumId)
              ->where($photoTableName . '.photo_id != ?', $photoId)
              ->setIntegrityCheck(false)
              ->joinLeft($albumTableName, $albumTableName . '.album_id = ' . $photoTableName . '.album_id', null);
      if ($limit == 3)
        $select = $select->order('rand()');
      return $photos->fetchAll($select);
    }
  }
    //get photo URL
  public function photoUrlGet($photo_id, $type = null) {
    if (empty($photo_id)) {
      $photoTable = Engine_Api::_()->getItemTable('sesevent_photo');
      $photoInfo = $photoTable->select()
              ->from($photoTable, array('photo_id', 'file_id'))
              ->where('album_id = ?', $this->album_id)
              ->order('order ASC')
              ->limit(1)
              ->query()
              ->fetch();
      if (!empty($photoInfo)) {
        $this->photo_id = $photo_id = $photoInfo['photo_id'];
        $this->save();
        $file_id = $photoInfo['file_id'];
      } else {
        return;
      }
    } else {
      $photoTable = Engine_Api::_()->getItemTable('sesevent_photo');
      $file_id = $photoTable->select()
              ->from($photoTable, 'file_id')
              ->where('photo_id = ?', $photo_id)
              ->query()
              ->fetchColumn();
    }
    if (!$file_id) {
      return;
    }
    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($file_id, $type);
    if (!$file) {
      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($file_id, '');
    }
    return $file->map();
  }
	public function getNextPhoto($album_id = '', $order = '') {
    $table = Engine_Api::_()->getDbTable('photos', 'sesevent');
    $select = $table->select()
            ->where('album_id = ?', $album_id)
            ->where('`order` > ?', $order)
            ->order('order ASC')
            ->limit(1);
    $photo = $table->fetchRow($select);
    if (!$photo) {
      // Get first photo instead
      $select = $table->select()
              ->where('album_id = ?', $album_id)
              ->order('order ASC')
              ->limit(1);
      $photo = $table->fetchRow($select);
    }
    return $photo;
  }
  public function getPreviousPhoto($album_id = '', $order = '') {
    $table = Engine_Api::_()->getDbTable('photos', 'sesevent');
    $select = $table->select()
            ->where('album_id = ?', $album_id)
            ->where('`order` < ?', $order)
            ->order('order DESC')
            ->limit(1);
    $photo = $table->fetchRow($select);
    if (!$photo) {
      // Get last photo instead
      $select = $table->select()
              ->where('album_id = ?', $album_id)
              ->order('order DESC')
              ->limit(1);
      $photo = $table->fetchRow($select);
    }
    return $photo;
  }
	public function allowReviewRating(){
		if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventreview') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.review', 1)){
		 	return true;
		}
		return false;
	}
  public function convertResponseParams($transaction,$gatewayInfo){
    $params = array();
    if($gatewayInfo->plugin == "Sesadvpmnt_Plugin_Gateway_Stripe") {
      $params['status'] = $transaction->status;
      $params['txnid'] = $transaction->id;
      $params['currency'] = strtoupper($transaction->currency);
      $params['amount'] = $transaction->amount/100;
    } else if ($gatewayInfo->plugin == "Epaytm_Plugin_Gateway_Paytm") {
      $params['status'] = $transaction["STATUS"];
      $params['txnid'] =  $transaction['TXNID'];
      $params['currency'] = $transaction['CURRENCY'];
      $params['amount'] = $transaction['TXNAMOUNT'];
    } 
   return $params;
  }
  public function orderTicketTransactionReturn($order,$transaction,$gatewayInfo){
    // Check that gateways match
    $params = Engine_Api::_()->sesevent()->convertResponseParams($transaction,$gatewayInfo);
    if($order->gateway_id != $gatewayInfo->gateway_id ) {
      throw new Engine_Payment_Plugin_Exception('Gateways do not match');
    }    
    // Get related info
    $user = $order->getUser();
    $orderTicket = $order->getSource();
    if ($orderTicket->state == 'pending') 
    {
      return 'pending';
    }
    // Check for cancel state - the user cancelled the transaction
//     if($params['status'] == 'TXN_FAILURE' || $params['status']== 'cancel') {
//       // Cancel order and subscription?
//       $order->onCancel();
//       $orderTicket->onOrderFailure();
// 			Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'failed'));
//       // Error
//       throw new Payment_Model_Exception('Your payment has been cancelled and ' .
//           'not been charged. If this is not correct, please try again later.');
//     }
		//payment currency
		$currentCurrency = Engine_Api::_()->sesevent()->getCurrentCurrency();
		$defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
		$settings = Engine_Api::_()->getApi('settings', 'core');
		$currencyValue = 1;
		if($currentCurrency != $defaultCurrency){
				$currencyValue = $settings->getSetting('sesmultiplecurrency.'.$currentCurrency);
		}
      // Get payment state
      $paymentStatus = null;
      $orderStatus = null;
      switch($params['status']) {
            case 'created':
            case 'pending':
                $paymentStatus = 'pending';
                $orderStatus = 'complete';
            break;
            case 'completed':
            case 'processed':
            case 'canceled_reversal':
            case 'succeeded':
            case "TXN_SUCCESS":
              $paymentStatus = 'okay';
              $orderStatus = 'complete';
            break;
            case 'denied':
            case "TXN_FAILURE":
              $paymentStatus = 'failed';
              $orderStatus = 'failed'; 
            break;
            case 'voided':
            case 'reversed':
            case 'refunded':
            case 'expired':
            default:
                $paymentStatus = 'failed';
                $orderStatus = 'failed'; // This should probably be 'failed'
            break;
      } 
      // Update order with profile info and complete status?
      $order->state = $orderStatus;
      $order->gateway_transaction_id = $params['txnid'];
      $order->save();
      // Insert transaction
      $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
      $transactionsTable->insert(array(
        'user_id' => $order->user_id,
        'gateway_id' => $gatewayInfo->gateway_id,
        'timestamp' => new Zend_Db_Expr('NOW()'),
        'order_id' => $order->order_id,
        'type' => 'payment',
        'state' => $paymentStatus,
        'gateway_transaction_id' => $params['txnid'],
        'amount' => $params['amount'], // @todo use this or gross (-fee)?
        'currency' => $params['currency'],
      ));
      // Get benefit setting
      $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'payment')
          ->getBenefitStatus($user); 
      // Check payment status
      if( $paymentStatus == 'okay' ||
          ($paymentStatus == 'pending' && $giveBenefit) ) {
        // Update order table info
        $orderTicket->gateway_id = $gatewayInfo->gateway_id;
        $orderTicket->gateway_transaction_id = transaction['TXNID'];
				$orderTicket->currency_symbol = $params['currency'];
				$orderTicket->change_rate = $currencyValue;
				$orderTicket->save();
        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('ecoupon')){
          $couponSessionCode = '-'.'-sesevent_event-'.$orderTicket->event_id.'-0'; 
          $orderTicket->ordercoupon_id = Engine_Api::_()->ecoupon()->setAppliedCouponDetails($couponSessionCode);
          $orderTicket->save();
        }
        //For Credit 
        $creditCode =  'credit'.'-sesevent-'.$orderTicket->event_id.'-'.$orderTicket->event_id;
        $sessionCredit = new Zend_Session_Namespace($creditCode);
        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescredit') && isset($sessionCredit)) {
          $orderTicket->credit_point = $sessionCredit->credit_value;  
          $orderTicket->credit_value =  $sessionCredit->purchaseValue;
          $orderTicket->save();
          $userCreditDetailTable = Engine_Api::_()->getDbTable('details', 'sescredit');
          try {
            $userCreditDetailTable->update(array('total_credit' => new Zend_Db_Expr('total_credit - ' . $sessionCredit->credit_value)), array('owner_id =?' => $order->user_id));
          } catch(Exception $e){

          }
        }
				$orderAmount = round($orderTicket->total_service_tax + $orderTicket->total_entertainment_tax + $orderTicket->total_amount,2);
				$commissionValue = round($orderTicket->commission_amount,2);
				if(isset($commissionValue) && $orderAmount > $commissionValue){
					$orderAmount = $orderAmount - $commissionValue;	
				}else{
					$orderTicket->commission_amount = 0;
				}
				//update EVENT OWNER REMAINING amount
				$tableRemaining = Engine_Api::_()->getDbtable('remainingpayments', 'sesevent');
				$tableName = $tableRemaining->info('name');
				$select = $tableRemaining->select()->from($tableName)->where('event_id =?',$orderTicket->event_id);
				$select = $tableRemaining->fetchAll($select);
				if(count($select)){
					$tableRemaining->update(array('remaining_payment' => new Zend_Db_Expr("remaining_payment + $orderAmount")),array('event_id =?'=>$orderTicket->event_id));
				}else{
					$tableRemaining->insert(array(
						'remaining_payment' => $orderAmount,
						'event_id' => $orderTicket->event_id,
					));
				}
				//update ticket state
				Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'complete'));
        // Payment success
        $orderTicket->onOrderComplete();
        // send notification
        if( $orderTicket->state == 'complete' ) {
          $ticket_id=  Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->getTicketId(array('order_id'=>$orderTicket->order_id));
          $tickets = Engine_Api::_()->getItem('sesevent_ticket', $ticket_id);
          $eventOrder = Engine_Api::_()->getItem('sesevent_order', $orderTicket->order_id);
		      //Notification Work
		      $event = Engine_Api::_()->getItem('sesevent_event', $orderTicket->event_id);
					$owner = $event->getOwner();
		      Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $user, $event, 'sesevent_event_ticketpurchased', array("ticketName" => $tickets->name));
		      //Activity Feed Work
		      $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
		      $action = $activityApi->addActivity($user, $event, 'sesevent_event_ticketpurchased', '',  array("ticketname" => '<b>' . $tickets->name . '</b>'));
			    if ($action) {
				    $activityApi->attachActivity($action, $event);
			    }
			    $totalAmount = @round($orderTicket->total_amount + $orderTicket->total_service_tax + $orderTicket->total_entertainment_tax,2);
			    if($orderTicket->total_tickets){
				    $total_price_t = @round($orderTicket->total_tickets * $tickets->price,2);
				  } else { 
					  $total_price_t = @round($tickets->price,2);
				  }
				  if($eventOrder->total_service_tax > 0){
				    $service_tax_t = Engine_Api::_()->sesevent()->getCurrencyPrice(@round($eventOrder->total_service_tax,2), $eventOrder->currency_symbol, $eventOrder->change_rate);
				  } else { 
					  $service_tax_t = "-";
				  }
				  if($eventOrder->total_entertainment_tax){
				    $entertainment_tax_t = Engine_Api::_()->sesevent()->getCurrencyPrice(@round($eventOrder->total_entertainment_tax,2), $eventOrder->currency_symbol, $eventOrder->change_rate);
				  } else { 
					  $entertainment_tax_t = "-";
				  }
					if($totalAmount <= 0) {
						$grandTottal = 'FREE';
					} else {
					  $grandTottal = Engine_Api::_()->sesevent()->getCurrencyPrice($totalAmount, $eventOrder->currency_symbol, $eventOrder->change_rate);
				  }
				  $orderTicketsDetails = Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->getOrderTicketDetails(array('order_id' => $orderTicket->order_id));
				  if($eventOrder->ragistration_number) {
						$fileName = $eventOrder->getType().'_'.$eventOrder->getIdentity().'.png';
						if(!file_exists(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public/sesevent_qrcode/'.$fileName)){ 
							$qrCode = Engine_Api::_()->sesevent()->generateQrCode($eventOrder->ragistration_number,$fileName);
						}else{
							$qrCode = ( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] .Zend_Registry::get('StaticBaseUrl') .'/public/sesevent_qrcode/'.$fileName;
						}
					}else
						$qrCode = '';
				  $ticketDetails = '';
				  foreach($orderTicketsDetails as $orderTiDetails) {
	          $ticketDetails .= '<tr><td>'.$orderTiDetails['title'] .'</td>';
	          $ticketDetails .= '<td align="right">';
            if($orderTiDetails->price <= 0){
	            $ticketDetails .= 'FREE';
            } else {
              $ticketDetails.= Engine_Api::_()->sesevent()->getCurrencyPrice($orderTiDetails->price,$eventOrder->currency_symbol,$eventOrder->change_rate); 
            }
            $ticketDetails .= '<br />';
            if($orderTiDetails->service_tax > 0) {
	            $ticketDetails .= 'Service Tax:' . @round($orderTiDetails->service_tax,2).'%';
	            $ticketDetails .= '<br />';
            }
            if($orderTiDetails->entertainment_tax >0) {
			        $ticketDetails .= 'Entertainment Tax:' . @round($orderTiDetails->entertainment_tax,2).'%'; 
		        }
		        $ticketDetails .= '</td>';
	          $ticketDetails .= '<td align="center">' .$orderTiDetails->quantity . '</td>';
	          $price = $orderTiDetails->price; 
	          if($price <= 0) {
	            $ticketDetails .= '<td align="center">';
		          $ticketDetails .= 'FREE';
	          } else {
	            $ticketDetails .= '<td align="right">';
		          $ticketDetails .= Engine_Api::_()->sesevent()->getCurrencyPrice(round($price*$orderTiDetails->quantity,2),$eventOrder->currency_symbol,$eventOrder->change_rate);
		          $ticketDetails .= '<br />';
	          }
	          if($orderTiDetails->service_tax > 0) {
		          $serviceTax = round(($price *($orderTiDetails->service_tax/100) )*$orderTiDetails->quantity,2); 
		          $ticketDetails .= 'Service Tax:';
		          $ticketDetails .= Engine_Api::_()->sesevent()->getCurrencyPrice(@round($serviceTax,2),$eventOrder->currency_symbol,$eventOrder->change_rate);
		          $ticketDetails .= '<br />';
		        }
		        if($orderTiDetails->entertainment_tax > 0) { 
			        $entertainmentTax = round(($price *($orderTiDetails->entertainment_tax/100) ) * $orderTiDetails->quantity,2);
			        $ticketDetails .= 'Entertainment Tax:';
			        $ticketDetails .= Engine_Api::_()->sesevent()->getCurrencyPrice(@round($entertainmentTax,2),$eventOrder->currency_symbol,$eventOrder->change_rate);
			      }
			      $ticketDetails .= '</td>';
						$ticketDetails .= '</tr>';
		      }
		      $totalAmount = @round($orderTicket->total_amount + $orderTicket->total_service_tax + $orderTicket->total_entertainment_tax,2);
		      $totalAmounts = '[';
		      $totalAmounts .= 'Total:';
		      if($totalAmount <= 0) {
		      $totalAmounts .= 'FREE';
		      } else {
			      $totalAmounts .= Engine_Api::_()->sesevent()->getCurrencyPrice(@round($totalAmount,2),$orderTicket->currency_symbol, $orderTicket->change_rate);
		      }
		      $totalAmounts .= ']';
		      $sub_total = '';
		      if($orderTicket->total_amount <= 0) {
			      $sub_total .= 'FREE';
		      } else {
			      $sub_total .= Engine_Api::_()->sesevent()->getCurrencyPrice(@round($orderTicket->total_amount,2), $orderTicket->currency_symbol, $orderTicket->change_rate);
		      }
			    $body .= '<table style="background-color:#f9f9f9;border:#ececec solid 1px;width:100%;"><tr><td><div style="margin:0 auto;width:600px;font:normal 13px Arial,Helvetica,sans-serif;padding:20px;"><div style="margin-bottom:10px;overflow:hidden;"><div style="float:left;"><b>Order Id: #' . $orderTicket->order_id . '</b></div><div style="float:right;"><b>'.$totalAmounts.'</b></div></div><table style="background-color:#fff;border:#ececec solid 1px;margin-bottom:20px;" cellpadding="0" cellspacing="0" width="100%"><tr valign="top" style="width:50%;"><td><div style="border-bottom:#ececec solid 1px;padding:20px;"><b style="display:block;margin-bottom:5px;">Ordered For</b><span style="display:block;margin-bottom:5px;"><a href="'.( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] .$event->getHref().'" style="color:#39F;text-decoration:none;">'.$event->getTitle().'</a></span><span style="display:block;margin-bottom:5px;">'.$event->starttime.' - '.$event->endtime.'</span></div><div style="padding:20px;border-bottom:#ececec solid 1px;"> <b style="display:block;margin-bottom:5px;">Ordered By</b><span style="display:block;margin-bottom:5px;"><a href="'.( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] .$orderTicket->getOwner()->getHref().'" style="color:#39F;text-decoration:none;">'.$orderTicket->fname.'</a></span><span style="display:block;margin-bottom:5px;">'.$orderTicket->email.'</span></div><div style="padding:20px;"><b style="display:block;margin-bottom:5px;">Payment Information</b><span style="display:block;margin-bottom:5px;">Payment Method: '.$orderTicket->gateway_type.'</span></div></td><td style="border-left:#ececec solid 1px;width:50%;"><div style="padding:20px;"><b style="display:block;margin-bottom:5px;">Order Information</b><span style="display:block;margin-bottom:5px;">Ordered Date: '.$orderTicket->creation_date.'</span>';
			    
			    if($orderTicket->total_service_tax)
				    $body .= '<span style="display:block;margin-bottom:5px;">Service Tax: $'.round($orderTicket->total_service_tax,2).'</span>';
			    
			    if($orderTicket->total_entertainment_tax)
				    $body .= '<span style="display:block;margin-bottom:5px;">Entertainment Tax: $'.round($orderTicket->total_entertainment_tax,2).'</span>';
			    
			    $body .= '</div>';
			    
			    if($qrCode)
				    $body .= '<div style="padding:20px;text-align:center;"><img style="height:150px;width:150px;" src="'.$qrCode.'"></div>';

			    $body .= '</td></tr></table><div style="margin-bottom:10px;"><b class="bold">Order Details</b></div><table bordercolor="#ececec"  border="1" style="background-color:#fff;margin-bottom:20px;border-collapse: collapse;" cellpadding="10" cellspacing="0" width="100%"><tbody><tr><th>Ticket Name</th><th>Price</th><th>Quantity</th><th>Sub Total</th></tr>' . $ticketDetails . '</tbody></table><div style="background-color:#fff;border:1px solid #ececec;padding:10px;"><div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;">Sub Total</span><span style="float:right;">'.$sub_total.'</span> </div>';
			    if($service_tax_t)
				    $body .= '<div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;">Service Taxes</span><span style="float:right;">'.$service_tax_t.'</span></div>';
			    if($entertainment_tax_t)
				    $body .= '<div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;">Entertainment Taxes</span><span style="float:right;">'.$entertainment_tax_t.'</span></div>';
			    $body .= '<div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;"><b>Grand Total</b></span><span style="float:right;"><b>'.$grandTottal.'</b></span></div></div></div> </td></tr></table>';

			    //Ticket Details
			    $orderDetails = Engine_Api::_()->getDbTable('orderticketdetails', 'sesevent')->orderTicketDetails(array('order_id' => $orderTicket->order_id));		
			    $ticketsContent = '';
					$pdfCreate = false;
				 //send pdf ticket if seseventpdf extention enabled and activated
				 if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventpdfticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventpdfticket.pluginactivated')){
					 try{						
						$mailApi = Engine_Api::_()->getApi('mail', 'core');
						$mail = $mailApi->create();
						$adminEmail = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.contact');
						$adminTitle = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.name');
						$mail->setFrom($adminEmail, $adminTitle)
										->setSubject("Your ticket to event" . $event->getTitle())
										->setBodyHtml('Hello');
						$mail->addTo($orderTicket->getOwner()->email);						
						 foreach($orderDetails as $keyDet => $item) {
							 	$itemId = $item->getIdentity();
								$pdfname =	Engine_Api::_()->getApi('core', 'seseventpdfticket')->createPdfFile($item,$event,$eventOrder,$user);
								if(!$pdfname){
										$pdfCreate = false;
										break;
								}else{
								 try{
									$pdfTicketFile = APPLICATION_PATH . '/public/sesevent_ticketpdf/'.$pdfname;
									$handle = @fopen($pdfTicketFile, "r");
									while (($buffer = fgets($handle)) !== false) {
										$content .= $buffer;
									}
									$attachment = $mail->createAttachment($content);
									$attachment->filename = "eventticket_$itemId".".pdf";
								 }catch(Exception $e){
										 $pdfCreate = false;
										 break;
										//silence 
									}
								}
								$pdfCreate = true;
						 }
						 if($pdfCreate)
							 $mailApi->send($mail);
					 }catch( Exception $e ){
							//silence 
							$pdfCreate = false;
					 }
				}
				if(!$pdfCreate){
			    foreach($orderDetails as $keyDet => $item) {
				    $ticketsContent .= '<table style="width:100%;"><tr><td><table border="0" cellpadding="0" cellpadding="0"  style="border-collapse:collapse;width:800px;margin:0 auto;font:normal 13px Arial,Helvetica,sans-serif;border:5px solid #ddd;background-color:#fff;"><tbody><tr valign="top"><td style="border-right:5px solid #ddd;width:590px;"><div style="border-bottom:5px solid #ddd;height:110px;display:block;float:left;position:relative;width:100%;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Event</div>';
				    $ticketsContent .= '<div style="font-size:20px;margin-top:40px;position:inherit;text-align:center;">';
				    $ticketsContent .= $event->getTitle(); 
				    $ticketsContent .= '</div>';
				    $ticketsContent .= '</div><div style="border-bottom:5px solid #ddd;border-right:5px solid #ddd;float:left;height:120px;width:280px;position:relative;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Date+Time</div><div style="bottom:5px;font-size:13px;position:absolute;right:5px;max-width:90%;">';
						$dateinfoParams['starttime'] = true;
						$dateinfoParams['endtime']  =  true;
						$dateinfoParams['timezone']  = true; 
						$dateinfoParams['isPrint']  = true; 
						$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
				    $ticketsContent .= $view->eventStartEndDates($event, $dateinfoParams);
				    $ticketsContent .= '</div></div><div style="border-bottom:5px solid #ddd;float:left;height:120px;width:275px;position:relative;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Location</div><div style="bottom:5px;font-size:13px;position:absolute;right:5px;max-width:90%;">';
				    if($event->location && !$event->is_webinar && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.location', 1)) {
					    $venue_name = '';
							if($event->venue_name){ 
								$venue_name = '<br />'. $event->venue_name;
							}
					    $location = $event->location . $venue_name;
				    } else {
					    $location = 'Webinar Event';
				    }
				    $ticketsContent .= $location;
				    $ticketsContent .= '</div></div>';
				    $ticketsContent .= '<div style="border-bottom:5px solid #ddd;clear:both;float:left;position:relative;width:100%;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Order Info</div><div style="margin:30px 5px 20px;text-align:right;">';
				    $ticketsContent .= 'Order # ' .$eventOrder->order_id;
				    $ticketsContent .= 'Ordered by ' .$user->getTitle();
				    $ticketsContent .= 'on ' . Engine_Api::_()->sesevent()->dateFormat($eventOrder->creation_date);
				    $ticketsContent .= '</div></div>';
				    $ticketsContent .= '<div style="clear:both;float:left;position:relative;width:100%;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Attendee Info</div><div style="margin:30px 5px 20px;text-align:right;">';
				    $ticketsContent .= $item->first_name .' '. $item->last_name . '<br />';
				    $ticketsContent .= $item->mobile . '<br />' . $item->email;
				    $ticketsContent .= '</div></div></td>';
				    $ticketsContent .= '<td style="width:238px;">
            <div style="height:110px;width:100%;">';
            $eventPhoto = ( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] .Zend_Registry::get('StaticBaseUrl') . $event->getPhotoUrl();
            $ticketsContent .= '<img alt="" src="'.$eventPhoto.'" style="height:100%;object-fit:contain;padding:10px;width:100%;"></div><div style="border-bottom:5px solid #ddd;float:left;height:60px;margin-top:60px;position:relative;width:100%;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Payment Method</div><div style="font-size:17px;margin:30px 0 20px;text-align:center;">';
            $ticketsContent .= $eventOrder->gateway_type;
            $ticketsContent .= '</div></div><div style="display:block;float:left;position:relative;text-align:center;width:100%;">';
						if($item->registration_number) {
						$fileName = $item->getType().'_'.$item->getIdentity().'.png';
						if(!file_exists(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public/sesevent_qrcode/'.$fileName)){ 
							$fileName = Engine_Api::_()->sesevent()->generateQrCode($item->registration_number,$fileName);
						} else{ 
							$fileName = ( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] .Zend_Registry::get('StaticBaseUrl') .'/public/sesevent_qrcode/'.$fileName;
						}
					}else
						$qrCode = '';						
            $ticketsContent .= '<img alt="'.$item->registration_number.'" src="'.$fileName.'" style="margin-top:20px;max-width:100px;"></div></td>';
				    $ticketsContent .= '</tr></tbody></table></td></tr></table>';
			    }
				}
				try{
			    //insert in membership table
					$membershipTable = Engine_Api::_()->getDbtable('membership', 'sesevent');
					$membershipTable->insert(array(
						'user_id' => $orderTicket->owner_id,
						'resource_id' => $orderTicket->event_id,
						'active' => 1,
						'resource_approved' => 1,
						'user_approved' => '1',
						'rsvp' => 2,
					));
				}catch (Exception $e){
					//silence	
				}
			if(!$pdfCreate){
			    //Tickets Details
			    Engine_Api::_()->getApi('mail', 'core')->sendSystem($orderTicket->getOwner(), 'sesevent_tikets_details', array('host' => $_SERVER['HTTP_HOST'], 'ticket_body' => $ticketsContent, 'event_title' => $event->getTitle()));
			}
				  //Ticket invoice mail to buyer
			    Engine_Api::_()->getApi('mail', 'core')->sendSystem($orderTicket->getOwner(), 'sesevent_tiketinvoice_buyer', array('invoice_body' => $body, 'host' => $_SERVER['HTTP_HOST']));
			
			    //Ticket Purchased Mail to Event Owner
			    $event_owner = Engine_Api::_()->getItem('user', $event->user_id);
			    Engine_Api::_()->getApi('mail', 'core')->sendSystem($event_owner, 'sesevent_ticketpurchased_eventowner', array('event_title' => $event->title, 'object_link' => $event->getHref(), 'buyer_name' => $user->getTitle(), 'host' => $_SERVER['HTTP_HOST']));
        }
				$orderTicket->creation_date	= date('Y-m-d H:i:s');
				$orderTicket->save();
        return 'active';
      }
      else if( $paymentStatus == 'pending' ) {
        // Update order  info
        $orderTicket->gateway_id = $gatewayInfo->gateway_id;
        $orderTicket->gateway_profile_id = transaction['TXNID'];
				$orderTicket->save();
        // Order pending
        $orderTicket->onOrderPending();
				//update ticket state
				Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'pending'));

        //Send Mail
        $event = Engine_Api::_()->getItem('sesevent_event', $orderTicket->event_id);
        
				Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'sesevent_payment_ticket_pending', array('event_title' => $event->title, 'evnet_description' => $event->description, 'object_link' => $event->getHref(), 'host' => $_SERVER['HTTP_HOST']));
        
        return 'pending';
      }
      else if( $paymentStatus == 'failed' ) {
        // Cancel order and subscription?
        $order->onFailure();
        $orderTicket->onOrderFailure();
				//update ticket state
				Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'failed'));
        // Payment failed
        throw new Payment_Model_Exception('Your payment could not be ' .
            'completed. Please ensure there are sufficient available funds ' .
            'in your account.');
      }
      else {
        // This is a sanity error and cannot produce information a user could use
        // to correct the problem.
        throw new Payment_Model_Exception('There was an error processing your ' .
            'transaction. Please try again later.');
      }
    return $paymentStatus;
	}
	
}
