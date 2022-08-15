<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: GetContent.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_View_Helper_GetContent
{

  const Emojione = 'Emojione';

  public function getContent($actions = null, array $data = array(),$break = true,$change = false)
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $group_feed_id = !empty($data['group_feed']) ? $data['group_feed'] : "";
    if($actions instanceof Sesadvancedactivity_Model_Action || $actions instanceof Activity_Model_Action){
      $model = Engine_Api::_()->getApi('core', 'sesadvancedactivity');
      $subject = $actions->getSubject();
      $object = $actions->getObject();
      $sesResourceType = empty($data['sesresource_type']) ? "" : $data['sesresource_type'];
      $sesResourceId = empty($data['sesresource_id']) ? 0 : $data['sesresource_id'] ;
      $params = array_merge(
        $actions->toArray(),
        (array) $actions->params,
        array(
          'subject' => $subject,
          'sesresource_type'=>$sesResourceType,
          'sesresource_id'=>$sesResourceId,
          'object' => $actions->getObject(),
          'owner' =>  $actions->type == "album_like" || $actions->type == "album_photo_like" ? Engine_Api::_()->getItem('user',$object->getOwner()) : "",
        )
      );


      $content = $model->assemble($actions->getTypeInfo()->body, $params,$break,$group_feed_id);
    }else {
      $content = $actions;
    }
    //change content for emojies
    $emoji = Engine_Api::_()->getApi('emoji','sesbasic')->getEmojisArray();
    $content = str_replace(array_keys($emoji),array_values($emoji),$content);
    //usage
    $content =  $this->gethashtags($content);
    $content = $this->getMentionTags($content);

    //Feeling Post share work
    if(defined('SESFEELINGACTIVITYENABLED') && $change) {
      $action_id = $actions->getIdentity();
      if($action_id) {
        $feelingposts = Engine_Api::_()->getDbTable('feelingposts','sesadvancedactivity')->getActionFeelingposts($action_id);
        if($feelingposts) {
          $feelings = Engine_Api::_()->getItem('sesfeelingactivity_feeling', $feelingposts->feeling_id);
          if($feelings->type == 1) {
            $feelingIcon = Engine_Api::_()->getItem('sesfeelingactivity_feelingicon', $feelingposts->feelingicon_id);
            $content = $content . " is <img class='sesfeeling_feeling_icon' src=".Engine_Api::_()->storage()->get($feelingIcon->feeling_icon, '')->getPhotoUrl()."> ".strtolower($feelings->title).' '.strtolower($feelingIcon->title);
          }  else if($feelings->type == 2 && $feelingposts->resource_type && $feelingposts->feelingicon_id) {
            $resource = Engine_Api::_()->getItem($feelingposts->resource_type, $feelingposts->feelingicon_id);
            $content = $content . " is <img title=".strtolower($resource->title).' class="sesfeeling_feeling_icon" src='. Engine_Api::_()->storage()->get($feelings->file_id, "")->getPhotoUrl().'> '.strtolower($feelings->title).' <a href='.strtolower($resource->getHref()).'>'.strtolower($resource->title).'</a>';
          }
        }
      }
    }
    //Feeling Post share work

    //location share post work
    if($change) {
      $action_id = $actions->getIdentity();
      if($action_id) {
        $location = Engine_Api::_()->getDbTable('locations','sesbasic')->getLocationData('activity_action', $action_id);
        if($location && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) {
          $content = $content. " in <a href=".'sesbasic/index/get-direction/resource_id/'.$action_id.'/resource_type/'.$actions->getType().' onClick="openSmoothBoxInUrl(this.href);return false;">'.$location->venue."</a>";
        } else if($location && !Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { 
          $content = $content. " in ".$location->venue;
        }
      }

      if($actions->body && defined('SESEMOJIENABLED')) {
        //Emoji Share Work
        require_once 'application/modules/Sesemoji/controllers/lib/php/autoload.php';
        $client = new Client(new Ruleset());
        $client->imagePathPNG = 'application/modules/Sesemoji/externals/images/emoji/';
        $emojisCode = Engine_Api::_()->sesemoji()->DecodeEmoji($content[1]);
        $content[1] = $client->toImage($emojisCode);
      }
    }
    //location share post work


    //Emojis Work
    if(defined('SESEMOJIENABLED')) {
      require_once 'application/modules/Sesemoji/controllers/lib/php/autoload.php';
      $client = new Client(new Ruleset());
      $client->imagePathPNG = 'application/modules/Sesemoji/externals/images/emoji/';
      $emojisCode = Engine_Api::_()->sesemoji()->DecodeEmoji($content[1]);
      $content[1] = $client->toImage($emojisCode);
    }
    //Emojis Work End

    //Text Work
    $sesadvancedactivitybigtext = $settings->getSetting('sesadvancedactivity.bigtext',1);
    $sesAdvancedactivityfonttextsize = $settings->getSetting('sesadvancedactivity.fonttextsize',24);
    $sesAdvancedactivitytextlimit = $settings->getSetting('sesadvancedactivity.textlimit',120);

    //Color work for specific string
    $getAllTextColors = Engine_Api::_()->getDbTable('textcolors', 'sesadvancedactivity')->getAllTextColors();
    $content[1] = trim($content[1], ' ');
    if(count($getAllTextColors) > 0) {
      foreach($getAllTextColors as $key => $textResult) {
        $searchText[] = "/\b". $textResult->string."\b/";
        if($textResult->animation)
          $cursor = "cursor:pointer;";
        else
          $cursor = "";
        $searchValue[] = '<span style="color:#'.$textResult->color.';'.$cursor.'" class="sesadv_animation_cls" data-animation="'.$textResult->animation.'"> '.$textResult->string.'</span> ';
      }
      if(!$change)
      $content[1] = ' ' . $content[1] . ' ';

      $content[1] = preg_replace($searchText, $searchValue, $content[1]);
    }
    if($sesadvancedactivitybigtext && isset($content[1]) && strlen(strip_tags($content[1])) <= $sesAdvancedactivitytextlimit && $actions->type == 'status' && !$change) {
      $content[1] =  '<span style="font-size:'.$sesAdvancedactivityfonttextsize.'px;">'.$content[1].'</span>';
    }

    //Text Work

    if( strpos( $content[1], $_SERVER['HTTP_HOST'] ) === false )
      $content[1] = str_replace('<a', '<a target="_blank"', $content[1]);
    $content[1] = trim($content[1], ' ');

    return $content;
  }
  function getMentionTags($content){
    if(is_array($content))
      $contentMention = $content[1];
    else
      $contentMention = $content;

    preg_match_all('/(^|\s)(@\w+)/', $contentMention, $result);
    foreach($result[2] as $value){
        $user_id = str_replace('@_user_','',$value);
        if(intval($user_id)>0){
          $user = Engine_Api::_()->getItem('user',$user_id);
          if(!$user || !$user->getIdentity())
           continue;
        }else{
          $itemArray = explode('_',$user_id);
          $resource_id = $itemArray[count($itemArray) - 1];
          unset($itemArray[count($itemArray) - 1]);
          $resource_type = implode('_',$itemArray);
            try {
                if(intval($resource_id) > 0)
                $user = Engine_Api::_()->getItem($resource_type, $resource_id);
            }catch (Exception $e){
                continue;
            }
          if(!$user || !$user->getIdentity())
            continue;
        }

        $contentMention = str_replace($value,'<a href="'.$user->getHref().'" data-src="'.$user->getGuid().'" class="ses_tooltip">'.$user->getTitle().'</a>',$contentMention);
    }

    if(is_array($content))
      $content[1] = $contentMention;
    else
      $content = $contentMention;

    return $content;

  }
   function gethashtags($content)
  {
   // return $parsedMessage = preg_replace(array('/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))/', '/(^|[^a-z0-9_])@([a-z0-9_]+)/i', '/(^|[^a-z0-9_])#([a-z0-9_]+)/i'), array('<a href="$1">$1</a>', '$1@$2', '$1<a href="hashtag?hashtag=$2">#$2</a>'), $content);
    preg_match_all("/#([\p{Pc}\p{N}\p{L}\p{Mn}]+)/u", @$content[1], $matches);
    $searchword = $replaceWord = array();
    foreach($matches[0] as $value){
      if(!in_array($value,$searchword)){
        $searchword[]=$value;
        $replaceWord[] = '<a target="_blank"  href="hashtag?hashtag='.str_replace('#','',$value).'">'.$value.'</a>';
      }
    }
    $content[1] = str_replace($searchword,$replaceWord, @$content[1]);
    return $content;
  }
}
