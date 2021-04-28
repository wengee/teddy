<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-04-28 16:44:08 +0800
 */

namespace Teddy\Console;

use Exception;
use function Swoole\Coroutine\run;
use fwkit\Console\Command as ConsoleCommand;
use Swoole\Runtime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @method Application getApplication()
 */
abstract class Command extends ConsoleCommand
{
    public function enableCoroutine(): bool
    {
        return false;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->doExecute();
        } catch (Exception $e) {
            /** @var SymfonyStyle $output */
            $output->error($e->getMessage());

            return 255;
        }

        return 0;
    }

    protected function doExecute(): void
    {
        if ($this->enableCoroutine()) {
            Runtime::enableCoroutine(true);
            run(function (): void {
                $this->handle();
            });
        } else {
            $this->handle();
        }
    }
}
