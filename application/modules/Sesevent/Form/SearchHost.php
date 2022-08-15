<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: SearchHost.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_SearchHost extends Engine_Form {
  public function init() {
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $content_table = Engine_Api::_()->getDbtable('content', 'core');
    $params = $content_table->select()
            ->from($content_table->info('name'), array('params'))
            ->where('name = ?', 'sesevent.host-browse-search')
            ->query()
            ->fetchColumn();
    $params = Zend_Json_Decoder::decode($params);
		if(isset($params['search_type'])){
			$defaultOptions = 	$params['search_type'];
		}else{
			$defaultOptions = array("creationSPdate" ,"featured","sponsored","viewSPcount","favouriteSPcount","verified",'mostSPevent');
		}
		if(isset($params['default_search_type'])){
			$defaultSelOptions = 	$params['default_search_type'];
		}else{
			$defaultSelOptions = array("creationSPdate");
		}
		if(!isset($params['searchOptionsType'])){
			$params['searchOptionsType'] = 	array('searchBox','show');
		}
    $this->setAttribs(array(
                'id' => 'filter_form',
                'class' => 'global_form_box',
            ))
            ->setMethod('GET');
    $this->setAction($view->url(array('module' => 'sesevent', 'controller' => 'index', 'action' => 'browse-host'), 'sesevent_general', true));
    parent::init();
    if (!empty($params['searchOptionsType']) && in_array('searchBox', $params['searchOptionsType'])) {
      $this->addElement('Text', 'title_name', array(
          'label' => 'Search Host',
          'placeholder' => 'Enter Host Name',
      ));
    }
    if (!empty($params['searchOptionsType']) && in_array('show', $params['searchOptionsType'])) {
			if(count($defaultOptions)){
				$optnOption=array("0"=>"");
				foreach($defaultOptions as $optn){
					$optnVal = str_replace('SP','_',$optn);
					$optnOption[$optnVal] = ucwords(str_replace('SP',' ',$optn));
				}
				$value = str_replace('SP','_',$defaultSelOptions);
				$this->addElement('Select', 'popularity', array(
						'label' => 'List By',
						'multiOptions' =>$optnOption,
						'value' =>$value,
				));
			}
    }
    //Element: execute
    $this->addElement('Button', 'execute', array(
        'label' => 'Search',
        'type' => 'submit',
        'ignore' => true
    ));
		$this->addElement('Dummy','loading-img-sesevent-host', array(
        'content' => '<img src="application/modules/Core/externals/images/loading.gif" alt="Loading" />',
   ));
  }

}