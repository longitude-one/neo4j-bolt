<?php

/**
 * This file is part of the LongitudeOne Neo4j Bolt driver for PHP.
 *
 * PHP version 7.2|7.3|7.4
 * Neo4j 3.0|3.5|4.0|4.1
 *
 * (c) Alexandre Tranchant <alexandre.tranchant@gmail.com>
 * (c) Longitude One 2020
 * (c) Graph Aware Limited <http://graphaware.com> 2015-2016
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$header = <<<EOF
This file is part of the LongitudeOne Neo4j Bolt driver for PHP.

PHP version 7.2|7.3|7.4
Neo4j 3.0|3.5|4.0|4.1

(c) Alexandre Tranchant <alexandre.tranchant@gmail.com>
(c) Longitude One 2020 
(c) Graph Aware Limited <http://graphaware.com> 2015-2016

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src/')
    ->in(__DIR__.'/tests/')
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PhpCsFixer' => true,
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header' => $header,
            'location' => 'after_open',
            'separate' => 'bottom'
        ],
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public', 'constant_protected', 'constant_private', 'constant',
                'property_public_static', 'property_protected_static', 'property_private_static', 'property_static',
                'property_public', 'property_protected', 'property_private',  'property',
                'construct', 'destruct',
                'phpunit',
                'method_public_static', 'method_protected_static', 'method_private_static', 'method_static',
                'method_public', 'method_protected', 'method_private', 'method', 'magic'
            ],
            'sortAlgorithm' => 'alpha'
        ],
        'linebreak_after_opening_tag' => true,
        // 'modernize_types_casting' => true,
         'no_useless_return' => true,
        // 'phpdoc_add_missing_param_annotation' => true,
        // 'protected_to_private' => true,
         'strict_param' => true,
    ])
    ->setFinder($finder)
;
