<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesmembershipswitch
 * @package    Sesmembershipswitch
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _notification.tpl  2018-10-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<div id="sesmembershipswitch_notificationplan-wrapper" class="form-wrapper">
  <div id="sesmembershipswitch_notificationplan-label" class="form-label">
    <label for="sesmembershipswitch_notificationplan" class="required">Email & Notification Reminders</label>
  </div>
  <div id="sesmembershipswitch_notificationplan-element" class="form-element">
  <div>
  <p class="description">Choose the duration from the Plan Expiry before which Notification and Email should be sent to members of your website. This duration will be calculated on the addition of expiry date of Plan and the Days Limit entered in the above setting. For example: Lets say, you choose 3 Days in this setting below and the Plan will be expiring on 20th Of a month. Now, if you have entered 7 in the above "Days Limit" setting which means members actual subscription will end in 20 + 7 days = 27th of month. So, as per the setting chosen below, they should get notification and email 3 days before the expiration of their plans and thus they will be notified on 24th of the month. (Note: Expiration for free plan is considered from the date of plan creation since there is no expiration of Free plans and for the <a href="https://support.socialengine.com/php/customer/en/portal/articles/2658794-setting-up-subscription-plans?b_id=14386%3C/a%3E" Target="_blank">Trial plan</a> expiration will depend on the "Billing Cycle" of the Trial plan.) </p>
  </div>
   <input type="text" name="number_switch" id="number_switch" value="1" pattern="[0-9]*">
    <select name="type_switch" id="type_switch">
      <option value="minutes">Minutes</option>
      <option value="hours">Hours</option>
      <option value="days" selected>Days</option>
      <option value="weeks">weeks</option>
    </select>
  </div>
</div>
