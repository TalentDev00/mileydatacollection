<?
use SCMiley\Email\ReceivingAddress;

require_once('../../../includes/adminGlobals.php');

ForceSecureURL();
CheckPageAdminSecurity();
if(strlen(Request("a")) > 0) { BlockCSRFFailingRequest(); }

switch(Request("a")) {
    case "search":			SearchEmails();										break;
    case 'importEmails':		    ImportEmailsViaCSV();		break;
    case "deleteEmails":	    DeleteEmailsFR();									break;
    case "export":			ExportLeadSearchResults();							break;
    case "checkEmail":
        $ra = ReceivingAddress::getByEmail(SRT('email'));
        $ra->checkEmail();
        break;
    default:				DisplayEmailSearchPage();
}
CloseDBConnection();
exit;


function DeleteEmailsFR() {
    $ReceivingAddressEmailList = implode("', '", RequestArray('EmailSet'));
    if(count($ReceivingAddressEmailList) == 0) return;
    ExecuteSQL2(
        "DELETE FROM tblReceivingAddresses
		  WHERE email IN('$ReceivingAddressEmailList')");
}

function ImportEmailsViaCSV()
{

    BeginOutputIFrame();
    echo "<p>Uploading your emails...</p>";
    $filename = applicationFileRoot . 'admin/csv/emailImport.csv';
    $uploadedFilename = strtolower($_FILES['theFile']['name']);
    $bits = explode(".", $uploadedFilename);
    $extension = end($bits);

    $allsWell = false;
    if (empty($_FILES['theFile']['size'])) {
        echo "Empty or no file uploaded.";
    } elseif ($extension != 'csv') {
        echo "Please upload a .csv file.";
    } elseif (!move_uploaded_file($_FILES['theFile']['tmp_name'], $filename)) {
        echo "Could not save your uploaded file.";
    } else {
        $allsWell = true;
    }

    if (!$allsWell) {
        echo '<script>window.top.endCSVUpload();</script>';
        return;
    }

    if ($handle = fopen($filename, "r")) {


        $addCount = 0;
        fgetcsv($handle, 1000, ","); // burn through header row
        while (($dataSet = fgetcsv($handle, 1000, ",")) !== false) {
            ///	Display($dataSet);
            $searchName = preg_replace('@https?://(www\.)?@', '', trim($dataSet[0]));
            $email = trim($dataSet[0]);
            if(!$email) {
                continue;
            }
            $password = trim($dataSet[1]);
            $proxy = trim($dataSet[2]);
            $daysToWait = ProperInt($dataSet[3]);
            $newEmail = (strtolower(trim($dataSet[4])) == 'x');
            $ra = new ReceivingAddress($email, $password, $daysToWait, time(), !$newEmail, $proxy);
            $ra->replaceIntoDB();
            $addCount++;
        }
        fclose($handle);
        echo Inflect::pluralize_if($addCount, 'Email') . ' added or updated.' .
            '<script>window.top.endCSVUpload();</script>';
    }
    unlink($filename);
    EndOutputIFrame();
}

function DisplayEmailSearchPage() {

    BeginAdminPage("Search Email Addresses");

    ?>
    <script type="text/javascript">

        window.addEvent('domready', function() {

            $$('input.date').each(function(x) { new DatePicker(x, { direction: -0.5, showOnInputClick: false })});
            $('theListing').addEvent('click:relay(.checkAll)', toggleAllRowsChecked);
            $('theListing').addEvent('click:relay(tr.clickable)', function(event) {
                if(event.target.tagName == 'INPUT'  ||  event.target.hasClass('checkbox')) return; // no action for clicking the checkboxes
                checkEmail(event);
            });
            App.DTH = new DataTableHelper('theListing', 'filterForm', '?a=search', {
                sortableFieldList: " -dateAdded searchName searchGeo  ownerName totalStatus",
                defaultSort: 'searchName ASC', paginationClass: 'paginationSet',
                onLoadStart: showLoad, onLoadComplete: hideLoad, PaginationOptions: { separator: '', includePrevNext: false }
            });
        });

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
            $$('[value=Search]')[0].click();
            hideLoad();
        }

        function deleteSelectedEmails() {
            var EmailSet = $('theListing').getElements('input[type=checkbox]:checked').map(
                function(x) { return x.getParent('tr').get('data-email'); });
            var doomedCount = EmailSet.length;
            if(doomedCount == 0) return;

            if(!confirm('Are you sure you want to delete ' + (doomedCount == 1 ? 'this email' : 'these ' + doomedCount + ' emails') + '?')) return;
            showLoad();
            new Request.HTML({ url: App.thisPage + '?a=deleteEmails',
                method: 'post',
                data: { EmailSet: EmailSet },
                onComplete: function() {
                    hideLoad();
                    App.roar.alert('The emails have been deleted.');
                    App.DTH.reload();
                }
            }).send();
        }
        var checkingEmail = false;
        function checkEmail(event) {
            if(checkingEmail) {
                alert('An email checking is already in progress.');
                return;
            }
            checkingEmail = true;
            showLoad();

            new Request.HTML({ url: App.thisPage + '?a=checkEmail',
                method: 'post',
                data: { email: event.target.getParent().get('data-email') },
                onComplete: function() {
                    hideLoad();
                    App.roar.alert('The email check has been run.');
                    checkingEmail = false;
                }
            }).send();
        }

    </script>
    <form enctype="multipart/form-data" id="CSVForm" method="POST" target="resultsIFrame"
          style="float: right; width: 350px;" onsubmit="return beginCSVUpload(this);">
        <h2>Upload Emails via CSV</h2>
        <input type="hidden" name="a" value="importEmails">
        <input type="file" name="theFile" style="margin-bottom: 10px"><br />
        <a href="csv/emailTemplate.csv">Download the CSV template</a><br />
        <div class="nextElement"></div>
        <input type="submit" value="Upload Emails" id="CSVSubmitButton"> &nbsp;
        <iframe src="about:blank" name="resultsIFrame" id="uploadIFrame" style="border: none; display: none;"></iframe>
    </form>

    <div id="main">
        <h1>Search Emails</h1>
        <form id="filterForm" class="box" style="width: 50%">
            <table>
                <col width="120">
                <col width="270">
                <col width="50">
                <tr>
                    <td>Search Email:</td>
                    <td><input type="text" name="q" maxlength="200" style="width: 270px;" /></td>
                    <td rowspan="99"></td>
                </tr>

                <tr>
                    <td>Next check between</td>
                    <td>
                        <input type="text" name="dateFrom" class="date" style="width: 90px;" id="dateFrom" /> and
                        <input type="text" name="dateTo" class="date" style="width: 90px;" id="dateTo" />
                    </td>
                </tr>
                <tr>
                    <td>Items Per Page</td>
                    <td>
                        <select name="pageSize" style="width: 70px;" id="pageSizeSelect"
                                onchange="Cookie.write('pageSize', this.value, {duration: 90 });">
                            <? DrawArrayOptions(Array(5, 10, 20, 50, 100, 200, 500), 0, CookieInt("pageSize", 50)); ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <input type="submit" class="button" value="Search" />
                    </td>
                </tr>
            </table>
        </form>
        <br />
        <br />
        <div class="left nextElement">
            <?=JSLink('Delete Selected Emails', 'deleteSelectedEmails()', 'button')?>
        </div>
        <div id="theListing"></div>
        <div class="nextSection"></div>
    </div>
    <?

    EndAdminPage();
}


