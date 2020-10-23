<?
require_once('../../../includes/adminGlobals.php');
/*=======================================================================
 hotLeadExport.php
 jpp 1/29/14

s=======================================================================*/

	ForceSecureURL();
	CheckPageAdminSecurity();
	if(strlen(Request("a")) > 0) { BlockCSRFFailingRequest(); }

	switch(Request("a")) {
		case "export":			ExportHotLeads();										break;
		default:				DisplayHotLeadExportPage();
	}
	CloseDBConnection();
	exit;


function DisplayHotLeadExportPage() {
	
	BeginAdminPage("Export Leads");
	$lastExport = ProperDate(GetDBValue('tblAdminUsers', CurrentAdminUserID(), 'dateLastHotExport'), '1/1/2014');
?>
<script type="text/javascript">

window.addEvent('domready', function() {
	$$('input.date').each(function(x) { new DatePicker(x, { direction: -0.5, showOnInputClick: false })});
});

	function debugExportHotLeads(theForm) {
		prepFormForCSRFLegitimacy(theForm); return true;
		
		new Request.HTML({ url: App.thisPage + '?a=export',
			method: 'post',
			data: theForm,
			update: 'theResult',
			onComplete: function() {
				
			}
		}).send();
		return false;
	}

	function exportLatestHotLeads(theButton) {
		var theForm = $(theButton).getParent('form');
		theForm.dateFrom.value = '<?=FormatDate($lastExport, '%m/%d/%y %i:%N%p')?>';
		theForm.dateFrom.style.width = '150px';
		prepFormForCSRFLegitimacy(theForm);
		theForm.submit();
		return false;
	}
</script>
  <div id="main">
    <h1>Export Hot Leads</h1>
    <form action="<?=thisPage?>" method="post" class="box" onsubmit="JavaScript: return debugExportHotLeads(this);">
     <input type="hidden" name="a" value="export" />
     <table>
      <col width="120">
      <col width="330">
      <col width="50">
      <col width="120">
      <col width="210">
      <tr>
      	<td>Found hot between</td>
      	<td>
          <input type="text" name="dateFrom" class="date" style="width: 90px;" id="dateFrom"
          		 value="<?=FormatDate(DateAdd('d', -7, time()), '%m/%d/%y')?>" /> and
  	      <input type="text" name="dateTo" class="date" style="width: 90px;" id="dateTo" value="<?=FormatDate(time(), '%m/%d/%y')?>" />
        </td>
        <td></td>
		<td>Owned By:</td>
		<td>
		  <? if(CurrentAdminUserHasRole('M')) { ?>
		  <select name="AdminUserID">
		  	<option value="0">anyone</option>
			<?=DrawSQLOptions("SELECT ID, CONCAT_WS(' ', firstName, lastName) FROM tblAdminUsers")?>
		   </select>
		  <? } else { ?>
		  	Me.
		  <? } ?>
		</td>
      </tr>
      <tr>
      	<td>Source of Hotness:</td>
      	<td>
		  <select name="source">
		  	<option value="GSA">- All -</option>
		  	<option value="G">Google search results</option>
		  	<option value="S">SEMrush traffic reports</option>
		  	<option value="A">SEMrush Estimated Ad Budget</option>
		   </select>
        </td>
        <td></td>
      </tr>
	  <tr>
        <td></td>
        <td>
          <input type="submit" style="padding: 4px 13px" class="button" value="Export" />
		  <?=JSLink('Export Latest', 'exportLatestHotLeads(this)', 'button')?>
		</td>
	  </tr>
     </table>
    </form>
    <br />
    <br />
    <div id="theResult"></div>
  </div>
<?

	EndAdminPage();
}


function GetLeadFiltersFromRequest() {
	$dateClause = RequestDateClause("dateHot", "dateFrom", "dateTo");
	
	$AdminUserID = RequestInt('AdminUserID');
	$AdminUserIDClause = (CurrentAdminUserHasRole('M') ?
		$AdminUserID > 0 ? "(AdminUserID=$AdminUserID)" : "1=1" :
		"AdminUserID=" . CurrentAdminUserID());
	
	return "$dateClause  AND  $AdminUserIDClause";
}

