<?php

use App\ParsedownWrapper;

/**
 * Parsedown compatibility class
 * This class provides backward compatibility with the original Parsedown API
 * while using League CommonMark internally to avoid PHP 8.4 deprecation warnings
 */
class Parsedown extends ParsedownWrapper
{
    /**
     * Version constant for compatibility with original Parsedown
     */
    public const version = '1.7.4-commonmark';
}
