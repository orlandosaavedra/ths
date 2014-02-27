

<?php

$a = new GtkWindow();
$b = GtkComboBox::new_text();
$b->append_text('uno');
$b->append_text('dos');
$c = new GtkButton('desactivar');
$c->connect_simple('clicked', array($b, 'set_sensitive'), false);
$d = new GtkButton('agregar');
$d->connect_simple('clicked', 'clear',$b);
function clear(GtkComboBox $b)
{
    $b->get_model()->clear();
    $b->
}
function changed(GtkComboBox $box)
{
    echo $box->is_focus();
    echo 'done'.PHP_EOL;
}

$b->connect('changed', 'changed');

$h = new GtkHBox();
$h->pack_start($b);
$h->pack_start($c);
$h->pack_start($d);
$a->add($h);
$a->show_all();
Gtk::main();