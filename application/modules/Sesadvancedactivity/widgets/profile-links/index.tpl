<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/styles/styles.css'); ?>
<script type="text/javascript">
  en4.core.runonce.add(function(){

    <?php if( !$this->renderOne ): ?>
    var anchor = $('anf_profile_links').getParent();
    $('anf_profile_links_previous').style.display = '<?php echo ( $this->paginator->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>';
    $('anf_profile_links_next').style.display = '<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' ) ?>';

    $('anf_profile_links_previous').removeEvents('click').addEvent('click', function(){
      en4.core.request.send(new Request.HTML({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() - 1) ?>
        }
      }), {
        'element' : anchor
      })
    });

    $('anf_profile_links_next').removeEvents('click').addEvent('click', function(){
      en4.core.request.send(new Request.HTML({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>
        }
      }), {
        'element' : anchor
      })
    });
    <?php endif; ?>
  });
</script>


<ul class="sesact_profile_links sesbasic_clearfix sesbasic_bxs" id="anf_profile_links">
  <?php foreach( $this->paginator as $link ): ?>
    <?php $width = '250'; if($link->photo_id): ?>
      <?php
        $photoURL = $link->getPhotoUrl();
        if(strpos($photoURL,'http') === false) {
          $baseURL =(!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on')) ? "https://" : 'http://';
          $photoURL = $baseURL. $_SERVER['HTTP_HOST'].$photoURL;
        }
        if($photoURL) {
          $imageHeightWidthData = getimagesize($photoURL); 
          $width = isset($imageHeightWidthData[0]) ? $imageHeightWidthData[0] : '250';
        }
      ?>
    <?php endif; ?>
    <li class="sesbasic_clearfix">
    	<div class="sesact_profile_link_attachemnt sesbasic_clearfix <?php if($width < 250): ?> sesact_profile_link_attachemnt_small <?php endif; ?> <?php if($link->ses_aaf_gif == 1): ?>sesact_profile_link_attachemnt_gif<?php endif; ?>">
      	<?php if($link->photo_id != 0):?>
          <div class="sesact_profile_link_attachemnt_photo">
            <?php echo $this->htmlLink($link->getHref(), $this->itemPhoto($link, 'thumb.main')) ?>
          </div>
        <?php else: ?>
          <div class="sesact_profile_link_attachemnt_photo">
            <?php if($link->ses_aaf_gif == 1): ?>
            	<div class="composer_link_gif_content">
                <img src="<?php echo  $link->title; ?>" data-original="<?php echo  $link->description; ?>" data-still="<?php echo  $link->title; ?>">
                <a href="javascript:;" class="link_play_activity" title="PLAY"></a>
              </div>
            <?php endif; ?>
          </div>
        <?php endif;?>
        <?php $explodeCode = explode('|| IFRAMEDATA',$link->description); ?>
        <div class="sesact_profile_link_attachemnt_item">
          <?php echo $explodeCode[1]; ?>
        </div>
        <div class="sesact_profile_link_attachemnt_cont">

          <div class="sesact_profile_link_title">
            <?php echo $this->htmlLink($link->getHref(), $link->getTitle()) ?>
          </div>
          <div class="profile_links_description">
            <?php echo $explodeCode[0];
            // echo $this->htmlLink($link->getHref(), $link->getDescription()) ?>
          </div>
          <?php if( !$link->getOwner()->isSelf($link->getParent()) ): ?>
            <div class="profile_links_author">
              <?php echo $this->translate('Posted by %s', $link->getOwner()->__toString()) ?>
              <?php echo $this->timestamp($link->creation_date) ?>
            </div>
          <?php endif; ?>
        </div>
        <?php
      if ($link->isDeletable()){
        echo "<br/>".$this->htmlLink(array('route' => 'default', 'module' => 'core', 'controller' => 'link', 'action' => 'delete', 'link_id' => $link->link_id, 'format' => 'smoothbox'), $this->translate(''), array( 'title' => $this->translate('Delete Link'), 'class' => 'smoothbox fas fa-times sesact_profile_link_delete'));
      }
      ?>
			</div>
    </li>
  <?php endforeach; ?>
</ul>

<div>
  <div id="anf_profile_links_previous" class="paginator_previous">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
      'onclick' => '',
      'class' => 'buttonlink icon_previous'
    )); ?>
  </div>
  <div id="anf_profile_links_next" class="paginator_next">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
      'onclick' => '',
      'class' => 'buttonlink_right icon_next'
    )); ?>
  </div>
</div>
