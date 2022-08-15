<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Album.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Album extends Engine_Form
{
  public function init()
  {
    $user_level = Engine_Api::_()->user()->getViewer()->level_id;
    $user = Engine_Api::_()->user()->getViewer();
    // Init form
    $this
      ->setTitle('Add New Photos')
      ->setDescription('Choose photos on your computer to add to this album.')
      ->setAttrib('id', 'form-upload')
      ->setAttrib('name', 'albums_create')
      ->setAttrib('enctype','multipart/form-data')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;
    // Init album
    $eventId = Zend_Controller_Front::getInstance()->getRequest()->getParam('event_id', null);
		$albumId = Zend_Controller_Front::getInstance()->getRequest()->getParam('album_id', null);
		if($albumId){
			$eventId = Engine_Api::_()->getItem('sesevent_album', $albumId)->event_id;	
			
		}
    $albumTable = Engine_Api::_()->getItemTable('sesevent_album');
    $myAlbums = $albumTable->select()
        ->from($albumTable, array('album_id', 'title'))
        ->where('event_id = ?', $eventId)
        ->query()
        ->fetchAll();
    $albumOptions = array('0' => 'Create A New Album');
    foreach( $myAlbums as $myAlbum ) {
      $albumOptions[$myAlbum['album_id']] = $myAlbum['title'];
    }
    $this->addElement('Select', 'album', array(
      'label' => 'Choose Album',
      'multiOptions' => $albumOptions,
      'onchange' => "updateTextFields()",
    ));
    // Init name
    $this->addElement('Text', 'title', array(
      'label' => 'Album Title',
      'maxlength' => '255',
      'filters' => array(
        //new Engine_Filter_HtmlSpecialChars(),
        'StripTags',
        new Engine_Filter_Censor(),
        new Engine_Filter_StringLength(array('max' => '63')),
      )
    ));
	
    // Init descriptions
    $this->addElement('Textarea', 'description', array(
      'label' => 'Album Description',
      'filters' => array(
        'StripTags',
        new Engine_Filter_Censor(),
        //new Engine_Filter_HtmlSpecialChars(),
        new Engine_Filter_EnableLinks(),
      ),
    ));
	 $restapi=Zend_Controller_Front::getInstance()->getRequest()->getParam( 'restApi', null );
    if ($restapi == 'Sesapi'){
       $this->addElement('file', 'album_photo', array(
         'label' => 'Album Photo',
       ));
       $this->album_photo->addValidator('Extension', false, 'jpg,png,gif,jpeg');
    }
    
    $translate = Zend_Registry::get('Zend_Translate');
    $this->addElement('Dummy', 'fancyuploadfileids', array('content'=>'<input id="fancyuploadfileids" name="file" type="hidden" value="" >'));
    
    $this->addElement('Dummy', 'tabs_form_albumcreate', array(
     'content' => '<div class="sesevent_create_form_tabs sesbasic_clearfix sesbm"><ul id="sesevent_create_form_tabs" class="sesbasic_clearfix"><li class="active sesbm"><i class="fas fa-arrows-alt sesbasic_text_light"></i><a href="javascript:;" class="drag_drop">'.$translate->translate('Drag & Drop').'</a></li><li class=" sesbm"><i class="fa fa-upload sesbasic_text_light"></i><a href="javascript:;" class="multi_upload">'.$translate->translate('Multi Upload').'</a></li><li class=" sesbm"><i class="fa fa-link sesbasic_text_light"></i><a href="javascript:;" class="from_url">'.$translate->translate('From URL').'</a></li></ul></div>',
    ));
    $this->addElement('Dummy', 'drag-drop', array(
      'content' => '<div id="dragandrophandler" class="sesevent_upload_dragdrop_content sesbasic_bxs">'.$translate->translate('Drag & Drop Photos Here').'</div>',
    ));
    $this->addElement('Dummy', 'from-url', array(
      'content' => '<div id="from-url" class="sesevent_upload_url_content sesbm"><input type="text" name="from_url" id="from_url_upload" value="" placeholder="'.$translate->translate('Enter Image URL to upload').'"><span id="loading_image"></span><span></span><button id="upload_from_url">'.$translate->translate('Upload').'</button></div>',
    ));	 
	
    $this->addElement('Dummy', 'file_multi', array('content'=>'<input type="file" accept="image/x-png,image/jpeg" onchange="readImageUrl(this)" multiple="multiple" id="file_multi" name="file_multi">'));
    $this->addElement('Dummy', 'uploadFileContainer', array('content'=>'<div id="show_photo_container" class="sesevent_upload_photos_container sesbasic_bxs sesbasic_custom_scroll clear"><div id="show_photo"></div></div>'));
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Photos',
      'type' => 'submit',
    ));
    
  }
  
  public function clearAlbum() {
    $this->getElement('album')->setValue(0);
  }
  
  public function saveValues() {
  
    $set_cover = false;
    $eventId = Zend_Controller_Front::getInstance()->getRequest()->getParam('event_id', null);
    $values = $this->getValues();
    $params = array();
    if ((empty($values['event_id'])) || (empty($values['user_id']))) {
      $params['owner_id'] = Engine_Api::_()->user()->getViewer()->user_id;
      $params['event_id'] = $eventId;
    }
    else {
      $params['owner_id'] = Engine_Api::_()->user()->getViewer()->user_id;
      $params['event_id'] = $eventId;
      throw new Zend_Exception("Non-user album owners not yet implemented");
    }
    if( ($values['album'] == 0) ) {
      $params['title'] = $values['title'];
      if (empty($params['title'])) {
        $params['title'] = "Event Profile Photos";
      }
      $params['description'] = $values['description'];
      $params['search'] = 1;
      $album = Engine_Api::_()->getDbtable('albums', 'sesevent')->createRow();
      $album->setFromArray($params);
      $album->save();
      $set_cover = true;
    }
    else {
      if (!isset($album)) {
        $album = Engine_Api::_()->getItem('sesevent_album', $values['album']);
      }
    }
	
    // Do other stuff
    $count = 0;
    if(isset($_POST['file'])) {
      $explodeFile = explode(' ',rtrim($_POST['file'],' '));
      foreach( $explodeFile as $photo_id ) {
	if($photo_id == '')
	continue;
	$photo = Engine_Api::_()->getItem("sesevent_photo", $photo_id);
	if( !($photo instanceof Core_Model_Item_Abstract) || !$photo->getIdentity() ) continue;
	if(isset($_POST['cover']) && $_POST['cover'] == $photo_id ){
	  $album->photo_id = $photo_id;
	  $album->save();
	  unset($_POST['cover']);
	  $set_cover = false;
	}
	else if( $set_cover){
	  $album->photo_id = $photo_id;
	  $album->save();
	  $set_cover = false;
	}
	$photo->album_id = $album->album_id;
	//$photo->order    = $photo_id;
	$photo->save();
	$count++;
      }
    }
    $album->save();
    return $album;
  }
}