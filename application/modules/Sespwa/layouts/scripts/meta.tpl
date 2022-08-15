<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: meta.tpl  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<?php

    $table = Engine_Api::_()->getDbTable('manifests','sespwa');
    $manifest = $table->fetchRow($table->select());
    if($manifest){
        $this->headLink(array('rel' => 'manifest','href' => "public/manifest.json"),'PREPEND');
        $this->headMeta()->appendName('apple-mobile-web-app-status-bar-style','black');
        $this->headMeta()->appendName('apple-mobile-web-app-title',$manifest->appname);
        $this->headMeta()->appendName('msapplication-TileColor',$manifest->backgroundcolor);
        $this->headMeta()->appendName('theme-color',$manifest->themecolor);
        $this->headMeta()->appendName('msapplication-TileImage',$this->absoluteUrl($this->layout()->staticBaseUrl.'sespwa/images/'.$manifest["app_icon"]));
        $this->headMeta()->appendName('mobile-web-app-capable','yes');

        $imageName = $this->absoluteUrl($this->layout()->staticBaseUrl.'sespwa/images/'.$manifest["app_icon"]);
        $this->headLink(array('rel'=>'icon','sizes' => '16x16','href' => $imageName),'PREPEND');
        $this->headLink(array('rel'=>'icon','sizes' => '32x32','href' => $imageName),'PREPEND');
        $this->headLink(array('rel'=>'apple-touch-icon','sizes' => '57x57','href' => $imageName),'PREPEND');
        $this->headLink(array('rel'=>'apple-touch-icon','sizes' => '72x72','href' => $imageName),'PREPEND');
        $this->headLink(array('rel'=>'apple-touch-icon','sizes' => '76x76','href' => $imageName),'PREPEND');
        $this->headLink(array('rel'=>'apple-touch-icon','sizes' => '114x114','href' => $imageName),'PREPEND');
        $this->headLink(array('rel'=>'apple-touch-icon','sizes' => '120x120','href' => $imageName),'PREPEND');
        $this->headLink(array('rel'=>'icon','sizes' => '128x128','href' => $imageName),'PREPEND');
        $this->headLink(array('rel'=>'apple-touch-icon-precomposed','sizes' => '128x128','href' => $imageName),'PREPEND');
        $this->headLink(array('rel'=>'apple-touch-icon-precomposed','href' => $imageName),'PREPEND');
        $this->headLink(array('rel'=>'apple-touch-icon','sizes' => '144x144','href' => $imageName),'PREPEND');
        $this->headLink(array('rel'=>'apple-touch-icon','sizes' => '152x152','href' => $imageName),'PREPEND');
        $this->headLink(array('rel'=>'apple-touch-icon','sizes' => '180x180','href' => $imageName),'PREPEND');
        $this->headLink(array('rel'=>'icon','sizes' => '192x192','href' => $imageName),'PREPEND');
        $this->headLink(array('rel'=>'apple-touch-startup-image','media' => '(device-width: 320px) and (device-height: 480px) and (-webkit-device-pixel-ratio: 2)','href' => $imageName),'PREPEND');
    }
?>