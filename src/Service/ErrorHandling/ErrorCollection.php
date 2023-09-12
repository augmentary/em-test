<?php

declare(strict_types=1);

namespace App\Service\ErrorHandling;

use App\Enum\OrderStatus;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ErrorCollection
{
    private readonly ConstraintViolationList $errors;

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly mixed $rootObject
    ) {
        $this->errors = new ConstraintViolationList();
    }

    public function validate(mixed $object): void
    {
        $this->errors->addAll($this->validator->validate($object));
    }

    public function add(string $path, mixed $value, string $message): void
    {
        $this->errors->add(new ConstraintViolation(
            $message,
            '',
            [],
            $this->rootObject,
            $path,
            $value
        ));
    }

    public function throwIfInvalid(): void
    {
        if(count($this->errors)) {
            throw new ValidationFailedException($this->rootObject, $this->errors);
        }
    }
}
