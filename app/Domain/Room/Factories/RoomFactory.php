<?php

namespace App\Domain\Room\Factories;

use App\Models\Room;

/**
 * Class RoomFactory
 * @package App\Domain\Room\Factories
 */
class RoomFactory
{
    public function create(string $name): Room
    {
        return Room::create([
            'name' => $name
        ]);
    }
}
