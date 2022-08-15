<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Comments.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Model_DbTable_Comments extends Core_Model_DbTable_Comments
{
  protected $_rowClass = 'Sesadvancedactivity_Model_Comment';
	protected $_name = 'activity_comments';
  public function getResourceType()
  {
    return 'activity_action';
  }
}