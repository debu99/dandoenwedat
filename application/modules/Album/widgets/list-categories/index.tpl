<?php
/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Album
* @copyright  Copyright 2006-2016 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: index.tpl 9747 2016-12-08 02:08:08Z john $
* @author     John
*/
?>

<?php echo $this->partial('_navCategories.tpl', 'core', array('categories' => $this->categories, 'type' => 'album'));
