<?php
/**
 * Created by PhpStorm.
 * User: evan
 * Date: 2/25/17
 * Time: 3:28 PM
 */

require_once(__DIR__ . '/../../../../includes/adminGlobals.php');

set_time_limit(80000);
ini_set('memory_limit','500M');
use SCMiley\Rss\TwentyFourSevenPressRelease\RssFeed;
date_default_timezone_set('America/New_York');
$todayDisplay = date('Y-m-d');
$fhhp = fopen(__DIR__ . "/../twentyFourSevenPressRelease/twentyFourSevenPressRelease_$todayDisplay.csv", 'w');
//$fhlp = fopen(__DIR__ . "/../businessWire/businessWire_{$todayDisplay}_lowPriority.csv", 'w');
$titles = [];
$header = ['URL', 'RSS Feed', 'Company', 'Company URL', 'Title', 'Release Summary', 'Text', 'Contacts', 'Email Addresses', 'Phone Numbers'];
fputcsv($fhhp, $header);
//fputcsv($fhlp, $header);
$companyNames = [];
    try {
//        echo "Fetching $rssId Feed\n";
        $rssFeed = new RssFeed($titles);
        foreach ($rssFeed->getResults() as $result) {
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
    } catch(\Exception $e) {
        echo "EXCEPTION - {$e->getMessage()}\n{$e->getTraceAsString()}";
    }
fclose($fhhp);
//fclose($fhlp);

