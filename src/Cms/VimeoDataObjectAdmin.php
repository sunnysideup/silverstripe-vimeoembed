<?php

namespace Sunnysideup\Vimeoembed\Cms;

use SilverStripe\Admin\ModelAdmin;
use Sunnysideup\Vimeoembed\Model\VimeoDataObject;

/**
 * @author: Nicolaas [at] sunnysideup.co.nz
 * @description: manage cards
 **/

class VimeoDataObjectAdmin extends ModelAdmin
{
    private static $managed_models = [VimeoDataObject::class];

    private static $url_segment = 'vimeos';

    private static $menu_title = 'Vimeos';
}
