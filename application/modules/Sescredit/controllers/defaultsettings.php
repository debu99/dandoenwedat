<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: defaultsettings.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
$db = Zend_Db_Table_Abstract::getDefaultAdapter();
$pageId = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sescredit_index_manage')
        ->limit(1)
        ->query()
        ->fetchColumn();

// insert if it doesn't exist yet
if (!$pageId) {
  $widgetOrder = 1;
// Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sescredit_index_manage',
      'displayname' => 'SES - Credits - Manage Credit Points Page',
      'title' => '',
      'description' => '',
      'custom' => 0,
  ));
  $pageId = $db->lastInsertId();

// Insert top
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'top',
      'page_id' => $pageId,
      'order' => 1,
  ));
  $topId = $db->lastInsertId();

// Insert main
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'main',
      'page_id' => $pageId,
      'order' => 2,
  ));
  $mainId = $db->lastInsertId();

// Insert top-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $pageId,
      'parent_content_id' => $topId,
  ));
  $topMiddleId = $db->lastInsertId();

// Insert main-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $pageId,
      'parent_content_id' => $mainId,
      'order' => 3,
  ));
  $mainMiddleId = $db->lastInsertId();

  // Insert main-left
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'left',
      'page_id' => $pageId,
      'parent_content_id' => $mainId,
      'order' => 1,
  ));
  $mainLeftId = $db->lastInsertId();

// Insert main-right
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'right',
      'page_id' => $pageId,
      'parent_content_id' => $mainId,
      'order' => 2,
  ));
  $mainRightId = $db->lastInsertId();

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.browse-menu',
      'page_id' => $pageId,
      'parent_content_id' => $topMiddleId,
      'order' => $widgetOrder++,
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.my-badge',
      'page_id' => $pageId,
      'parent_content_id' => $mainLeftId,
      'order' => $widgetOrder++,
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.send-point-friend',
      'page_id' => $pageId,
      'parent_content_id' => $mainLeftId,
      'order' => $widgetOrder++,
      'params' => '{"title":"Send Point to Friend","name":"sescredit.send-point-friend"}',
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.my-points',
      'page_id' => $pageId,
      'parent_content_id' => $mainMiddleId,
      'order' => $widgetOrder++,
      'params' => '{"title":"","name":"sescredit.my-points"}',
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.terms',
      'page_id' => $pageId,
      'parent_content_id' => $mainMiddleId,
      'order' => $widgetOrder++,
      'params' => '{"terms":"<h3>The standard Lorem Ipsum passage, used since the 1500s<\/h3>\r\n<p>\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\"<\/p>\r\n<h3>Section 1.10.32 of \"de Finibus Bonorum et Malorum\", written by Cicero in 45 BC<\/h3>\r\n<p>\"Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?\"<\/p>\r\n<h3>1914 translation by H. Rackham<\/h3>\r\n<p>\"But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness. No one rejects, dislikes, or avoids pleasure itself, because it is pleasure, but because those who do not know how to pursue pleasure rationally encounter consequences that are extremely painful. Nor again is there anyone who loves or pursues or desires to obtain pain of itself, because it is pain, but because occasionally circumstances occur in which toil and pain can procure him some great pleasure. To take a trivial example, which of us ever undertakes laborious physical exercise, except to obtain some advantage from it? But who has any right to find fault with a man who chooses to enjoy a pleasure that has no annoying consequences, or one who avoids a pain that produces no resultant pleasure?\"<\/p>\r\n<h3>Section 1.10.33 of \"de Finibus Bonorum et Malorum\", written by Cicero in 45 BC<\/h3>\r\n<p>\"At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut offic iis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.\"<\/p>","title":"Terms & Conditions","nomobile":"0","name":"sescredit.terms"}'
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.recent-point-activity',
      'page_id' => $pageId,
      'parent_content_id' => $mainRightId,
      'order' => $widgetOrder++,
      'params' => '{"limit":"5","title":"My Feed","nomobile":"0","name":"sescredit.recent-point-activity"}',
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.update-member-level',
      'page_id' => $pageId,
      'parent_content_id' => $mainRightId,
      'order' => $widgetOrder++,
  ));
}
$pageId = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sescredit_index_transaction')
        ->limit(1)
        ->query()
        ->fetchColumn();

// insert if it doesn't exist yet
if (!$pageId) {
  $widgetOrder = 1;
// Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sescredit_index_transaction',
      'displayname' => 'SES - Credits - Transactions Page',
      'title' => '',
      'description' => '',
      'custom' => 0,
  ));
  $pageId = $db->lastInsertId();

