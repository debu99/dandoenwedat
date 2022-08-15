<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Global.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Form_Admin_Global extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Global Settings')
      ->setDescription('These settings affect all members in your community.');


    $this->addElement('Text', 'album_page', array(
      'label' => 'Thumbnails Per Page',
      'description' => 'How many albums will be shown per page?',
      'value' => 20
    ));

    $this->addElement('Radio', 'album_searchable', array(
      'label' => 'Make default albums searchable?',
      'description' => 'Do you want to make a default album searchable ? (If set to no,'
        . ' albums that get created by default like Blog Photos, Forum Photos, etc will not'
        . ' be displayed in the album search results and widgets, but will still be displayed in'
        . ' the Profile Albums tab on a users profile page.)',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      ),
      'value' => 0,
    ));
      $this->addElement('Radio', 'album_allow_unauthorized', array(
          'label' => 'Make unauthorized album searchable?',
          'description' => 'Do you want to make a unauthorized albums searchable? (If set to no, albums that are not authorized for the current user will not be displayed in the album search results and widgets.)',
          'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('album.allow.unauthorized',0),
          'multiOptions' => array(
              '1' => 'Yes',
              '0' => 'No',
          ),
      ));
    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}
