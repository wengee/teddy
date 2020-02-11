<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-02-11 21:04:42 +0800
 */

namespace Teddy\Scf;

use Teddy\Abstracts\AbstractApp;

class App extends AbstractApp
{
    public function run($event, $context): array
    {
        $request = ServerRequestFactory::createRequest($event, $context);
        $response = $this->slimInstance->handle($request);
        return ResponseEmitter::emit($response);
    }
}
