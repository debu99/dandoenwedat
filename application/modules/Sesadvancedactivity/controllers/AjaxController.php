<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AjaxController.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_AjaxController extends Core_Controller_Action_Standard
{
  public function facebookpostpreviewAction() {

    $this->_helper->contextSwitch->addActionContext('create', 'json')->addActionContext('preview', 'json')->initContext();
    if( !$this->_helper->requireUser()->isValid())
      return;
    $uri = $this->_getParam('uri');
    $this->view->url = $uri;
  }

  public function previewAction(){
     $this->_helper->contextSwitch
      ->addActionContext('create', 'json')
      ->addActionContext('preview', 'json')
      ->initContext();
     if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireAuth()->setAuthParams('core_link', null, 'create')->isValid() ) return;

    // clean URL for html code
    $uri = trim(strip_tags($this->_getParam('uri')));
    //$uri = $this->_getParam('uri');
    $info = parse_url($uri);
    $this->view->url = $uri;

    try
    {
      $client = new Zend_Http_Client($uri, array(
        'maxredirects' => 3,
        'timeout'      => 20,
      ));

      // Try to mimic the requesting user's UA
      $client->setHeaders(array(
        'User-Agent' => $_SERVER['HTTP_USER_AGENT'],
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'X-Powered-By' => 'Zend Framework'
      ));

      $response = $client->request();
      // Get DOM


      $this->view->isGif = false;
      $this->view->isIframe = false;
      $body = $response->getBody();
      $body = trim($body);
      if( preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getHeader('content-type'), $matches) ||
          preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getBody(), $matches) ) {
        $this->view->charset = $charset = trim($matches[1]);
      } else {
        $this->view->charset = $charset = 'UTF-8';
      }
      if( function_exists('mb_convert_encoding') ) {
        $body = mb_convert_encoding($body, 'HTML-ENTITIES', $charset);
      }
       if( class_exists('DOMDocument') ) {
        $dom = new Zend_Dom_Query($body);
      } else {
        $dom = null; // Maybe add b/c later
      }

       if($dom && $gifImage = $dom->queryXpath("//meta[@property='og:url']")){
        if($gifImage->current() && strpos($gifImage->current()->getAttribute('content'),'.gif') !== false){
         $this->view->isGif = true;
         $gifImageUrl = $dom->queryXpath("//meta[@property='og:image']");
         if(strpos($gifImageUrl->current()->getAttribute('content'),'.jpg') !== false)
          $image = $gifImageUrl->current()->getAttribute('content');
         else{
          $image = $gifImageUrl->current()->getAttribute('content');
         }
         $this->view->gifImageUrl = $image;
         $this->view->gifUrl = $gifImage->current()->getAttribute('content');
         $this->view->title = '';
         $this->view->description = '';
         $this->view->images = array();
         $this->view->imageCount = 0;
        }
      }
      $uploadedFile = '';
      if(strpos($uri, '.gif') !== false){
        $tmp_path = APPLICATION_PATH . '/temporary/link';

        if( !is_dir($tmp_path) && !mkdir($tmp_path, 0777, true) ) {
          throw new Sesadvancedactivity_Model_Exception('Unable to create tmp link folder : ' . $tmp_path);
        }
        $imgPath = $tmp_path.time().'.gif';
        $contentImage = imagepng(imagecreatefromstring(file_get_contents($uri)), $imgPath);;
        //$contentImage = file_put_contents($imgPath, file_get_contents($uri));
        $thumbnail = (string) @$imgPath;
        $thumbnail_parsed = @parse_url($thumbnail);

        $tmp_file = $tmp_path . '/' . md5($thumbnail);

        $src_fh = fopen($thumbnail, 'r');
        $tmp_fh = fopen($tmp_file, 'w');
        stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
        fclose($src_fh);
        fclose($tmp_fh);
        if( ($info = getimagesize($tmp_file)) && !empty($info[2]) ) {
          $ext = Engine_Image::image_type_to_extension($info[2]);
          $thumb_file = $tmp_path . '/thumb_'.md5($thumbnail) . '.'.$ext;

          $image = Engine_Image::factory();
          $image->open($tmp_file)
            ->autoRotate()
            ->resize(500, 500)
            ->write($thumb_file)
            ->destroy();

          $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
            'parent_type' => 'core_link',
            'parent_id' => '999999999999999'
          ));
          $uploadedFile = $thumbFileRow->map();
          @unlink($thumb_file);
          @unlink($imgPath);
        }
        $this->view->isGif = true;
        $this->view->gifImageUrl = $uploadedFile;
        $this->view->gifUrl = $uri;
        $this->view->title = '';
        $this->view->description = '';
        $this->view->images = array();
        $this->view->imageCount = 0;
      }else if(strpos($uri,'youtubevideo') !== false || strpos($uri,'vimeovideo') !== false || strpos($uri,'soundcloud') !== false || strpos($uri,'https://youtu.be/') !== false){

        $title = null;
        if( $dom ) {
          $titleList = $dom->query('title');
          if( count($titleList) > 0 ) {
            $title = trim($titleList->current()->textContent);
            $title = substr($title, 0, 255);
          }
        }
        $this->view->title = $title;

        $description = null;
        if( $dom ) {
          $descriptionList = $dom->queryXpath("//meta[@name='description']");
          // Why are they using caps? -_-
          if( count($descriptionList) == 0 ) {
            $descriptionList = $dom->queryXpath("//meta[@name='Description']");
          }
          // Try to get description which is set under og tag
          if( count($descriptionList) == 0 ) {
            $descriptionList = $dom->queryXpath("//meta[@property='og:description']");
          }
          if( count($descriptionList) > 0 ) {
            $description = trim($descriptionList->current()->getAttribute('content'));
            $description = substr($description, 0, 255);
          }
        }
       
        $this->view->description = $description;
        $this->view->isGif = false;
        $this->view->gifUrl = '';
        $parseUrl = parse_url($uri);
        $url = parse_str($parseUrl['query'],$array);
        if(strpos($uri,'https://youtu.be') !== false){
          $array['v'] = end(explode('/',$uri));
          $uri = 'youtubevideo';
        }


        if(strpos($uri,'youtubevideo') !== false ){
          $this->view->thumb = '<iframe width="100%" height="320" src="https://www.youtube.com/embed/'.$array["v"].'?'.(!empty($array['list']) ? 'list='.$array['list'] : '').'" frameborder="0" allowfullscreen></iframe>';
        }
        else if(strpos($uri,'soundcloud') !== false ){
          $this->view->thumb = '<iframe frameborder="no" width="100%" height="400" src="https://w.soundcloud.com/player/?visual=true&url='.$uri.'&show_artwork=true" scrolling="no"></iframe>';
        }
        else
          $this->view->thumb = '<iframe src="'.str_replace('vimeo.com','player.vimeo.com/video',$uri).'" width="100%" height="320" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        $this->view->imageCount = 0;
        $this->view->images = array();
        $this->view->isIframe = true;
      }

      if(!$this->view->isGif && !$this->view->isIframe){
        // Get content-type
        list($contentType) = explode(';', $response->getHeader('content-type'));
        $this->view->contentType = $contentType;

        // Prepare
        $this->view->isGif = false;
        $this->view->gifUrl = '';
        $this->view->title = null;
        $this->view->description = null;
        $this->view->thumb = null;
        $this->view->imageCount = 0;
        $this->view->images = array();

        // Handling based on content-type
        switch( strtolower($contentType) ) {

          // Images
          case 'image/gif':
          case 'image/jpeg':
          case 'image/jpg':
          case 'image/tif': // Might not work
          case 'image/xbm':
          case 'image/xpm':
          case 'image/png':
          case 'image/bmp': // Might not work
            $this->_previewImage($uri, $response);
            break;

          // HTML
          case '':
          case 'text/html':
            $this->_previewHtml($uri, $response);
            break;

          // Plain text
          case 'text/plain':
            $this->_previewText($uri, $response);
            break;

          // Unknown
          default:
            break;
        }
       }
    }

    catch( Exception $e )
    {
      throw $e;
      //$this->view->title = $uri;
      //$this->view->description = $uri;
      //$this->view->images = array();
      //$this->view->imageCount = 0;
    }

   }
   protected function _previewImage($uri, Zend_Http_Response $response)
   {
    $this->view->imageCount = 1;
    $this->view->images = array($uri);
  }

  protected function _previewText($uri, Zend_Http_Response $response)
  {
    $body = $response->getBody();
    if( preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getHeader('content-type'), $matches) ||
        preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getBody(), $matches) ) {
      $charset = trim($matches[1]);
    } else {
      $charset = 'UTF-8';
    }
