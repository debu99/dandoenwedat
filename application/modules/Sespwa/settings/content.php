<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: content.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
$arrayGallery = array();

if(Engine_Api::_()->getDbtable("modules", "core")->isModuleEnabled("sespwa") && Engine_Api::_()->getApi('settings', 'core')->getSetting('sespwa.pluginactivated')) {
  $results = Engine_Api::_()->getDbtable('banners', 'sespwa')->getBanner(array('fetchAll' => true));
  if (count($results) > 0) {
    foreach ($results as $gallery)
      $arrayGallery[$gallery['banner_id']] = $gallery['banner_name'];
  }
}
return array(
    array(
        'title' => 'SES - Progressive Web App - Login or Signup',
        'description' => 'Displays a login form and a signup link for members that are not logged in.',
        'category' => 'SES - Progressive Web App Plugin',
        'type' => 'widget',
        'name' => 'sespwa.login-or-signup',
        'requirements' => array(
            'no-subject',
        ),
    ),
    array(
        'title' => 'SES - Progressive Web App - Header',
        'description' => '',
        'category' => 'SES - Progressive Web App Plugin',
        'type' => 'widget',
        'name' => 'sespwa.header',
        'autoEdit' => false,
    ),
    array(
        'title' => 'SES - Progressive Web App - Banner Slideshow',
        'description' => 'Displays banner slideshows as configured by you in the admin panel of this theme. Edit this widget to choose the slideshow to be shown and configure various settings.',
        'category' => 'SES - Progressive Web App Plugin',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sespwa.banner-slideshow',
        'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'banner_id',
                    array(
                        'label' => 'Choose the Banner to be shown in this widget.',
                        'multiOptions' => $arrayGallery,
                        'value' => 1,
                    )
                ),
                array(
                    'Select',
                    'full_width',
                    array(
                        'label' => 'Do you want to show this Banner in full width?',
                        'multiOptions' => array(
                            '1' => 'Yes',
                            '0' => 'No'
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of this Banner (in pixels).',
                        'value' => 200,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    ),
                ),
            ),
        ),
    ),
    array(
        'title' => 'SES - Progressive Web App - Startup Screen',
        'description' => '',
        'category' => 'SES - Progressive Web App Plugin',
        'type' => 'widget',
        'name' => 'sespwa.startup',
        'autoEdit' => true,
        'adminForm' => 'Sespwa_Form_Admin_Startup',

    ),
);
