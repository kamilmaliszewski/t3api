<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'T3api',
    'description' => 'REST API for your TYPO3 project. Config with annotations, build in filtering, pagination, typolinks, image processing, serialization contexts, responses in Hydra/JSON-LD format.',
    'category' => 'plugin',
    'author' => 'SourceBroker Team',
    'author_email' => 'office@sourcebroker.dev',
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-9.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
