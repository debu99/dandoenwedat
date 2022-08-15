<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _creditRange.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<div class="sescredit_rang_slider sesbasic_clearfix">
  <!--<div id="slider-range" class="price-filter-range" name="rangeInput"></div>-->
  <div class="form-label"><label for="show_date_field" class="optional"><?php echo $this->translate("Choose Points Range"); ?></label></div>
  <div class="form-element">
    <input type="text" name="min_point" onblur="validity.valid||(value='0');" onkeypress="return isNumberKey(event)" id="min_price" class="price-range-field _min"  value="<?php echo isset($_GET['min_point']) ? $_GET['min_point'] : '';?>" placeholder='<?php echo $this->translate("Min")?>' />
    <input type="text" name="max_point" onblur="validity.valid||(value='10000');" onkeypress="return isNumberKey(event)" id="max_price" class="price-range-field _max" value="<?php echo isset($_GET['max_point']) ? $_GET['max_point'] : '';?>" placeholder='<?php echo $this->translate("Max")?>' />
  </div>
</div>
<style type='text/css'>
  #slider-range {
   display:block !important;
  }
</style>
<script type="text/javascript">
  function isNumberKey(evt){
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    return !(charCode > 31 && (charCode < 48 || charCode > 57));
  }
</script>