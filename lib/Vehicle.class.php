<?php

/**
 * Description of Vehicle
 *
 * @author orlando
 */
class Vehicle 
{
    public $id = null;
    public $model = null;
    public $year = null;
    public $version = null;
    public $transmission = null;
    
    /**
     * 
     * @param type $vid
     * @return Vehicle
     */
    public static function getFromId($vid)
    {
        $dbm = new THSModel();
        $vehicle =  $dbm->getVehicle($vid);
        $dbm->close();
        return $vehicle;
    }
}
