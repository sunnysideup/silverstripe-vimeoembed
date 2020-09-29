<?php

/**
 * @author: Nicolaas [at] sunnysideup.co.nz
 * @description: manage cards
 **/

class VimeoDataObjectAdmin extends ModelAdmin
{
    private static $managed_models = array("VimeoDataObject");

    private static $url_segment = "vimeos";

    private static $menu_title = "Vimeos";
}

