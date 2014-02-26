<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IncomeModule
 *
 * @author orlando
 */
class IncomeModule
{
    /**
     *
     * @var GtkWindow
     */
    private $window;
    
    public function __construct()
    {
        $this->window = new IncomeWindow();
        $this->window->show_all();
        $this->window->connect_simple('create', array($this, 'create'));
        $this->window->connect_simple('modify', array($this, 'modify'));
    }
    
    /**
     * Handles the create event of the main window
     */
    public function create()
    {
        $this->window->destroy();
        $cwindow = new IncomeCreateWindow();
        $cwindow->show_all();
    }
    
    public function modify()
    {
        $this->window->destroy();
    }
}
