<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Likes.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


class Sesadvancedactivity_Model_DbTable_Likes extends Core_Model_DbTable_Likes
{
  protected $_rowClass = 'Sesadvancedactivity_Model_Like';
	protected $_name = 'activity_likes';
}