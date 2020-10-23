<?
use \SCMiley\Entities\Template;
require_once('../../../../includes/adminGlobals.php');
/*==========================================================================
	regularTasks.php
	jpl 11/27/13

	Run scheduled tasks when called.  Should be called every minute.
	Handles:
		Scheduled Lead Monitoring
		Daily notifcation of hot leads

==========================================================================*/
//	if(Request('test') != 'go') exit();

//	if(Request('test') == 'go') { UnlockApplicationTask("MonitorLeads"); SetLogMessageOutput(); }
	set_time_limit(5000);
//	tic();
	// First make sure things are getting done around here:
//	$lastMonitorRunTime = GetSetting('lastMonitorRunTime');
//	LogMessage("========================================");
//	LogMessage("Starting, lastMonitorRunTime=" . FormatDate($lastMonitorRunTime, '%i:%N:%S %p'));
//	if(!isDevInstallation  &&  time() - $lastMonitorRunTime > (3*60)) {
//		SendBugAlertEmail("It's been over 3 minutes since lastMonitorRunTime has been successfully marked.");
//	}

//	PerformScheduledLeadScans(GetSetting('leadsToScanPerMinute'));

	// ScanIncompleteRipoffReports();

//	$elapsed = toc();
//	if($elapsed > 60  ||  date('i') == 0) // if this took more than 5 seconds, log it--good to know.  Also log on the hour
//		LogMessage($elapsed . "s", 'cronLog');
//
	// How about our daily send off of hot leads?
//	$sendTime = TimeToMinutesFromMidnight(GetSetting('hotLeadNotificationSendTime'));
//	if($sendTime < CurrentServerTimeOfMinutes()  &&  DateDiff('h', GetSetting('lastHotLeadNotificationTime'), time()) >= 23) {
//		SendDailyNewHotLeadsNotification();
//		SetSetting('lastHotLeadNotificationTime', time());
//	}


//	SetSetting('lastMonitorRunTime', time());
//	LogMessage("Completed at " . FormatDate(time(), "%i:%N%p") . ", took $elapsed seconds");
//	LogMessage('*************************************************');
//	echo "Completed at " . FormatDate(time(), "%i:%N%p") . ", took $elapsed seconds";
//	CloseDBConnection();
//	exit;

\SCMiley\Entities\LeadGroup::scanNextQueuedGroup();

if(LockApplicationTask('createTemplate', 600)) {
    $template = Template::getNextQueuedRMCReportTemplate();
    if($template) {
        $template->create();
    }

    $seopProposalTemplate = Template::getNextQueuedSEOPProposalTemplate();
    if($seopProposalTemplate) {
        $seopProposalTemplate->create();
    }
    UnlockApplicationTask('createTemplate');
}


if(LockApplicationTask('regularCisionSearch', 1200)) {
    $em = \SCMiley\Database\DoctrineEntityManager::get();
    $linkClassName = \SCMiley\Entities\NewsSearchLink::class;
    $linkQuery = $em->createQuery(<<<DQL
        SELECT L
        FROM $linkClassName L
        JOIN L.viewAlls VA
        LEFT JOIN VA.terms T 
        LEFT JOIN T.companies C
        WHERE (C.collectAuthorInformation=1 OR VA.scanIntensively=1)
        AND L.authorSearched=FALSE
DQL
    );
    $links = $linkQuery->getResult();
    \SCMiley\Entities\NewsSearchLink::getLinkSetInformationFromCision($links, 20);
    $em->flush();
    \SCMiley\Entities\CisionListSearch::runNextCisionSearch();
    UnlockApplicationTask('regularCisionSearch');
}


\SCMiley\Google\GoogleSearch::searchNewsTerms();