function ExportHotLeads() {
	
	$dateFrom = FormatDate(RD('dateFrom'), '%Y-%m-%d');
	$dateTo   = FormatDate(RD('dateTo'),   '%Y-%m-%d');
	$source   = str_split(SRT('source', 3));
	
	$filename = "csv/hotLeadsExport{$dateFrom}to{$dateTo}.csv";
	$fh = fopen($filename, 'w') or die("can't open $filename for ExportHotLeads");
	
	$lRS = GetRS(
		"SELECT L.ID, L.searchName, L.searchGeo, L.dateAdded,
				L.site, L.contactName, L.contactPhone, L.contactEmail, L.dateHot,
				CONCAT(AU.firstName, ' ', AU.lastName) AS ownerName,
				IFNULL(L.SEMrushAdBudget, 'N/A') SEMrushAdBudget
		   FROM tblLeads L
		   LEFT JOIN (SELECT LeadID, COUNT(*) AS scans FROM tblLeadScans GROUP BY LeadID) LS
			 ON LS.LeadID=L.ID
		   LEFT JOIN tblAdminUsers AU
			 ON L.AdminUserID=AU.ID
		  WHERE L.isHot=1
		    AND " . GetLeadFiltersFromRequest());
	
	fwrite($fh, "LeadID,Owner,Added,Search Term,Search Geo,Website,Contact Name, Contact Phone, Contact Email,Date Hot,Estimated Ad Budget,Flags\r\n");
	while($lR = mysqli_fetch_assoc($lRS)) {
		$flagSet = array();
		if(in_array('G', $source)) { // we're interested if flagged by Google searching
			$srRS = GetRS(
				"SELECT SR.URL, LSR.position
				   FROM tblLeadScans LS
				  INNER JOIN tblLeadScanResults LSR
				     ON LSR.LeadScanID=LS.ID
				  INNER JOIN tblSearchResults SR
					 ON LSR.SearchResultID=SR.ID
				  WHERE LS.LeadID={$lR['ID']}
					AND LS.flagCount > 0
					AND LSR.score < 0");
			while($srR = mysqli_fetch_assoc($srRS)) {
				$flagSet[] = CSVValue("Position {$srR['position']}: {$srR['URL']}");
			}
		}
		if(in_array('S', $source)) { // we're interested if flagged by SEMrush traffic
			$srRS = GetRS("SELECT ID, dateOf FROM tblSEMrushReports WHERE LeadID={$lR['ID']}");
			$lastDate = null;
			while($srR = mysqli_fetch_assoc($srRS)) {
				if($lastDate) {
					$percentageChange = GetSEMrushComparisonScore($lR['ID'], $lastDate, $srR['dateOf']);
					if($percentageChange < SEMRUSH_FLAG_TRAFFIC_DROP) {
						$flagSet[] = CSVValue(number_format(abs($percentageChange), 0) .
							"% drop in traffic in " . FormatDate($srR['dateOf'], '%m/%y'));
					}
				}
				$lastDate = $srR['dateOf'];
			}
		}
		if(in_array('A', $source)) // we're interested if flagged by having a SEMrush estimated ad budget
			if($lR['SEMrushAdBudget'] > 0) $flagSet[] = CSVValue(
				'SEMrush Estimated Ad Budget of ' . FormatMoney($lR['SEMrushAdBudget']));

 		if(count($flagSet) == 0) continue; // not interesting afterall!
 		
		$dataSet = array_merge(array(
			$lR['ID'],
			CSVValue($lR['ownerName']),
			FormatDate($lR['dateAdded'], "%m/%d/%y"),
			CSVValue($lR['searchName']),
			CSVValue($lR['searchGeo']),
			CSVValue($lR['site']),
			CSVValue($lR['contactName']),
			CSVValue($lR['contactPhone']),
			CSVValue($lR['contactEmail']),
			FormatDate($lR['dateHot'], "%m/%d/%y"),
			CSVValue(FormatMoney($lR['SEMrushAdBudget']))
		), $flagSet);
		
		
		fwrite($fh, implode(',', $dataSet) . "\r\n");
	}
	fclose($fh);
//	echo HTMLWhiteSpace(GetFileText($filename));
	DeliverFileAsInlineDownload($filename);
	unlink($filename);
	ExecuteUpdate('tblAdminUsers', CurrentAdminUserID(), 'dateLastHotExport:D' . time());
}