// Insert top
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'top',
      'page_id' => $pageId,
      'order' => 1,
  ));
  $topId = $db->lastInsertId();

// Insert main
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'main',
      'page_id' => $pageId,
      'order' => 2,
  ));
  $mainId = $db->lastInsertId();

// Insert top-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $pageId,
      'parent_content_id' => $topId,
  ));
  $topMiddleId = $db->lastInsertId();

// Insert main-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $pageId,
      'parent_content_id' => $mainId,
      'order' => 3,
  ));
  $mainMiddleId = $db->lastInsertId();

  // Insert main-left
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'left',
      'page_id' => $pageId,
      'parent_content_id' => $mainId,
      'order' => 1,
  ));
  $mainLeftId = $db->lastInsertId();

// Insert main-right
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'right',
      'page_id' => $pageId,
      'parent_content_id' => $mainId,
      'order' => 2,
  ));
  $mainRightId = $db->lastInsertId();

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.browse-menu',
      'page_id' => $pageId,
      'parent_content_id' => $topMiddleId,
      'order' => $widgetOrder++,
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.invite-friends',
      'page_id' => $pageId,
      'parent_content_id' => $mainLeftId,
      'order' => $widgetOrder++,
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.recent-point-activity',
      'page_id' => $pageId,
      'parent_content_id' => $mainLeftId,
      'order' => $widgetOrder++,
      'params' => '{"limit":"10","title":"My Recent Activities","nomobile":"0","name":"sescredit.recent-point-activity"}',
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.browse-search',
      'page_id' => $pageId,
      'parent_content_id' => $mainMiddleId,
      'order' => $widgetOrder++,
      'params' => '{"criteria":["0","today","week","month"],"default_view_search_type":"0","show_option":["view","chooseDate"],"title":"","nomobile":"0","name":"sescredit.browse-search"}',
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.my-transactions',
      'page_id' => $pageId,
      'parent_content_id' => $mainMiddleId,
      'order' => $widgetOrder++,
      'params' => '{"title":"My Transactions","name":"sescredit.my-transactions"}',
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.my-points-information',
      'page_id' => $pageId,
      'parent_content_id' => $mainRightId,
      'order' => $widgetOrder++,
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.purchase-points',
      'page_id' => $pageId,
      'parent_content_id' => $mainRightId,
      'order' => $widgetOrder++,
      'params' => '{"title":"Purchase Points","name":"sescredit.purchase-points"}',
  ));
}
$pageId = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sescredit_index_earn-credit')
        ->limit(1)
        ->query()
        ->fetchColumn();

// insert if it doesn't exist yet
if (!$pageId) {
  $widgetOrder = 1;
// Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sescredit_index_earn-credit',
      'displayname' => 'SES - Credits - Credit Listing & Information Page',
      'title' => '',
      'description' => '',
      'custom' => 0,
  ));
  $pageId = $db->lastInsertId();

// Insert top
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'top',
      'page_id' => $pageId,
      'order' => 1,
  ));
  $topId = $db->lastInsertId();

// Insert main
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'main',
      'page_id' => $pageId,
      'order' => 2,
  ));
  $mainId = $db->lastInsertId();

// Insert top-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $pageId,
      'parent_content_id' => $topId,
  ));
  $topMiddleId = $db->lastInsertId();

// Insert main-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $pageId,
      'parent_content_id' => $mainId,
      'order' => 3,
  ));
  $mainMiddleId = $db->lastInsertId();

// Insert main-right
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'right',
      'page_id' => $pageId,
      'parent_content_id' => $mainId,
      'order' => 2,
  ));
  $mainRightId = $db->lastInsertId();

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.browse-menu',
      'page_id' => $pageId,
      'parent_content_id' => $topMiddleId,
      'order' => $widgetOrder++,
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.activity-points-info',
      'page_id' => $pageId,
      'parent_content_id' => $mainMiddleId,
      'order' => $widgetOrder++,
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.how-to-earn-points',
      'page_id' => $pageId,
      'parent_content_id' => $mainMiddleId,
      'order' => $widgetOrder++,
      'params' => '{"guideline":"<div>\r\n<p><strong>Lorem Ipsum<\/strong>&nbsp;is simply dummy text of the printing and typesetting industry.<\/p>\r\n<ol>\r\n<li>Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, <\/li>\r\n<li>When an unknown printer took a galley of type and scrambled it to make a type specimen book.<\/li>\r\n<li>It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.<\/li>\r\n<li>It was popularised in the 1960s with the release of Letraset sheets containing.<\/li>\r\n<li>Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.<\/li>\r\n<\/ol>\r\n<\/div>","title":"How To Earn Point","nomobile":"0","name":"sescredit.how-to-earn-points"}',
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.recent-point-activity',
      'page_id' => $pageId,
      'parent_content_id' => $mainRightId,
      'order' => $widgetOrder++,
      'params' => '{"limit":"5","title":"Recent Activity","nomobile":"0","name":"sescredit.recent-point-activity"}',
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.top-point-receiver-members',
      'page_id' => $pageId,
      'parent_content_id' => $mainRightId,
      'order' => $widgetOrder++,
      'params' => '{"limit":"5","title":"Top Members","nomobile":"0","name":"sescredit.top-point-receiver-members"}',
  ));
}
$pageId = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sescredit_index_help')
        ->limit(1)
        ->query()
        ->fetchColumn();

