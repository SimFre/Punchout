<?php
$payload = uniqid();
$username = $_POST['USERNAME'];
$password = $_POST['PASSWORD'];
$date = date("Y-m-d\TH:i:sP");
$setupURL = $_POST['PUNCHOUT_LOGIN_URL'];
$hook = "https://www.lan2k.org/punchout/cxml/hook.php";

$xmldata = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE cXML SYSTEM "http://xml.cxml.org/schemas/cXML/1.1.007/cXML.dtd">
<cXML version="1.1.007" xml:lang="en-US" payloadID="$payload" timestamp="$date">
    <Header>
        <From>
            <Credential domain="DUNS">
                <Identity>$username</Identity>
            </Credential>
        </From>
        <To>
            <Credential domain="Name">
                <Identity>Somewhere</Identity>
            </Credential>
        </To>
        <Sender>
            <Credential domain="DUNS">
                <Identity>$username</Identity>
                <SharedSecret>$password</SharedSecret>
            </Credential>
            <UserAgent>Lan2k cXML Punchout</UserAgent>
        </Sender>
    </Header>
    <Request>
        <PunchOutSetupRequest operation="create">
            <BuyerCookie>$payload</BuyerCookie>
            <Extrinsic name="User">TestUser</Extrinsic>
            <BrowserFormPost>
                <URL>$hook</URL>
            </BrowserFormPost>
            <Contact>
                <Name xml:lang="en-US">First Lastname</Name>
                <Email>first.lastname@example.com</Email>
            </Contact>
            <SupplierSetup>
                <URL>$setupURL</URL>
            </SupplierSetup>
        </PunchOutSetupRequest>
    </Request>
</cXML>
EOT;


$ch = curl_init($setupURL);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $xmldata);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
$result = curl_getinfo($ch);
$xmldoc = simplexml_load_string($output);
curl_close($ch);

$ok = false;
$url = "";
if ($result['http_code'] == 200) {
   $ok = true;
   $url = $xmldoc->Response[0]->PunchOutSetupResponse[0]->StartPage[0]->URL[0];
}

$dom = new DOMDocument();
$dom->loadXML($output);
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$cxml = $dom->saveXML();
$cxml = htmlentities($cxml);

?><!doctype html>
<html lang="en">
 <head>
  <meta charset="utf-8">
  <title>Punch Out Test</title>
  <script type="text/javascript" src="sh_main.min.js"></script>
  <script type="text/javascript" src="sh_xml.min.js"></script>
  <link type="text/css" rel="stylesheet" href="sh_emacs.min.css" />
 </head>

 <body onload="sh_highlightDocument();">
  <h2><?= ($ok ? "Success!" : "Failure!") ?></h2>
  <? if ($ok): ?>
   <p><a target="_top" href="<?= htmlentities($url) ?>">Proceed to <?= htmlentities($url) ?></a></p>
  <? endif ?>

  <hr />

  <h2>XML:</h2>
  <pre class="sh_xml"><?= $cxml ?></pre>

  <h2>cURL:</h2>
  <pre><?= print_r($result, true) ?></pre>


 </body>
</html>
