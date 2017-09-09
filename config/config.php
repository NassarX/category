<?php

/*
 * NOTICE OF LICENSE
 *
 * Part of the Rinvex Category Package.
 *
 * This source file is subject to The MIT License (MIT)
 * that is bundled with this package in the LICENSE file.
 *
 * Package: Rinvex Category Package
 * License: The MIT License (MIT)
 * Link:    https://rinvex.com
 */

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Category Database Tables
    |--------------------------------------------------------------------------
    */

    'tables' => [

        /*
        |--------------------------------------------------------------------------
        | Categories Table
        |--------------------------------------------------------------------------
        |
        | Specify database table name that should be used to store
        | your categories. You may use whatever you like.
        |
        | Default: "categories"
        |
        */

        'categories' => 'categories',

        /*
        |--------------------------------------------------------------------------
        | Categorizables Table
        |--------------------------------------------------------------------------
        |
        | Specify database table name that should be used to store the relation
        | between "categories" and "entities". You may use whatever you like.
        |
        | Default: "categorizables"
        |
        */

        'categorizables' => 'categorizables',

    ],

];
