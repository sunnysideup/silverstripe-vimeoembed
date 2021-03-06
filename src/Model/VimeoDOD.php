<?php

namespace Sunnysideup\Vimeoembed\Model;

use Page;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataExtension;

/**
 * ### @@@@ START REPLACEMENT @@@@ ###
 * WHY: automated upgrade
 * OLD:  extends DataExtension (ignore case)
 * NEW:  extends DataExtension (COMPLEX)
 * EXP: Check for use of $this->anyVar and replace with $this->anyVar[$this->owner->ID] or consider turning the class into a trait
 * ### @@@@ STOP REPLACEMENT @@@@ ###
 */
class VimeoDOD extends DataExtension
{
    private static $has_one = [
        'VimeoDataObject' => VimeoDataObject::class,
    ];

    private static $exclude_vimeo_from_page_classes = [];

    private static $include_vimeo_in_page_classes = [];

    public function updateCMSFields(FieldList $fields)
    {
        if ($this->HasVimeo()) {
            $listObject = VimeoDataObject::get();
            if ($listObject->count()) {
                $tab = _t('VimeoDOD.TAB', 'Root.Vimeo');
                $list = [0 => _t('VimeoDOD.EMPTYSTRING', '--- select vimeo video ---')] + $listObject->map($index = 'ID', $titleField = 'Title')->toArray();
                $fields->addFieldToTab($tab, new DropdownField('VimeoDataObjectID', _t('VimeoDOD.URLFIELD', 'Video'), $list));
                $linkToModelAdmin = _t('VimeoDOD.LINKTOMODELADMIN', 'To edit your videos, please go to <a href="/admin/vimeos">Vimeo Editing Page</a>.');
                $fields->addFieldToTab($tab, new LiteralField('VimeoDataObjectIDEDIT', "<p>${linkToModelAdmin}</p>"));
            }
        }
        return $fields;
    }

    public function HasVimeo()
    {
        $hasVimeo = true;
        $includeClasses = $this->owner->Config()->get('include_vimeo_in_page_classes');
        if (count($includeClasses)) {
            if (! in_array($this->owner->ClassName, Config::inst()->get(VimeoDOD::class, 'include_vimeo_in_page_classes'), true)) {
                $hasVimeo = false;
            }
        }
        $excludeClasses = Config::inst()->get(VimeoDOD::class, 'exclude_vimeo_from_page_classes');
        if (count($excludeClasses)) {
            if (in_array($this->owner->ClassName, $excludeClasses, true)) {
                $hasVimeo = false;
            }
        }
        return $hasVimeo;
    }

    public function VimeosInThisSection()
    {
        return Page::get()->filter([
            'ParentID' => [intval($this->owner->ParentID), intval($this->owner->ID)],
            'VimeoDataObjectID:GreaterThan' => 0,
            'ShowInSearch' => 1,
        ]);
    }
}
