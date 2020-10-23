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
use SCMiley\Rss\PrWeb\RssFeed;

date_default_timezone_set('America/New_York');
$rssIds = [
    'entertainment', 'artbooks', '9910celebrities', 'aecountrymusic', '9912dance', 'fineart', 'aemagazines', 'aemovies', 'aemuseums', 'music', 'ipodmp3', 'aenewstalk', 'aeperforming', '9914photography', 'television', 'aevideogames', 'aewebsites', 'automotive', 'autoaftermkt', 'autoclassic', 'autoconsumerpubs', 'autobike', 'autoracing', 'autorv', 'autorepair', 'autotrades', 'business', 'advertising', 'bizbooks', '9916consumerresearch', 'bizcorp', '9918directmarketing', 'bizecomm', 'economy', 'employmentcareers', '9920entrepreneurs', 'nbizexecs', 'finance', 'franchise', 'bizhr', 'bizinsurance', 'investment', '9920bizmanagement', 'markets', 'nbizacquire', 'mlm', 'nbizequity', 'biz_pubcom', 'bizpubs', 'realestate', 'retail', 'smallbiz', 'bizstartup', 'stocks', 'supermarkets', 'trade', 'businesstravel', 'consumerwww', '9924bizwomen', 'computers', 'computermacintosh', 'computerdatabases', 'computergames', 'computerinstruction', 'opensource', 'computermicrosotwindowspc', 'computeros', 'computerprogram', 'computersecurity', 'software', 'computerutilities', 'education', 'educollege', 'homeschooling', 'eduk12', 'edupostgrad', 'edutech', 'environment', 'eventstradeshows', 'government', 'govedu', 'elections', 'enviroregs', 'govbudget', 'foreignconflict', 'foriengpolicy', '9940governmentjudicial', 'govlaw', 'govlegislative', 'govlocal', '9962military', 'govnational', 'politics', 'govpublicsvc', '9942govsecurity', 'govstate', 'govtransport', 'medical', 'medicalabortion', '9954addiction', '9956allergies', 'medaltmed', 'medasthma', 'medcancer', 'medcardiology', 'medchiropractic', 'meddental', 'meddermatology', 'meddiabetes', 'med911', 'medfamily', 'medgeneral', 'medgeriatrics', 'medhospitals', 'meddisease', 'medim', 'medhmo', 'medproducts', 'medmentalhealth', 'medneurology', 'mednursing', 'mednutrition', 'medobgyn', '9966occupationalsafety', 'medpediatrics', 'pharmaceuticals', 'medpt', '9958plasticsurgery', '9970psychology', 'medimaging', 'medresearch', '9960sportsmedicine', 'medsurgery', 'veterinary', 'medvision', 'home', 'homefinance', '9978loss', 'homeinteriors', 'landscapinggardening', '9944marriage', '9964money', 'homeparenting', '9968pets', '9946taxes', 'weddingbridal', 'industry', 'aerospacedefense', 'agriculture', 'enviroalt', 'appareltextiles', 'architectural', 'construction', 'indelectrical', 'enviroenergy', '9936engineering', 'indfood', 'foodsafety', '9938fraud', 'indfuneral', 'gaming', 'healthcare', 'insurance', 'leisurehospitality', 'indlogistics', 'machinery', 'manucacturing', 'maritime', 'miningmetals', 'nonprofit', 'oilenergy', 'paperforestproducts', 'indphvac', 'utilities', 'restaurants', 'telecom', 'tobacco', 'indtoy', 'transportation', 'legal', 'law_attorneys', 'law_cr', 'law_gl', 'law_ip', 'law_firms', 'law_pi', 'law_re', 'lifestyle', 'lsbeauty', '9926coaching', 'consumer', '9950datingsingles', '9952diet', 'lsfashion', 'foodbeverage', 'consumergifts', 'lshealthfitness', 'consumerhobbies', 'hotelresorts', 'lspastimes', 'lsrestaurants', 'lsretire', '9972personalgrowth', 'travel', 'media', 'blogging', 'indbroadcast', '9928design', '9930graphicdesign', '9932industrialdesign', '9976seo', 'podcasting', '9948printingindustry', 'printmedia', 'bizpr', 'publishing', 'radio', 'rsscontentsyndication', '9934webdesign', 'miscellaneous', 'opinion', 'podcastingannounce', 'podcastingtools', 'religionother', 'scienceresearch', 'sciastr', 'scibi', 'biotechnology', 'chemical', 'nanotechnology', 'sciphys', 'weather', 'society', 'societyaffirm', 'socafricanamerican', 'americapost911', 'enviroanimal', 'socasian', 'societychildren', 'religionchristian', 'civilrights', 'societycrime', 'deathpenalty', '9980disabled', 'socgay', 'envirowarm', 'societygun', 'sochispanic', 'humanrights', 'religionislam', 'religionjewish', 'socmen', 'societynativeamerican', 'enivroresource', '10254socpeople', 'religion', 'socseniors', 'societyss', 'religion_spirituality', 'socteen', 'volunteer', 'societywomen', 'sports', 'sportsbaseball', 'sportsbasketball', 'sportsbike', 'sportsboat', 'sportsbowling', 'sportsboxing', 'sportfishing', 'sportfootball', 'sportsgolf', 'sportshockey', 'sportshunting', 'sportsmartialarts', 'sportsolympics', 'sportsoutdoors', 'sportsrugby', 'sportssoccer', 'sportswater', 'sportswinter', 'technology', 'techcomputer', 'electronics', 'techentsoftware', 'techgames', 'techprinting', 'techhardware', 'techindustrial', '9974techinfo', 'internet', 'techmobile', 'techmultimedia', 'technano', 'technetworking', 'techgaming', 'techgov', 'techrobotics', 'electronicssemicond', 'techsoftware', 'techtelecom', 'webmasters'
];
$yesterdayDisplay = date('Y-m-d',strtotime("-1 days"));
$fhhp = fopen(__DIR__ . "/../prWeb/prWeb_$yesterdayDisplay.csv", 'w');
$fhlp = fopen(__DIR__ . "/../prWeb/prWeb_{$yesterdayDisplay}_lowPriority.csv", 'w');
$titles = [];
$header = ['URL', 'RSS Feed', 'Companies', 'Main Link', 'Title', 'Release Summary', 'Text', 'Contacts', 'Contact Names', 'Email Addresses', 'Phone Numbers'];
fputcsv($fhhp, $header);
fputcsv($fhlp, $header);
$companyNames = [];
foreach($rssIds as $rssId) {
    try {
        echo "Fetching $rssId Feed\n";
        $rssFeed = new RssFeed($rssId, $titles);
        foreach ($rssFeed->getResults() as $result) {
            /* @var $result \SCMiley\Rss\PrWeb\Result */
            $titles[] = $result->getTitle();
            if(!$result->getCompanyNames() || !in_array($result->getFirstCompanyName(), $companyNames)) {
                if($result->getFirstCompanyName()) {
                    $companyNames[] = $result->getFirstCompanyName();
                }
                echo "Adding {$result->getTitle()} to file. \n";
                $fh = $result->isGoodTarget() ? $fhhp : $fhlp;
                fputcsv($fh, $result->getArrayForCsv());
            } else {
                echo "Skipping duplicate for {$result->getFirstCompanyName()}.\n";
            }
        }
    } catch(\Exception $e) {
        echo "EXCEPTION - {$e->getMessage()}\n{$e->getTraceAsString()}";
    }
}
fclose($fh);

