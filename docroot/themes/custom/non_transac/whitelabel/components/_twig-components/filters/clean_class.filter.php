<?php

/**
 * @file
 * Filter to support dummy clean_class in the Pattern Lab.
 */

$filter = new Twig_SimpleFilter('clean_class', fn($string) => $string);
