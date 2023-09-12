<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: ExceptionEvent::class)]
class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $nested = $exception;

        do {
            if($nested instanceof ValidationFailedException) {
                $violations = [];
                foreach($nested->getViolations() as $violation) {
                    $violations[$violation->getPropertyPath()] = $violation->getMessage();
                }

                $event->setResponse(new JsonResponse([
                    'code' => 422,
                    'message' => 'Validation Failed',
                    'errors' => $violations,
                ], Response::HTTP_UNPROCESSABLE_ENTITY));

                return;
            }
            $nested = $nested->getPrevious();
        } while (null !== $nested);

        $response = new JsonResponse([
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTrace()
        ]);

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }
}
