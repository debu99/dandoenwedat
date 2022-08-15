<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _emoji.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<div  class="emoji_contents">
  <?php 
    if($this->edit)
      $class="edit";
    else
      $class = '';
    $emojis = Engine_Api::_()->getApi('emoji','sesbasic')->getEmojisArray();?>
    <div class="sesbasic_custom_scroll">
    <ul class="_simemoji">
    <?php
    foreach($emojis as $key=>$emoji){ ?>   
      <li rel="<?php echo $key; ?>"><a href="javascript:;" class="select_emoji_adv<?php echo $class; ?>"><?php echo $emoji; ?></a></li>  
  <?php 
    } ?>
    </ul>
    </div>
    <?php if(!$this->edit){ ?>
    <script type="application/javascript">
    sesJqueryObject(document).on('click','.select_emoji_adv > img',function(e){
      var code = sesJqueryObject(this).parent().parent().attr('rel');
      var html = sesJqueryObject('.compose-content').html();
      if(html == '<br>')
        sesJqueryObject('.compose-content').html('');
      composeInstance.setContent(composeInstance.getContent()+' '+code);
    });
    </script>
    <?php } ?>
  </div>