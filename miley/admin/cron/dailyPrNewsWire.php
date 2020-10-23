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
use SCMiley\Rss\PrNewsWire\RssFeed;
date_default_timezone_set('America/New_York');
$rssIds = [
    'auto-transportation/aerospace-defense-news',
    'auto-transportation/air-freight-news',
    'auto-transportation/airlines-aviation-news',
    'auto-transportation/automotive-news',
    'auto-transportation/maritime-shipbuilding-news',
    'auto-transportation/railroads-intermodal-transportation-news',
    'auto-transportation/transportation-trucking-railroad-news',
    'auto-transportation/travel-news',
    'auto-transportation/trucking-road-transportation-news',
    'business-technology/broadcast-tech-news',
    'business-technology/computer-electronics-news',
    'business-technology/computer-hardware-news',
    'business-technology/computer-software-news',
    'business-technology/electronic-commerce-news',
    'business-technology/electronic-components-news',
    'business-technology/electronic-design-automation-news',
    'business-technology/electronics-performance-measurement',
    'business-technology/high-tech-security',
    'business-technology/internet-technology-news',
    'business-technology/nanotechnology-news',
    'business-technology/networks-news',
    'business-technology/peripherals-news',
    'business-technology/rfid-news',
    'business-technology/semantic-web-news',
    'business-technology/semiconductors-news',
    'consumer-products-retail/animals-pet-news',
    'consumer-products-retail/beers-wines-spirits-news',
    'consumer-products-retail/beverages-news',
    'consumer-products-retail/bridal-services',
    'consumer-products-retail/cosmetics-personal-care-news',
    'consumer-products-retail/fashion-news',
    'consumer-products-retail/food-beverages-news',
    'consumer-products-retail/furniture-furnishings-news',
    'consumer-products-retail/home-improvements-news',
    'consumer-products-retail/household-products-news',
    'consumer-products-retail/household-consumer-cosmetics-news',
    'consumer-products-retail/jewelry-news',
    'consumer-products-retail/non-alcoholic-beverages-news',
    'consumer-products-retail/office-products-news',
    'consumer-products-retail/organic-food-news',
    'consumer-products-retail/product-recalls-news',
    'consumer-products-retail/restaurants-news',
    'consumer-products/retail-news',
    'consumer-products-retail/supermarkets-news',
    'consumer-products-retail/supermarkets-news',
    'consumer-products-retail/toys-news',
    'consumer-technology/computer-electronics-news',
    'consumer-technology/computer-hardware-news',
    'consumer-technology/computer-software-news',
    'consumer-technology/consumer-electronics-news',
    'consumer-technology/electronic-commerce-news',
    'consumer-technology/electronic-gaming-news',
    'consumer-technology/mobile-entertainment-news',
    'consumer-technology/multimedia-internet-news',
    'consumer-technology/peripherals-news',
    'consumer-technology/social-media-news',
    'consumer-technology/website-news',
    'consumer-technology/wireless-communications-news',
    'energy/alternative-energies-news',
    'energy/chemical-news',
    'energy/electrical-utilities-news',
    'energy/gas-news',
    'energy/mining-news',
    'energy/mining-metals-news',
    'energy/oil-energy-news',
    'energy/oil-gas-discoveries-news',
    'energy/utilities-news',
    'energy/water-utilities-news',
    'entertainment-media/advertising-news',
    'entertainment-media/art-news',
    'entertainment-media/books-news',
    'entertainment-media/entertainment-news',
    'entertainment-media/film-motion-picture-news',
    'entertainment-media/magazines-news',
    'entertainment-media/music-news',
    'entertainment-media/publishing-information-services-news',
    'entertainment-media/radio-news',
    'entertainment-media/television-news',
    'environment/conservation-recycling-news',
    'environment/environmental-issues-news',
    'environment/environmental-policy-news',
    'environment/environmental-products-services-news',
    'environment/green-technology-news',
    'financial-services/accounting-news-issues-news',
    'financial-services/acquisitions-mergers-takeovers-news',
    'financial-services/banking-financial-services-news',
    'financial-services/bankruptcy-news',
    'financial-services/bond-stock-ratings-news',
    'financial-services/contracts-news',
    'financial-services/dividends-news',
    'financial-services/earnings-news',
    'financial-services/earnings-forecasts-projections-news',
    'financial-services/financing-agreements-news',
    'financial-services/insurance-news',
    'financial-services/investments-opinions-news',
    'financial-services/joint-ventures-news',
    'financial-services/mutual-funds-news',
    'financial-services/otc-small-cap-news',
    'financial-services/real-estate-news',
    'financial-services/restructuring-recapitalization-news',
    'financial-services/sales-reports-news',
    'financial-services/shareholders-rights-plans-news',
    'financial-services/stock-offering-news',
    'financial-services/stock-split-news',
    'financial-services/venture-capital-news',
    'general-business/agency-roster-news',
    'general-business/awards-news',
    'general-business/commercial-real-estate-news',
    'general-business/conference-call-announcements-news',
    'general-business/corporate-expansion-news',
    'general-business/earnings-news',
    'general-business/human-resource-workforce-management-news',
    'general-business/licensing-news',
    'general-business/new-products-services-news',
    'general-business/obituaries',
    'general-business/outsourcing-businesses-news',
    'general-business/overseas-real-estate-news',
    'general-business/personnel-announcements-news',
    'general-business/real-estate-transactions-news',
    'general-business/residential-real-estate-news',
    'general-business/small-business-services-news',
    'general-business/socially-responsible-investing-news',
    'general-business/surveys-polls-research-news',
    'general-business/trade-show-news',
    'health/biometrics-news',
    'health/biotechnology-news',
    'health/clinical-trials-medial-discoveries-news',
    'health/dentistry-news',
    'health/health-care-hospitals-news',
    'health/health-insurance-news',
    'health/infection-control-news',
    'health/medical-equipment-news',
    'health/medical-pharmaceuticals-news',
    'health/mental-health-news',
    'health/pharmaceuticals-news',
    'health/supplemental-medicine-news',
    'heavy-industry-manufacturing/aerospace-defense-news',
    'heavy-industry-manufacturing/agriculture-news',
    'heavy-industry-manufacturing/chemical-news',
    'heavy-industry-manufacturing/construction-building-news',
    'heavy-industry-manufacturing/hvac-news',
    'heavy-industry-manufacturing/machine-tools-metalworking-metallury-news',
    'heavy-industry-manufacturing/machinery-news',
    'heavy-industry-manufacturing/mining-news',
    'heavy-industry-manufacturing/mining-metals-news',
    'heavy-industry-manufacturing/paper-forest-products-containers-news',
    'heavy-industry-manufacturing/precious-metals-news',
    'heavy-industry-manufacturing/textiles-news',
    'heavy-industry-manufacturing/tobacco-news',
    'multicultural/african-american-related-news',
    'multicultural/asian-related-news',
    'multicultural/children-related-news',
    'multicultural/handicapped-disabled',
    'multicultural/hispanic-oriented-news',
    'multicultural/lesbian-gay-bisexual',
    'multicultural/native-american',
    'multicultural/religion',
    'multicultural/senior-citizens',
    'multicultural/veterans',
    'multicultural/women-related-news',
    'policy-public-interest/animal-welfare-news',
    'policy-public-interest/corporate-social-responsibility-news',
    'policy-public-interest/domestic-policy-news',
    'policy-public-interest/economic-news-trends-analysis-news',
    'policy-public-interest/education-news',
    'policy-public-interest/environmental-news',
    'policy-public-interest/european-government-news',
    'policy-public-interest/fda-approval-news',
    'policy-public-interest/federal-state-legislation-news',
    'policy-public-interest/federal-executive-branch-agency-news',
    'policy-public-interest/foreign-policy-international-affairs-news',
    'policy-public-interest/homeland-security-news',
    'policy-public-interest/labor-union--news',
    'policy-public-interest/legal-issues-news',
    'policy-public-interest/not-for-profit-news',
    'policy-public-interest/political-campaigns-news',
    'policy-public-interest/public-safety-news',
    'policy-public-interest/trade-policy-news',
    'policy-public-interest/us-state-policy-news',
    'sports/sporting-news',
    'sports/sporting-events-news',
    'sports/sports-equipment-accessories-news',
    'telecommunications/carriers-services-news',
    'telecommunications/mobile-entertainment-news',
    'telecommunications/networks-news',
    'telecommunications/peripherals-news',
    'telecommunications/telecommunications-equipment-news',
    'telecommunications/telecommunications-news',
    'telecommunications/voip-news',
    'telecommunications/wireless-communications-news',
    'travel/amusement-parks-tourist-attractions-news',
    'travel/gambling-casinos-news',
    'travel/hotels-resorts-news',
    'travel/leisure-tourism-hotels-news',
    'travel/passenger-aviation-news',
    'travel/travel-news'
];
$yesterdayDisplay = date('Y-m-d');//,strtotime("-1 days"));
$fhhp = fopen(__DIR__ . "/../prNewsWire/prNewsWire_$yesterdayDisplay.csv", 'w');
$fhlp = fopen(__DIR__ . "/../prNewsWire/prNewsWire_{$yesterdayDisplay}_lowPriority.csv", 'w');
fputs($fhhp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
fputs($fhlp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
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
            /* @var $result \SCMiley\Rss\PrNewsWire\Result */
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
}
fclose($fhhp);
fclose($fhlp);