// insert if it doesn't exist yet
if (!$pageId) {
  $widgetOrder = 1;
// Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sescredit_index_help',
      'displayname' => 'SES - Credits - Help & Learn More Page',
      'title' => '',
      'description' => '',
      'custom' => 0,
  ));
  $pageId = $db->lastInsertId();

// Insert top
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'top',
      'page_id' => $pageId,
      'order' => 1,
  ));
  $topId = $db->lastInsertId();

// Insert main
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'main',
      'page_id' => $pageId,
      'order' => 2,
  ));
  $mainId = $db->lastInsertId();

// Insert top-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $pageId,
      'parent_content_id' => $topId,
  ));
  $topMiddleId = $db->lastInsertId();

// Insert main-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $pageId,
      'parent_content_id' => $mainId,
      'order' => 3,
  ));
  $mainMiddleId = $db->lastInsertId();

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.browse-menu',
      'page_id' => $pageId,
      'parent_content_id' => $topMiddleId,
      'order' => $widgetOrder++,
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.help-and-learn',
      'page_id' => $pageId,
      'parent_content_id' => $mainMiddleId,
      'order' => $widgetOrder++,
      'params' => '{"title":"","name":"sescredit.help-and-learn"}',
  ));
}
$pageId = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sescredit_index_badges')
        ->limit(1)
        ->query()
        ->fetchColumn();

// insert if it doesn't exist yet
if (!$pageId) {
  $widgetOrder = 1;
// Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sescredit_index_badges',
      'displayname' => 'SES - Credits - Badges Page',
      'title' => '',
      'description' => '',
      'custom' => 0,
  ));
  $pageId = $db->lastInsertId();

// Insert top
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'top',
      'page_id' => $pageId,
      'order' => 1,
  ));
  $topId = $db->lastInsertId();

// Insert main
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'main',
      'page_id' => $pageId,
      'order' => 2,
  ));
  $mainId = $db->lastInsertId();

// Insert top-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $pageId,
      'parent_content_id' => $topId,
  ));
  $topMiddleId = $db->lastInsertId();

// Insert main-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $pageId,
      'parent_content_id' => $mainId,
      'order' => 3,
  ));
  $mainMiddleId = $db->lastInsertId();

// Insert main-right
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'right',
      'page_id' => $pageId,
      'parent_content_id' => $mainId,
      'order' => 2,
  ));
  $mainRightId = $db->lastInsertId();

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.browse-menu',
      'page_id' => $pageId,
      'parent_content_id' => $topMiddleId,
      'order' => $widgetOrder++,
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.badges',
      'page_id' => $pageId,
      'parent_content_id' => $mainMiddleId,
      'order' => $widgetOrder++,
      'params' => '{"title":"","name":"sescredit.badges"}',
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.top-point-receiver-members',
      'page_id' => $pageId,
      'parent_content_id' => $mainRightId,
      'order' => $widgetOrder++,
      'params' => '{"limit":"5","title":"Top Members","nomobile":"0","name":"sescredit.top-point-receiver-members"}',
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.update-member-level',
      'page_id' => $pageId,
      'parent_content_id' => $mainRightId,
      'order' => $widgetOrder++,
  ));
}
$pageId = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sescredit_index_leaderboard')
        ->limit(1)
        ->query()
        ->fetchColumn();

// insert if it doesn't exist yet
if (!$pageId) {
  $widgetOrder = 1;
// Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sescredit_index_leaderboard',
      'displayname' => 'SES - Credits - Leaderboard Page',
      'title' => '',
      'description' => '',
      'custom' => 0,
  ));
  $pageId = $db->lastInsertId();

// Insert top
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'top',
      'page_id' => $pageId,
      'order' => 1,
  ));
  $topId = $db->lastInsertId();

