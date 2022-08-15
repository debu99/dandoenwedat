<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: content.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
return array(
  array(
      'title' => 'Width for Page Columns',
      'description' => 'This widget allows you to modify the width of any column of any widgetized page.',
      'category' => 'SES Basic Required',
      'type' => 'widget',
      'name' => 'sesbasic.column-layout-width',
      'autoEdit' => true,
      'adminForm' => array(
          'elements' => array(
              array(
                  'Radio',
                  'layoutColumnWidthType',
                  array(
                      'label' => 'Choose unit of width.',
                      'multiOptions' => array(
                          '%' => 'Percentage (%)',
                          'px' => 'Pixels (px)',
                      ),
                      'value' => '%',
                  )
              ),
              array(
                  'text',
                  'columnWidth',
                  array(
                      'label' => 'Enter the new width for the column in which this widget will be placed.',
                  )
              ),

          )
      )
  ),
  array(
      'title' => 'Scroll Top to Bottom',
      'description' => 'Displays a "Scroll Top to Bottom" button on the page on which this widget is placed. If you want this "Scroll Top to Bottom" button to appear on all the pages of your site, then just place the widget in the Footer of your site.',
      'category' => 'SES Basic Required',
      'type' => 'widget',
      'name' => 'sesbasic.scroll-bottom-top',
      'defaultParams' => array(
          'title' => '',
          'titleCount' => true,
      ),
  ),
  array(
      'title' => 'SES - Social Share',
      'description' => 'Displays a "Social Share" buttons on the page on which this widget is placed. If you want this "Social Share" button to appear on all the pages of your site, then just place the widget in the Footer/header of your site.',
      'category' => 'SES Basic Required',
      'type' => 'widget',
      'name' => 'sesbasic.content-share',
      'autoEdit' => true,
      'adminForm' => array(
          'elements' => array(
              array(
                  'Select',
                  'codeEnable',
                  array(
                      'label' => "Choose from social share widget?",
                      'multiOptions' => array(
                          'socialeShare' => 'Sociale share widget',
                          'jiathis' => 'Jia This Share',
                      ),
                      'value' => 'socialeShare',
                  )
              ),
        ),
    ),
  ),
  array(
      'title' => 'SES - Advanced HTML Block',
      'description' => 'This widget allows you to insert any HTML of your choice via the WYSIWYG editor.',
      'category' => 'SES Basic Required',
      'type' => 'widget',
      'autoEdit' => true,
      'name' => 'sesbasic.html-block',
      'adminForm' => 'Sesbasic_Form_ContentForm'
  ),
  array(
      'title' => 'SES - Simple HTML with Translation Option',
      'description' => 'Inserts any HTML of your choice. You can also add HTML content in various languages enabled on your website.',
      'category' => 'SES Basic Required',
      'type' => 'widget',
      'autoEdit' => true,
      'name' => 'sesbasic.simple-html-block',
      'adminForm' => 'Sesbasic_Form_ContentFormSimple'
  ),
  array(
      'title' => 'SES - Page CSS Modifier',
      'description' => 'This widget displays class for the body of any particular page mentioned in it. The recommended page is content Profile Page.',
      'category' => 'SES Basic Required',
      'type' => 'widget',
      'autoEdit' => true,
      'name' => 'sesbasic.body-class',
      'adminForm' => array(
          'elements' => array(
              array(
                  'Text',
                  'bodyclass',
                  array(
                      'label' => "Add class Name",
                  )
              ),
        ),
    ),
  ),
);
