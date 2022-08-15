<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _colorchooser.tpl 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php 
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/mooRainbow.js');

$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/mooRainbow.css'); 
?> 
<script type="text/javascript">
  window.addEvent('domready', function() {
    new MooRainbow('colorChooser', {
      id: 'colorChooser',
      'onChange': function(color) {
        $('sesbasic_color').value = color.hex;
      }
    });
  });
</script>
<div id="sesbasic_color-wrapper" class="form-wrapper">
  <div id="sesbasic_color-element" class="form-element">
    <p class="description"><?php echo $this->translate('Choose Color')?></p>
    <input name="sesbasic_color" id="sesbasic_color" value="#ffffff" type="text">
    <input name="colorChooser" id="colorChooser" src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sesbasic/externals/images/colorpicker/rainbow.png" link="true" type="image">
  </div>
</div>