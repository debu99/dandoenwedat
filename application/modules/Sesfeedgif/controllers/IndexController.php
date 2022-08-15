<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeedgif
 * @package    Sesfeedgif
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: IndexController.php  2017-12-06 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeedgif_IndexController extends Core_Controller_Action_Standard
{
  public function gifAction() {
    $this->view->edit = $this->_getParam('edit',false);
    $this->renderScript('_gif.tpl');
  }
  
  public function searchGifAction() {
  
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $text = $this->_getParam('text','ha');
    $this->view->is_ajax = $this->_getParam('is_ajax', 1);
    $this->view->searchvalue = $this->_getParam('searchvalue', 0);
    $paginator = $this->view->paginator = Engine_Api::_()->getDbTable('images', 'sesfeedgif')->searchGif($text);
		$paginator->setItemCountPerPage(10);
		$this->view->page = $page ;
		$paginator->setCurrentPageNumber($page);
  }
}
