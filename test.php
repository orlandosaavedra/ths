<?php

$ids = Gtk::stock_list_ids();

foreach ($ids as $id){
    $const = str_replace('gtk-', '',$id);
    $const = str_replace('-','_', $const);
    $const = strtoupper($const);
    
    $const = $const;
    
    if (defined('Gtk::'.$const)){
        if (is_string(constant('Gtk::'.$const)))
            echo 'const '. $const. '=\''.constant('Gtk::'.$const).'\';'.PHP_EOL;
        else
            echo 'const '. $const. '='.constant('Gtk::'.$const).';'.PHP_EOL;
    }
}