<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-04 16:06:02 +0800
 */

namespace Teddy\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Middleware\BodyParsingMiddleware as SlimBodyParsingMiddleware;
use Teddy\Traits\HasUriMatch;

class BodyParsingMiddleware extends SlimBodyParsingMiddleware
{
    use HasUriMatch;

    protected $conditions = [
        'path' => null,
        'ignore' => null,
    ];

    public function __construct(array $bodyParsers = [])
    {
        if (isset($bodyParsers['path'])) {
            $this->conditions['path'] = $bodyParsers['path'];
            unset($bodyParsers['path']);
        }

        if (isset($bodyParsers['ignore'])) {
            $this->conditions['ignore'] = $bodyParsers['ignore'];
            unset($bodyParsers['ignore']);
        }

        parent::__construct($bodyParsers);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isUriMatch($request, $this->conditions)) {
            return parent::process($request, $handler);
        }

        return $handler->handle($request);
    }
}
