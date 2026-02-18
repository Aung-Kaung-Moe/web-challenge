<?php
// flag.php
header("Content-Type: text/plain; charset=UTF-8");

$FLAG = "flag{y0u_4r3_4_b0yfRi3nd_n0w}";

/* ================= helpers ================= */

function sanitize_girl(string $girl): string {
  $girl = trim($girl);
  if ($girl === "" || !preg_match("/^[a-zA-Z0-9 .\\-']{1,40}$/", $girl)) {
    return "Nobody";
  }
  return $girl;
}

function fail(string $girl = "Nobody"): void {
  echo "Sorry bro! " . sanitize_girl($girl) . " doesn't like you";
  exit;
}

function latin_fold_to_ascii(string $s): string {
  $manual = [
  // b
  "ḃ"=>"b","ƀ"=>"b","ɓ"=>"b","ᵬ"=>"b","ḅ"=>"b","ḇ"=>"b","ᵇ"=>"b",

  // o
  "ó"=>"o","ö"=>"o","ò"=>"o","ô"=>"o","õ"=>"o","ō"=>"o","ŏ"=>"o","ő"=>"o",
  "ø"=>"o","ǿ"=>"o","ȯ"=>"o","ọ"=>"o","ỏ"=>"o","ǒ"=>"o","ȍ"=>"o","ȏ"=>"o",

  // y
  "ý"=>"y","ÿ"=>"y","ŷ"=>"y","ȳ"=>"y","ẏ"=>"y","ỵ"=>"y","ỷ"=>"y","ỹ"=>"y","ẙ"=>"y",

  // f
  "ḟ"=>"f","ƒ"=>"f","ᵮ"=>"f",

  // r
  "ŕ"=>"r","ř"=>"r","ŗ"=>"r","ȑ"=>"r","ȓ"=>"r","ṛ"=>"r","ṙ"=>"r","ṝ"=>"r","ṟ"=>"r",

  // i
  "í"=>"i","ï"=>"i","ì"=>"i","î"=>"i","ī"=>"i","ĭ"=>"i","į"=>"i","ı"=>"i",
  "ǐ"=>"i","ȉ"=>"i","ȋ"=>"i","ị"=>"i","ỉ"=>"i","ĩ"=>"i","ḭ"=>"i","ḯ"=>"i",

  // e
  "é"=>"e","ë"=>"e","è"=>"e","ê"=>"e","ẽ"=>"e","ē"=>"e","ĕ"=>"e","ė"=>"e","ę"=>"e","ě"=>"e",
  "ȅ"=>"e","ȇ"=>"e","ẹ"=>"e","ẻ"=>"e","ḙ"=>"e","ḛ"=>"e",

  // n
  "ń"=>"n","ñ"=>"n","ň"=>"n","ņ"=>"n","ṅ"=>"n","ṇ"=>"n","ṋ"=>"n","ǹ"=>"n","ṋ"=>"n",

  // d
  "ď"=>"d","đ"=>"d","ḋ"=>"d","ḍ"=>"d","ḏ"=>"d","ḓ"=>"d","ƌ"=>"d","ȡ"=>"d",

  // optional: allow uppercase too (helps users who send mixed case)
  "Ḃ"=>"b","Ɓ"=>"b","Ó"=>"o","Ö"=>"o","Ò"=>"o","Ô"=>"o","Õ"=>"o","Ō"=>"o","Ŏ"=>"o","Ő"=>"o","Ø"=>"o",
  "Ý"=>"y","Ÿ"=>"y","Ŷ"=>"y","Ȳ"=>"y","Ḟ"=>"f","Ƒ"=>"f","Ŕ"=>"r","Ř"=>"r","Ŗ"=>"r",
  "Í"=>"i","Ï"=>"i","Ì"=>"i","Î"=>"i","Ī"=>"i","Ĭ"=>"i","Į"=>"i","İ"=>"i",
  "É"=>"e","Ë"=>"e","È"=>"e","Ê"=>"e","Ẽ"=>"e","Ē"=>"e","Ĕ"=>"e","Ė"=>"e","Ę"=>"e","Ě"=>"e",
  "Ń"=>"n","Ñ"=>"n","Ň"=>"n","Ņ"=>"n","Đ"=>"d","Ď"=>"d",
];


  if (class_exists('Normalizer')) {
    $s = Normalizer::normalize($s, Normalizer::FORM_KD);
    $s = preg_replace('/\p{Mn}+/u', '', $s);
  }

  return strtolower(strtr($s, $manual));
}

function is_unicode_boyfriend_key(string $key): bool {
  if (!preg_match('/^\p{Latin}{9}$/u', $key)) return false;
  if ($key === "boyfriend") return false; // must change at least one char
  return latin_fold_to_ascii($key) === "boyfriend";
}

/* ================= GET → UI ================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Content-Type: text/html; charset=UTF-8");
  ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Flag</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="flag-wrap">
    <a id="imgLink" href="#" target="_blank">
      <img id="girlImg" class="flag-photo" src="" alt="">
    </a>

    <p class="flag-text">Be someone's boyfriend to get the FLAG.</p>
    <button id="bfBtn" class="flag-btn"></button>
    <p id="resultBox" class="result"></p>
  </main>

  <script src="flag.js"></script>
</body>
</html>
  <?php
  exit;
}

/* ================= POST → CHECK ================= */

$ct = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
$raw = file_get_contents("php://input");
$girl = "Nobody";

/* form-urlencoded (default browser click) */
if ($ct === "application/x-www-form-urlencoded") {
  if (isset($_POST['girl'])) {
    $girl = $_POST['girl'];
  }
  // must be JSON to get flag
  fail($girl);
}

/* must be JSON */
if ($ct !== "application/json") {
  fail($girl);
}

$data = json_decode($raw, true);
if (!is_array($data)) fail($girl);

/* optional dynamic name */
if (isset($data['girl']) && is_string($data['girl'])) {
  $girl = $data['girl'];
}

/* find unicode-boyfriend key */
$chosenKey = null;
foreach ($data as $k => $_) {
  if (is_string($k) && is_unicode_boyfriend_key($k)) {
    $chosenKey = $k;
    break;
  }
}
if ($chosenKey === null) fail($girl);

/* value must be boolean true */
if ($data[$chosenKey] !== true) fail($girl);

/* SUCCESS */
echo $FLAG;
