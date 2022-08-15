<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: create-color-variant.tpl  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<div class="settings">
  <?php echo $this->form->render($this) ?>
</div>

<script type="text/javascript">
  var fetchColorVariant = function(variantName) {
    var url = en4.core.baseUrl+'admin/themes/create-color-variant/name/';
    window.location.href = url + variantName;
  }

  var showSubmit = function() {
    $('submitWrapper').setStyle('display', 'block');
  }

</script>
