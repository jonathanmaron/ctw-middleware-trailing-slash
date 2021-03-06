<?php
declare(strict_types=1);

namespace Ctw\Middleware\TrailingSlashMiddleware;

use Ctw\Http\HttpStatus;
use Middlewares\Utils\Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TrailingSlashMiddleware extends AbstractTrailingSlashMiddleware
{
    private const HEADER = 'Location';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $uri  = $request->getUri();
        $path = $this->normalize($uri->getPath());

        if ($path === $uri->getPath()) {
            return $response;
        }

        $location = $uri->withPath($path)->__toString();
        $factory  = Factory::getResponseFactory();
        $response = $factory->createResponse(HttpStatus::STATUS_MOVED_PERMANENTLY);

        return $response->withHeader(self::HEADER, $location);
    }

    private function normalize(string $path): string
    {
        $slash = '/';

        if (0 === strlen($path)) {
            return $slash;
        }

        if (strlen($path) > 1) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if ($slash !== substr($path, -1) && 0 === strlen(/** @scrutinizer ignore-type */ $extension)) {
                return $path . $slash;
            }
        }

        return $path;
    }
}
