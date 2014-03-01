<?php

$win = new GtkWindow();
$win->connect_simple('destroy', array('gtk', 'main_quit'));
$win->set_size_request(600,400);

$btn = new GtkButton('save');
$win->add($btn);

$btn->connect('clicked', 'save', $win);

function save($btn, $win){
    $dialog = new GtkFileChooserDialog(
            'Guardar',
            $win,
            Gtk::FILE_CHOOSER_ACTION_SAVE,
            array(Gtk::STOCK_OK, Gtk::RESPONSE_OK,
                Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL));
    $dialog->set_current_folder('/tmp');
    $dialog->set_current_name('cotizacion.pdf');
    
    $dialog->run();
    
    echo $dialog->get_filename();
}


$win->show_all();

Gtk::main();