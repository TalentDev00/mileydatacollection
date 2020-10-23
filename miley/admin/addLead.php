<?php
//require_once('../../../includes/adminGlobals.php');
require_once('../../../includes/adminGlobals.php');
/*=======================================================================
addLead.php
jpl 11/19/13

=======================================================================*/
	
	ForceSecureURL();
	CheckPageAdminSecurity();
	BlockCSRFFailingRequest();

	switch(Request('a')) {
		case 'addLead':			    AddLeadFR();				break;
		case 'importLeads':		    ImportLeadsViaCSV();		break;
		default:					DrawAddLeadPage();
	}
	CloseDBConnection();
	exit();

function AddLeadFR() {
	
	$allsWell = false; // until we see otherwise
	//$errorMessage;
	
	$searchName = SRT('searchName');
	$searchGeo  = SRT('searchGeo');
    if($searchName == '')
    	$errorMessage = "Please enter a search term for this lead.";
	elseif($duplicateLeadID = LeadAlreadyExists($searchName, $searchGeo))
		$errorMessage = "du:$duplicateLeadID";
    elseif($poisonMessage = IsLeadPoison($searchName . ' ' . $searchGeo))
		$errorMessage = implode(' ', $poisonMessage);
	else
    	$allsWell = true;

	if(!$allsWell) {
		echo $errorMessage;
	} else {
		$LeadID = ExecuteInsertFromRequest('tblLeads',
			"searchName:100 searchGeo:100 site:200 contactName:50 contactPhone:50 contactEmail:50 location:200 notes:2000 " .
			"monitorPages:i monitorPeriod:i flagThreshold:i monitorPriority:i monitorSEMrush:c " .
			"AdminUserID:i" . CurrentAdminUserID());
		echo "ok:$LeadID";
	}
}

