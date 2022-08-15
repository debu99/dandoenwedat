<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: viewcomment.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if( !isset($this->form) ) return; ?>


<?php echo $this->translate("Comment:") ?>
<?php echo $this->form->render($this) ?>

<script type="text/javascript">
//<![CDATA[
document.getElementsByTagName('form')[0].style.display = 'block';
//]]>
</script>
