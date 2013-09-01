<?php

class VimeoDOD extends DataExtension {

	private static $has_one = array(
		"VimeoDataObject" => "VimeoDataObject"
	);

	private static $exclude_vimeo_from_page_classes = array();

	private static $include_vimeo_in_page_classes = array();

	public function updateCMSFields(FieldList $fields) {
		if($this->HasVimeo()) {
			$listObject = VimeoDataObject::get();
			if($listObject->count()) {
				$tab = _t("VimeoDOD.TAB", "Root.Vimeo");
				$list = array( 0 => _t("VimeoDOD.EMPTYSTRING", "--- select vimeo video ---")) + $listObject->map($index = 'ID', $titleField = 'Title')->toArray();
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

	function VimeosInThisSection(){
		return Page::get()->filter(array(
			"ParentID" => array(intval($this->owner->ParentID), intval($this->owner->ID)),
			"VimeoDataObjectID:GreaterThan" => 0,
			"ShowInSearch" => 1
		));
	}

}


