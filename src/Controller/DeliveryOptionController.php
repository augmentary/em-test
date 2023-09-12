<?php

declare(strict_types=1);

namespace App\Controller;

use App\Handler\OrderCreateHandler;
use App\Handler\OrderUpdateHandler;
use App\Repository\DeliveryOptionRepository;
use App\Repository\OrderRepository;
use App\Request\OrderCreateRequest;
use App\Request\OrderFilterRequest;
use App\Request\OrderUpdateRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class DeliveryOptionController extends AbstractController
{
    public function __construct(
        private readonly DeliveryOptionRepository $repository,
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route('/delivery-option', name: 'app_delivery_status_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $options = $this->repository->findAll();
        return JsonResponse::fromJsonString(
            $this->serializer->serialize($options, 'json', ['groups' => 'get'])
        );
    }
}
