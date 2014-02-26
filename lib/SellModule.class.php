<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SellModule
 *
 * @author orlando
 */
class SellModule 
{
    public function __construct()
    {
        $this->window = new SellWindow();
        $this->window->show_all();
    }
}
