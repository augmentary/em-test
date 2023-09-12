<?php

declare(strict_types=1);

namespace App\Service\ErrorHandling;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class ErrorCollectionBuilder
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function build(mixed $rootObject): ErrorCollection
    {
        return new ErrorCollection($this->validator, $rootObject);
    }
}
