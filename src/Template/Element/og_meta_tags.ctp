<?php
/**
 * @var \App\View\AppView $this
 */

$titleForLayout = "Muncie Events";
    # remember to make the title dynamic instead of static
    # at some point when you're not cutting corners trying
    # to get the site functional

    $default_og_meta_tags = [
        'og:title' => $titleForLayout,
        'og:type' => 'website', // was muncieevents:website
        'og:image' => '/img/facebook_logo.png',
        'og:site_name' => 'Muncie Events',
        'fb:admins' => [
            '20721049', // Graham Watson
            '681411028' // Mary Ogle
        ],
        'fb:app_id' => '496726620385625',
        'og:description' => 'Upcoming events in Muncie, IN',
        'og:locale' => 'en_US'
    ];

    if (isset($og_meta_tags)) {
        foreach ($og_meta_tags as $property => $contents) {
            if (!is_array($contents)) {
                $contents = [$contents];
            }
            foreach ($contents as $content) {
                switch ($property) {
                    case 'og:description':
                        $content = $this->Text->truncate(strip_tags($content), 1000, [
                            'exact' => false
                        ]);
                        break;
                }
                echo '<meta property="'.$property.'" content="'.htmlentities($content).'" />';
            }
        }
    }

    foreach ($default_og_meta_tags as $property => $default_contents) {
        if (isset($og_meta_tags[$property])) {
            continue;
        }
        if (!is_array($default_contents)) {
            $default_contents = [$default_contents];
        }
        foreach ($default_contents as $content) {
            switch ($property) {
                case 'og:title':
                    if ($titleForLayout) {
                        $content = trim(strip_tags($titleForLayout));
                    } else {
                        $content = 'Muncie Events';
                    }
                    break;
            }
            echo '<meta property="'.$property.'" content="'.htmlentities($content).'" />';
        }
    }
