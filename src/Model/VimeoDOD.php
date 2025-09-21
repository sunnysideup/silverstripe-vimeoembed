<?php

namespace Sunnysideup\Vimeoembed\Model;

use Page;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;

class VimeoDOD extends Extension
{
    private static $has_one = [
        'VimeoDataObject' => VimeoDataObject::class,
    ];

    private static array $exclude_vimeo_from_page_classes = [];

    private static array $include_vimeo_in_page_classes = [];

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

    public function HasVimeo(): bool
    {
        $owner = $this->getOwner();
        $hasVimeo = true;
        $includeClasses = $owner->Config()->get('include_vimeo_in_page_classes');
        if (count($includeClasses)) {
            if (! in_array($owner->ClassName, Config::inst()->get(VimeoDOD::class, 'include_vimeo_in_page_classes'), true)) {
                $hasVimeo = false;
            }
        }
        $excludeClasses = Config::inst()->get(VimeoDOD::class, 'exclude_vimeo_from_page_classes');
        if (count($excludeClasses)) {
            if (in_array($owner->ClassName, $excludeClasses, true)) {
                $hasVimeo = false;
            }
        }
        return $hasVimeo;
    }

    public function VimeosInThisSection(?string $className = Page::class): DataList
    {
        $owner = $this->getOwner();
        return $className::get()->filter([
            'ParentID' => [intval($owner->ParentID), intval($owner->ID)],
            'VimeoDataObjectID:GreaterThan' => 0,
            'ShowInSearch' => 1,
        ]);
    }
}
