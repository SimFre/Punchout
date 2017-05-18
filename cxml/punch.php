<?php
$payload = uniqid();
$date = date("Y-m-d\TH:i:sP");
$hook = "https://www.lan2k.org/punchout/cxml/hook.php";
$inputXML = NULL;
$replaceHook = true;

if (!empty($_POST['inputXML'])) {
    $replaceHook = isset($_POST['replaceHook']);
    $inputXML = $_POST['inputXML'];
} else {
    $replaceHook = true;
    $username = (isset($_POST['USERNAME']) ? $_POST['USERNAME'] : null);
    $password = (isset($_POST['PASSWORD']) ? $_POST['PASSWORD'] : null);
    $setupURL = (isset($_POST['PUNCHOUT_LOGIN_URL']) ? $_POST['PUNCHOUT_LOGIN_URL'] : null);
    $inputXML = <<<EOT
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
                <URL>HOOK PLACEHOLDER</URL>
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
}
?><?php //Trick to fool syntax higlighting

$postedXML = trim($inputXML);
try {
    $inputDoc = new SimpleXMLElement($postedXML);
    $setupURL = $inputDoc->Request->PunchOutSetupRequest->SupplierSetup->URL[0];
    if ($replaceHook) {
        $inputDoc->Request->PunchOutSetupRequest->BrowserFormPost->URL[0] = $hook;
        $postedXML = $inputDoc->asXML();
    }

    $ch = curl_init($setupURL);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postedXML);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $curlOutput = curl_exec($ch);
    $result = curl_getinfo($ch);
    curl_close($ch);

    $ok = false;
    $url = "";
    if ($result["http_code"] >= 200 && $result["http_code"] <= 299) {
        $ok = true;
        $outputDoc = new SimpleXMLElement($curlOutput);
        $url = $outputDoc->Response[0]->PunchOutSetupResponse[0]->StartPage[0]->URL[0];
    } else {
        throw new Exception($curlOutput, $result["http_code"]);
    }

    $dom = new DOMDocument();
    $dom->loadXML($curlOutput);
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $outputXML = $dom->saveXML();
    unset($dom);

    $dom = new DOMDocument();
    $dom->loadXML($postedXML);
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->normalizeDocument();
    $postedXML = $dom->saveXML();
    unset($dom);

} catch (Exception $e) {
    $dom = new DOMDocument();
    $dom->appendChild($dom->createElement("message", $e->getMessage()));
    $dom->appendChild($dom->createElement("code",    $e->getCode()));
    //$dom->appendChild($dom->createElement("file",    $e->getFile()));
    //$dom->appendChild($dom->createElement("line",    $e->getLine()));
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->normalizeDocument();
    $outputXML = $dom->saveXML();
}


$outputXML = htmlentities($outputXML);
$inputXML = htmlentities($inputXML);
$postedXML = htmlentities($postedXML);

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

  <h2>XML Response:</h2>
  <pre class="sh_xml"><?= $outputXML ?></pre>

  <h2>XML Post:</h2>
  <pre class="sh_xml"><?= $inputXML ?></pre>

  <h2>XML Input:</h2>
  <pre class="sh_xml"><?= $inputXML ?></pre>

  <h2>cURL:</h2>
  <pre><?= htmlentities(print_r($result, true)) ?></pre>

  <h2>POST:</h2>
  <pre><?= htmlentities(print_r($_POST, true)) ?></pre>


 </body>
</html><