<?php

namespace Sunnysideup\Vimeoembed\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;
use Sunnysideup\Vimeoembed\Model\VimeoDataObject;

class VimeoDataObjectRedoTask extends BuildTask
{
    protected $title = 'Redo meta-data for Vimeo Objects';

    protected $description = 'Removes all the cached meta-data for all vimeo objects and re-applies them. Should end with the word Completed.';

    public function run($request)
    {
        $objects = VimeoDataObject::get();
        foreach ($objects as $obj) {
            DB::alteration_message('Saving data for object with code ' . $obj->VimeoCode, 'created');
            $obj->HTML(true);
        }
        DB::alteration_message('================ COMPLETED ====================');
    }
}
