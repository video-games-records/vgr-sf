<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Application\Service;

class BbCodeToHtmlService
{
    public function convert(string $text): string
    {
        // Preserve existing HTML - skip conversion if no BBCode detected
        if (!$this->hasBbCode($text)) {
            return $text;
        }

        $text = $this->convertBasicTags($text);
        $text = $this->convertLinks($text);
        $text = $this->convertImages($text);
        $text = $this->convertQuotes($text);
        $text = $this->convertCode($text);
        $text = $this->convertLists($text);
        $text = $this->convertNewlines($text);

        return $text;
    }

    public function hasBbCode(string $text): bool
    {
        return (bool) preg_match('/\[\/?(b|i|u|s|url|img|quote|code|list|color|size|center)[\]=\s]/i', $text);
    }

    private function convertBasicTags(string $text): string
    {
        $replacements = [
            '/\[b\](.*?)\[\/b\]/is'      => '<strong>$1</strong>',
            '/\[i\](.*?)\[\/i\]/is'      => '<em>$1</em>',
            '/\[u\](.*?)\[\/u\]/is'      => '<u>$1</u>',
            '/\[s\](.*?)\[\/s\]/is'      => '<s>$1</s>',
            '/\[center\](.*?)\[\/center\]/is' => '<div class="text-center">$1</div>',
            '/\[color=([a-zA-Z]+|#[0-9a-fA-F]{3,6})\](.*?)\[\/color\]/is' => '<span style="color:$1">$2</span>',
        ];

        return preg_replace(array_keys($replacements), array_values($replacements), $text) ?? $text;
    }

    private function convertLinks(string $text): string
    {
        // [url=http://...]Texte[/url]
        $text = preg_replace(
            '/\[url=([^\]]+)\](.*?)\[\/url\]/is',
            '<a href="$1" rel="nofollow">$2</a>',
            $text
        ) ?? $text;

        // [url]http://...[/url]
        $text = preg_replace(
            '/\[url\](https?:\/\/[^\[]+)\[\/url\]/is',
            '<a href="$1" rel="nofollow">$1</a>',
            $text
        ) ?? $text;

        return $text;
    }

    private function convertImages(string $text): string
    {
        return preg_replace(
            '/\[img\](https?:\/\/[^\[]+)\[\/img\]/is',
            '<img src="$1" alt="" class="img-fluid">',
            $text
        ) ?? $text;
    }

    private function convertQuotes(string $text): string
    {
        // [quote=Auteur]texte[/quote]
        $text = preg_replace(
            '/\[quote=([^\]]+)\](.*?)\[\/quote\]/is',
            '<blockquote class="blockquote border-start ps-3"><footer class="blockquote-footer">$1</footer><p>$2</p></blockquote>',
            $text
        ) ?? $text;

        // [quote]texte[/quote]
        $text = preg_replace(
            '/\[quote\](.*?)\[\/quote\]/is',
            '<blockquote class="blockquote border-start ps-3"><p>$1</p></blockquote>',
            $text
        ) ?? $text;

        return $text;
    }

    private function convertCode(string $text): string
    {
        return preg_replace(
            '/\[code\](.*?)\[\/code\]/is',
            '<pre><code>$1</code></pre>',
            $text
        ) ?? $text;
    }

    private function convertLists(string $text): string
    {
        // [list=1]...[/list] → ordered list
        $text = preg_replace_callback(
            '/\[list=1\](.*?)\[\/list\]/is',
            function (array $matches): string {
                $items = preg_replace('/\[\*\]\s?/s', '<li>', $matches[1]);
                $items = preg_replace('/<li>(.*?)(?=<li>|$)/s', '<li>$1</li>', $items ?? '');
                return '<ol>' . trim($items ?? '') . '</ol>';
            },
            $text
        ) ?? $text;

        // [list]...[/list] → unordered list
        $text = preg_replace_callback(
            '/\[list\](.*?)\[\/list\]/is',
            function (array $matches): string {
                $items = preg_replace('/\[\*\]\s?/s', '<li>', $matches[1]);
                $items = preg_replace('/<li>(.*?)(?=<li>|$)/s', '<li>$1</li>', $items ?? '');
                return '<ul>' . trim($items ?? '') . '</ul>';
            },
            $text
        ) ?? $text;

        return $text;
    }

    private function convertNewlines(string $text): string
    {
        // Only convert newlines if the text doesn't already contain HTML block elements
        if (preg_match('/<(p|div|ul|ol|blockquote|pre|h[1-6])[^>]*>/i', $text)) {
            return $text;
        }

        return nl2br($text);
    }
}
