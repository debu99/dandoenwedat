<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: post.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>

<h2>
  <?php echo $this->event->__toString()." ".$this->translate("&#187; Discussions") ?>
</h2>

<h3>
  <?php echo $this->topic->__toString() ?>
</h3>

<br />

<?php if( $this->message ) echo $this->message ?>

<?php if( $this->form ) echo $this->form->render($this) ?>