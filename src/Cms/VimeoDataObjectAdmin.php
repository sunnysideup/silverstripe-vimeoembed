<?php

namespace Sunnysideup\Vimeoembed\Cms;


use Sunnysideup\Vimeoembed\Model\VimeoDataObject;
use SilverStripe\Admin\ModelAdmin;



/**
 * @author: Nicolaas [at] sunnysideup.co.nz
 * @description: manage cards
 **/

class VimeoDataObjectAdmin extends ModelAdmin
{
    private static $managed_models = array(VimeoDataObject::class);

    private static $url_segment = "vimeos";

    private static $menu_title = "Vimeos";
}

