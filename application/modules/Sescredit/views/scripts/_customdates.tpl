<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _customdates.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<?php $setting = Engine_Api::_()->getApi('settings','core');?>
<div class="sescredit_choose_date" id="sescredit_announcement_date">
  <div id="credit_end_time-wrapper" class="form-wrapper">
    <div id="credit_end_time-label" class="form-label">
      <label for="credit_end_time" class="optional"><?php echo 'Credit Expiry Time Duration'; ?></label>
    </div>
    <?php $year =  $setting->getSetting('sescredit.year');?>
    <?php $month = $setting->getSetting('sescredit.month');?>
    <div id="credit_end_time-element" class="form-element">
      <span class="sescredit-date-field">
        <select name="sescredit_year" id="year" class="">
          <option value="">Select Year</option>
          <?php for($i=0;$i<=15;$i++):?>
          <option value="<?php echo $i;?>" <?php if($year == $i):?>selected<?php endif;?>><?php echo $i;?></option>
          <?php endfor;?>
        </select>
        <select name="sescredit_month" id="month" class="">
          <option value="0">Select Month</option>
          <?php for($i=1;$i<=12;$i++):?>
            <option value="<?php echo $i;?>" <?php if($month == $i):?>selected<?php endif;?>><?php echo $i;?></option>
          <?php endfor;?>
        </select>
      </span>
    </div>
  </div>
</div>