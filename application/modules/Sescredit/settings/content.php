<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: content.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
$followButton = array();
if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesmember')) {
  $followButton = array(
      'Radio',
      'followButton',
      array(
          'label' => "Do you want to show Follow button for members shown in this widget? ",
          'multiOptions' => array(
              '1' => 'Yes',
              '0' => 'No',
          ),
          'value' => 1,
      )
  );
}
return array(
    array(
        'title' => 'SES - Credits - My Points',
        'description' => 'This widget will display the total credit points earned by the current user. The recommended page for this widget is Manage Credit Points Page.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.my-points',
    ),
    array(
        'title' => 'SES - Credits - Upgrade Membership',
        'description' => 'Members of your site can use this widget to upgrade their member level if they have enough credit points to do so.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.update-member-level',
    ),
    array(
        'title' => 'SES - Credits - Purchase Points',
        'description' => 'With this widget users can purchase desired credit points either through site offers or by making payment to the site owner. The recommended page for this widget is Transactions Page.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.purchase-points',
    ),
    array(
        'title' => 'SES - Credits - Recent Activity',
        'description' => 'This widget displays all the information about credit points on your website based on modules & activities. It displays credit points based on First Time, Next Time, Max Points/Day, activity Deduction Points.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.recent-point-activity',
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'limit',
                    array(
                        'label' => "Enter the total number of activities.",
                        'value' => 10,
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Credits - Send Point to Friend',
        'description' => 'Users can send earned credit points to their friends with this widget. The recommended page for this widget is Manage Credit Points Page.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.send-point-friend',
    ),
    array(
        'title' => 'SES - Credits - Referral Signup',
        'description' => 'With this widget current user can send referral link to their friends for Signup and can earn credit points with these signups.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.invite-friends',
    ),
    array(
        'title' => 'SES - Credits - Terms & Conditions',
        'description' => 'This widget will display the terms & conditions which users need to follow for earning the credit points by doing various activities on your site.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.terms',
        'autoEdit' => true,
        'adminForm' => 'Sescredit_Form_Admin_Terms',
    ),
    array(
        'title' => 'SES - Credits - How To Earn Point',
        'description' => 'This widget will display the list of activities by doing which users can earn credit points on your website. The recommended page for this widget is Earn Credit Page.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.how-to-earn-points',
        'autoEdit' => true,
        'adminForm' => 'Sescredit_Form_Admin_Earnguidelines',
    ),
    array(
        'title' => 'SES - Credits - My Transactions',
        'description' => 'This widget will display all the Transactions for activity points for the current user. The recommended page for this widget is Transactions Page.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.my-transactions',
    ),
    array(
        'title' => 'SES - Credits - Top Members',
        'description' => 'This widget will display all the top members with their name and total earned credit points who have earned maximum credit points till the current time.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.top-point-receiver-members',
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'limit',
                    array(
                        'label' => "Enter the total number of users.",
                        'value' => 10,
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Credits - My Badge',
        'description' => ' This widget will display the Badge of current user on your website. You can place this widget at any page of this plugin.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.my-badge',
    ),
    array(
        'title' => 'SES - Credits - My Points Information',
        'description' => 'This widget will display all the details & information about the total earned credit points for the current user.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.my-points-information',
    ),
    array(
        'title' => 'SES - Credits - Credit Points Details & Information',
        'description' => 'This widget displays all the information about credit points on your website.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.activity-points-info',
    ),
    array(
        'title' => 'SES - Credits - Navigation Menu',
        'description' => 'Displays a navigation menu bar in the credits pages like My Credits, My Transactions, Categories, etc.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.browse-menu',
        'requirements' => array(
            'no-subject',
        ),
    ),
    array(
        'title' => 'SES - Credits -  Transaction Browse Search',
        'description' => 'Displays search form in the credit transaction page. This widget should be placed on "Credits - Transaction Page.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'autoEdit' => 'true',
        'name' => 'sescredit.browse-search',
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'criteria',
                    array(
                        'label' => "Choose options to be shown in 'View' search fields.",
                        'multiOptions' => array(
                            '0' => 'All Credits',
                            'today' => 'Today',
                            'week' => 'This Week',
                            'month' => 'This Month',
                        ),
                    )
                ),
                array(
                    'Select',
                    'default_view_search_type',
                    array(
                        'label' => "Default value for 'View' search field.",
                        'multiOptions' => array(
                            '0' => 'All Credits',
                            'today' => 'Today',
                            'week' => 'This Week',
                            'month' => 'This Month',
                        ),
                    )
                ),
                array(
                    'MultiCheckbox',
                    'show_option',
                    array(
                        'label' => "Choose from below the search fields to be shown in this widget.",
                        'multiOptions' => array(
                            'view' => 'View',
                            'chooseDate' => 'Choose Date Range',
                        ),
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Credits - Browse Top Members',
        'description' => 'With this widget, Site users can browse top members who have earned maximum credit points on your website by doing various activities.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.browse-top-members',
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'friendButton',
                    array(
                        'label' => "Do you want to show Add Friend button in this widget?",
                        'multiOptions' => array(
                            '1' => 'Yes',
                            '0' => 'No',
                        ),
                        'value' => 1,
                    )
                ),
                $followButton,
                array(
                    'Text',
                    'limit',
                    array(
                        'label' => "Enter the total number of users.",
                        'value' => 10,
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Credits - Help And Learn',
        'description' => 'This widget displays Help & Learn center of Credits on your website.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.help-and-learn',
    ),
    array(
        'title' => 'SES - Credits - Badges',
        'description' => 'This widget will display all the badges which users will get for the credit points they earn.',
        'category' => 'SES - Credits & Activity / Reward Points Plugin',
        'type' => 'widget',
        'name' => 'sescredit.badges',
    ),
);
