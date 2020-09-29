<?php

namespace Sunnysideup\Vimeoembed\Tasks;

use BuildTask;
use VimeoDataObject;
use DB;


class VimeoDataObjectRedoTask extends BuildTask
{
    protected $title = "Redo meta-data for Vimeo Objects";

    protected $description = "Removes all the cached meta-data for all vimeo objects and re-applies them. Should end with the word Completed.";

    public function run($request)
    {
        $count = VimeoDataObject::get()->count() + 1;
        for ($i = 0; $i < $count; $i++) {
            $obj = VimeoDataObject::get()->limit(1, $i)->First();
            if ($obj) {
                DB::alteration_message("Saving data for object with code ".$obj->VimeoCode, "created");
                $obj->HTML(true);
            }
        }
        DB::alteration_message("================ COMPLETED ====================");
    }
}

