<?
require_once('../../../includes/adminGlobals.php');
/*=======================================================================
graph.php
jpl 2/05/14

=======================================================================*/
	
	
	$LeadID = RI('LeadID');
	
	switch(Request('a')) {
		case 'leadTraffic':				GraphLeadTraffic($LeadID);				break;
		case 'renderLeadGoogleSERP':	RenderLeadGoogleSERP($LeadID);			break;
	}
	CloseDBConnection();
	exit();


function GraphLeadTraffic($LeadID) {
	
	$dateRange = GetR("SELECT MIN(dateOf) AS dateFrom, MAX(dateOf) AS dateTo FROM tblSEMrushReports WHERE LeadID=$LeadID");
	if($dateRange['dateFrom'] == $dateRange['dateTo']) return;
	$site = GetDBValue('tblLeads', $LeadID, 'site');
	$reportSet = BuildSEMrushLeadTrafficHistory($LeadID, $dateRange['dateFrom'], $dateRange['dateTo']);
	
	$lastDate = null;
	$dateLabelSet = array();
	$trafficSet   = array();
	$maxTraffic   = 0;
	foreach($reportSet as $report) {
		$dateLabelSet[] = FormatDate($report[0], "%m/%y");
		$trafficSet[]   = $report[1];
		$maxTraffic		= max($maxTraffic, $report[1]);
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  
  <script type="text/javascript" src="../../assets/admin/scripts/highcharts/adapters/standalone-framework.js"></script>
  <script type="text/javascript" src="../../assets/admin/scripts/highcharts/highcharts.js"></script>
  <script type="text/javascript" src="../../assets/admin/scripts/highcharts/themes/RMC.js"></script>
<style>
	body, html { padding: 0; margin: 0; height: 100%; font-family: Arial, Tahoma, sans-serif; }
</style>
</head>
<body>
  <div id="theGraph" style="width: 100%; height: 100%; margin: 0; padding: 0;"></div>

<script>
//$(function() { 
//	document.getElementById('theGraph').highcharts({
	var chart1 = new Highcharts.Chart({
		chart: {
			type: 'areaspline',
			renderTo: 'theGraph',
			zoomType: 'x'
		},
		title: {
			text: 'Organic traffic to <?=JSSafe($site)?>'
		},
		subtitle: {
			text: '<?=FormatDate($dateRange['dateFrom'], '%B %Y')?> through <?=FormatDate($dateRange['dateTo'], '%B %Y')?>'
		},
		xAxis: {
			categories: <?=json_encode($dateLabelSet)?>,
			tickmarkPlacement: 'on',
			type: 'datetime',
			title: {
				enabled: false
			}
		/*	labels: {
				formatter: function() {
					if(this.isFirst  ||  this.isLast) return '';
					return this.value;
				}
			},
            pointStart: Date.UTC(<?=FormatDate($dateRange['dateFrom'], "%Y, %m, %d")?>), // (2010, 0, 1),
            pointInterval: 24 * 3600 * 1000 // one month(ish)
        */
		},
		yAxis: {
			title: {
				text: 'Traffic',
				margin: 20
			},
			labels: {
				formatter: function() {
					return this.value; //  / 1000;
				}
			}
		},
		tooltip: {
			shared: true,
			valueSuffix: '' // ' millions'
		},
		plotOptions: {
			areaspline: {
				animation: false,
				stacking: 'normal',
				lineColor: '#0071BC',
				lineWidth: 3,
				marker: {
					lineWidth: 1,
					lineColor: '#ffffff',
					radius: 5
				}
			}
		},
		legend: {
			enabled: false
		},
		series: [{
			name: 'Relative traffic',
			fillColor : {
				linearGradient : [0, 0, 0, 400],
				stops : [
					[0, '#2B89C4'],
					[1, 'rgba(255,255,255,0)']
				]
        	},
			data: <?=json_encode($trafficSet)?>
		}]
	});
//});
</script>
</body>
</html>
<?
}


function RenderLeadGoogleSERP($LeadID) {
	
	$LeadScanID = GetSQLValue("SELECT ID FROM tblLeadScans WHERE LeadID=$LeadID ORDER BY ID DESC LIMIT 1", 0);
	if(!$LeadScanID) return;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <link href="../../assets/admin/css/admin.css" rel="stylesheet" type="text/css" />
</head>
<body>
  <div class="googleMimic"><? RenderLeadScan($LeadScanID) ?></div>
</body>
</html>
<?
}