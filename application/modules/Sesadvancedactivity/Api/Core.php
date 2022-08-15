<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Api_Core extends Core_Api_Abstract
{
  /**`
   * Loader for parsers
   *
   * @var Zend_Loader_PluginLoader
   */
  protected $_pluginLoader;
   //get supported currencies

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
  
  public function getDetailsTableColumn(){
      $details = array('schedule_time', 'commentable', 'sesapproved', 'detail_id', 'sesresource_id', 'sesresource_type','is_community_ad');
      $db = Zend_Db_Table_Abstract::getDefaultAdapter();
      $column = $db->query('SHOW COLUMNS FROM engine4_sesadvancedactivity_details LIKE \'view_count\'')->fetch();
      if (!empty($column )) {
        $details[] = "view_count";
        $details[] = "share_count";
      }
      $column = $db->query('SHOW COLUMNS FROM engine4_sesadvancedactivity_details LIKE \'posting_type\'')->fetch();
      if (!empty($column )) {
        $details[] = "posting_type";
      }
      return $details;
  }
  public function getSupportedCurrency(){
    if(!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])){
      $currency =  Engine_Api::_()->sesmultiplecurrency()->getSupportedCurrency();
      $currencies = array();
        $defaultCurrency = Engine_Api::_()->sesmultiplecurrency()->defaultCurrency();
        $settings = Engine_Api::_()->getApi('settings', 'core');
      foreach ($currency as $key=>$value){
          if(!$settings->getSetting('sesmultiplecurrency.'.$key.'active','0') && $key != $defaultCurrency)
              continue;
          if(!$settings->getSetting('sesmultiplecurrency.' . $key) && $key != $defaultCurrency)
              continue;
          $currencies[$key] = $value;
      }
      return $currencies;
    }else{
      return array();
    }
  }

  function multiCurrencyActive(){
    if(!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])){
      return Engine_Api::_()->sesmultiplecurrency()->multiCurrencyActive();
    }else{
      return false;
    }
  }
  function isMultiCurrencyAvailable(){
    if(!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])){
      return Engine_Api::_()->sesmultiplecurrency()->isMultiCurrencyAvailable();
    }else{
      return false;
    }
  }

  function getCurrencyPrice($price = 0, $givenSymbol = '', $change_rate = 1){
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $precisionValue = $settings->getSetting('sesmultiplecurrency.precision', 2);
    $defaultParams['precision'] = $precisionValue;
    if(!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])){
      return Engine_Api::_()->sesmultiplecurrency()->getCurrencyPrice($price, $givenSymbol, $change_rate);
    }else{
      return Zend_Registry::get('Zend_View')->locale()->toCurrency($price, $givenSymbol, $defaultParams);
    }
  }
  function getCurrentCurrency(){
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if(!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])){
      return Engine_Api::_()->sesmultiplecurrency()->getCurrentCurrency();
    }else{
      return $settings->getSetting('payment.currency', 'USD');
    }
  }
  function defaultCurrency(){
    if(!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])){
      return Engine_Api::_()->sesmultiplecurrency()->defaultCurrency();
    }else{
      $settings = Engine_Api::_()->getApi('settings', 'core');
      return $settings->getSetting('payment.currency', 'USD');
    }
  }

  public function getWelcomeMessage($viewer){
    //check message sent already
    $select = Engine_Api::_()->getDbtable('welcomemessages', 'sesadvancedactivity')->select()->where('user_id =?',$viewer->getIdentity())->where('creation_date =?',date('Y-m-d'));
    $message = Engine_Api::_()->getDbtable('welcomemessages', 'sesadvancedactivity')->fetchRow($select);
    $status = 0;
    $image = '';
    if(!$message){
      $oldTimeZone = date_default_timezone_get();
      date_default_timezone_set($viewer->timezone);
      // 24-hour format of an hour without leading zeros (0 through 23)
      $Hour = date('G');
       date_default_timezone_set($oldTimeZone);
      //insert record
      $values['user_id'] = $viewer->getIdentity();
      $values['creation_date'] = date('Y-m-d');
      Engine_Api::_()->getDbtable('welcomemessages', 'sesadvancedactivity')->insert($values);

      $message = '';
      if ( $Hour >= 5 && $Hour <= 11 ) {
        // "Good Morning";
         $status = 1;
         $message = Zend_Registry::get('Zend_Translate')->_("Good Morning");
         $image = 'morning.png';
      } else if ( $Hour >= 12 && $Hour <= 18 ) {
      // "Good Afternoon";
         $status = 2;
         $message = Zend_Registry::get('Zend_Translate')->_("Good Afternoon");
         $image = 'noon.png';
      } else if ( $Hour >= 19 || $Hour <= 4 ) {
        // "Good Evening";
         $status = 3;
         $message = Zend_Registry::get('Zend_Translate')->_("Good Evening");
         $image = 'evening.png';
      }
    }
    return array('status'=>$status,'message'=>$message,'image'=>$image);
  }
  public function getBirthdayViewer($viewer , $fields){
     //check message sent already
     $dateOfBirth = !empty($fields['birthdate']) ? $fields['birthdate'] : '';
     if(!$dateOfBirth)
        return 0;
     $status = 0;
     $oldTimeZone = date_default_timezone_get();
     date_default_timezone_set($viewer->timezone);
    $select = Engine_Api::_()->getDbtable('birthdaymessages', 'sesadvancedactivity')->select()->where('user_id =?',$viewer->getIdentity())->where('creation_date =?',date('Y-m-d'));
    $message = Engine_Api::_()->getDbtable('birthdaymessages', 'sesadvancedactivity')->fetchRow($select);
    if(!$message){
      $time = date('m-d');
      if($time == date('m-d',strtotime($dateOfBirth))){
        $status =  1;
        //insert record
        $values['user_id'] = $viewer->getIdentity();
        $values['creation_date'] = date('Y-m-d');
        Engine_Api::_()->getDbtable('birthdaymessages', 'sesadvancedactivity')->insert($values);
      }
    }
      date_default_timezone_set($oldTimeZone);
    return $status;
  }

  function loggedinFriendBirthday($params = array(),$viewer){

    if(!empty($params['single'])){
      $select = Engine_Api::_()->getDbtable('friendbirthdaymessages', 'sesadvancedactivity')->select()->where('user_id =?',$viewer->getIdentity())->where('creation_date =?',date('Y-m-d'));
      $message = Engine_Api::_()->getDbtable('friendbirthdaymessages', 'sesadvancedactivity')->fetchRow($select);
      if($message)
        return false;
      //insert record
      $values['user_id'] = $viewer->getIdentity();
      $values['creation_date'] = date('Y-m-d');
      Engine_Api::_()->getDbtable('friendbirthdaymessages', 'sesadvancedactivity')->insert($values);
    }

    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();;
    $meta = Engine_Api::_()->fields()->getTable('user', 'meta');
    $metaName = $meta->info('name');
    $valuetable = Engine_Api::_()->fields()->getTable('user', 'values');;
    $valueName = $valuetable->info('name');
    $usertable = Engine_Api::_()->getDbTable('users', 'user');
    $userName = $usertable->info('name');
    $membershiptable = Engine_Api::_()->getDbTable('membership', 'user');
    $membershipName = $membershiptable->info('name');
    $select =$meta->select()
                  ->setIntegrityCheck(false)
                  ->from($metaName, array($valueName. '.item_id'))
                  ->join($valueName, $valueName . '.field_id = ' . $metaName . '.field_id',null)
                  ->join($userName, $valueName . '.item_id = ' . $userName . '.user_id', null)
                  ->join($membershipName, $valueName . '.item_id = ' . $membershipName . '.resource_id',null)
                  ->where($metaName . '.type = ?', 'birthdate')
                  ->where("DATE_FORMAT(" . $valueName. " .value, '%m-%d') = ?",date('m-d'))
                  ->where($valueName. '.item_id <> ?', $viewer_id)
                  ->where($membershipName. '.user_id = ?', $viewer_id)
                  ->where($membershipName. '.active = ?', 1);
   if(!empty($params['single'])){
    $select->limit(1);
    return $meta->fetchRow($select);
   }
    return $meta->fetchAll($select);
  }
  // Parsing

  /**
   * Sesadvancedactivity template parsing
   *
   * @param string $body
   * @param array $params
   * @return string
   */
  public function fetchAction($action_ids, $param = 0) {

    $table = Engine_Api::_()->getDbTable('actions','sesadvancedactivity');
    $tableName = $table->info('name');
    $select = $table->select()->from($tableName,array("action_id","subject_type","subject_id"))->group("subject_id");
    if(empty($param)) {
      $select->where("action_id IN (".$action_ids.")");
    } else {
      $select->where("action_id IN (?)", $action_ids);
    }

    return ($table->fetchAll($select));
  }

  public function assemble($body, array $params = array(),$break = true,$group_feed = false)
  {
    $paramsArray = $params['params'];
    if(is_array($paramsArray) && count($paramsArray)){
        if(!empty($paramsArray['owner']) && empty($params['owner'])){
           unset($params['owner']);
           $params =  array_merge(array('owner'=> Engine_Api::_()->getItemByGuid($paramsArray['owner'])),$params);
        }
    }
    // Translate body
    $body = $this->getHelper('translate')->direct($body);
    $body =  $body.'|||||---|||++'.$break;
    // Do other stuff
    if($group_feed){
      $explodeData = explode(',',$group_feed);
      if(count($explodeData) > 1){
        $group_feed = preg_replace('/,[^,]*$/', '', $group_feed);
        $getTotalFeedBySubject = $this->fetchAction($group_feed);
        if(count($getTotalFeedBySubject) > 0){
          $bodyExplode = explode(" ",$body);
          $bodyExplodeCurrent = current($bodyExplode);
          unset($bodyExplode[0]);
          $actionIds = "";
          $htmlTitle = "";
          $count = 0;
          foreach($getTotalFeedBySubject as $actionId){
            if($count < 20){
              $htmlTitle = $htmlTitle.$actionId->getSubject()->getTitle().'<br>';
            }else if($content == 21){
              $htmlTitle = $htmlTitle.'and more';
            }
            $count++;
            $actionIds = $actionIds.$actionId["action_id"].',' ;
          }
          $htmlTitle = rtrim($htmlTitle,"<br>");
          $actionIds = rtrim($actionIds,',');
          $bodyExplodeCurrent = $bodyExplodeCurrent.' and <a class="sessmoothbox sesadv_tooltip" title="'.$htmlTitle.'" data-url="sesadvancedactivity/ajax/group-feed/resource_id/'.$actionIds.'" href="javascript:;">'.(count($explodeData) - 1).' other</a>';
          $array = array_merge(array($bodyExplodeCurrent),$bodyExplode);
          $body = implode(' ',$array);
        }
      }
    }
    preg_match_all('~\{([^{}]+)\}~', $body, $matches, PREG_SET_ORDER);

    foreach( $matches as $match )
    {
      $tag = $match[0];
      $args = explode(':', $match[1]);
      $helper = array_shift($args);

      $helperArgs = array();
      foreach( $args as $arg )
      {
        if( substr($arg, 0, 1) === '$' )
        {
          $valid = true;
          $arg = substr($arg, 1);
          if($arg == "subject" && !empty($params['sesresource_id']) && !empty($params['sesresource_type'])){
            $item = Engine_Api::_()->getItem($params['sesresource_type'],$params['sesresource_id']);
            if($item){
              $helperArgs[] =  $item;
              $valid = false;
            }
          }
          if($valid)
            $helperArgs[] = ( isset($params[$arg]) ? $params[$arg] : null );
        }
        else
        {
          $helperArgs[] = $arg;
        }
      }

      $helper = $this->getHelper($helper);
      $r = new ReflectionMethod($helper, 'direct');

      $content = $r->invokeArgs($helper, $helperArgs);
      $content = preg_replace('/\$(\d)/', '\\\\$\1', $content);
      $body = preg_replace("/" . preg_quote($tag) . "/", $content, $body, 1);
    }
    $body = str_replace('|||||---|||++'.$break,'',$body);
    if($break)
		  $body = explode('BODYSTRING',$body);
    else
      $body = str_replace('BODYSTRING','',$body);
    return $body;
  }
  public function getCurrencySymbol($currency = ''){
    if(!$currency)
      $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
    $curArr = Zend_Locale::getTranslationList('CurrencySymbol');
    return $curArr[$currency];
  }
  /**
   * Gets the plugin loader
   *
   * @return Zend_Loader_PluginLoader
   */
  public function getPluginLoader()
  {
    if( null === $this->_pluginLoader )
    {
      $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR
          . 'modules' . DIRECTORY_SEPARATOR
          . 'Sesadvancedactivity';
      $this->_pluginLoader = new Zend_Loader_PluginLoader(array(
        'Sesadvancedactivity_Model_Helper_' => $path . '/Model/Helper/'
      ));
    }

    return $this->_pluginLoader;
  }

  /**
   * Get a helper
   *
   * @param string $name
   * @return Sesadvancedactivity_Model_Helper_Abstract
   */
  public function getHelper($name)
  {
    $name = $this->_normalizeHelperName($name);
    if( !isset($this->_helpers[$name]) )
    {
      $helper = $this->getPluginLoader()->load($name);
      $this->_helpers[$name] = new $helper;
    }

    return $this->_helpers[$name];
  }

  /**
   * Normalize helper name
   *
   * @param string $name
   * @return string
   */
  protected function _normalizeHelperName($name)
  {
    $name = preg_replace('/[^A-Za-z0-9]/', '', $name);
    //$name = strtolower($name);
    $name = ucfirst($name);
    return $name;
  }
  function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    // Uncomment one of the following alternatives
    // $bytes /= pow(1024, $pow);
     $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
  }
  function file_upload_max_size() {
  static $max_size = -1;

  if ($max_size < 0) {
    // Start with post_max_size.
    $max_size = $this->parse_size(ini_get('post_max_size'));

    // If upload_max_size is less, then reduce. Except if upload_max_size is
    // zero, which indicates no limit.
    $upload_max = $this->parse_size(ini_get('upload_max_filesize'));
    if ($upload_max > 0 && $upload_max < $max_size) {
      $max_size = $upload_max;
    }
      }
      return $max_size;
    }

    function parse_size($size) {
      $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
      $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
      if ($unit) {
        // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
      }
      else {
        return round($size);
      }
    }

    function baseUrl()
    {
      $http = 'http://';
      if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
        $http = 'https://';
      }
      $baseUrl =  $_SERVER['HTTP_HOST'];
      return $http . str_replace('//', '/', $baseUrl . $url);
    }

		function file_types($type) {
      $counter = 0;
      $types = array(
      // Image formats
      'image_'.$counter++ => 'image/jpeg',
      'image_'.$counter++ => 'image/gif',
      'image_'.$counter++ => 'image/png',
      'image_'.$counter++ => 'image/bmp',
      'image_'.$counter++ => 'image/tiff',
      'image_'.$counter++ => 'image/x-icon',
      // Video formats
      'video_'.$counter++ => 'video/x-ms-asf',
      'video_'.$counter++ => 'video/x-ms-wmv',
      'video_'.$counter++ => 'video/x-ms-wmx',
      'video_'.$counter++ => 'video/x-ms-wm',
      'video_'.$counter++ => 'video/avi',
      'video_'.$counter++ => 'video/divx',
      'video_'.$counter++ => 'video/x-flv',
      'video_'.$counter++ => 'video/quicktime',
      'video_'.$counter++ => 'video/mpeg',
      'video_'.$counter++ => 'video/mp4',
      'video_'.$counter++ => 'video/ogg',
      'video_'.$counter++ => 'video/webm',
      'video_'.$counter++ => 'video/x-matroska',
      // Text formats
      'text_'.$counter++ => 'text/plain',
			'code_'.$counter++ => 'application/octet-stream',
      'csv_'.$counter++ => 'text/csv',
      'text_'.$counter++ => 'text/tab-separated-values',
      'calander_'.$counter++ => 'text/calendar',
      'text_'.$counter++ => 'text/richtext',
      'code_'.$counter++ => 'text/css',
      'code_'.$counter++ => 'text/html',
      // Audio formats
      'audio_'.$counter++ => 'audio/mpeg',
      'audio_'.$counter++ => 'audio/x-realaudio',
      'audio_'.$counter++ => 'audio/wav',
      'audio_'.$counter++ => 'audio/amr',
       'audio_'.$counter++ => 'audio/mp3',
      'audio_'.$counter++ => 'audio/ogg',
      'audio_'.$counter++ => 'audio/midi',
      'audio_'.$counter++ => 'audio/x-ms-wma',
      'audio_'.$counter++ => 'audio/x-ms-wax',
      'audio_'.$counter++ => 'audio/x-matroska',
      // Misc application formats
      'file_'.$counter++ => 'application/rtf',
      'code_'.$counter++ => 'application/javascript',
      'pdf_'.$counter++ => 'application/pdf',
      'file_'.$counter++ => 'application/x-shockwave-flash',
      'file_'.$counter++ => 'application/java',
      'archive_'.$counter++ => 'application/x-tar',
      'archive_'.$counter++ => 'application/zip',
      'archive_'.$counter++ => 'application/x-gzip',
      'archive_'.$counter++ => 'application/rar',
      'file_'.$counter++ => 'application/x-7z-compressed',
      'exe_'.$counter++ => 'application/x-msdownload',
      // MS Office formats
      'document_'.$counter++ => 'application/msword',
      'document_'.$counter++ => 'application/vnd.ms-powerpoint',
      'document_'.$counter++ => 'application/vnd.ms-write',
      'document_'.$counter++ => 'application/vnd.ms-excel',
      'document_'.$counter++ => 'application/vnd.ms-access',
      'document_'.$counter++ => 'application/vnd.ms-project',
      'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'document_'.$counter++ => 'application/vnd.ms-word.document.macroEnabled.12',
      'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
      'document_'.$counter++ => 'application/vnd.ms-word.template.macroEnabled.12',
      'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'document_'.$counter++ => 'application/vnd.ms-excel.sheet.macroEnabled.12',
      'document_'.$counter++ => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
      'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
      'document_'.$counter++ => 'application/vnd.ms-excel.template.macroEnabled.12',
      'document_'.$counter++ => 'application/vnd.ms-excel.addin.macroEnabled.12',
      'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
      'document_'.$counter++ => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
      'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
      'document_'.$counter++ => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
      'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.presentationml.template',
      'document_'.$counter++ => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
      'document_'.$counter++ => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
      'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
      'document_'.$counter++ => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
      'document_'.$counter++ => 'application/onenote',
      // OpenOffice formats
      'file_'.$counter++ => 'application/vnd.oasis.opendocument.text',
      'file_'.$counter++ => 'application/vnd.oasis.opendocument.presentation',
      'file_'.$counter++ => 'application/vnd.oasis.opendocument.spreadsheet',
      'file_'.$counter++ => 'application/vnd.oasis.opendocument.graphics',
      'file_'.$counter++ => 'application/vnd.oasis.opendocument.chart',
      'file_'.$counter++ => 'application/vnd.oasis.opendocument.database',
      'file_'.$counter++ => 'application/vnd.oasis.opendocument.formula',
      // WordPerfect formats
      'file_'.$counter++ => 'application/wordperfect',
      // iWork formats
      'file_'.$counter++ => 'application/vnd.apple.keynote',
      'file_'.$counter++ => 'application/vnd.apple.numbers',
      'file_'.$counter++ => 'application/vnd.apple.pages',
      );
      if(false !== $key = array_search($type, $types)){
        return $key;
      }else{
        return "";
      }

		}
}
