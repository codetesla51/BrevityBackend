<?php
require "vendor/autoload.php";
// composer.json
use voku\helper\UTF8;
use ForceUTF8\Encoding;
use Symfony\Component\String\UnicodeString;

class TextCleaner
{
    public function cleanText($text)
    {
        if (empty($text)) {
            return '';
        }

        // Step 1: Force UTF-8 using ForceUTF8
        $text = Encoding::toUTF8($text);

        // Step 2: Use Portable UTF8 for deep cleaning
        $text = UTF8::cleanup($text);
        $text = UTF8::fix_simple_utf8($text);

        // Step 3: Use Symfony String for final normalization
        $text = (new UnicodeString($text))
            ->normalize(UnicodeString::NFKC)
            ->toString();

    $replacements = [
      "â€“" => "—",
      "â€" => "—",
      "\xe2\x80\x94" => "—",
      "â€" => "–",
      "\xe2\x80\x93" => "–",
      "â€œ" => "\"",
      "â€" => "\"",
      "â€˜" => "'",
      "â€™" => "'",
      "â€™" => "'",
      "â€¦" => "…",
      "—" => "-",
      "–" => "-",
      "\"" => "\"",
      "â€•" => "'",
      "…" => "...",
    ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}

// Usage example:
$cleaner = new TextCleaner();

// Test cases
$tests = [
    "Elaraâ€™s",
    "itâ€™s",
    "â€œImagine,â€•"
];

foreach ($tests as $test) {
    echo $cleaner->cleanText($test) . "\n";
}