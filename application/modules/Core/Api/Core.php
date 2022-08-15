<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Api_Core extends Core_Api_Abstract
{
    /**
     * @var Core_Model_Item_Abstract|mixed The object that represents the subject of the page
     */
    protected $_subject;

    /**
     * Set the object that represents the subject of the page
     *
     * @param Core_Model_Item_Abstract|mixed $subject
     * @return Core_Api_Core
     */
    public function setSubject($subject)
    {
        if( null !== $this->_subject ) {
            throw new Core_Model_Exception("The subject may not be set twice");
        }

        if( !($subject instanceof Core_Model_Item_Abstract) ) {
            throw new Core_Model_Exception("The subject must be an instance of Core_Model_Item_Abstract");
        }

        $this->_subject = $subject;
        return $this;
    }

    /**
     * Get the previously set subject of the page
     *
     * @return Core_Model_Item_Abstract|null
     */
    public function getSubject($type = null)
    {
        if( null === $this->_subject ) {
            throw new Core_Model_Exception("getSubject was called without first setting a subject.  Use hasSubject to check");
        } else if( is_string($type) && $type !== $this->_subject->getType() ) {
            throw new Core_Model_Exception("getSubject was given a type other than the set subject");
        } else if( is_array($type) && !in_array($this->_subject->getType(), $type) ) {
            throw new Core_Model_Exception("getSubject was given a type other than the set subject");
        }

        return $this->_subject;
    }

    /**
     * Checks if a subject has been set
     *
     * @return bool
     */
    public function hasSubject($type = null)
    {
        if( null === $this->_subject ) {
            return false;
        } else if( null === $type ) {
            return true;
        } else {
            return ( $type === $this->_subject->getType() );
        }
    }

    public function clearSubject()
    {
        $this->_subject = null;
        return $this;
    }

    public function getCaptchaOptions(array $params = array())
    {
        $spamSettings = Engine_Api::_()->getApi('settings', 'core')->core_spam;
        //echo "<pre>"; print_r($spamSettings);die;
        if( (empty($spamSettings['recaptchaenabled']) ||
            empty($spamSettings['recaptchapublic']) ||
            empty($spamSettings['recaptchaprivate'])) ) {
            // Image captcha
            return array_merge(array(
                'label' => 'Human Verification',
                'description' => 'Please type the characters you see in the image.',
                'captcha' => 'image',
                'required' =>false,
                'captchaOptions' => array(
                    'wordLen' => 6,
                    'fontSize' => '30',
                    'timeout' => 300,
                    'imgDir' => APPLICATION_PATH . '/public/temporary/',
                    'imgUrl' => Zend_Registry::get('Zend_View')->baseUrl() . '/public/temporary',
                    'font' => APPLICATION_PATH . '/application/modules/Core/externals/fonts/arial.ttf',
                ),
            ), $params);
        } else {
            // Recaptcha
            return array_merge(array(
                'label' => 'Human Verification',
                'captcha' => 'ReCaptcha2',
                'required' => true,
                'captchaOptions' => array(
                    'privkey' => $spamSettings['recaptchaprivate'],
                    'pubkey' => $spamSettings['recaptchapublic'],
                    'theme' => 'light',
                    'size' => (isset($params['size']) ? $params['size'] : 'normal' ),
                    'lang' => Zend_Registry::get('Locale')->getLanguage(),
                    'tabindex' => (isset($params['tabindex']) ? $params['tabindex'] : null ),
                    'ssl' => constant('_ENGINE_SSL'),   // Fixed Captcha does not work well when ssl is enabled on website
                    'onload' => 'en4CoreReCaptcha',
                    'render' => 'explicit',
                    'loadJs' => 'en4.core.reCaptcha.loadJs'
                ),
            ), $params);
        }
    }

    public function smileyToEmoticons($string = null)
    {
        $emoticonsTag = Engine_Api::_()->activity()->getEmoticons(true);
        if (empty($emoticonsTag)) {
            return $string;
        }

        $string = str_replace("&lt;:o)", "<:o)", $string);
        $string = str_replace("(&amp;)", "(&)", $string);

        return strtr($string, $emoticonsTag);
    }
    public  function floodCheckMessage($data = array(),$view){
        if(count($data)){
            $duration = $data[0];
            $type = $data[1];
            //$time = $duration.' '.($duration == 1 ? $type : $type."s");
            $time =  "1 ".$type;
            return $view->translate('You have reached maximum limit of posting in %s. Try again after this duration expires.',$time);
        }
        return "";
    }
    public function clearLogs() {

        $logfileSize = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.logfile.size', 50);

        if(file_exists(APPLICATION_PATH . '/temporary/log/main.log')) {
            $mainLogSize = filesize(APPLICATION_PATH . '/temporary/log/main.log');
            $mainLogSize = number_format($mainLogSize / 1048576, 2);
            if($logfileSize < $mainLogSize) {
                file_put_contents(APPLICATION_PATH . '/temporary/log/main.log', '');
            }
        }

        if(file_exists(APPLICATION_PATH . '/temporary/log/warnings.log')) {
            $warningLogSize = filesize(APPLICATION_PATH . '/temporary/log/warnings.log');
            $warningLogSize = number_format($warningLogSize / 1048576, 2);
            if($logfileSize < $mainLogSize) {
                file_put_contents(APPLICATION_PATH . '/temporary/log/warnings.log', '');
            }
        }
    }
    
  public function getFileUrl($image) {
    
    $table = Engine_Api::_()->getDbTable('files', 'core');
    $result = $table->select()
                ->from($table->info('name'), 'storage_file_id')
                ->where('storage_path =?', $image)
                ->query()
                ->fetchColumn();
    if(!empty($result)) {
      $storage = Engine_Api::_()->getItem('storage_file', $result);
      if($storage != null) return $storage->map();
      else return $image;
    } else {
      return $image;
    }
  }
}
