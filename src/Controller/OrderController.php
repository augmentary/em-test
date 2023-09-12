<?php

declare(strict_types=1);

namespace App\Controller;

use App\Handler\OrderCreateHandler;
use App\Handler\OrderUpdateHandler;
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

class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route('/orders', name: 'app_orders_index', methods: ['GET'])]
    public function index(
        #[MapQueryString]
        OrderFilterRequest $payload = new OrderFilterRequest()
    ): JsonResponse {
        $orders = $this->orderRepository->matching($payload->toCriteria());
        return JsonResponse::fromJsonString(
            $this->serializer->serialize($orders, 'json', ['groups' => 'get'])
        );
    }

    #[Route('/orders', name: 'app_orders_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        OrderCreateRequest $payload,
        OrderCreateHandler $handler
    ): JsonResponse {
        $order = $handler($payload);
        return JsonResponse::fromJsonString(
            $this->serializer->serialize($order, 'json', ['groups' => 'get'])
        );
    }

    #[Route('/orders', name: 'app_orders_update', methods: ['PATCH'])]
    public function update(
        #[MapRequestPayload]
        OrderUpdateRequest $payload,
        OrderUpdateHandler $handler
    ): JsonResponse {
        $order = $handler($payload);
        return JsonResponse::fromJsonString(
            $this->serializer->serialize($order, 'json', ['groups' => 'get'])
        );
    }
}