function ImportLeadsViaCSV() {

	BeginOutputIFrame();
	echo "<p>Uploading your leads...</p>";
	$filename = applicationFileRoot . 'admin/csv/leadImport.csv';
	$uploadedFilename = strtolower($_FILES['theFile']['name']);
	$bits = explode(".", $uploadedFilename);
	$extension = end($bits);
	
	$monitorPages		= RequestInt('monitorPages');
	$monitorPeriod		= RequestInt('monitorPeriod');
	$flagThreshold		= RequestInt('flagThreshold');
	$monitorPriority	= RequestInt('monitorPriority');
	$monitorSEMrush		= RequestCheckbox('monitorSEMrush');
	
	$allsWell = false;
	if(empty($_FILES['theFile']['size']))
		echo "Empty or no file uploaded.";
	elseif($extension != 'csv')
		echo "Please upload a .csv file.";
	elseif(!move_uploaded_file($_FILES['theFile']['tmp_name'], $filename))
		echo "Could not save your uploaded file.";
	elseif($monitorPages === 0 || $monitorPeriod === 0 || $flagThreshold === 0) 
		echo "Please enter a non-zero integer for each monitor setting.";
	else
		$allsWell = true;
	
	if(!$allsWell) {
		echo '<script>window.top.endCSVUpload();</script>';
		return;
	}

    if($handle = fopen($filename, "r")) {
    	
		
		$addCount = 0;
		$dupCount = 0;
		$poisonLeadMessageSet = [];
		$poisonCount = 0;
		fgetcsv($handle, 1000, ","); // burn through header row
		while(($dataSet = fgetcsv($handle, 1000, ",")) !== FALSE) {
		///	Display($dataSet);
			$searchName = preg_replace('@https?://(www\.)?@', '', trim($dataSet[0]));
			$searchGeo	= trim($dataSet[1]);
			$searchTerm = $searchName . ' ' . $searchGeo;
			if($poisonReasonSet = IsLeadPoison($searchTerm)) {
				$poisonLeadMessageSet[] = "$searchTerm was skipped because:<br />" . implode('<br />', $poisonReasonSet);
				$poisonCount++;
				continue;
			}
			$site		= trim($dataSet[2]);
			if($searchName == '') $searchName = $site;
			
			if(LeadAlreadyExists($searchName, $searchGeo)) {
				$dupCount++;
				continue;
			}
			$contactName	= SQLValue($dataSet[3]);
			$contactPhone	= SQLValue($dataSet[4]);
			$contactEmail	= SQLValue($dataSet[5]);
			$location		= SQLValue($dataSet[6]);
			$notes			= SQLValue($dataSet[7]);
			$monitorSEMrush = SQLBit($monitorSEMrush  &&  IsValidDomain($site));
			
			$LeadID = ExecuteInsert(
				"INSERT INTO tblLeads
					   (searchName, searchGeo, site, contactName, contactPhone, contactEmail, location, notes,
						monitorPages, monitorPeriod, flagThreshold, monitorPriority, monitorSEMrush, AdminUserID)
				 VALUES(" . SQLValue($searchName) . ", 
					 	" . SQLValue($searchGeo) . ",
					 	" . SQLValue($site) . ",
					 	$contactName, $contactPhone, $contactEmail, $location, $notes,
						$monitorPages, $monitorPeriod, $flagThreshold, $monitorPriority, $monitorSEMrush, " . CurrentAdminUserID() . ");");
			
//			SetLeadMonitorDefaultParameters($LeadID);
			$addCount++;
		}
		fclose($handle);
		echo Inflect::pluralize_if($addCount, 'lead') . ' added,<br />' .
			 Inflect::pluralize_if($dupCount, 'duplicate') . ' skipped.<br /><br /> ' .
			'<a href="leadListing">View leads</a>' .
			'<script>window.top.endCSVUpload();</script>';
		if($poisonCount > 0)
			echo '<br /><br />' . Inflect::pluralize_if($poisonCount, 'invalid lead') . ' skipped for the following reasons:<br /><br />' . 
				 implode('<br /><br />', $poisonLeadMessageSet) . '<br />';
	}
	unlink($filename);
	EndOutputIFrame();
}

function DrawAddLeadPage() {
	BeginAdminPage('Add a Lead');
	$searchValidationAttributes = [
		'pattern'	=> '^[a-zA-Z0-9][a-zA-Z0-9\s\.&\\"\'\-_\/]*$',
		'title'		=> 'Search terms must begin with an alphanumeric character and cannot contain punctuation other than .&"\'-_/'
	];
?>
<script>
	function addLead(theForm) {
		theForm = $(theForm);
		if(!confirmNonEmpty(theForm.searchName, 'Please indicate the search name.')) return false;
		var domainRegexp = /^(?:[a-zA-Z0-9]+(?:\-*[a-zA-Z0-9])*\.)+[a-zA-Z]{2,6}$/;
		if(theForm.monitorSEMrush.checked && !theForm.site.value.match(domainRegexp)) {
			alert('Please enter a valid website for SEMrush (must be a domain like "example.com").');
			return false;
		}
		
		showLoad();
		new Request.HTML({ url: App.thisPage + '?a=addLead',
			method: 'post',
			data: theForm,
			onComplete: function() {
				hideLoad();
				var LeadID = this.response.text.substring(3); // probably, unless we got an error message back
				if(this.response.text.substring(0, 3) == 'ok:') {
					window.location.href = 'lead.php?LeadID=' + LeadID;
				} else if(this.response.text.substring(0, 3) == 'du:') {
					if(confirm('Actually, a lead already exists with this search term + geography.\n\n' +
						'Would you like to go and see it now?'))
						window.location.href = 'lead.php?LeadID=' + LeadID;
				} else {
					alert(this.response.text);
				}
			}
		}).send();
	}
	
	function beginCSVUpload(theForm) {
		if(theForm.theFile.value == '') {
			alert('Please choose a file.');
			return false;
		}
		disableFormSubmitButton($('CSVSubmitButton'), 'Uploading...');
		$('uploadIFrame').setStyle('display', 'block');
		showLoad();
		prepFormForCSRFLegitimacy(theForm);
		return true;
	}
	function endCSVUpload() {
		restoreFormSubmitButton($('CSVSubmitButton'));
		hideLoad();
	}
</script>
  <h1>Add New Lead</h1>
  
  <form enctype="multipart/form-data" id="CSVForm" method="POST" target="resultsIFrame"
	  style="float: right; width: 350px;" onsubmit="JavaScript: return beginCSVUpload(this);">
	<h2>Upload Leads via CSV</h2>
    <input type="hidden" name="a" value="importLeads">
    <input type="file" name="theFile" style="margin-bottom: 10px"><br />
    Scan <?php echo TextInput('monitorPages', GetSetting('monitorPages'), 1, 25)?> page(s) of search results
      	every <?php echo TextInput('monitorPeriod', GetSetting('monitorPeriod'), 3, 50)?> days.
    <div class="nextElement"></div>
   	Flag when <?php echo TextInput('flagThreshold', GetSetting('hotLeadFlagThreshold'), 3, 25)?> negative result(s) are found.
    <div class="nextElement"></div>
    Scan Priority: <?php echo TextInput('monitorPriority', 0, 3, 35)?>
    <div class="nextElement"></div>
	<?php echo CheckboxInput('monitorSEMrush', false, 'Monitor SEMrush keyword positions for leads with a website (such as example.com)')?>
    <div class="nextElement"></div>
    <a href="csv/sample.csv">Download a sample leads CSV</a><br />
    <div class="nextElement"></div>
    <input type="submit" value="Upload Leads" id="CSVSubmitButton"> &nbsp;
    <iframe src="about:blank" name="resultsIFrame" id="uploadIFrame" style="border: none; display: none;"></iframe>
  </form>

<form action="JavaScript:void(0);" onsubmit="JavaScript: addLead(this);">
  <table width="490" class="elementSet">
    <tr>
      <td width="120">Search Name:</td>
	  <td><?php echo TextInput('searchName', '', 100, 350, $searchValidationAttributes)?></td>
    </tr>
	<tr>
	  <td></td>
	  <td><i>Search terms should be organic (<b>google</b> not <b>Google, Inc</b>).<br />Avoid special characters and punctuation.</i></td>
	</tr>
	<tr>
      <td width="120">Geography Term:</td>
      <td><?php echo TextInput('searchGeo', '', 100, 350, array_merge(['p' => '(Optional)'], $searchValidationAttributes))?></td>
    </tr>
	<tr>
	  <td></td>
	  <td><i>This should be a region or city, not an exact address!</i></td>
	</tr>
    <tr>
      <td>Website:</td>
      <td><?php echo TextInput('site', '', 200, 350)?></td>
    </tr>
    <tr>
      <td>Contact Name:</td>
      <td><?php echo TextInput('contactName', '', 50, 350)?></td>
    </tr>
    <tr>
      <td>Contact Phone:</td>
      <td><?php echo TextInput('contactPhone', '', 50, 350)?></td>
    </tr>
    <tr>
      <td>Contact Email:</td>
      <td><?php echo TextInput('contactEmail', '', 50, 350)?></td>
    </tr>
    <tr>
      <td>Location:</td>
      <td><?php echo TextInput('location', '', 200, 350)?></td>
    </tr>
    <tr>
      <td class="top">Monitor Settings:</td>
      <td>
        Scan <?php echo TextInput('monitorPages', GetSetting('monitorPages'), 1, 25)?> page(s) of search results
      	every <?php echo TextInput('monitorPeriod', GetSetting('monitorPeriod'), 3, 50)?> days.
      	Flag when <?php echo TextInput('flagThreshold', GetSetting('hotLeadFlagThreshold'), 3, 25)?> negative result(s) are found.
      	Priority: <?php echo TextInput('monitorPriority', 0, 3, 35)?>
      </td>
    </tr>
    <tr>
      <td>SEMrush:</td>
      <td>
		<?php echo CheckboxInput('monitorSEMrush', false, 'Monitor SEMrush keyword positions')?>
	  </td>
   </tr>
    <tr>
      <td class="top">Notes:</td>
      <td>
        <textarea name="notes" style="width: 350px; height: 60px;" maxlength="2000" placeholder="Optional."></textarea>
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <input type="submit" value="Add Lead" class="nextElement jumbo">
      </td>
    </tr>
  </table>
</form>
<?php 	
	EndAdminPage();
}

function IsLeadPoison($searchTerm) {
	$poisonReasonSet = [];
	if(preg_match_all('@([^a-zA-Z0-9\s\.&"\'\-_/])@', $searchTerm, $resultSet))
		$poisonReasonSet[] = 'The Search Name and Search Geo cannot contain ' . htmlspecialchars(implode('', array_unique($resultSet[0]))) . ' .';
	if(preg_match('@^[^a-zA-Z0-9]@', $searchTerm))
		$poisonReasonSet[] = 'The search name must begin with an alphanumeric character.';
	return empty($poisonReasonSet) ? false : $poisonReasonSet;
}
