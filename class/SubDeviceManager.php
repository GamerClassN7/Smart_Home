<?php
class SubDeviceManager
{
    public static $devices;

    public function getAllSubDevices($deviceId)
    {
        return Db::loadAll("SELECT * FROM subdevices WHERE device_id = ?", array($deviceId));
    }

    public function getSubDeviceMaster($subDeviceId)
    {
        return Db::loadOne("SELECT * FROM devices WHERE device_id = (SELECT device_id FROM subdevices WHERE subdevice_id = ?)", array($subDeviceId));
    }

    public function getSubDeviceByMaster($deviceId, $subDeviceType = null)
    {
        if ($subDeviceType == null) {
            return Db::loadOne("SELECT * FROM subdevices WHERE device_id = ?;", array($deviceId));
        } else {
            return Db::loadOne("SELECT * FROM subdevices WHERE device_id = ? AND type = ?;", array($deviceId, $subDeviceType));
        }
    }

    public function getSubDeviceByMasterAndType($deviceId, $subDeviceType = null)
    {
        if (!empty($subDeviceType)) {
            return Db::loadOne("SELECT * FROM subdevices WHERE device_id = ?;", array($deviceId));
        } else {
            return Db::loadOne("SELECT * FROM subdevices WHERE device_id = ? AND type = ?;", array($deviceId, $subDeviceType));
        }
    }

    public function getSubDevice($subDeviceId)
    {
        return Db::loadOne("SELECT * FROM subdevices WHERE subdevice_id = ?;", array($subDeviceId));
    }

    //check if dubdevice exist

    public function create($deviceId, $type, $unit)
    {
        $record = array(
            'device_id' => $deviceId,
            'type' => $type,
            'unit' => $unit,
        );
        try {
            Db::add('subdevices', $record);
        } catch (PDOException $error) {
            echo $error->getMessage();
            die();
        }
    }
}
