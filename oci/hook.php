<?php
session_start();
?>
<h1>
 <?php
   if ($_SERVER['PATH_INFO'] == "/CANCEL") { echo "Operation aborted!"; }
   elseif ($_SERVER['PATH_INFO'] == "/HOOK") { echo "Punched Out!"; }
   else { echo "Punch Out Client"; }
 ?>
</h1>

<?PHP
$keys = array();
$params = array();
foreach ($_POST as $k => $val) {
   if (is_array($val)) {
      //if (count($val) > $maxCount) { $maxCount = count($val); }
      //$keys = array_merge($keys, array_keys($val));
      foreach(array_keys($val) as $kv) {
         if (!in_array($kv, $keys)) {
            $keys[] = $kv;
         }
      }
      $params[] = $k;
   }
}

$out = array();
foreach(array_values($keys) as $i) {
   foreach (array_values($params) as $key) {
      if (isset($_POST[$key][$i])) {
        $out[$i][$key] = $_POST[$key][$i];
      }
   }
}
?>

<h2>OCI Callback</h2>
<pre><? print_r($out); ?></pre>

<hr />
<h2>POST    </h2> <pre><? print_r($_POST);    ?></pre>
<h2>GET     </h2> <pre><? print_r($_GET);     ?></pre>
<h2>SESSION </h2> <pre><? print_r($_SESSION); ?></pre>
<h2>COOKIE  </h2> <pre><? print_r($_COOKIE);  ?></pre>
<h2>SERVER  </h2> <pre><? print_r($_SERVER);  ?></pre>
