<?php

//***************************************************************************
//******************************** Разное ***********************************
//***************************************************************************

if (!function_exists('is_laravel')) {
    /**
     * Проверка что пакет используется в рамках проекта Laravel.
     *
     * @return string
     */
    function is_laravel()
    {
        return function_exists('app') && is_a(app(), 'Illuminate\Foundation\Application');
    }
}