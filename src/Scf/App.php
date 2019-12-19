<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-19 15:46:03 +0800
 */

namespace Teddy\Scf;

use Teddy\App as BaseApp;

class App extends BaseApp
{
    public function run($event, $context): array
    {
        $request = ServerRequestFactory::createRequest($event, $context);
        $response = $this->slimInstance->handle($request);
        return ResponseEmitter::emit($response);
    }
}
