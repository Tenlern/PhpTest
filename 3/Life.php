<?php

namespace Life;

include 'Game.php';
include 'Grid.php';

$opts = [];

$game = new Game($opts);
$game->loop();

print "\nGame Over!\n\n";