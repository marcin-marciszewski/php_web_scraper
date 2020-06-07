<?php

include('simple_html_dom.php');

// Get the page for scraping
$html = file_get_html('https://jobs.sanctuary-group.co.uk/search/');

// Get total number of results
$total_results = $html->find('span.paginationLabel',0)->find('b',1)->innertext;;
$total_results = (int) $total_results;

// Create links to results subpages
$result_pages = [];

for($i=0; $i<$total_results;$i+=25){
    $result_pages[] ="https://jobs.sanctuary-group.co.uk/search/?q=&sortColumn=referencedate&sortDirection=desc&startrow=$i";
}

// Get links to all adverts
$all_ads_links =[];
foreach($result_pages as $result_page){
    $page = file_get_html($result_page);
    $job_ad_links = $page->find('span.jobTitle.hidden-phone');

    foreach($job_ad_links as $job_ad_link ){
        $single_link =$job_ad_link->first_child()->href;
        $all_ads_links[] ="https://jobs.sanctuary-group.co.uk$single_link";
    }
}

// Scraping require information from single add
function scrape_single_page($link) {
    $ad_page = file_get_html($link);
    $job_title =$ad_page->find('span[itemprop="title"]',0);
    echo($job_title);
    echo '<br>';
}

// scrape_single_page($all_ads_links[0]);

foreach($all_ads_links as $ad_link){
    scrape_single_page($ad_link);
}


// scrape_single_page($all_ads_links[0]);


// foreach($all_ads_links as $ads_link){
//     echo $ads_link;
//     echo '<br>';
// }





// $page =file_get_html($result_pages[0]);
// $ads_links = $page->find('span.jobTitle.hidden-phone a');

// foreach($found_ads_links as $link) {
//     echo $link->href;
//     echo '<br>';
// }


// foreach($result_pages as $link) {
//     echo $link;
//     echo '<br>';
// }

//  $list = $html->find('div[class="w3-bar w3-theme w3-card-2 w3-wide notranslate"]',0);


// $list_array = $list->find('a');

// for($i=0;$i<sizeof($list_array);$i++){
//     echo $list_array[$i]->plaintext;
//     echo '<br>';
// }
?> 

