<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

// Priority just above the security firewall's exception listener (1), so our JSON
// response isn't overwritten by its login redirect or by the default HTML error page.
#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
final class ApiExceptionListener
{
    private const API_PATH_PREFIX = '/api';

    public function __construct(
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), self::API_PATH_PREFIX)) {
            return;
        }

        $exception = $event->getThrowable();

        $status = match (true) {
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            $exception instanceof AuthenticationException => Response::HTTP_UNAUTHORIZED,
            $exception instanceof AccessDeniedException => Response::HTTP_FORBIDDEN,
            default => Response::HTTP_INTERNAL_SERVER_ERROR,
        };

        $headers = $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];

        if (Response::HTTP_INTERNAL_SERVER_ERROR === $status) {
            // stopPropagation() below prevents Symfony's own exception logging from running, so we log ourselves.
            $this->logger?->error($exception->getMessage(), ['exception' => $exception]);
        }

        $message = $status === Response::HTTP_INTERNAL_SERVER_ERROR ? 'Internal server error' : $exception->getMessage();

        $event->setResponse(new JsonResponse(['error' => $message], $status, $headers));
        $event->stopPropagation();
    }
}
