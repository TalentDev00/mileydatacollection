<?php
/**
 * Created by PhpStorm.
 * User: evan
 * Date: 8/28/17
 * Time: 10:44 AM
 */

use SCMiley\Email\Email;

require_once('../../../../includes/adminGlobals.php');
//die();
//$em = \SCMiley\Database\DoctrineEntityManager::get();
//
//\SCMiley\Email\Email::sendInterdependenceSystemMessage(
//    'evan@seop.com', 'running quarter hourly', 'this is the email', 'this is the email'
//);
//\SCMiley\Google\GoogleSearch::searchNewsTerms();
die();
$intensiveScanQueue = $em->getRepository(\SCMiley\Entities\NewsSearchViewAll::class)
    ->createQueryBuilder('nsva')
    ->where('nsva.scanIntensively=true AND nsva.intensiveScanCutoff > CURRENT_TIMESTAMP()')
    ->getQuery()
    ->getResult();
foreach($intensiveScanQueue as $viewAll) {
    /** @var $viewAll \SCMiley\Entities\NewsSearchViewAll */
    try {
        $results = $viewAll->fetchResults();
    } catch(\Doctrine\ORM\OptimisticLockException $doctrineLockException) {
        SendBugAlertEmail("Doctrine exception: {$doctrineLockException->getMessage()} - {$doctrineLockException->getTraceAsString()}");
        continue;
    }


    $emails = $viewAll->getEmails();
    if(!count($emails) || !count($results)) {
        echo "No emails or no results associated with {$viewAll->getID()}.\n";
        continue;
    }
    $emailLines[] = "Interdependence's automated search has found results for <strong><a href='{$viewAll->getUrl()}'>{$viewAll->getGoogleIdDisplay()}</a>:</strong>";

    foreach($results as $result) {
        var_dump($result->getID());
        $emailLines[] = "<a href='{$result->getUrl()}'>{$result->getTitle()}</a>" .
            ($result->getDatePublished() ? " - {$result->getDatePublished()->format('D M j G:i:s T Y')}" : '');
        $emailLines[] = '';
    }

    $termList = $viewAll->getTermList();
    if($termList) {
        $emailLines[] = "{$viewAll->getGoogleIdDisplay()} is associated with the following terms:";
        $emailLines[] = $termList;
    }

    $emailBody = implode('<br>', $emailLines);
    if(!$results) {
        echo 'No passing links<br>';
    } else {
        echo 'Passing links found:<br>';
        echo $emailBody;
        $associatedEmails = array_map(function(\SCMiley\Entities\NewsEmail $email) { return $email->getEmail(); }, $emails->toArray());
        try {
            Email::sendInterdependenceSystemMessage(
                $associatedEmails,
//                ['evan@seop.com'],
                'New results found for: ' . $viewAll->getGoogleIdDisplay(),
                strip_tags($emailBody),
                $emailBody
            );
        } catch(\Exception $e) {
            SendBugAlertEmail("Error sending interdependence email:\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n" . print_r([$associatedEmails, $emailBody], true));
        }
    }
}

