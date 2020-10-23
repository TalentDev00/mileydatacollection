<?php
/**
 * Created by PhpStorm.
 * User: evan
 * Date: 10/11/17
 * Time: 1:37 AM
 */

require_once(__DIR__ . '/../../../../includes/adminGlobals.php');

set_time_limit(80000);
ini_set('memory_limit','500M');
use SCMiley\Rss\GlobeNewsWire\RssFeed;

$titles = [];
$companyNames = [];

$todayDisplay = date('Y-m-d');
$normalFilename = __DIR__ . "/../globeNewswire/globeNewswire$todayDisplay.csv";
$lowPriorityFilename = __DIR__ . "/../globeNewswire/globeNewswire{$todayDisplay}_lowPriority.csv";

$rssFeed = new RssFeed('country/United%20States/feedTitle/GlobeNewswire%20-%20News%20from%20United%20States', []);

if(!file_exists($normalFilename)) {
    $fhhp = fopen($normalFilename, 'w');
    $fhlp = fopen($lowPriorityFilename, 'w');

    fputs($fhhp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
    fputs($fhlp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
    $header = [
        'URL', 'Company', 'Company URL', 'Title', 'Release Summary', 'Text', 'Contact', 'Contact [Extracted]', 'Email Address', 'Phone Number'
    ];
//    $this->url, $this->companyUrl, $this->title,
//            $this->releaseSummary, $this->lastWords, $this->name,
//            $this->emailAddress, '="' . $this->phoneNumber . '"'
    fputcsv($fhhp, $header);
    fputcsv($fhlp, $header);
} else {
    $fhhpr = fopen($normalFilename, 'r');
    $fhlpr = fopen($lowPriorityFilename, 'r');
    while($row = fgetcsv($fhhpr)) {
        $companyNames[] = $row[1];
    }
    while($row = fgetcsv($fhlpr)) {
        $companyNames[] = $row[1];
    }
    fclose($fhhpr);
    fclose($fhlpr);
    $fhhp = fopen($normalFilename, 'a');
    $fhlp = fopen($lowPriorityFilename, 'a');
}
foreach ($rssFeed->getResults() as $result) {
    /* @var $result \SCMiley\Rss\BusinessWire\Result */
    $titles[] = $result->getTitle();
    if(!in_array($result->getCompanyName(), $companyNames)) {
        $companyNames[] = $result->getCompanyName();
        echo "Adding {$result->getTitle()} to file. \n";
        $fh = $result->isGoodTarget() ? $fhhp : $fhlp;
        fputcsv($fh, $result->getArrayForCsv());
    } else {
        echo "Skipping duplicate for {$result->getCompanyName()}.\n";
    }
}
fclose($fhhp);
fclose($fhlp);