<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = [
    'NAME'        => 'Кастомный фильтр каталога',
    'DESCRIPTION' => 'Фильтрация через D7 ORM + Highload-блоки',
    'ICON'        => '/images/icon.gif',
    'SORT'        => 10,
    'CACHE_PATH'  => 'Y',
    'PATH'        => [
        'ID'    => 'pet',
        'NAME'  => 'Pet-проект',
    ],
];