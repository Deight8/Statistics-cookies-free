<?php
// Nastavení hlaviček
header('Content-Type: application/json');

// Nastavení cesty k XML souborům
define('VISITORS_FILE', __DIR__ . '/visitors.xml');
define('EVENTS_FILE', __DIR__ . '/events.xml');

// Nastavení časové zóny
date_default_timezone_set('Europe/Prague');
