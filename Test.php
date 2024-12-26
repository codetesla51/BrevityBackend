<?php
require "vendor/autoload.php";
use voku\helper\UTF8;

function cleanText($text)
{
  // Use voku/portable-utf8 to clean up the text encoding
  $text = UTF8::cleanup($text);

  // Handle bold text properly
  $text = preg_replace_callback(
    "/\*\*(.*?)\*\*/",
    function ($matches) {
      return "{{BOLD_START}}" . $matches[1] . "{{BOLD_END}}";
    },
    $text
  );
  // Restore bold markers
  $text = str_replace(
    ["{{BOLD_START}}", "{{BOLD_END}}"],
    ["<<BOLD>>", "<<END>>"],
    $text
  );

  return trim($text);
}

$text = "eBayâ€™s";
$cleanedText = cleanText($text);

echo $cleanedText; // Output: 0's and 1'str_repla