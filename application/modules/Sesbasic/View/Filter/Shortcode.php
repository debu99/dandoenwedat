<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Sesbasic.php 2016-11-22 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesbasic_View_Filter_Shortcode {
	public function filter($string) {
        $stringArray  =  Engine_Api::_()->sesbasic()->get_string_between($string);
        try {
            foreach ($stringArray as $array){
                if($array && 1 === preg_match('~[0-9]~', $array)){
                    $explodeStr = explode('_',$array);
                    array_pop($explodeStr);
                    $encExploddeSTR = join('_',$explodeStr);
                    if($encExploddeSTR && Engine_Api::_()->hasItemType(strtolower($encExploddeSTR))) {
                        $item = Engine_Api::_()->getItemByGuid(strtolower($array));
                        if($item) {
                        $codeData = $item->shortCodeData($array);
                        if($codeData){
                            $string = str_replace('['.$array.']',$codeData,$string);
                        }
                        }
                    }
                }
            }
        }catch (Exception $e){
           // throw $e;
        }
        return $string;
  }
}
