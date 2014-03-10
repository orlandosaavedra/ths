<?php

/**
 * Description of Vehicle
 *
 * @author orlando
 */
class Vehicle 
{
    const MATCH_ALL = 'TODOS';
    
    public $id = null;
    public $model = null;
    public $version = null;
    public $other = null;
    
    /**
     * 
     * @param type $vid
     * @return Vehicle
     */
    public static function fetch($vid)
    {
        $dbm = THSModel::singleton();
        $vehicle =  $dbm->getVehicle($vid);
        //$dbm->close();
        return $vehicle;
    }
}
