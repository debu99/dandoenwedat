<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: account.tpl 10143 2014-03-26 16:18:25Z andres $
 * @author     John
 */
?>

<style>
#signup_account_form #name-wrapper {
  display: none;
}
</style>

<script type="text/javascript">
//<![CDATA[
  window.addEvent('load', function() {
    if( $('username') && $('profile_address') ) {
      $('profile_address').innerHTML = $('profile_address')
        .innerHTML
        .replace('<?php echo /*$this->translate(*/'yourname'/*)*/?>',
          '<span id="profile_address_text"><?php echo $this->translate('yourname') ?></span>');

      $('username').addEvent('keyup', function() {
        var text = '<?php echo $this->translate('yourname') ?>';
        if( this.value != '' ) {
          text = this.value;
        }
        
        $('profile_address_text').innerHTML = text.replace(/[^a-z0-9]/gi,'');
      });
      // trigger on page-load
      if( $('username').value.length ) {
          $('username').fireEvent('keyup');
      }
    }
  });
//]]>
</script>

<?php echo $this->form->render($this) ?>

<script type="text/javascript">
  function passwordRoutine(value){
      var pswd = value;
      // valid length
      if ( pswd.length < 6) {
        $('passwordroutine_length').removeClass('valid').addClass('invalid');
      } else {
        $('passwordroutine_length').removeClass('invalid').addClass('valid');
      }

      //validate special character
      if ( pswd.match(/[#?!@$%^&*-]/) ) {
          if ( pswd.match(/[\\\\:\/]/) ) {
              $('passwordroutine_specialcharacters').removeClass('valid').addClass('invalid');
          } else {
              $('passwordroutine_specialcharacters').removeClass('invalid').addClass('valid');
          }
      } else {
          $('passwordroutine_specialcharacters').removeClass('valid').addClass('invalid');
      }

      //validate capital letter
      if ( pswd.match(/[A-Z]/) ) {
          $('passwordroutine_capital').removeClass('invalid').addClass('valid');
      } else {
          $('passwordroutine_capital').removeClass('valid').addClass('invalid');
      }

      //validate small letter
      if ( pswd.match(/[a-z]/) ) {
          $('passwordroutine_lowerLetter').removeClass('invalid').addClass('valid');
      } else {
          $('passwordroutine_lowerLetter').removeClass('valid').addClass('invalid');
      }

      //validate number
      if ( pswd.match(/\d{1}/) ) {
          $('passwordroutine_number').removeClass('invalid').addClass('valid');
      } else {
          $('passwordroutine_number').removeClass('valid').addClass('invalid');
      }
  }
</script>
