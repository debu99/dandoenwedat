<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Attachement.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_Api_Attachment extends Core_Api_Abstract { 
  public function onAttachLink($data)
  {
    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      if( Engine_Api::_()->sesapi()->hasSubject() ) {
        $subject = Engine_Api::_()->sesapi()->getSubject();
        if( $subject->getType() != 'user' ) {
          $data['parent_type'] = $subject->getType();
          $data['parent_id'] = $subject->getIdentity();
        }
      }

      // Filter HTML
      $filter = new Zend_Filter();
      $filter->addFilter(new Engine_Filter_Censor());
      $filter->addFilter(new Engine_Filter_HtmlSpecialChars());
      if( !empty($data['title']) ) {
        $data['title'] = $filter->filter($data['title']);
      }
      if( !empty($data['description']) ) {
        $data['description'] = $filter->filter($data['description']);
      }
      
      $link = $this->createLink($viewer, $data);
    } catch( Exception $e ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array())); 
    }
    return $link;
  }
  public function createLink(Core_Model_Item_Abstract $owner, $data)
  {
    $table = Engine_Api::_()->getDbtable('links', 'core');

    if( empty($data['parent_type']) || empty($data['parent_id']) )
    {
      $data['parent_type'] = $owner->getType();
      $data['parent_id'] = $owner->getIdentity();
    }
    $uri = $data["uri"];
    $client = new Zend_Http_Client($uri, array(
        'maxredirects' => 2,
        'timeout'      => 10,
      ));
      // Try to mimic the requesting user's UA
      $client->setHeaders(array(
        'User-Agent' => $_SERVER['HTTP_USER_AGENT'],
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'X-Powered-By' => 'Zend Framework'
      ));
    $response = $client->request();
    $result =  Engine_Api::_()->getApi('attachment','sesapi')->previewHtml($uri, $response);
    $data = array_merge($result['link'],$data);
    if(empty($data["description"]))
      $data["description"] = $data["title"];
    $link = $table->createRow();
    $link->setFromArray($data);
    $link->owner_type = $owner->getType();
    $link->owner_id = $owner->getIdentity();
    $link->save();

    // Now try to create thumbnail
    $thumbnail = (string) @$data['images'];
    $thumbnail_parsed = @parse_url($thumbnail);
    //$ext = @ltrim(strrchr($thumbnail_parsed['path'], '.'), '.');
    //$link_parsed = @parse_url($link->uri);

    // Make sure to not allow thumbnails from domains other than the link (problems with subdomains, disabled for now)
    //if( $thumbnail && $thumbnail_parsed && $thumbnail_parsed['host'] === $link_parsed['host'] )
    //if( $thumbnail && $ext && $thumbnail_parsed && in_array($ext, array('jpg', 'jpeg', 'gif', 'png')) )
    if( $thumbnail && $thumbnail_parsed )
    {
      $tmp_path = APPLICATION_PATH . '/temporary/link';
      $tmp_file = $tmp_path . '/' . md5($thumbnail);

      if( !is_dir($tmp_path) && !mkdir($tmp_path, 0777, true) ) {
        throw new Core_Model_Exception('Unable to create tmp link folder : ' . $tmp_path);
      }

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
          ->resize(120, 240)
          ->write($thumb_file)
          ->destroy();

        $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
          'parent_type' => $link->getType(),
          'parent_id' => $link->getIdentity()
        ));

        $link->photo_id = $thumbFileRow->file_id;
        $link->save();

        @unlink($thumb_file);
      }

      @unlink($tmp_file);
    }

    return $link;
  }
  public function previewHtml($uri, Zend_Http_Response $response)
  {
    $result = array();
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
      $dom = ""; // Maybe add b/c later
    }
    $title = "";
    if( $dom ) {
      $titleList = $dom->query('title');
      if( count($titleList) > 0 ) {
        $title = trim($titleList->current()->textContent);
        $title = substr($title, 0, 255);
      }
    }
    $result['link']['title'] = $title;
    $description = "";
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
     $result['link']['description'] = $description;
    $thumb = "";
    if( $dom ) {
      $thumbList = $dom->queryXpath("//link[@rel='image_src']");
      if( count($thumbList) > 0 ) {
        $thumb = $thumbList->current()->getAttribute('href');
      }
    }
     $result['link']['thumb'] = $thumb;
    $medium = "";
    if( $dom ) {
      $mediumList = $dom->queryXpath("//meta[@name='medium']");
      if( count($mediumList) > 0 ) {
        $medium = $mediumList->current()->getAttribute('content');
      }
    }
     $result['link']['medium'] = $medium;
    // Get baseUrl and baseHref to parse . paths
    $baseUrlInfo = parse_url($uri);
    $baseUrl = "";
    $baseHostUrl = "";
    $baseUrlScheme = $baseUrlInfo['scheme'];
    $baseUrlHost = $baseUrlInfo['host'];
    if( $dom ) {
      $baseUrlList = $dom->query('base');
      if( $baseUrlList && count($baseUrlList) > 0 && $baseUrlList->current()->getAttribute('href') ) {
        $baseUrl = $baseUrlList->current()->getAttribute('href');
        $baseUrlInfo = parse_url($baseUrl);
        if (!isset($baseUrlInfo['scheme']) || empty($baseUrlInfo['scheme'])) {
          $baseUrlInfo['scheme'] = $baseUrlScheme;
        }
        if (!isset($baseUrlInfo['host']) || empty($baseUrlInfo['host'])) {
          $baseUrlInfo['host'] = $baseUrlHost;
        }
        $baseHostUrl = $baseUrlInfo['scheme'].'://'.$baseUrlInfo['host'].'/';
      }
    }
    if( !$baseUrl ) {
      $baseHostUrl = $baseUrlInfo['scheme'].'://'.$baseUrlInfo['host'].'/';
      if( empty($baseUrlInfo['path']) ) {
        $baseUrl = $baseHostUrl;
      } else {
        $baseUrl = explode('/', $baseUrlInfo['path']);
        array_pop($baseUrl);
        $baseUrl = join('/', $baseUrl);
        $baseUrl = trim($baseUrl, '/');
        $baseUrl = $baseUrlInfo['scheme'].'://'.$baseUrlInfo['host'].'/'.$baseUrl.'/';
      }
    }
    $images = array();
    if( $thumb ) {
      $images[] = $thumb;
    }
    if( $dom ) {
      $imageQuery = $dom->query('img');
      foreach( $imageQuery as $image )
      {
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
        } else if( strpos($src, './') === 0 ) {
          // If relative to current path, add baseUrl
          $src = $baseUrl . substr($src, 2);
        } else if( !empty($srcInfo['scheme']) && !empty($srcInfo['host']) ) {
          // Contians host and scheme, do nothing
        } else if( empty($srcInfo['scheme']) && empty($srcInfo['host']) ) {
          // if not contains scheme or host, add base
          $src = $baseUrl . ltrim($src, '/');
        } else if( empty($srcInfo['scheme']) && !empty($srcInfo['host']) ) {
          // if contains host, but not scheme, add scheme?
          $src = $baseUrlInfo['scheme'] . ltrim($src, '/');
        } else {
          // Just add base
          $src = $baseUrl . ltrim($src, '/');
        }
        // Ignore images that don't come from the same domain
        //if( strpos($src, $srcInfo['host']) === false ) {
          // @todo should we do this? disabled for now
          //continue;
        //}
        // Ignore images that don't end in an image extension
        if( !in_array($ext, array('jpg', 'jpeg', 'gif', 'png')) ) {
          // @todo should we do this? disabled for now
          //continue;
        }
        if( !in_array($src, $images) ) {
          $images[] = $src;
        }
      }
    }
    // Unique
    $images = array_values(array_unique($images));
    $imagePreview = 'https://www.google.com//images//branding//googlelogo//2x//googlelogo_color_120x44dp.png';
    // Truncate if greater than 20
    if( count($images) > 0 ) {
      $imagePreview = $images[0];
    }
     $result['link']['images'] = $imagePreview;
     return $result;
  }
  public function onAttachVideo($data) {
    if (!is_array($data) || empty($data['video_id'])) {
      return;
    }
    $video = Engine_Api::_()->getItem('video', $data['video_id']);
    // update $video with new title and description
    $video->title = $data['title'];
    $video->description = !empty($data['description']) ? $data['description'] : '';
    // Set parents of the video
    if (Engine_Api::_()->sesapi()->hasSubject()) {
      $subject = Engine_Api::_()->sesapi()->getSubject();
      $subject_type = $subject->getType();
      $subject_id = $subject->getIdentity();

      $video->parent_type = $subject_type;
      $video->parent_id = $subject_id;
    }
    $video->search = 1;
    $video->save();
    if (!($video instanceof Core_Model_Item_Abstract) || !$video->getIdentity()) {
      return;
    }
    return $video;
  }
}
