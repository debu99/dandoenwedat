<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _share.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php $href = $this->href; 
      $action = $this->action;
      $isShareEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.socialshare','1');
      $AdvShare = $this->AdvShare;
      if(!$isShareEnable)
        return;
      $enablesocialshare = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.enablesocialshare', 1);
      $enablesessocialshare = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.enablesessocialshare', 1);
?>
<?php if($enablesocialshare) { ?>
  <div class="sesadvcmt_hoverbox"> 
    <span> 
      <span class="sesadvcmt_hoverbox_btn" onClick="openSmoothBoxInUrl('<?php echo !empty($AdvShare) ? $AdvShare : $href; ?>');">
        <div class="sesadvcmt_hoverbox_btn_icon"><i class="like" style="background-image:url(application/modules/Sesadvancedcomment/externals/images/share.png)"></i> </div>
      </span>
      <div class="text">
        <div><?php echo $this->translate("Share on %s", $_SERVER['HTTP_HOST']); ?></div>
      </div>
    </span> 
    <span> 
      <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->facebookShareUrl($href,$action); ?>','Facebook');">
        <div class="sesadvcmt_hoverbox_btn_icon"> <i class="love" style="background-image:url(application/modules/Sesadvancedcomment/externals/images/facebook.png); "></i> </div>
      </span>
      <div class="text">
        <div><?php echo $this->translate("Facebook"); ?></div>
      </div>
    </span> 
    <span> 
      <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->twitterShareUrl($href,$action); ?>','Twitter');">
        <div class="sesadvcmt_hoverbox_btn_icon"> <i class="anger" style="background-image:url(application/modules/Sesadvancedcomment/externals/images/twitter.png); "></i> </div>
      </span>
      <div class="text">
        <div><?php echo $this->translate("Twitter"); ?></div>
      </div>
    </span>

    <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.linkedin.enable')) { ?>
      <span> 
        <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->LinkedinShareUrl($href,$action); ?>','Linkedin');">
          <div class="sesadvcmt_hoverbox_btn_icon"><i class="wow" style="background-image:url(application/modules/Sesadvancedcomment/externals/images/linkedin.png); "></i> </div>
        </span>
        <div class="text">
          <div><?php echo $this->translate("Linkedin"); ?></div>
        </div>
      </span>  
    <?php } ?>
  </div>
