<?php
// check.php
header("Content-Type: text/plain; charset=UTF-8");

$FLAG = "infosec{Y0u_4r3_7ru3_b0yfRi3nd!}";

function fail() {
  echo "Sorry bro!";
  exit;
}

$ct = $_SERVER['CONTENT_TYPE'] ?? '';
$ct = strtolower(trim(explode(';', $ct)[0]));

/**
 * Fold Latin-ish variants back to ASCII-ish for comparison.
 * - Prefer Normalizer (NFKD) + remove combining marks
 * - Fallback: manual mapping for safe letters
 */
function latin_fold_to_ascii(string $s): string {
  $manual = [
    "ḃ" => "b",
    "ó" => "o",
    "ý" => "y",
    "ḟ" => "f",
    "ŕ" => "r",
    "í" => "i",
    "é" => "e",
    "ń" => "n",
    "ď" => "d",
  ];

  if (class_exists('Normalizer')) {
    $s = Normalizer::normalize($s, Normalizer::FORM_KD);
    $s = preg_replace('/\p{Mn}+/u', '', $s);
    return strtolower($s);
  }

  $s = strtr($s, $manual);
  return strtolower($s);
}

/**
 * Condition #2:
 * Key must be 9 Latin letters and fold to "boyfriend",
 * BUT must NOT be exactly "boyfriend" (=> at least 1 character changed).
 * More than one changed is OK.
 */
function is_unicode_boyfriend_key(string $key): bool {
  if (!preg_match('/^\p{Latin}{9}$/u', $key)) return false;
  if ($key === 'boyfriend') return false; // must change at least one character
  return latin_fold_to_ascii($key) === 'boyfriend';
}

/* Condition #1: must be application/json */
if ($ct !== "application/json") {
  fail();
}

/* Parse JSON body */
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!is_array($data)) fail();

/* Find unicode-boyfriend key */
$chosenKey = null;
foreach (array_keys($data) as $k) {
  if (is_string($k) && is_unicode_boyfriend_key($k)) {
    $chosenKey = $k;
    break;
  }
}
if ($chosenKey === null) fail();

/* Value must be boolean true */
if (!isset($data[$chosenKey]) || $data[$chosenKey] !== true) fail();

echo $FLAG;
