<?php


include('simple_html_dom.php');

// Get the page for scraping
$html = file_get_html('https://jobs.sanctuary-group.co.uk/search/');

// Get total number of results
$total_results = $html->find('span.paginationLabel',0)->find('b',1)->plaintext;;
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




// Scraping require information from single ad
function scrape_single_page($link) {
    $ad_page = file_get_html($link);
    $title = $ad_page->find('span[itemprop="title"]',0)->plaintext;
    $title = trim($title);
    $operation = $ad_page->find('span[itemprop="facility"]',0)->plaintext;
    $operation = trim($operation);
    $location = $ad_page->find('span[itemprop="jobLocation"]',0)->plaintext;
    $requisition_number = $ad_page->find('span[itemprop="customfield5"]',0)->plaintext;
    if($ad_page->find('span[itemprop="department"]',0)) {
        $department = $ad_page->find('span[itemprop="department"]',0)->plaintext;
    } else {
        $department = $ad_page->find('span[itemprop="dept"]',0)->plaintext;
    }

    $job_description_full =$ad_page->find('span[class="jobdescription"]',0);

    $ps =$job_description_full->find('p');

    // Find closing date paragraph and extract the date
    $first = true;
    $descriptions =[];
    foreach($ps as $p){
        if (strpos($p->plaintext, 'Closing Date:') !== false ||strpos($p->plaintext, 'Closing date:') !== false) {
            // Remove "Closing date:" from the string
            $text = substr($p->plaintext, 0, 13);
            $date = str_replace($text,'',$p->plaintext);
            $closing_date= $date;
        }

        // Find paragraph with salary
        if (strpos(strtolower($p->plaintext), 'per hour') !== false ||strpos(strtolower($p->plaintext), 'per annum') !== false ||strpos(strtolower($p->plaintext), 'salary') !== false ||strpos(strtolower($p->plaintext), 'p/h') !== false ||strpos(strtolower($p->plaintext), 'relocation') !== false) {
            $salary= $p->plaintext;
        }

        // Get the name of the care home
        if (preg_match('/Nursing Home|Home Nursing|Care Home|Home Care|Nursing House|House Nursing|Care House|Court|Lodge|Home$|House$|Home,|House,|Residential|Centre|Meadows|Place|Road|Street|Gardens|Lane|Avenue|Foyer/',$p->plaintext)&&(strlen(trim($p->plaintext))<110)&& $first) {
            $carehome_name= $p->plaintext;
            $first=false;
        } 

    
        if(strlen(trim($p->plaintext))>200) {
            $descriptions[] = $p->plaintext;
        }
    }

    $description = join($descriptions);

    
    



    

    echo $description ;
    echo '<br>';
    echo $title;
    echo '<br>';
    echo $carehome_name;
    echo '<br>';
    echo $location;
    echo '<br>';
    echo $department;
    echo '<br>';
    echo $operation;
    echo '<br>';
    echo $requisition_number;
    echo '<br>';  
    echo $salary;    
    echo '<br>';
    echo $closing_date;
    echo '<br>';
   
    echo '====================================================='; 
    echo '<br>';

}

scrape_single_page($all_ads_links[1]);

// foreach($all_ads_links as $ad_link){
//     scrape_single_page($ad_link);
// }




//  scrape_single_page($all_ads_links[0]);


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




