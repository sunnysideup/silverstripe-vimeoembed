<?php

/**
 * @author: Nicolaas [at] sunnysideup.co.nz
 * @description: manage cards
 **/

class VimeoDataObjectAdmin extends ModelAdmin {

	public static $managed_models = array("VimeoDataObject");

	public static $url_segment = "vimeos";

	public static $menu_title = "Vimeos";


}
