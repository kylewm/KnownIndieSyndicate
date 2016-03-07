<?php
// require __DIR__ . '/vendor/autoload.php';

if (!function_exists('\BarnabyWalters\Mf2\hasNumericKeys')) {
    require_once __DIR__ . '/external/barnabywalters/mf-cleaner/src/BarnabyWalters/Mf2/Functions.php';
}
require_once __DIR__ . '/external/indieweb/link-rel-parser/src/IndieWeb/link_rel_parser.php';
require_once __DIR__ . '/external/indieauth/client/src/IndieAuth/Client.php';
loader()->registerNamespace('IndieAuth\Client', __DIR__ . '/external/indieauth/client/src');
