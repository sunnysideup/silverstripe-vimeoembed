<?php

class VimeoDOD extends DataObjectDecorator {

	function extraStatics () {
		return array(
			"has_one" => array("VimeoDataObject" => "VimeoDataObject")
		);
	}

	protected static $exclude_vimeo_from_page_classes = array();
		static function set_exclude_vimeo_from_page_classes($v){self::$exclude_vimeo_from_page_classes = $v;}
		static function get_exclude_vimeo_from_page_classes (){return self::$exclude_vimeo_from_page_classes;}

	protected static $include_vimeo_in_page_classes = array();
		static function set_include_vimeo_in_page_classes($v){self::$include_vimeo_in_page_classes = $v;}
		static function get_include_vimeo_in_page_classes(){return self::$include_vimeo_in_page_classes;}

	public function updateCMSFields(&$fields) {
		if($this->HasVimeo()) {
			$listObject = DataObject::get("VimeoDataObject");
			if($listObject) {
				$tab = _t("VimeoDOD.TAB", "Root.Content.Vimeo");
				$list = $listObject->toDropDownMap($index = 'ID', $titleField = 'Title', $emptyString = _t("VimeoDOD.EMPTYSTRING", "--- select vimeo video ---"), $sort = false);
				$fields->addFieldToTab($tab, new DropdownField("VimeoDataObjectID", _t("VimeoDOD.URLFIELD", "Video"), $list));
				$linkToModelAdmin = _t("VimeoDOD.LINKTOMODELADMIN", "To edit your videos, please go to <a href=\"/admin/vimeos\">Vimeo Editing Page</a>.");
				$fields->addFieldToTab($tab, new LiteralField("VimeoDataObjectIDEDIT", "<p>$linkToModelAdmin</p>"));
			}
		}
		return $fields;
	}

	function HasVimeo() {
		$hasVimeo = false;
		if(in_array($this->owner->ClassName, self::get_include_vimeo_in_page_classes()) || !count(self::get_include_vimeo_in_page_classes())) {
			$hasVimeo = true;
		}
		if(in_array($this->owner->ClassName, self::get_exclude_vimeo_from_page_classes())) {
			$hasVimeo = false;
		}
		return $hasVimeo;
	}

}


