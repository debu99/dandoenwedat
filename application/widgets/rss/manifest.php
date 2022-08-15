<?php
/**
 * SocialEngine
 *
 * @category   Application_Widget
 * @package    Rss
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */
return array(
  'package' => array(
    'type' => 'widget',
    'name' => 'rss',
    'version' => '5.0.1',
    'revision' => '$Revision: 9808 $',
    'path' => 'application/widgets/rss',
    'repository' => 'socialengine.com',
    'title' => 'RSS Feed',
    'description' => 'Displays an RSS feed.',
    'author' => 'Webligo Developments',
    'directories' => array(
      'application/widgets/rss',
    ),
  ),

  // Backwards compatibility
  'type' => 'widget',
  'name' => 'rss',
  'version' => '5.0.1',
  'revision' => '$Revision: 9808 $',
  'title' => 'RSS',
  'description' => 'Displays an RSS feed.',
  'category' => 'Widgets',
  'defaultParams' => array(
    'timeout' => 900,
  ),
  'adminForm' => array(
    'elements' => array(
      array(
        'Text',
        'title',
        array(
          'label' => 'Title'
        )
      ),
      array(
        'Text',
        'url',
        array(
          'label' => 'URL'
        )
      ),
      array(
        'Text',
        'timeout',
        array(
          'label' => 'Cache TTL',
          'description' => 'How long would you like to cache results before ' .
              'they are fetched again? Leave empty to disable caching.',
          'validators' => array(
            array('Int')
          ),
        ),
        'value' => 900,
      ),
      array(
        'Radio',
        'strip',
        array(
          'label' => 'Strip HTML?',
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
          ),
          'value' => 1,
        )
      ),
    ),
  ),
) ?>
