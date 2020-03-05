<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-05 17:44:35 +0800
 */

namespace App\Commands;

use App\Models\Qrcode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('test')
            ->setDescription('This is a test command')
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'config file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getOption('file');
        $output->writeln($file);

        $output->writeln('测试');

        for ($i = 0; $i < 10; $i++) {
            $qrcode = Qrcode::query()->first();
            sleep(2);
        }

        $output->writeln(\var_export($qrcode, true));
        return 0;
    }
}