function GetEmailFiltersFromRequest() {
    $dateClause = RequestDateClause("nextCheckScheduled", "dateFrom", "dateTo");

    $q = SQLSafe(StraightRequestText("q", 100));
    $queryClause = strlen($q) > 0 ? "(email LIKE '%$q%')" : "1=1";

    return "$queryClause  AND  $dateClause";
}

function SearchEmails() {

    $pageNumber = RequestInt("page", 1);
    $sortableFieldList = "-email password proxy daysBetweenChecks nextCheckScheduled captchaFail";
    $sortableFieldSet = explode(' ', str_replace('-', '', $sortableFieldList));

    $sortBits = explode(' ', Request("sort") . " ");
    $sortDirection = (strtoupper($sortBits[1]) == "ASC" ? "ASC" : "DESC");
    $sortField = (in_array($sortBits[0], $sortableFieldSet) ? $sortBits[0] : 'email');

    $raSQL =
        "SELECT email, password, proxy, daysBetweenChecks, nextCheckScheduled, captchaFail
            FROM tblReceivingAddresses
		  WHERE " . GetEmailFiltersFromRequest();

    $totalPages = 0;
    $raRS = GetComplexSortPageRecordSet($raSQL, "$sortField $sortDirection", $pageNumber, RI("pageSize", 20), $totalPages);
    echo "<span id=\"totalPages\" style=\"display: none;\">$totalPages</span>";  // hint to DataTableHelper
    if($totalPages == 0) {
        echo "Sorry, no emails were found matching the criteria you provided.";
        return;
    }
    ?>
    <table class="data largeData nextElement" cellpadding="0" cellspacing="0" style="width: 100%;">
        <thead>
            <tr>
                <th width="20" class="checkAll"></th>
                <th>Email</th>
                <th>Password</th>
                <th>Proxy</th>
                <th>Days Between Checks</th>
                <th>Next Check</th>
                <th>Needs Attention</th>
            </tr>
        </thead>
        <tbody>
            <?
            while($raR = mysqli_fetch_assoc($raRS)) {
                ?>
                <tr class="clickable highlightable" data-email="<?=$raR["email"]?>">
                    <td class="checkbox"><input type="checkbox" name=""></td>
                    <td><?=$raR["email"]?></td>
                    <td><?=$raR["password"]?></td>
                    <td><?=$raR["proxy"]?></td>
                    <td><?=$raR["daysBetweenChecks"]?></td>
                    <td><?=FormatDate($raR["nextCheckScheduled"], '%m/%d/%y')?></td>
                    <td><?=$raR['captchaFail'] ? 'Yes' : 'No'?></td>
                </tr>
                <?

            }
            ?>
    </table>
    <?=JSLink('Delete Selected Emails', 'deleteSelectedEmails()', 'button')?>
    <?
    return $totalPages;
}

