<?php

namespace App;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Extension\Autolink\AutolinkExtension;

/**
 * Wrapper class that provides Parsedown-compatible interface using League CommonMark
 * This resolves PHP 8.4 deprecation warnings while maintaining backward compatibility
 */
class ParsedownWrapper
{
    private MarkdownConverter $converter;
    private bool $breaksEnabled = false;
    private static ?self $instance = null;

    public function __construct()
    {
        $this->initializeConverter();
    }

    /**
     * Get singleton instance (compatible with Parsedown::instance())
     */
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Convert markdown text to HTML (compatible with Parsedown::text())
     */
    public function text(string $text): string
    {
        $result = $this->converter->convert($text)->getContent();

        // If breaks are enabled, post-process to convert single newlines to <br> tags
        // This mimics Parsedown's setBreaksEnabled behavior
        if ($this->breaksEnabled) {
            // Convert single newlines within paragraphs to <br> tags
            $result = preg_replace_callback('/<p>(.*?)<\/p>/s', function($matches) {
                $content = $matches[1];
                // Convert single newlines to <br> but preserve existing HTML
                $content = preg_replace('/(?<!>)\n(?!<)/', '<br>' . "\n", $content);
                return '<p>' . $content . '</p>';
            }, $result);
        }

        return $result;
    }

    /**
     * Enable/disable line breaks (compatible with Parsedown::setBreaksEnabled())
     */
    public function setBreaksEnabled(bool $enabled): self
    {
        $this->breaksEnabled = $enabled;
        $this->initializeConverter();
        return $this;
    }

    /**
     * Initialize the CommonMark converter with appropriate configuration
     */
    private function initializeConverter(): void
    {
        $config = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ];

        if ($this->breaksEnabled) {
            $config['renderer'] = [
                'soft_break' => "<br>\n",
            ];
            $config['enable_em'] = true;
            $config['enable_strong'] = true;
        }

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());

        // Add autolink extension for better compatibility
        $environment->addExtension(new AutolinkExtension());

        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * Version constant for compatibility
     */
    public const version = '1.7.4-commonmark';
}
