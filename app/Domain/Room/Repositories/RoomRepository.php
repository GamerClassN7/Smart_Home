<?php

namespace App\Domain\Room\Repositories;

use App\Models\Room;

/**
 * Class RoomRepository
 * @package App\Domain\Room\Repositories
 */
class RoomRepository
{
    public function all()
    {
        return Room::all();
    }
}