//function ExportLeadSearchResults() {
//
//    $LIMIT = (SRT('q') == '' ? "LIMIT 1000" : ''); // no filter at all?  Limit output to only 10000 as a precaution
//
//    $lsrRS = GetRS(
//        "SELECT L.ID AS LeadID, L.isHot, L.searchName, L.searchGeo,
//		       LSR.position, LSR.rating, LSR.score,
//		       SR.title, SR.URL, SR.blurb
//		  FROM tblLeadScanResults LSR
//		 INNER JOIN tblSearchResults SR
//		    ON LSR.SearchResultID=SR.ID
//		 INNER JOIN tblLeadScans LS
//		    ON LSR.LeadScanID=LS.ID
//		 INNER JOIN tblLeads L
//		    ON LS.LeadID=L.ID
//		 WHERE " . GetLeadFiltersFromRequest() . "
//		   AND LS.ID IN(SELECT MAX(ID) FROM tblLeadScans GROUP BY LeadID)
//		 ORDER BY L.searchName, L.searchGeo, L.ID DESC, LSR.position
//		 $LIMIT");
//
//    $filename = "csv/leadSearchResultExport-" . time() . ".csv";
//    WriteRecordSetAsCSV($lsrRS, $filename);
//    DeliverFileAsInlineDownload($filename);
//    unlink($filename);
//
//}