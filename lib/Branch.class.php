<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Branch
 *
 * @author orlando
 */
class Branch 
{
    public $id;
    public $name;
    public $address;
    
    /**
     * 
     * @param integer $bid
     * @return Branch
     */
    public static function retrieve($bid)
    {
        $dbm = THSModel::singleton();
        $branch = $dbm->getBranch($bid);
        //$dbm->close();
        return $branch;
    }
}
