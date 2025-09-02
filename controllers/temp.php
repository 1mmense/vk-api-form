<?php

use Areas\Areas;

require_once '../classes/Areas.php';

$mode = $_REQUEST['mode'] ?? null;

if ($mode === 'filter') {
    $perthMetroCities = Areas::getPmCities();
    $perthMetroPostcodes = Areas::getPmZipcodes();
    $waCities = Areas::getWaCities();
    $waPostcodes = Areas::getWaZipcodes();

    // Объединяем города и индексы в пары
    $perthMetroPairs = array_map(null, $perthMetroCities, $perthMetroPostcodes);
    $waPairs = array_map(null, $waCities, $waPostcodes);

    // Фильтруем WA-зону, исключая те пары, которые уже есть в Perth Metro
    $filteredWAPairs = array_filter($waPairs, function ($pair) use ($perthMetroPairs) {
        return !in_array($pair, $perthMetroPairs);
    });

    // Разделяем обратно на два списка
    $filteredWACities = array_column($filteredWAPairs, 0);
    $filteredWAPostcodes = array_column($filteredWAPairs, 1);

    array_walk($filteredWACities, function(&$value) {
        $value = "{$value}\n";
    });

    array_walk($filteredWAPostcodes, function(&$value) {
        $value = "{$value}\n";
    });

    $cities_filename = '1_wa_cities.txt';
    $zipcodes_filename = '1_wa_zipcodes.txt';

    file_put_contents(
        FILES_FOLDER . '/' . $cities_filename,
        $filteredWACities
    );

    file_put_contents(
        FILES_FOLDER . '/' . $zipcodes_filename,
        $filteredWAPostcodes
    );
}
