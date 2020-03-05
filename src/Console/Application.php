<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-05 12:17:30 +0800
 */

namespace Teddy\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Teddy\Abstracts\AbstractApp;
use Teddy\Console\Commands\ServCommand;

class Application extends SymfonyApplication
{
    protected $app;

    public function __construct(AbstractApp $app, ?string $version = null)
    {
        $version = $version ?: config('app.version', 'UNKNOWN');
        parent::__construct('Teddy Framework', $version);
        $this->app = $app;

        $this->add(new ServCommand);
        $commandList = config('command.list', []);
        if (!empty($commandList) && is_array($commandList)) {
            $this->addCommands($commandList);
        }

        $defaultCommand = config('command.default', 'serv');
        $this->setDefaultCommand($defaultCommand);
    }
}
