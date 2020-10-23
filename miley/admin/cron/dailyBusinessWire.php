<?php
/**
 * Created by PhpStorm.
 * User: evan
 * Date: 2/25/17
 * Time: 3:28 PM
 */

require_once(__DIR__ . '/../../../../includes/adminGlobals.php');
ini_set("display_errors", 1);
error_reporting(E_ALL);

set_time_limit(80000);
ini_set('memory_limit','500M');
use SCMiley\Rss\BusinessWire\RssFeed;
echo '<pre>';
date_default_timezone_set('America/New_York');
$rssIds = [
    'G1QFDERJXkJeEVlZXw==',
    'G1QFDERJXkJeGV1SXw==',
    'G1QFDERJXkJeEFpRVQ==',
    'G1QFDERJXkJeEFpRVA==',
    'G1QFDERJXkJeEFpQXQ==',
    'G1QFDERJXkJeFFlUXw==',
    'G1QFDERJXkJeGVpZWQ==',
    'G1QFDERJXkJeEFpQXA==',
    'G1QFDERJXkJeEFpQXw==',
    'G1QFDERJXkJeEVlWWw==',
    'G1QFDERJXkJeGFNUWg==',
    'G1QFDERJXkJeEFtRXA==',
    'G1QFDERJXkJeEVlZWA==',
    'G1QFDERJXkJfEVxWXw==',
    'G1QFDERJXkJeEFpTXA==',
    'G1QFDERJXkJeGVtXWw==',
    'G1QFDERJXkJeEFpQWA==',
    'G1QFDERJXkJeEFpTXw==',
    'G1QFDERJXkJeEFpQWw==',
    'G1QFDERJXkJeEFpQWQ==',
    'G1QFDERJXkJeEF5XWQ==',
    'G1QFDERJXkJeGVtXWQ==',
    'G1QFDERJXkJeEVlZXg==',
    'G1QFDERJXkJeEFpQWg==',
    'G1QFDERJXkJeEFxXVA==',
    'G1QFDERJXkJeEFpQVQ==',
    'G1QFDERJXkJeEVlZWQ==',
    'G1QFDERJXkJdEVhZXw==',
    'G1QFDERJXkJeEFxQWQ==',
    'G1QFDERJXkJeEFpRWw==',
    'G1QFDERJXkJeGVtVVQ==',
    'G1QFDERJXkJeEFtRXQ==',
    'G1QFDERJXkJeGVtYXw==',
    'G1QFDERJXkJeEF5XWw==',
    'G1QFDERJXkJeGVtYXg==',
    'G1QFDERJXkJeGV1SXw==',
    'G1QFDERJXkJeGVtYWQ==',
    'G1QFDERJXkJeGVtYWA==',
    'G1QFDERJXkJeGVtYWw==',
    'G1QFDERJXkJeEF5XWA==',
    'G1QFDERJXkJeFFlUXw==',
    'G1QFDERJXkJeGVtWXQ==',
    'G1QFDERJXkJeEF9ZVA==',
    'G1QFDERJXkJeEF9YXA==',
    'G1QFDERJXkJeGVtWWQ==',
    'G1QFDERJXkJeEFtRXA==',
    'G1QFDERJXkJeGVtWWA==',
    'G1QFDERJXkJeEFtRXw==',
    'G1QFDERJXkJeGVtWWw==',
    'G1QFDERJXkJeEF9YXQ==',
    'G1QFDERJXkJeEFtRXg==',
    'G1QFDERJXkJeEFtRWQ==',
    'G1QFDERJXkJeEFtRWA==',
    'G1QFDERJXkJeGVtXWA==',
    'G1QFDERJXkJeEFxRXA==',
    'G1QFDERJXkJeGVtXWg==',
    'G1QFDERJXkJeEFtRWw==',
    'G1QFDERJXkJeGVtXVA==',
    'G1QFDERJXkJeGVtWXA==',
    'G1QFDERJXkJeGVtWXw==',
    'G1QFDERJXkJeGVtWXg==',
    'G1QFDERJXkJeEFtRVQ==',
    'G1QFDERJXkJeEFxXVA==',
    'G1QFDERJXkJeEFxQWQ=='
];
$yesterdayDisplay = date('Y-m-d',strtotime("-1 days"));
$fhhp = fopen(__DIR__ . "/../businessWire/businessWire_$yesterdayDisplay.csv", 'w');
$fhlp = fopen(__DIR__ . "/../businessWire/businessWire_{$yesterdayDisplay}_lowPriority.csv", 'w');
$titles = [];
$header = ['URL', 'RSS Feed', 'Company', 'Company URL', 'Title', 'Release Summary', 'Text', 'Contacts', 'Contacts [Cleaned]', 'Email Addresses', 'Phone Numbers'];
fputcsv($fhhp, $header);
fputcsv($fhlp, $header);
$companyNames = [];
foreach($rssIds as $rssId) {
    try {
        echo "Fetching $rssId Feed\n";
        $rssFeed = new RssFeed($rssId, $titles);
        foreach ($rssFeed->getResults() as $result) {
            /* @var $result \SCMiley\Rss\BusinessWire\Result */
            $titles[] = $result->getTitle();
            if(!in_array($result->getCompanyName(), $companyNames)) {
                $companyNames[] = $result->getCompanyName();
                echo "Adding {$result->getTitle()} to file. \n";
                $fh = $result->isGoodTarget() ? $fhhp : $fhlp;
                fputcsv($fh, $result->getArrayForCsv());
                var_dump([$result->getTitle(), $result->getCompanyName(), $result->getContacts()]);
            } else {
                echo "Skipping duplicate for {$result->getCompanyName()}.\n";
            }
        }
    } catch(\Exception $e) {
        echo "EXCEPTION - {$e->getMessage()}\n{$e->getTraceAsString()}";
    }
}
fclose($fhhp);
fclose($fhlp);

