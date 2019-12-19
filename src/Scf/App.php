<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-19 16:47:25 +0800
 */

namespace Teddy\Scf;

use Teddy\BaseApp;

class App extends BaseApp
{
    public function run($event, $context): array
    {
        $request = ServerRequestFactory::createRequest($event, $context);
        $response = $this->slimInstance->handle($request);
        return ResponseEmitter::emit($response);
    }
}
