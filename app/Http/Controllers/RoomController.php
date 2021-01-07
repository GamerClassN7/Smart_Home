<?php

namespace App\Http\Controllers;

use App\Domain\Room\Factories\RoomFactory;
use App\Domain\Room\Repositories\RoomRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    private RoomRepository $roomRepository;

    public function __construct(RoomRepository $roomRepository)
    {
        $this->roomRepository = $roomRepository;
    }

    public function index(): JsonResponse
    {
        $rooms = $this->roomRepository->all();
        return response()->json(
            $rooms->toArray()
        );
    }

    public function store(RoomFactory $roomFactory): JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string|unique:rooms,name'
        ]);

        $room = $roomFactory->create(
          request()->post('name')
        );

        return response()->json(
            $room->toArray()
        );
    }


}
