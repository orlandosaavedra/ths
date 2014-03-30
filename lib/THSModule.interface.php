<?php

/**
 * Description of THSModule
 *
 * @author orlando
 */
interface THSModule
{
    /**
     * @return string
     */
    public function getTitle();
    /**
     * Should perform all required actions to prepare the 
     * module to be loaded into the main window
     * @return bool True if the load process went ok, false if something failed. 
     */
    public function load();
}
