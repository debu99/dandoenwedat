<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Links.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Api_Links extends Core_Api_Abstract
{
  public function createLink(Core_Model_Item_Abstract $owner, $data)
  {
    $table = Engine_Api::_()->getDbTable('links', 'core');

    if( empty($data['parent_type']) || empty($data['parent_id']) )
    {
      $data['parent_type'] = $owner->getType();
      $data['parent_id'] = $owner->getIdentity();
    }

    $link = $table->createRow();
    $link->setFromArray($data);
    $link->owner_type = $owner->getType();
    $link->owner_id = $owner->getIdentity();
    $link->save();

    // Now try to create thumbnail
    $thumbnail = (string) @$data['thumb'];
    $thumbnail_parsed = @parse_url($thumbnail);
    //$ext = @ltrim(strrchr($thumbnail_parsed['path'], '.'), '.');
    //$link_parsed = @parse_url($link->uri);

    // Make sure to not allow thumbnails from domains other than the link (problems with subdomains, disabled for now)
    //if( $thumbnail && $thumbnail_parsed && $thumbnail_parsed['host'] === $link_parsed['host'] )
    //if( $thumbnail && $ext && $thumbnail_parsed && in_array($ext, array('jpg', 'jpeg', 'gif', 'png')) )

    if(@$data['isGif'] == 'true'){
      $gifUrl = $data['gifUrl'];
      if( $gifUrl ) {
       $link->description = $gifUrl;
       $link->uri = $gifUrls;
       Engine_Api::_()->getDbTable('links', 'sesadvancedactivity')->isRowExists($link->getIdentity(), 1);
          $link->save();
      }
    }else if(@$data['isIframe'] == 'true'){
       $link->title = $data['title'];
       $link->description = $data['description'].' || IFRAMEDATA'.$data['thumb'];
       $link->uri = $data['uri'];
       Engine_Api::_()->getDbTable('links', 'sesadvancedactivity')->isRowExists($link->getIdentity(), 2);
       $link->save();
    }else if( $thumbnail && $thumbnail_parsed )
    {
      $tmp_path = APPLICATION_PATH . '/temporary/link';
      $tmp_file = $tmp_path . '/' . md5($thumbnail);

      if( !is_dir($tmp_path) && !mkdir($tmp_path, 0777, true) ) {
        throw new Core_Model_Exception('Unable to create tmp link folder : ' . $tmp_path);
      }
      $ext = ltrim(strrchr($thumbnail, '.'), '.');
      $content = $this->url_get_contents($thumbnail);
      if ($content) {
          $valid_thumb = true;
          file_put_contents($tmp_file, $content);
      } else {
          $valid_thumb = false;
      }
      if($valid_thumb && ($info = getimagesize($tmp_file)) && !empty($info[2]) ) {
        $ext = Engine_Image::image_type_to_extension($info[2]);
        $thumb_file = $tmp_path . '/thumb_'.md5($thumbnail) . '.'.$ext;

        $image = Engine_Image::factory();
        $image->open($tmp_file)
          ->autoRotate()
          ->resize(700, 700)
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
   function url_get_contents ($Url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}
