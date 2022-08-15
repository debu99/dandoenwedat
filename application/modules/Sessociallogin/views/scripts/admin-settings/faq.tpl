<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php include APPLICATION_PATH .  '/application/modules/Sessociallogin/views/scripts/dismiss_message.tpl'; ?>
<div class="sesbasic_faqs">
  <ul>
    <li>
      <div class="faq_ques"><?php echo $this->translate("Question: After upgrading my SocialEngine, I have faced Fatal Error in admin Signup process page. What should I do?");?></a></div>
      <div class='faq_ans'>
        <?php echo $this->translate("Ans: As we informed you during the activation of the plugin that we need to update code in 3 files of SocialEngine’s user plugin, after the upgrade of SocialEngine, our code is lost from those 3 files. So, now you need to add the code again into those files, and for that please use any of the 2 methods mentioned below:");?><br /><br />
        <p style="float:right;"><?php echo "<a href='admin/sessociallogin/settings/codewrite' class='sesbasic_button'>Update Code</a>"; ?></p>
        <p class="bold"><?php echo $this->translate("Method 1: Automatic Updation of code into the files.");?></p>
        <p><?php echo "If you have not done any custom work in the User Module >> Form >> Admin >> Signup folder, then you can use this method."; ?></p><br />
        <p class="bold"><?php echo "Method 2:" ?></p>
        <p><?php echo "If you have done any custom work in the User Module >> Form >> Admin >> Signup folder, then please write the code manually to make this plugin work:"; ?></p>
        <p><b class="bold">Step1: </b> Open below files:</p>
        <p>i) File at path: 'application/modules/User/Form/Admin/Signup/Account.php'</p>
        <p>ii) File at path: 'application/modules/User/Form/Admin/Signup/Fields.php'</p>
        <p>iii) File at path: 'application/modules/User/Form/Admin/Signup/Photo.php'</p>
        <p><b class="bold">Step2: </b> Search the line of code as shown below:</p>
        <code class="codebox"><?php echo '$stepSelect = $stepTable->select()->where("class = ?", str_replace("_Form_Admin_", "_Plugin_", get_class($this)));'; ?></code>
        <p><b class="bold">Step3: </b> Replace the code in step2 with the code mentioned below:</p>
        <code class="codebox"><?php echo '//Add Social Login Plugin Work'; ?><br />
          <?php echo '$socialloginPluginEnable = Engine_Api::_()->getDbTable("modules", "core")->isModuleEnabled("sessociallogin");'; ?><br />
          <?php echo 'if($socialloginPluginEnable) {'; ?><br />
          <?php echo '$stepSelect = $stepTable->select()->where("class = ?", str_replace("User_Form_Admin_", "Sessociallogin_Plugin_", get_class($this)));'; ?><br />
          <?php echo '} else {'; ?><br />
          <?php echo '$stepSelect = $stepTable->select()->where("class = ?", str_replace("_Form_Admin_", "_Plugin_", get_class($this)));'; ?><br />
          <?php echo '}'; ?>
        </code>
      </div>
    </li>
  </ul>
</div>