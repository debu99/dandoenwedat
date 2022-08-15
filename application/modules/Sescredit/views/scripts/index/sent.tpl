<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: sent.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<h2>
  <?php echo $this->translate(array("Invitation Sent","Invitations Sent",$this->emails_sent)) ?>
</h2>
<?php if (!empty($this->form->invalid_emails)): ?>
  <p><?php echo $this->translate('Invites were not sent to these email addresses because they do not appear to be valid:') ?></p>
  <ul>
    <?php foreach ($this->form->invalid_emails as $email): ?>
    <li><?php echo $email ?></li>
    <?php endforeach ?>
  </ul>
<?php endif ?>
<?php if (!empty($this->form->already_members)): ?>
  <p>
    <?php echo $this->translate('Some of the email addresses you provided belong to existing members:') ?>
    <?php foreach ($this->form->already_members as $user): ?>
      <?php echo $user->toString() ?>
    <?php endforeach ?>
  </p>
<?php endif ?>
<br />
<a href="javascript:void(0);" onclick="javascript:parent.Smoothbox.close()" ><?php echo $this->translate('OK, thanks!');?></a>
