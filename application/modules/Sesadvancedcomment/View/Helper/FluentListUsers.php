<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id FluentListUsers.php  2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedcomment_View_Helper_FluentListUsers extends Zend_View_Helper_Abstract
{
  public function FluentListUsers($items, $translate = false,$isLike = false,$viewer)
  {
    
    $itemsS = array();
    foreach($items as $itemS){
      $itemsS[] = $itemS->getType() != "user" ?  Engine_Api::_()->getItem($itemS->poster_type,$itemS->poster_id) : $itemS;  
    }
    $items = $itemsS;
    if( 0 === ($num = count($items)) )
    {
      return '';
    }
    $isLike = false;
    $comma = $this->view->translate(',');
    $and = $this->view->translate('AND');
    $countItems = count($items);
    $index = 1;
    if($num > 3)
      $num = 4;
    if($isLike){
      if(($num - 1) != 0)
        $content = $this->view->translate('YOU');
      else
        $content = $viewer->getTitle();
      $index = 1;
      if($num > 3)
      $num = 3;
    }else{
      $content = '';
    }
    $break = false;
    foreach( $items as $item )
    {
      if($isLike && $viewer->getIdentity() == $item->getIdentity()){
        continue;
      }
       //if( $num > 1 && $index != $countItems) $content .= $comma . ' '; else $content .= ' ';
      //if( $countItems > 1 && $index == $countItems - 1  ) $content .= $and . ' ';
      
      if($index >= 3 && (($num >= 3 && $isLike) || $num >= 4)){
        $break = true;
        break;
      }
      
      $content .= $comma.' ';
      
      $href = null;
      $title = null;

      if( is_object($item) ) {
        if( method_exists($item, 'getTitle') && method_exists($item, 'getHref') ) {
          $href = $item->getHref();
          $title = $item->getTitle();
        } else if( method_exists($item, '__toString') ) {
          $title = $item->__toString();
        } else {
          $title = (string) $item;
        }
      } else {
        $title = (string) $item;
      }
      
      if( $translate ) {
        $title = $this->view->translate($title);
      }

      //if( null === $href ) {
        $content .= $title;
     // } else {
     //   $content .= $this->view->htmlLink($href, $title);
     // }
      
      $index++;
    }
    $content = trim($content,',');
    if($break){
      $text = ($countItems - 2) > 1 ? $this->view->translate('others') : $this->view->translate('other');
      $content .= $this->view->translate(' and ').($countItems - 2).' '.$text;
    }else {
     $content =  strrev(implode(strrev($this->view->translate(' and')), explode(strrev(','), strrev($content), 2)));  
    }
    return trim($content,',');
  }
}