<?php
// Load variables from .env file
try {
    josegonzalez\Dotenv\Loader::load([
        'filepath' => __DIR__ . DS . '.env',
        'toServer' => false,
        'skipExisting' => ['toServer'],
        'raiseExceptions' => true
    ]);
} catch (InvalidArgumentException $e) {
    // If there's a problem loading the .env file - load .env.default
    // That means the code can assume appropriate env config always exists
    // Don't trap this incase there's some other fundamental error
    josegonzalez\Dotenv\Loader::load([
        'filepath' => __DIR__ . DS . '.env.default',
        'toServer' => false,
        'skipExisting' => ['toServer'],
        'raiseExceptions' => false
    ]);
}

/**
 * Recaptcha Default Configuration
 *
 * @author   cake17
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://blog.cake-websites.com/
 */
return [
    'Recaptcha' => [
        // Register API keys at https://www.google.com/recaptcha/admin
        'sitekey' => env('RECAPTCHA_KEY'),
        'secret' => env('RECAPTCHA_SECRET'),
        // reCAPTCHA supported 40+ languages listed
        // here: https://developers.google.com/recaptcha/docs/language
        'lang' => 'en',
        // either light or dark
        'theme' => 'light',
        // either image or audio
        'type' => 'image',
    ]
];
