<?php
// flag.php
header("Content-Type: text/plain; charset=UTF-8");

$FLAG = "flag{fake}";

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
    "ḃ"=>"b","ó"=>"o","ý"=>"y","ḟ"=>"f","ŕ"=>"r",
    "í"=>"i","é"=>"e","ń"=>"n","ď"=>"d"
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
