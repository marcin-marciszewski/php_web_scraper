<?php

// Web scraping library
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

    // Create an array with all ads
    foreach($job_ad_links as $job_ad_link ){
        $single_link =$job_ad_link->first_child()->href;
        $all_ads_links[] ="https://jobs.sanctuary-group.co.uk$single_link";
    }
}

// Scraping require information from single ad
function scrape_single_page($link) {
    $ad_page = file_get_html($link);
    // Title
    $title = $ad_page->find('span[itemprop="title"]',0)->plaintext;
    $title = trim($title);
    // Operation
    $operation = $ad_page->find('span[itemprop="facility"]',0)->plaintext;
    $operation = trim($operation);
    // Location
    $location = $ad_page->find('span[itemprop="jobLocation"]',0)->plaintext;
    // Requisition number
    $requisition_number = $ad_page->find('span[itemprop="customfield5"]',0)->plaintext;
    // Department
    if($ad_page->find('span[itemprop="department"]',0)) {
        $department = $ad_page->find('span[itemprop="department"]',0)->plaintext;
    } else {
        $department = $ad_page->find('span[itemprop="dept"]',0)->plaintext;
    }

    // Get all paragraph in the description
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

        // Get description 
        if(strlen(trim($p->plaintext))>200) {
            $descriptions[] = $p->plaintext;
        }
    }

    // Join all the paragraphs of the description
    $description = join($descriptions);

    // Create an array with all the data
     $records = array(
        'title' => $title,
        'carehome_name' => $carehome_name,
        'location' => $location,
        'department' => $department,
        'operation' => $operation,
        'requisition_number' => $requisition_number,
        'salary' => $salary,
        'closing_date' => $closing_date,
        'description' => $description 
);

    // Write data to the csv file
    $file = fopen('records.csv', "a");

    fputcsv($file,$records);

    fclose($file);

    echo 'CSV Created';
}


// Execute the scraping function on all the ads
foreach($all_ads_links as $ad_link){
    scrape_single_page($ad_link);
}
?> 




