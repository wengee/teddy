<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-14 17:07:56 +0800
 */

namespace Teddy\Interfaces;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface KernelInterface
{
    public function handle(InputInterface $input, ?OutputInterface $output = null);
}
