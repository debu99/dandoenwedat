<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Affiliate.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_Admin_Settings_Affiliate extends Engine_Form
{
  public function init()
  { 
    // Set form attributes
    $description = Zend_Registry::get('Zend_Translate')->_("Earn with SocialEngine! <a href='%s' target='_blank'>Sign up for our Affiliate Program</a> and make money in your spare time!");
    $description= sprintf($description, "http://www.socialengine.com/affiliate");
    $this->setTitle('SocialEngine Affiliate Program');
    $this->setDescription($description);
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);
  }
}
