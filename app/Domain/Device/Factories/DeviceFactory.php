<?php

namespace App\Domain\Device\Factories;

use App\Models\Device;

/**
 * Class DeviceFactory
 * @package App\Domain\Device\Factories
 */
class DeviceFactory
{
    public function create(string $name, ?string $description = null): Device
    {
        return Device::create([
            'name' => $name,
            'description' => $description
        ]);
    }

}
