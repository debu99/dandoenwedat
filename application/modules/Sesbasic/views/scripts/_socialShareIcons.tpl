<?php ?>

<?php
  $item = $this->resource; 
  $param = $this->param;

  $socialshare_enable_plusicon = $this->socialshare_enable_plusicon; 
  if(!isset($this->socialshare_icon_limit))
    $count = 2;
  else
    $count = $this->socialshare_icon_limit;
  $item_id = $item->getIdentity();
  $item_type = $item->getType();
  $facebokClientId = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbasic.facebookclientid', '');
  $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $item->getHref()); 
?>


<script type="application/javascript">

sesJqueryObject(document).on('click','.ss_whatsapp',function(){
	var text = <?php echo json_encode(strip_tags($item->getTitle())); ?>;
	var url = '<?php echo ((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == "on") ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . $item->getHref(); ?>';
	var message = encodeURIComponent(text) + " - " + encodeURIComponent(url);
	var whatsapp_url = "whatsapp://send?text=" + message;
	window.location.href = whatsapp_url;
});

function socialSharingPopUpShare(url,title){
	window.open(url, title ,'height=500,width=500');
	return false;
}
</script>


<?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sessocialshare') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sessocialshare.pluginactivated')) { ?>
  <?php $socialicons = Engine_Api::_()->getDbTable('socialicons', 'sessocialshare')->getSocialInfo(array('enabled' => 1, 'limit' => $count)); ?>
    <?php foreach($socialicons as $socialicon):  ?>
      <?php if($socialicon->type == 'facebook') { ?>
        <a href="<?php echo 'http://www.facebook.com/sharer/sharer.php?u=' . $urlencode . '&t=' . strip_tags($item->getTitle()); ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_facebook_btn"><i class="fab fa-facebook-f"></i><span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?></span></a>
      <?php } elseif($socialicon->type == 'twitter') { ?>
        <a href="<?php echo 'https://twitter.com/intent/tweet?url=' . $urlencode . '&text=' . htmlspecialchars(urlencode(html_entity_decode($item->getTitle('encode'), ENT_COMPAT, 'UTF-8')), ENT_COMPAT, 'UTF-8').'%0a'; ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title)?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_twitter_btn"><i class="fab fa-twitter"></i><span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span></a>
      <?php } elseif($socialicon->type == 'pinterest') { ?>
        <a href="<?php echo 'http://pinterest.com/pin/create/button/?url='.$urlencode; ?>&media=<?php echo urlencode((strpos($item->getPhotoUrl(),'http') === FALSE ? (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on')) ? "https://" : "http://") . $_SERVER['HTTP_HOST'].$item->getPhotoUrl() ) : $item->getPhotoUrl())); ?>&description=<?php echo strip_tags($item->getTitle());?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_pintrest_btn"><i class="fab fa-pinterest-p"></i><span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span></a>
      <?php } elseif($socialicon->type == 'linkedin') { ?>
        <a href="<?php echo 'https://www.linkedin.com/shareArticle?mini=true&url='.$urlencode; ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>')" class="sesbasic_icon_btn sesbasic_icon_linkedin_btn"><i class="fab fa-linkedin-in"></i><span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span></a>
      <?php } elseif($socialicon->type == 'gmail') { ?>
        <a href="<?php echo 'https://mail.google.com/mail/u/0/?view=cm&fs=1&to&su='.strip_tags($item->getTitle()).'&body='.$urlencode.'&ui=2&tf=1'; ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>')" class="sesbasic_icon_btn sesbasic_icon_gmail_btn"><img src="application/modules/Sessocialshare/externals/images/social/gmail.png" /><span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span></a>
      <?php } elseif($socialicon->type == 'tumblr') { ?>
        <a href="<?php echo 'http://www.tumblr.com/share/link?url='.$urlencode; ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_tumblr_btn"><i class="fab fa-tumblr-square"></i><span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span></a>
      <?php } elseif($socialicon->type == 'digg') { ?>
        <a href="<?php echo 'http://www.tumblr.com/share/link?url='.$urlencode; ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_digg_btn"><i class="fab fa-digg"></i><span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span></a>
      <?php } elseif($socialicon->type == 'stumbleupon') { ?>
        <a href="<?php echo 'http://www.stumbleupon.com/submit?url='.$urlencode.'&title='.strip_tags($item->getTitle()); ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_stumbleupon_btn"><i class="fab fa-stumbleupon"></i><span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span></a>
      <?php } elseif($socialicon->type == 'myspace') { ?>
        <a href="<?php echo 'http://www.myspace.com/Modules/PostTo/Pages/?t='.strip_tags($item->getTitle()) .'&u='.$urlencode.'&l=3'; ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_myspace_btn">
        <img src="application/modules/Sessocialshare/externals/images/social/myspace.png" />
        <span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span>
  		</a>
      <?php } elseif($socialicon->type == 'facebookmessager' && $facebokClientId) { ?>
        <a href="<?php echo 'https://www.facebook.com/dialog/send?link='.$urlencode.'&redirect_uri='.$urlencode.'&app_id='.$facebokClientId; ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_facebookmessager_btn">
        <img src="application/modules/Sessocialshare/externals/images/social/facebook_messenger.png" />
        <span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span>
      </a>
      <?php } elseif($socialicon->type == 'rediff') { ?>
        <a href="<?php echo 'http://share.rediff.com/bookmark/addbookmark?title='.strip_tags($item->getTitle()).'&bookmarkurl='.$urlencode; ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_rediff_btn"><img src="application/modules/Sessocialshare/externals/images/social/rediff.png" />
        <span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span>
      </a>
      <?php } elseif($socialicon->type == 'googlebookmark') { ?>
        <a href="<?php echo 'https://www.google.com/bookmarks/mark?op=edit&output=popup&bkmk='.$urlencode.'&title='.strip_tags($item->getTitle()) ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_bookmarks_btn"><i class="fa fa-bookmark"></i><span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span></a>
      <?php } elseif($socialicon->type == 'flipboard') { ?>
        <a href="<?php echo 'https://share.flipboard.com/bookmarklet/popout?v=2&title='.strip_tags($item->getTitle()).'&url='.$urlencode ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_flipboard_btn">
        <img src="application/modules/Sessocialshare/externals/images/social/flipboard.png" />
        <span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span>
        </a>
      <?php } elseif($socialicon->type == 'skype') { ?>
        <a href="<?php echo 'https://web.skype.com/share?url='.$urlencode.'&lang=en' ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_skype_btn">
        <img src="application/modules/Sessocialshare/externals/images/social/skype.png" />
        <span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span>
        </a>
      <?php } elseif($socialicon->type == 'yahoo') { ?>
        <a href="<?php echo 'http://compose.mail.yahoo.com/?body='.$urlencode ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_yahoo_btn">
        <img src="application/modules/Sessocialshare/externals/images/social/yahoo.png" />
        <span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span>
        </a>
      <?php } elseif($socialicon->type == 'vk') { ?>
        <a href="<?php echo 'https://vk.com/share.php?url='.$urlencode ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_vk_btn">
        <i class="fab fa-vk"></i>
        <span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span>
        </a>
      <?php } elseif($socialicon->type == 'whatsapp') { ?>
        <a onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')"  href="javascript:;" class="ss_whatsapp sesbasic_icon_whatsapp_btn sesbasic_icon_btn"><i class="fab fa-whatsapp"></i><span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span></a>
      <?php } elseif($socialicon->type == 'print') { ?>
        <a onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')"  onclick="socialshareprint();" href="javascript:;" class="sesbasic_icon_print_btn sesbasic_icon_btn"><i class="fab fa-print"></i><span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span></a>
        
      <?php } elseif($socialicon->type == 'email') { ?>
        <?php if($item) { ?>
          <a target="_blank" href="sessocialshare/index/email/resource_id/<?php echo $item->getType() ?>/resource_id/<?php echo $item->getIdentity() ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_email_btn">
            <img src="application/modules/Sessocialshare/externals/images/social/email.png" />
            <span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span>
          </a>
        <?php } else { ?>
          <a target="_blank" href="sessocialshare/index/email/" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate($socialicon->title); ?>', '<?php echo $urlencode ?>','<?php echo $this->translate($socialicon->type); ?>')" class="sesbasic_icon_btn sesbasic_icon_email_btn">
            <img src="application/modules/Sessocialshare/externals/images/social/email.png" />
            <span class="social_icon_title"><?php echo $this->translate($socialicon->title); ?> </span>
          </a>
        <?php } ?>
        
      <?php } ?>
    <?php endforeach; ?>

    <?php if(count($socialicons) > 0 && !empty($socialshare_enable_plusicon)): ?>
      <a title="<?php echo $this->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sessocialshare.more.title', 'More')); ?>" href="javascript:;" data-url="<?php echo $this->layout()->staticBaseUrl.'sessocialshare/index/index/resource_id/'.$item_id.'/resource_type/'.$item_type; ?>" class="sesbasic_icon_btn sessocial_icon_add_btn sessmoothbox sesbasic_icon_more"><i class="fa fa-plus"></i> <span class="social_icon_title"><?php echo $this->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sessocialshare.more.title', 'More')); ?> </span></a>
    <?php endif; ?>
<?php } else { ?>
  <?php if(empty($param)) { ?>
    <a href="<?php echo 'http://www.facebook.com/sharer/sharer.php?u=' . $urlencode . '&t=' . strip_tags($item->getTitle()); ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate('Facebook'); ?>')" class="sesbasic_icon_btn sesbasic_icon_facebook_btn"><i class="fab fa-facebook-f"></i></a>
    <a href="<?php echo 'https://twitter.com/intent/tweet?url=' . $urlencode . '&text=' . htmlspecialchars(urlencode(html_entity_decode($item->getTitle('encode'), ENT_COMPAT, 'UTF-8')), ENT_COMPAT, 'UTF-8').'%0a'; ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate('Twitter')?>')" class="sesbasic_icon_btn sesbasic_icon_twitter_btn"><i class="fab fa-twitter"></i></a>
    <a href="<?php echo 'http://pinterest.com/pin/create/button/?url='.$urlencode; ?>&media=<?php echo urlencode((strpos($item->getPhotoUrl(),'http') === FALSE ? (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on')) ? "https://" : "http://") . $_SERVER['HTTP_HOST'].$item->getPhotoUrl() ) : $item->getPhotoUrl())); ?>&description=<?php echo strip_tags($item->getTitle());?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate('Pinterest'); ?>')" class="sesbasic_icon_btn sesbasic_icon_pintrest_btn"><i class="fab fa-pinterest-p"></i></a>
  <?php } else if($param == 'feed') { ?>
    <?php echo '<a href="http://www.facebook.com/sharer/sharer.php?u=' . $urlencode . '&t=' . strip_tags($item->getTitle()).'" onclick="return socialSharingPopUp(this.href,\''. Zend_Registry::get('Zend_Translate')->_('Facebook').'\')" class="sesbasic_icon_btn sesbasic_icon_facebook_btn"><i class="fab fa-facebook-f"></i></a>
    <a href="https://twitter.com/intent/tweet?url=' . $urlencode . '&text=' . htmlspecialchars(urlencode(html_entity_decode($item->getTitle('encode'), ENT_COMPAT, 'UTF-8')), ENT_COMPAT, 'UTF-8').'%0a" onclick="return socialSharingPopUp(this.href,\''.Zend_Registry::get('Zend_Translate')->_('Twitter').'\')" class="sesbasic_icon_btn sesbasic_icon_twitter_btn"><i class="fab fa-twitter"></i></a>
    <a href="http://pinterest.com/pin/create/button/?url='.$urlencode.'&media='.urlencode((strpos($item->getPhotoUrl(),'http') === FALSE ? (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on')) ? "https://" : "http://") . $_SERVER['HTTP_HOST'].$item->getPhotoUrl() ) : $item->getPhotoUrl())).'&description='. strip_tags($item->getTitle()).'" onclick="return socialSharingPopUp(this.href,\''.Zend_Registry::get('Zend_Translate')->_('Pinterest').'\')" class="sesbasic_icon_btn sesbasic_icon_pintrest_btn"><i class="fab fa-pinterest-p"></i></a>'; ?>
  <?php } else if($param == 'photoviewpage') { ?>
    <li><a href="<?php echo 'http://www.facebook.com/sharer/sharer.php?u=' . $urlencode . '&t=' . strip_tags($item->getTitle()); ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate('Facebook'); ?>')" class="sesalbum_facebook_button"><i class="fab fa-facebook-f"></i></a></li>
    <li><a href="<?php echo 'https://twitter.com/intent/tweet?url=' . $urlencode . '&text=' . htmlspecialchars(urlencode(html_entity_decode($item->getTitle('encode'), ENT_COMPAT, 'UTF-8')), ENT_COMPAT, 'UTF-8').'%0a'; ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate('Twitter')?>')" class="sesalbum_twitter_button"><i class="fab fa-twitter"></i></a></li>
    <li><a  href="<?php echo 'http://pinterest.com/pin/create/button/?url='.$urlencode; ?>&media=<?php echo urlencode((strpos($item->getPhotoUrl('thumb.main'),'http') === FALSE ? (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on')) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] .$item->getPhotoUrl() ) : $item->getPhotoUrl('thumb.main')) . $item->getPhotoUrl('thumb.main')); ?>&description=<?php echo strip_tags($item->getTitle());?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate('Pinterest'); ?>')" class="sesalbum_pintrest_button"><i class="fab fa-pinterest-p"></i></a></li>
  <?php } ?>
<?php } ?>