//    if( function_exists('mb_convert_encoding') ) {
//      $body = mb_convert_encoding($body, 'HTML-ENTITIES', $charset);
//    }

    // Reduce whitespace
    $body = preg_replace('/[\n\r\t\v ]+/', ' ', $body);

    $this->view->title = substr($body, 0, 63);
    $this->view->description = substr($body, 0, 255);
  }

  protected function _previewHtml($uri, Zend_Http_Response $response)
  {
    $body = $response->getBody();
    $body = trim($body);
    if( preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getHeader('content-type'), $matches) ||
      preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getBody(), $matches) ) {
      $this->view->charset = $charset = trim($matches[1]);
    } else {
      $this->view->charset = $charset = 'UTF-8';
    }
    if( function_exists('mb_convert_encoding') ) {
      $body = mb_convert_encoding($body, 'HTML-ENTITIES', $charset);
    }
    // Get DOM
    if( class_exists('DOMDocument') ) {
      $dom = new Zend_Dom_Query($body);
    } else {
      $dom = null; // Maybe add b/c later
    }
    $title = null;
    if( $dom ) {
      $titleList = $dom->query('title');
      if( count($titleList) > 0 ) {
        $title = trim($titleList->current()->textContent);
      }
    }
    $this->view->title = $title;
    $description = null;
    if( $dom ) {
      $descriptionList = $dom->queryXpath("//meta[@name='description']");
      // Why are they using caps? -_-
      if( count($descriptionList) == 0 ) {
        $descriptionList = $dom->queryXpath("//meta[@name='Description']");
      }
      // Try to get description which is set under og tag
      if( count($descriptionList) == 0 ) {
        $descriptionList = $dom->queryXpath("//meta[@property='og:description']");
      }
      if( count($descriptionList) > 0 ) {
        $description = trim($descriptionList->current()->getAttribute('content'));
      }
    }
    $this->view->description = $description;
    $thumb = null;
    if( $dom ) {
      $thumbList = $dom->queryXpath("//link[@rel='image_src']");
      $attributeType = 'href';
      if(count($thumbList) == 0 ) {
        $thumbList = $dom->queryXpath("//meta[@property='og:image']");
        $attributeType = 'content';
      }
      if( count($thumbList) > 0 ) {
        $thumb = $thumbList->current()->getAttribute($attributeType);
      }
    }
    $this->view->thumb = $thumb;
    $medium = null;
    if( $dom ) {
      $mediumList = $dom->queryXpath("//meta[@name='medium']");
      if( count($mediumList) > 0 ) {
        $medium = $mediumList->current()->getAttribute('content');
      }
    }
    $this->view->medium = $medium;
    // Get baseUrl and baseHref to parse . paths
    $baseUrlInfo = parse_url($uri);
    $baseUrl = null;
    $baseHostUrl = null;
    $baseUrlScheme = $baseUrlInfo['scheme'];
    $baseUrlHost = $baseUrlInfo['host'];
    if( $dom ) {
      $baseUrlList = $dom->query('base');
      if( $baseUrlList && count($baseUrlList) > 0 && $baseUrlList->current()->getAttribute('href') ) {
        $baseUrl = $baseUrlList->current()->getAttribute('href');
        $baseUrlInfo = parse_url($baseUrl);
        if( !isset($baseUrlInfo['scheme']) || empty($baseUrlInfo['scheme']) ) {
          $baseUrlInfo['scheme'] = $baseUrlScheme;
        }
        if( !isset($baseUrlInfo['host']) || empty($baseUrlInfo['host']) ) {
          $baseUrlInfo['host'] = $baseUrlHost;
        }
        $baseHostUrl = $baseUrlInfo['scheme'] . '://' . $baseUrlInfo['host'] . '/';
      }
    }
    if( !$baseUrl ) {
      $baseHostUrl = $baseUrlInfo['scheme'] . '://' . $baseUrlInfo['host'] . '/';
      if( empty($baseUrlInfo['path']) ) {
        $baseUrl = $baseHostUrl;
      } else {
        $baseUrl = explode('/', $baseUrlInfo['path']);
        array_pop($baseUrl);
        $baseUrl = join('/', $baseUrl);
        $baseUrl = trim($baseUrl, '/');
        $baseUrl = $baseUrlInfo['scheme'] . '://' . $baseUrlInfo['host'] . '/' . $baseUrl . '/';
      }
    }
    $images = array();
    if( $thumb ) {
      $images[] = $thumb;
    }
    if( $dom ) {
      $imageQuery = $dom->query('img');
      foreach( $imageQuery as $image ) {
        $src = $image->getAttribute('src');
        // Ignore images that don't have a src
        if( !$src || false === ($srcInfo = @parse_url($src)) ) {
          continue;
        }
        $ext = ltrim(strrchr($src, '.'), '.');
        // Detect absolute url
        if( strpos($src, '/') === 0 ) {
          // If relative to root, add host
          $src = $baseHostUrl . ltrim($src, '/');
        } elseif( strpos($src, './') === 0 ) {
          // If relative to current path, add baseUrl
          $src = $baseUrl . substr($src, 2);
        } elseif( !empty($srcInfo['scheme']) && !empty($srcInfo['host']) ) {
          // Contians host and scheme, do nothing
        } elseif( empty($srcInfo['scheme']) && empty($srcInfo['host']) ) {
          // if not contains scheme or host, add base
          $src = $baseUrl . ltrim($src, '/');
        } elseif( empty($srcInfo['scheme']) && !empty($srcInfo['host']) ) {
          // if contains host, but not scheme, add scheme?
          $src = $baseUrlInfo['scheme'] . ltrim($src, '/');
        } else {
          // Just add base
          $src = $baseUrl . ltrim($src, '/');
        }

        if( !in_array($src, $images) ) {
          $images[] = $src;
        }
      }
    }
    // Unique
    $images = array_values(array_unique($images));
    // Truncate if greater than 20
    if( count($images) > 30 ) {
      array_splice($images, 30, count($images));
    }
    $this->view->imageCount = count($images);
    $this->view->images = $images;
  }
  public function emojiAction(){
     $this->view->edit = $this->_getParam('edit',false);
    $this->renderScript('_emoji.tpl');
  }

  public function commentLikesAction() {

    $this->view->resource_id = $resource_id = $this->_getParam('id');
    $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax_content',false);
    $this->view->comment_id = $comment_id = $this->_getParam('comment_id');

    $action = Engine_Api::_()->getDbtable('actions', 'sesadvancedactivity')->getActionById($resource_id);
    $resource = $action->likes(true);

    if($resource->getType() == 'activity_action') {
        $resource_type = 'activity_comment';
        $table = Engine_Api::_()->getItemTable('core_like');
        $liketable = Engine_Api::_()->getItemTable('sesadvancedactivity_corelike')->info('name');
    } else {
        $resource_type = 'core_comment';
        $table = Engine_Api::_()->getItemTable('core_like');
        $liketable = Engine_Api::_()->getItemTable('sesadvancedactivity_corelike')->info('name');
    }
    $tableName = $table->info('name');

    $this->view->title = $this->view->translate('People Who Like This');
    $this->view->page = $page = $this->_getParam('page',1);

    $select = $table->select()
                    ->from($tableName,'*')
                    ->setIntegrityCheck(false);
    if($resource_type == 'activity_action') {
        $select->joinLeft($liketable, $liketable.'.activity_like_id ='.$table->info('name').'.like_id', 'type');
    } else {
        $select->joinLeft($liketable, $liketable.'.core_like_id ='.$table->info('name').'.like_id', 'type');
    }
    $select->where('resource_id =?',$comment_id);

    if($resource_type != 'activity_action')
      $select->where('resource_type =?',$resource_type);


    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    $users = array();
    $this->view->users =  $paginator; 
    if($is_ajax){
      echo $this->view->partial(
        '_contentlikesuser.tpl',
        'sesadvancedactivity',
        array('users'=>$this->view->users,'paginator'=>$this->view->paginator,'randonNumber'=>'contentlikeusers','resource_id'=>$this->view->resource_id,'resource_type'=>$resource_type,'execute'=>true,'page'=>$this->view->page)
      );die;
    }
  }

  public function tagPeopleAction(){
		$this->view->resource_id = $resource_id = $this->_getParam('action_id');
    $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax_content',false);
    $action = Engine_Api::_()->getDbtable('actions', 'sesadvancedactivity')->getActionById($resource_id);
    $select = Engine_Api::_()->getDbTable('tagusers','sesadvancedactivity')->getActionMembers($action->getIdentity());
    $resource_type = 'activity_action';
    $this->view->title = $this->view->translate('People Tagged');
    $this->view->page = $page = $this->_getParam('page',1);

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    $users = array();
    foreach( $paginator as $data )
    {

        $users[] = $data['user_id'];

    }
    $users = array_values(array_unique($users));
    $this->view->users =  Engine_Api::_()->getItemMulti('user', $users);
    if($is_ajax){
      echo $this->view->partial(
            '_contentlikesuser.tpl',
            'sesadvancedactivity',
            array('users'=>$this->view->users,'paginator'=>$this->view->paginator,'randonNumber'=>'contentlikeusers','resource_id'=>$this->view->resource_id,'resource_type'=>$resource_type,'execute'=>true,'page'=>$this->view->page)
          );die;

    }

  }
  public function groupFeedAction(){
		$this->view->resource_id = $resource_id = $this->_getParam('action_id',$this->_getParam('resource_id',''));
    $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax_content',false);

    $resource_type = 'activity_action';
    $this->view->title = $this->view->translate('People');
    $this->view->page = $page = $this->_getParam('page',1);
    $table = Engine_Api::_()->getDbTable('actions','sesadvancedactivity');
    $select = $table->select()->where('action_id IN('.$resource_id.')');
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    $users = array();
    foreach( $paginator as $data )
    {
        $users[] = $data['subject_id'];
    }
    $users = array_values(array_unique($users));
    $this->view->users =  Engine_Api::_()->getItemMulti('user', $users);
    if($is_ajax){
      echo $this->view->partial(
            '_contentlikesuser.tpl',
            'sesadvancedactivity',
            array('users'=>$this->view->users,'paginator'=>$this->view->paginator,'randonNumber'=>'contentgroupfeed','resource_id'=>$this->view->resource_id,'resource_type'=>$resource_type,'execute'=>true,'page'=>$this->view->page)
          );die;

    }
  }
  public function likesAction(){

    $this->view->resource_id = $resource_id = $this->_getParam('id');
    $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax_content',false);
    $this->view->resource_type = $resource_type = $this->_getParam('resource_type');
    $this->view->typeSelected = $typeSelected = $this->_getParam('type','all');
    $this->view->item_id = $item_id = $this->_getParam('item_id',false);
    if(!$typeSelected)
      $this->view->typeSelected = $typeSelected  = 'all';
    if($resource_type == 'activity_action') {
      $table = Engine_Api::_()->getItemTable('activity_like');
      $liketable = Engine_Api::_()->getItemTable('sesadvancedactivity_activitylike')->info('name');
    } else {
      $table = Engine_Api::_()->getItemTable('core_like');
      $liketable = Engine_Api::_()->getItemTable('sesadvancedactivity_corelike')->info('name');
    }
    $this->view->page = $page = $this->_getParam('page',1);

    $select = $table->select()
                    ->from($table->info('name'),'*')
                    ->setIntegrityCheck(false);
    if($resource_type == 'activity_action') {
        $select->joinLeft($liketable, $liketable.'.activity_like_id ='.$table->info('name').'.like_id', 'type');
    } else {
        $select->joinLeft($liketable, $liketable.'.core_like_id ='.$table->info('name').'.like_id', 'type');
    }
    $select->where($table->info('name').'.resource_id =?',$item_id);

    if($resource_type != 'activity_action')
      $select->where($table->info('name').'.resource_type =?',$resource_type);

    if($typeSelected != 'all')
      $select->where($liketable.'.type =?',$typeSelected);

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    $users = array();
    $type = array();
    foreach( $paginator as $data )
    {
      $type[$data['poster_id'].'_'.$data['poster_type']] = $data['type'];
      /*if( $data['poster_type'] == 'user' )
      {
        $users[] = $data['poster_id'];
      }*/
    }
    //$users = array_values(array_unique($users));
    $this->view->type = $type;
    if(!$is_ajax){
      $this->view->action = $action =  Engine_Api::_()->getDbtable('actions', 'sesadvancedactivity')->getActionById($resource_id);
      $AllTypesCount = Engine_Api::_()->sesadvancedcomment()->likesGroup($action);
      $this->view->AllTypesCount = $AllTypesCount['data'];
      $countAllLikes = 0;
      $typesLikeData = array('all'=>'all');
      foreach($this->view->AllTypesCount as $countlikes){
        $typesLikeData[$countlikes['type']] = $countlikes['type'];
       $countAllLikes = $countAllLikes+ $countlikes['counts'];
      }

      $this->view->typesLikeData = $typesLikeData;
      $this->view->countAll =$countAllLikes;
    }

    $this->view->users =  $paginator;

    if($is_ajax){
      echo $this->view->partial(
            '_reactionlikesuser.tpl',
            'sesadvancedactivity',
            array('users'=>$this->view->users,'paginator'=>$this->view->paginator,'randonNumber'=>$this->view->typeSelected,'resource_id'=>$this->view->resource_id,'resource_type'=>$this->view->resource_type,'typeSelected'=>$this->view->typeSelected,'execute'=>true,'page'=>$this->view->page,'type'=>$this->view->type,'item_id'=>$item_id)
          );die;

    }
	}
  public function feedAction()
  {
    // Get config options for activity
    $config = array(
      'action_id' => (int) $this->_getParam('action_id'),
      'max_id' => (int) $this->_getParam('maxid'),
      'min_id' => (int) $this->_getParam('minid'),
      'limit' => (int) $this->_getParam('limit'),
    );

    $viewer = Engine_Api::_()->user()->getViewer();

    if( !isset($subject) && Engine_Api::_()->core()->hasSubject() ) {
      $subject = Engine_Api::_()->core()->getSubject();
    }

    if( !empty($subject) ) {
      $activity = Engine_Api::_()->getDbtable('actions', 'sesadvancedactivity')->getActivityAbout($subject, $viewer, $config);
      $this->view->subjectGuid = $subject->getGuid(false);
    } else {
      $activity = Engine_Api::_()->getDbtable('actions', 'sesadvancedactivity')->getActivity($viewer, $config);
      $this->view->subjectGuid = null;
    }

    $feed = array();
    foreach( $activity as $action ) {
      $attachments = array();
      if( $action->attachment_count > 0 ) {
        foreach( $action->getAttachments() as $attachment ) {
          $attachments[] = array(
            'meta' => $attachment->meta->toArray(),
            'item' => $attachment->item->toRemoteArray(),
          );
        }
      }
      $feed[] = array(
        'typeinfo' => $action->getTypeInfo()->toArray(),
        'action' => $action->toArray(),
        'subject' => $action->getSubject()->toRemoteArray(),
        'object' => $action->getObject()->toRemoteArray(),
        'attachments' => $attachments
      );
    }
    $this->view->feed = $feed;
  }
  /*function to set your files*/
  function downloadAction()
  {
      $storage_id = $this->_getParam('storage_id','');
      $storage = Engine_Api::_()->getItem('storage_file',$storage_id);

      if(!$storage_id || Engine_Api::_()->user()->getViewer()->getIdentity() == 0 || !$storage)
        return $this->_forward('notfound', 'error', 'core');

      $fileOrg = $storage->map();
      $name = $storage->name;
      $mime_type = $storage->mime_major.'/'.$storage->mime_minor;
      echo $body = $storage->temporary();die;
      set_time_limit(0);

      header('Content-type: ' . $mime_type);
      echo $body;
      $file = file_get_contents($fileOrg);
      //if(!is_readable($file)) die('File not found or inaccessible!');
      $fileName = time().'_sesalbum';
      $fileOrg = current(explode('?',$fileOrg));
			$PhotoExtension='.'.pathinfo($fileOrg, PATHINFO_EXTENSION);
			$filenameInsert=$fileName.$PhotoExtension;
			$copySuccess=@copy($fileOrg, APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary/'.$filenameInsert);
      $name = rawurldecode($name);
      @ob_end_clean();
      $file =  APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary/'.$filenameInsert;
       $size = filesize($file);


      if(ini_get('zlib.output_compression'))
      ini_set('zlib.output_compression', 'Off');
      header('Content-Type: ' . $mime_type);
      header('Content-Disposition: attachment; filename="'.$name.'"');
      header("Content-Transfer-Encoding: binary");
      header('Accept-Ranges: bytes');

      if(isset($_SERVER['HTTP_RANGE']))
      {
          list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
          list($range) = explode(",",$range,2);
          list($range, $range_end) = explode("-", $range);
          $range=intval($range);
          if(!$range_end) {
              $range_end=$size-1;
          } else {
              $range_end=intval($range_end);
          }

          $new_length = $range_end-$range+1;
          header("HTTP/1.1 206 Partial Content");
          header("Content-Length: $new_length");
          header("Content-Range: bytes $range-$range_end/$size");
      } else {
          $new_length=$size;
          header("Content-Length: ".$size);
      }

      $chunksize = 1*(1024*1024);
      $bytes_send = 0;
      if ($file = fopen($file, 'r'))
      {
          if(isset($_SERVER['HTTP_RANGE']))
          fseek($file, $range);

          while(!feof($file) &&
              (!connection_aborted()) &&
              ($bytes_send<$new_length)
          )
          {
              $buffer = fread($file, $chunksize);
              echo($buffer);
              flush();
              $bytes_send += strlen($buffer);
          }
          @unlink($file);
          fclose($file);
      } else
          die('Error - can not open file.');

      die();
  }
  public function feedBuySellAction(){
    $this->view->action_id = $action_id = $this->_getParam('action_id',false);
    $this->view->photo_id = $this->_getParam('photo_id',false);
    $this->view->action = Engine_Api::_()->getItem('sesadvancedactivity_action',$action_id);
    $this->view->item = $this->view->action->getBuySellItem();
    $this->view->main_action = Engine_Api::_()->getItem('sesadvancedactivity_action',$this->_getParam('main_action'));
  }
  public function messageAction(){
    $this->view->action_id = $action_id = $this->_getParam('action_id',false);
    $this->view->action = Engine_Api::_()->getItem('sesadvancedactivity_action',$action_id);
    $this->view->item = $this->view->action->getBuySellItem();
    // Make form
    $this->view->form = $form = new Sesadvancedactivity_Form_Message();

    // Not post
    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Not a post');
      return;
    }

    // Not valid
    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    // Start transaction
    $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
    $db->beginTransaction();

    try
    {
      $values = $form->getValues();
      $recipientsUser = Engine_Api::_()->getItem('user',$this->view->action->subject_id);
      $recipients = $recipientsUser->getIdentity();
      $viewer = Engine_Api::_()->user()->getViewer();
      // Create conversation
      $body = $this->view->partial('ajax/message.tpl','sesadvancedactivity',array('isajax'=>true,'action'=>$this->view->action,'item'=>$this->view->item));
     $body = $values['body'].'<br><br>'.$body;
      $conversation = Engine_Api::_()->getItemTable('messages_conversation')->send(
        $viewer,
        $recipients,
        $values['title'],
        $body,
        $attachment
      );


      Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
        $recipientsUser,
        $viewer,
        $conversation,
        'message_new'
      );
      $db->commit();
       return $this->_forward('success', 'utility', 'core', array(
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your message has been sent successfully.')),
        'smoothboxClose' => true,
      ));

    }catch(Exception $e){
        $db->rollBack();
      throw $e;
    }
  }
  public function buysellsoldAction(){
    $action_id = $this->_getParam('action_id');
    $action = Engine_Api::_()->getItem('sesadvancedactivity_action',$action_id);
    $item = $action->getBuySellItem();
    $item->is_sold = 1;
    $item->save();
    echo true;die;
  }
  public function savefeedAction(){
    $actionid = $this->_getParam('action_id',false);
    if(!$actionid){
      echo false;die;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $isSaved = Engine_Api::_()->getDbTable('savefeeds','sesadvancedactivity')->isSaved(array('action_id'=>$actionid,'user_id'=>$viewer->getIdentity()));
    if($isSaved){
      $isSaved->delete();
      echo json_encode(array('status'=>1,'issaved'=>0));die;
    }else{
      $db = Engine_Db_Table::getDefaultAdapter();
      $data = array(
          'action_id'      => $actionid,
          'user_id' => $viewer->getIdentity(),
      );
     $db->insert('engine4_sesadvancedactivity_savefeeds', $data);
     echo json_encode(array('status'=>1,'issaved'=>1));die;
    }
  }
  public function commentableAction(){
    $action_id = $this->getParam('action_id',false);

    if(!$action_id)
    { echo false;die;}

    $action = Engine_Api::_()->getItem('sesadvancedactivity_action',$action_id);

    $detailTable = Engine_Api::_()->getDbTable('details', 'sesadvancedactivity');
    $detail_id = Engine_Api::_()->getDbTable('details', 'sesadvancedactivity')->isRowExists($action_id);
    if($detail_id) {
      $detailAction = Engine_Api::_()->getItem('sesadvancedactivity_detail',$detail_id);
      $detailAction->commentable = !$detailAction->commentable;
      $detailAction->save();
    }
    $feed = $this->view->activity($action,array('ulInclude'=>true));
    echo json_encode(array('status'=> 1,'action_id'=>$action_id,'feed'=>$feed),JSON_HEX_QUOT | JSON_HEX_TAG);die;
  }
  public function unhidefeedAction(){
      $action_id = $this->_getParam('action_id',false);
       $type =  $this->_getParam('type','post');
      $viewer = Engine_Api::_()->user()->getViewer();
       $db = Engine_Db_Table::getDefaultAdapter();
       $db->delete('engine4_sesadvancedactivity_hides', array(
          'resource_id =?'      => $action_id,
          'resource_type =?'    => $type,
          'user_id =?' => $viewer->getIdentity(),
      ));
      echo true;die;
  }
  public function hidefeedAction(){
    $action_id = $this->_getParam('action_id',false);
    $subject_id = $this->_getParam('subject_id',false);
    $remove = $this->getParam('remove',false);
    $type =  $this->_getParam('type','post');
    $viewer = Engine_Api::_()->user()->getViewer();
     $db = Engine_Db_Table::getDefaultAdapter();
    if(!$action_id)
    { echo false;die;}
    if($type != 'user'){
      $resource_id = $action_id;
      $id = Engine_Api::_()->getItem('sesadvancedactivity_action',$action_id)->getSubject()->getIdentity();
      $db->delete('engine4_sesadvancedactivity_hides', array(
          'resource_id =?'      => $id,
          'resource_type =?'    => 'user',
          'user_id =?' => $viewer->getIdentity(),
      ));
    }
    else{
      $resource_id = Engine_Api::_()->getItem('sesadvancedactivity_action',$action_id)->getSubject()->getIdentity();
      $db->delete('engine4_sesadvancedactivity_hides', array(
          'resource_id =?'      => $action_id,
          'resource_type =?'    => 'post',
          'user_id =?' => $viewer->getIdentity(),
      ));
    }


    if(!$remove){
     $data = array(
          'resource_id'      => $resource_id,
          'subject_id'      => $subject_id,
          'resource_type'    => $type,
          'user_id' => $viewer->getIdentity(),
      );
    $db->insert('engine4_sesadvancedactivity_hides', $data);
    }else{
      $db->delete('engine4_sesadvancedactivity_hides', array(
          'resource_id =?'      => $resource_id,
          'resource_type =?'    => $type,
          'user_id =?' => $viewer->getIdentity(),
      ));
    }
    $lists = $this->_getParam('lists',false);
      $users = array();
    if($lists){
      $lists = explode(',',$lists);
      foreach($lists as $list){
       $action = Engine_Api::_()->getItem('sesadvancedactivity_action',$list);
       if($action->getSubject()->getIdentity() == $resource_id)
        $users[] = $list;
      }
    }
    echo json_encode(array('list'=>$users));;die;
  }
  public function settingsAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
     $this->view->resource_id = $resource_id = $viewer->getIdentity();
    $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax_content',false);
    $table = Engine_Api::_()->getDbTable('hides','sesadvancedactivity');
    $this->view->title = $this->view->translate('See whose activity feeds you have hidden');
    $this->view->page = $page = $this->_getParam('page',1);
    $select = $table->select()->where('user_id =?',$resource_id);
    $select->where('resource_type =?','post');
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    $users = array();
    foreach( $paginator as $data )
    {
      $users[] = $data['resource_id']; 
    }
    $users = array_values(array_unique($users)); 
    $this->view->users =  Engine_Api::_()->getItemMulti('sesadvancedactivity_action', $users);
    if($is_ajax){
      echo $this->view->partial(
            '_contentlikesuser.tpl',
            'sesadvancedactivity',
            array('users'=>$this->view->users,'paginator'=>$this->view->paginator,'randonNumber'=>'contentlikeusers','resource_id'=>$this->view->resource_id,'resource_type'=>$resource_type,'execute'=>true,'page'=>$this->view->page)
          );die;

    }
  }
  public function settingremoveAction(){
    $users = $this->_getParam('user',false);
    if(!$users){
      echo false;die;
    }
     $user = explode(',',ltrim($users,','));
     $viewer = Engine_Api::_()->user()->getViewer();
     $db = Engine_Db_Table::getDefaultAdapter();
     foreach(array_filter($user) as $us){
      $db->delete('engine4_sesadvancedactivity_hides', array(
            'subject_id =?'      => $us,
            'resource_type =?'    => 'post',
            'user_id =?' => $viewer->getIdentity(),
        ));
     }
     echo true;die;
  }
  public function friendsAction(){

    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      $data = null;
    } else {
      $data = array();
      $table = Engine_Api::_()->getItemTable('user');
      $select = Engine_Api::_()->user()->getViewer()->membership()->getMembersObjectSelect();
      $subtableName = 'engine4_user_membership';
      $select = $table->select();

      if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sespage')){
        $select->from($table->info('name'));
      }
      $select
                ->joinRight($subtableName, '`'.$subtableName.'`.`user_id` = `'.$table->info('name').'`.`user_id`', null)
                ->where('`'.$subtableName.'`.`resource_id` = ?', $this->view->viewer()->getIdentity());

      $select->where('`'.$subtableName.'`.`active` = ?', 1);

      if( 0 < ($limit = (int) $this->_getParam('limit', 10)) ) {
        $select->limit($limit);
      }
      if( null !== ($text = $this->_getParam('query', '')) ) {
        $select->where('`'.$table->info('name').'`.`displayname` LIKE ?', '%'. $text .'%');
      }
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sespage') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesgroup') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesbusiness')){
        $select->from($table->info('name'),array('user_id as item_id',new Zend_Db_Expr('"user" AS item_type')));
        $sqls = "";
        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sespage') ){
          $pageTableName = Engine_Api::_()->getItemTable('sespage_page')->info('name');
          $sql2 = Engine_Api::_()->getItemTable('sespage_page')->select()->from($pageTableName,array('page_id as item_id',new Zend_Db_Expr('"sespage_page" AS item_type')))->where('search =?',1)->where('draft =?',1)->where('other_tag =?',1);
          if( null !== ($text = $this->_getParam('query', '')) ) {
            $sql2->where('`'.$pageTableName.'`.`title` LIKE "%'.$text.'%" || `'.$pageTableName.'`.`custom_url` LIKE "%'.str_replace('@','',$text).'%"');
            $sql2->limit($limit);
          }
          $sqls .= 'UNION ('.$sql2.') ';
        }
        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesgroup') ){
          $pageTableName = Engine_Api::_()->getItemTable('sesgroup_group')->info('name');
          $sql2 = Engine_Api::_()->getItemTable('sesgroup_group')->select()->from($pageTableName,array('group_id as item_id',new Zend_Db_Expr('"sesgroup_group" AS item_type')))->where('search =?',1)->where('draft =?',1)->where('other_tag =?',1);
          if( null !== ($text = $this->_getParam('query', '')) ) {
            $sql2->where('`'.$pageTableName.'`.`title` LIKE "%'.$text.'%" || `'.$pageTableName.'`.`custom_url` LIKE "%'.str_replace('@','',$text).'%"');
            $sql2->limit($limit);
          }
          $sqls .= 'UNION ('.$sql2.') ';
        }
        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesbusiness') ){
          $pageTableName = Engine_Api::_()->getItemTable('businesses')->info('name');
          $sql2 = Engine_Api::_()->getItemTable('businesses')->select()->from($pageTableName,array('business_id as item_id',new Zend_Db_Expr('"businesses" AS item_type')))->where('search =?',1)->where('draft =?',1)->where('other_tag =?',1);
          if( null !== ($text = $this->_getParam('query', '')) ) {
            $sql2->where('`'.$pageTableName.'`.`title` LIKE "%'.$text.'%" || `'.$pageTableName.'`.`custom_url` LIKE "%'.str_replace('@','',$text).'%"');
            $sql2->limit($limit);
          }
          $sqls .= 'UNION ('.$sql2.') ';
        }
        $selectUnion = '('.$select.') '.$sqls;
          $db = Engine_Db_Table::getDefaultAdapter();
        foreach( $db->fetchAll($selectUnion) as $friend ) {
          $item = Engine_Api::_()->getItem($friend['item_type'],$friend['item_id']);
        $data[] = array(
          'type'  => 'user',
          'id'    => $item->getGuid().' ',
          'name' => $item->getTitle(),
          'avatar' => $this->view->itemPhoto($item, 'thumb.icon'),
        );
      }

      }else{
        foreach( $table->fetchAll($select) as $friend ) {
          $data[] = array(
            'type'  => 'user',
            'id'    => $friend->getIdentity().' ',
            'name' => $friend->getTitle(),
            'avatar' => $this->view->itemPhoto($friend, 'thumb.icon'),
          );
        }
      }


    }
      return $this->_helper->json($data);
  }
}