<?php } else if($enablesessocialshare && Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sessocialshare')) { ?>

  <?php 
    $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'].$href);

    $facebokClientId = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.facebook.appid', '');
    $socialicons = Engine_Api::_()->getDbTable('socialicons', 'sessocialshare')->getSocialInfo(array('enabled' => 1, 'limit' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.iconlimit', 3))); 
  ?>
  <script>
  sesJqueryObject(document).on('click','.ss_whatsapp',function(){
    var text = '';
    var url = '<?php echo ((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ?>';
    
    //Counter increase
    var	urlsave = en4.core.baseUrl+'sessocialshare/index/savesocialsharecount/';
    var socialShareCountSave =	(new Request.HTML({
        method: 'post',
        'url': urlsave,
        'data': {
          title: '',
          pageurl: '<?php echo $urlencode; ?>',
          type: 'whatsapp',
          format: 'html',
        },
        onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
          //keep Silence
          //location.reload();
          if(showCount == 1) {
            var countType = sesJqueryObject('.sessocialshare_count_'+type).html();
            sesJqueryObject('.sessocialshare_count_'+type).html(++countType);
          }
        }
    }));
    socialShareCountSave.send();
    
    var message = encodeURIComponent(text) + " - " + encodeURIComponent(url);
    var whatsapp_url = "whatsapp://send?text=" + message;
    window.location.href = whatsapp_url;
  });

  </script>
  <div class="sesadvcmt_hoverbox"> 
  
    <span>
      <span class="sesadvcmt_hoverbox_btn" onClick="openSmoothBoxInUrl('<?php echo !empty($AdvShare) ? $AdvShare : $href; ?>');">
        <div class="sesadvcmt_hoverbox_btn_icon"> <i class="like" style="background-image:url(application/modules/Sesadvancedcomment/externals/images/share.png)"></i> </div>
      </span>
      <div class="text">
        <div><?php echo $this->translate("Share on %s", $_SERVER['HTTP_HOST']); ?></div>
      </div>
    </span>
    
    <?php foreach($socialicons as $socialicon):  ?>
      <?php if($socialicon->type == 'facebook') { ?>

        <span> 
          <span class="sesadvcmt_hoverbox_btn" onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->facebookShareUrl($href,$action); ?>','<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>');">
            <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_facebook" style="background-image:url(application/modules/Sessocialshare/externals/images/social/facebook.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On Facebook"); ?></div>
          </div>
        </span>
        
      <?php } elseif($socialicon->type == 'twitter') { ?>

        <span> 
          <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->twitterShareUrl($href,$action); ?>','<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>');">
            <div class="sesadvcmt_hoverbox_btn_icon"> <i class="sessocialshare_feed_icon sessocialshare_feed_icon_twitter" style="background-image:url(application/modules/Sessocialshare/externals/images/social/twitter.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On Twitter"); ?></div>
          </div>
        </span>
        
        
      <?php } elseif($socialicon->type == 'linkedin') { ?>
      
        <span> 
          <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->LinkedinShareUrl($href,$action); ?>','<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>');">
            <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_linkedin" style="background-image:url(application/modules/Sessocialshare/externals/images/social/linkedin.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On LinkedIn"); ?></div>
          </div>
        </span>  
      <?php } elseif($socialicon->type == 'googleplus') { ?>
      
        <span> 
          <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->googlePlusShareUrl($href,$action); ?>','<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>);">
            <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_google" style="background-image:url(application/modules/Sessocialshare/externals/images/social/google-plus.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On Google Plus"); ?></div>
          </div>
        </span>
        
      <?php } elseif($socialicon->type == 'gmail') { ?>

        <span> 
          <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->advShareUrl($href, $action, 'gmail'); ?>','<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>');">
            <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_gmail" style="background-image:url(application/modules/Sessocialshare/externals/images/social/gmail.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On Gmail"); ?></div>
          </div>
        </span>
      <?php } elseif($socialicon->type == 'tumblr') { ?>

        <span> 
          <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->advShareUrl($href, $action, 'tumblr'); ?>','<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>');">
            <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_tumblr" style="background-image:url(application/modules/Sessocialshare/externals/images/social/tumblr.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On Tumblr"); ?></div>
          </div>
        </span>
        
      <?php } elseif($socialicon->type == 'digg') { ?>

        <span> 
          <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->advShareUrl($href, $action, 'digg'); ?>','<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>');">
            <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_digg" style="background-image:url(application/modules/Sessocialshare/externals/images/social/digg.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On Digg"); ?></div>
          </div>
        </span>
      <?php } elseif($socialicon->type == 'stumbleupon') { ?>
        <span> 
          <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->advShareUrl($href, $action, 'stumbleupon'); ?>','<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>');">
            <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_stumbleupon" style="background-image:url(application/modules/Sessocialshare/externals/images/social/stumbleupon.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On Stumbleupon"); ?></div>
          </div>
        </span>
      <?php } elseif($socialicon->type == 'myspace') { ?>

        <span> 
          <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->advShareUrl($href, $action, 'myspace'); ?>','<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>');">
            <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_myspace" style="background-image:url(application/modules/Sessocialshare/externals/images/social/myspace.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On Myspace"); ?></div>
          </div>
        </span>
        
      <?php } elseif($socialicon->type == 'facebookmessager' && Engine_Api::_()->getApi('settings', 'core')->getSetting('core.facebook.appid', '')) { ?>

        <span> 
          <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->advShareUrl($href, $action, 'facebookmessager'); ?>','<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>');">
            <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_facebook_messenger" style="background-image:url(application/modules/Sessocialshare/externals/images/social/facebook_messenger.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On Facebook Messenger"); ?></div>
          </div>
        </span>
      <?php } elseif($socialicon->type == 'rediff') { ?>
        <span> 
          <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->advShareUrl($href, $action, 'rediff'); ?>','<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>');">
            <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_rediff" style="background-image:url(application/modules/Sessocialshare/externals/images/social/rediff.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On Rediff"); ?></div>
          </div>
        </span>
      <?php } elseif($socialicon->type == 'googlebookmark') { ?>
        <span> 
          <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->advShareUrl($href, $action, 'googlebookmark'); ?>','<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>');">
            <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_bookmark" style="background-image:url(application/modules/Sessocialshare/externals/images/social/bookmark.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On Google Bookmark"); ?></div>
          </div>
        </span> 
      <?php } elseif($socialicon->type == 'flipboard') { ?>

        <span> 
          <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->advShareUrl($href, $action, 'flipboard'); ?>','<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>');">
            <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_flipboard" style="background-image:url(application/modules/Sessocialshare/externals/images/social/flipboard.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On Flipboard"); ?></div>
          </div>
        </span> 
      <?php } elseif($socialicon->type == 'skype') { ?>
        <span> 
          <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->advShareUrl($href, $action, 'skype'); ?>','<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>');">
            <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_skype" style="background-image:url(application/modules/Sessocialshare/externals/images/social/skype.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On Skype"); ?></div>
          </div>
        </span> 
      <?php } elseif($socialicon->type == 'whatsapp') { ?>

        <span class="sesadvcmt_hoverbox_btn_whatsapp"> 
          <span class="sesadvcmt_hoverbox_btn ss_whatsapp">
            <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_whatsapp" style="background-image:url(application/modules/Sessocialshare/externals/images/social/whatsapp.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On Whatsapp"); ?></div>
          </div>
        </span> 
        
      <?php } elseif($socialicon->type == 'pinterest') { ?>
        <span> 
          <span class="sesadvcmt_hoverbox_btn"  onClick="socialSharingPopUp('<?php echo Engine_Api::_()->sesbasic()->advShareUrl($href, $action, 'pinterest'); ?>','<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>');">
            <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_pintrest" style="background-image:url(application/modules/Sessocialshare/externals/images/social/pinterest.png); "></i> </div>
          </span>
          <div class="text">
            <div><?php echo $this->translate("Share On Pinterest"); ?></div>
          </div>
        </span> 
      <?php } ?>
    <?php endforeach; ?>
    <?php if(count($socialicons) > 0 && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.enableplusicon', 0)) { ?>
    
      <span> 
        <span class="open sesadvcmt_hoverbox_btn sessocial_icon_add_btn sessmoothbox" data-url="<?php echo $this->layout()->staticBaseUrl.'sessocialshare/index/index?url='.$urlencode; ?>">
          <div class="sesadvcmt_hoverbox_btn_icon"><i class="sessocialshare_feed_icon sessocialshare_feed_icon_plus" style="background-image:url(application/modules/Sessocialshare/externals/images/social/more.png); "></i> </div>
        </span>
        <div class="text">
          <div><?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sessocialshare.more.title', 'More'); ?></div>
        </div>
      </span> 
      
    <?php } ?>
  </div>
<?php } ?>