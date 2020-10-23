<?
require_once('../../../includes/adminGlobals.php');

	CheckPageAdminSecurity();
	if(!CurrentAdminUserIsSystemDeveloper())
		Redirect('main.php');
	$em = \SCMiley\Database\DoctrineEntityManager::get();
	switch(Request('a')) {
		case "execute":
			set_time_limit(RequestInt("timeoutSeconds", 999));
			try {
          $commands = $_REQUEST["commands"];
          if (RC("stripSlashes")) {
              $commands = stripslashes($commands);
          }

          if (RC('toIFrame')) {
              BeginDSIFrame();
          }
          eval($commands);
          if (RC('toIFrame')) {
              EndDSIFrame();
          }
      } catch(\Exception $e) {
			  echo $e->getMessage() . $e->getTraceAsString();
      }
			break;
		default:
			DisplayExecutionEngine();
	}
	
	exit();
	
	
function DisplayExecutionEngine() {

	if(function_exists('BeginAdminPage'))
		BeginAdminPage('PHP Dev Studio');
	else {
?>
<html>
<head>
  <title>PHP Dev Studio</title>
  <script type="text/javascript" src="../../assets/admin/scripts/library/mootools-1.2.4-core.js"></script>
  <script type="text/javascript" src="../../assets/admin/scripts/library/mootools-1.2.4.4-more.js"></script>
  <script type="text/javascript" src="../../assets/admin/scripts/library/dbug.js"></script>
</head>
<body>
<?
	}
?>
<script type="text/javascript">
	function sendExecution(theForm) {
		$('resultLabel').setStyle('opacity', 0);
		if(theForm.toIFrame.checked) {
			swapSections('executionResult', 'executionIframe', 50);
			theForm.action = App.thisPage;
			theForm.submit();
			theForm.action = "JavaScript:void(0);"; // switch back for regular AJAX
		} else {
			swapSections('executionIframe', 'executionResult', 50);
			new Request.HTML({ url: '<?=thisPage?>',
				method: 'post',
				data: theForm,
				update: 'executionResult',
				evalScripts: true,
				onComplete: function() {
					$('resultLabel').fade('in');
				},
				onFailure: function() {
					$('resultLabel').fade('in');
					var resultDiv = new Element('div', { html: this.xhr.responseText });
					resultDiv.setStyle('color', '#a00');
					$('executionResult').empty().adopt(resultDiv);
				}
			}).send();
		}
	}
</script>
<div style="width: 95%; margin: 20px auto 20px auto;">
  <h1>PHP Execution Engine</h1>
  <form method="post" action="JavaScript: void(0);" target="executionIframe"
        onsubmit="JavaScript: sendExecution(this); return false;">
    <input type="hidden" name="a" value="execute" />
    <textarea name="commands" rows="10" style="width: 100%; font-family: monospace; font-size: 13px;"></textarea>
    <br />
    <input type="submit" value="Execute" /> &nbsp; &nbsp;
    <input type="checkbox" name="stripSlashes"> Strip Slashes &nbsp; &nbsp;
    <input type="checkbox" name="toIFrame"> To Iframe
  </form>
  <br/>
  <div id="resultLabel">Execution Result:</div><hr>
  <div id="executionResult" style="min-height: 100px;"></div>
  <iframe id="executionIframe" name="executionIframe" style="min-height: 100px; width: 100%; display: none;"></iframe>
  <hr>
</div>
<?
	if(function_exists('EndAdminPage'))
		EndAdminPage();
	else {
?>
</body>
</html>
<?
	}
}


function BeginDSIFrame() {
	header('Content-type: text/html; charset=utf-8');
?>
<!DOCTYPE HTML>
<html>
<head>
  <script type="text/javascript" src="../../assets/scripts/library/mootools-core-1.4.5.js"></script>
  <link href="../../assets/admin/css/admin.css" rel="stylesheet" type="text/css" />
  <style>
    body { background: transparent; box-shadow: none; padding: 10px 15px; }
    table.data td { font-family: monospace; }
  </style>
</head>
<body class="iframe">
<?
}
function EndDSIFrame() {
?>
<script>window.parent.$('resultLabel').fade('in');</script>
</body>
</html>
<?
}