<?php

$win = new GtkWindow();
$win->set_wmclass('THS', 'THS');
$win->set_icon_from_file('./img/logo.png');

$win2 = new GtkWindow();
$win2->set_transient_for($win);
//$win->set_modal(true);

$win->show_all();
$win2->show_all();


$win->connect_simple('destroy', array('gtk','main_quit'));

Gtk::main();