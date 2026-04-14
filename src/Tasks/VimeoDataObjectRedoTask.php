<?php

declare(strict_types=1);

namespace Sunnysideup\Vimeoembed\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Sunnysideup\Vimeoembed\Model\VimeoDataObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class VimeoDataObjectRedoTask extends BuildTask
{
    protected static string $commandName = 'vimeo-data-object-redo';

    protected string $title = 'Redo meta-data for Vimeo Objects';

    protected static string $description = 'Removes all the cached meta-data for all vimeo objects and re-applies them.';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $objects = VimeoDataObject::get();
        foreach ($objects as $obj) {
            $output->writeln('Saving data for object with code ' . $obj->VimeoCode);
            $obj->HTML(true);
        }

        $output->writeln('================ COMPLETED ====================');

        return Command::SUCCESS;
    }
}