// Insert main
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'main',
      'page_id' => $pageId,
      'order' => 2,
  ));
  $mainId = $db->lastInsertId();

// Insert top-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $pageId,
      'parent_content_id' => $topId,
  ));
  $topMiddleId = $db->lastInsertId();

// Insert main-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $pageId,
      'parent_content_id' => $mainId,
      'order' => 3,
  ));
  $mainMiddleId = $db->lastInsertId();

  // Insert main-left
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'left',
      'page_id' => $pageId,
      'parent_content_id' => $mainId,
      'order' => 1,
  ));
  $mainLeftId = $db->lastInsertId();

// Insert main-right
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'right',
      'page_id' => $pageId,
      'parent_content_id' => $mainId,
      'order' => 2,
  ));
  $mainRightId = $db->lastInsertId();

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.browse-menu',
      'page_id' => $pageId,
      'parent_content_id' => $topMiddleId,
      'order' => $widgetOrder ++,
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.purchase-points',
      'page_id' => $pageId,
      'parent_content_id' => $mainLeftId,
      'order' => $widgetOrder ++,
      'params' => '{"title":"Purchase Points","name":"sescredit.purchase-points"}',
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.my-points-information',
      'page_id' => $pageId,
      'parent_content_id' => $mainLeftId,
      'order' => $widgetOrder ++,
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sescredit.browse-top-members',
      'page_id' => $pageId,
      'parent_content_id' => $mainMiddleId,
      'order' => $widgetOrder ++,
      'params' => '{"friendButton":"1","followButton":"1","limit":"5","title":"Our Top Earners","nomobile":"0","name":"sescredit.browse-top-members"}',
  ));
  if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sescommunityads')) {
    $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'sescommunityads.want-more-customers',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => $widgetOrder ++,
    ));
    $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'sescommunityads.sidebar-widget-ads',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => $widgetOrder ++,
        'params' => '{"category":"","featured_sponsored":"3","limit":"1","title":"","nomobile":"0","name":"sescommunityads.sidebar-widget-ads"}',
    ));
  }
}
$badgeTable = Engine_Api::_()->getDbtable('badges', 'sescredit');
$currentDate = date('Y-m-d H:i:s');
$badges = array(0 => array('badge_id' => 1, 'title' => 'Starter', 'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'credit_value' => '100', 'image' => 'Starter.png'), 1 => array('badge_id' => 2, 'title' => 'Silver', 'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'credit_value' => '1000', 'image' => 'Silver.png'), 2 => array('badge_id' => 3, 'title' => 'Gold', 'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'credit_value' => '2500', 'image' => 'Gold.png'), 3 => array('badge_id' => 4, 'title' => 'Premium', 'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'credit_value' => '5000', 'image' => 'Premium.png'), 4 => array('badge_id' => 5, 'title' => 'Platinum', 'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'credit_value' => '10000', 'image' => 'Platinum.png'));
foreach ($badges as $badge) {
  $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sescredit' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "badges" . DIRECTORY_SEPARATOR . $badge['image'];

  $db->query("INSERT IGNORE INTO `engine4_sescredit_badges` (`badge_id`,`title`,`description`,`credit_value`,`enabled`,`creation_date`) VALUES ( '" . $badge['badge_id'] . "','" . $badge['title'] . "','" . $badge['description'] . "','" . $badge['credit_value'] . "', '1', '" . $currentDate . "')");
  $badgeId = $db->lastInsertId();
  $photoId = Engine_Api::_()->sescredit()->setPhoto($PathFile, array('badge_id' => $badgeId));
  if (!empty($photoId)) {
    $db->update('engine4_sescredit_badges', array('photo_id' => $photoId), array('badge_id =?' => $badgeId));
  }
}
$siteOffers = array(0 => array('offer_id' => 1, 'point' => 500, 'point_value' => 1), 1 => array('offer_id' => 2, 'point' => 1000, 'point_value' => 5));
foreach ($siteOffers as $siteOffer) {
  $db->query("INSERT IGNORE INTO `engine4_sescredit_offers` (`offer_id`,`point_value`,`point`,`limit_offer`,`user_avail`,`offer_time`,`starttime`,`endtime`,`enable`) VALUES ( '" . $siteOffer['offer_id'] . "','" . $siteOffer['point_value'] . "','" . $siteOffer['point'] . "','5','5','0','','',1)");
}

$db->query("ALTER TABLE `engine4_sescredit_transactions` ADD `ordercoupon_id` INT NULL DEFAULT '0';");
