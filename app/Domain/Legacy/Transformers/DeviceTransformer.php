<?php

namespace App\Domain\Legacy\Transformers;

use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;

class DeviceTransformer
{
    public function index(Collection $devices): array
    {
        return $devices->map(function (Device $device) {
            return [
                "device" => [
                    "hostname" => "2",
                    "sleepTime" => 0,
                ],
                "state" => "success",
                "value" => "0"
            ];
       })->toArray();
    }

    public function show(Device $device): array
    {
        return [
            "device" => [
                "hostname" => "2",
                "sleepTime" => 0,
            ],
            "state" => "success",
            "value" => "0"
        ];
    }
}
