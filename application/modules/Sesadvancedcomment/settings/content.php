<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: content.php 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
return array(
  array(
    'title' => 'Advanced Nested Comments',
    'description' => 'Shows the comments, replies, attachments in comments like photos, videos, emoticons & stickers as configured by you about an item.',
    'category' => 'SNS - Advanced Nested Comments with Attachments Plugin',
    'type' => 'widget',
    'name' => 'sesadvancedcomment.comments',
    'defaultParams' => array(
      'title' => 'Comments'
    ),
    'requirements' => array(
      'subject',
    ),
  ),
);