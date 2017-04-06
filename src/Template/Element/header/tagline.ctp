<?php
    $phrases = [
        'we\'ll MEet you there',
        'pencil ME in',
        'party tiME',
        'coME out and play',
        'let\'s MEet up',
        'show tiME',
        'make MEmories',
        'coME as you are'
    ];
    $phrase = $phrases[array_rand($phrases)];
    if (in_array(substr($phrase, -1), ['.', '?', '!'])) {
        $punct = substr($phrase, -1);
        $phrase = substr($phrase, 0, (strlen($phrase) - 1));
        $phrase .= '<span class="punctuation">'.$punct.'</span>';
    } else {
        $phrase .= '<span class="punctuation">.</span>';
    }
    $phrase = str_replace('ME', '<i class="icon-me-logo"></i>', $phrase);
    echo $phrase;
