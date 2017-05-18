<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
 <head>
  <title>Punch Out Test</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
 </head>
 
 <body >

  <script type="text/javascript">
  function OnSubmitForm(frm) {
     //document.pu.OCI_CANCEL.value = document.pu.HOOK_URL.value;
     document.pu.action = frm.PUNCHOUT_LOGIN_URL.value;
     document.getElementById("_buid").setAttribute("name", frm._bun.value);
     document.getElementById("_buid").setAttribute("value", frm._buv.value);
     return true;
  }
 </script>

 <form name="pu" target="mid" method="post" action="#" onsubmit="return OnSubmitForm(this);">
  <input type="hidden" name="" value="" id="_buid" />
  Punchout URL: <input type="text" id="PUNCHOUT_LOGIN_URL" size="35" value="https://www.example.org/punchout.do" />
  Username:     <input type="text" name="USERNAME" size="25" value="" />
  Password:     <input type="text" name="PASSWORD" size="25" value="" />
                <input type="text" name="_bun" size="35" value="BUYER_USERNAME" />:
                <input type="text" name="_buv" size="35" value="TestUser" />
                <input type="hidden" name="HOOK_URL" value="https://www.lan2k.org/punchout/oci/hook.php/HOOK" />
                <input type="hidden" name="OCI_CANCEL" value="https://www.lan2k.org/punchout/oci/hook.php/CANCEL" />
                <input type="submit" value="Go" />
 </form>


 </body>
</html>
