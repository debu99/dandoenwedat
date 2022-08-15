<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <john@socialengine.com>
 */

$url = $this->url(array(
    'module' => 'core',
    'controller' => 'index',
    'action' => 'featured-products'
));
?>
<div id="admin-featured-products-holder"></div>
<script>
    en4.core.runonce.add(function () {
        var r = new Request({
            url: '<?php echo $url; ?>',
            onSuccess: function (res) {
                $('admin-featured-products-holder').set('html', res);
            }
        });

        r.send();
    });
</script>
