<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: spam.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>

<h2>
  <?php echo $this->translate("Spam & Banning Tools") ?>
</h2>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>

<script type="application/javascript">
function changeLock(obj) {
    var value = obj.value
    if(value == 1){
        document.getElementById('lockattempts-wrapper').style.display = "block";
        document.getElementById('lockduration-wrapper').style.display = "block";
    }else{
        document.getElementById('lockattempts-wrapper').style.display = "none";
        document.getElementById('lockduration-wrapper').style.display = "none";
    }
}

window.addEvent('domready', function() {
  changeLock($$('input[name=lockaccount]:checked')[0]);
});
</script>
