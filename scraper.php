<?php

// Web scraping library
include('simple_html_dom.php');

// Get the page for scraping
$html = file_get_html('https://jobs.sanctuary-group.co.uk/search/');

$ads_number = count($html->find('span.jobTitle.hidden-phone'));

// Get total number of results
$total_results = $html->find('span.paginationLabel',0)->find('b',1)->plaintext;;
$total_results = (int) $total_results;

// Create links to results subpages
$result_pages = [];

for($i=0; $i<$total_results;$i+=$ads_number){
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

    $ps = $job_description_full->find('p');
    $uls = $job_description_full->find('ul');
    $description_elms = array_merge($ps,$uls);
    $first = true;
    $paragraphs = [];
    foreach($description_elms as  $value){
        // Convert ul and li tag to paragraphs
        foreach($value->find('li') as $li){
            $li->tag= 'span';
        }

        if($value->tag == "ul"){
            $value->tag = "span";
        }

         
        if($value->tag == "p"){
        // Find closing date
        if (strpos($value->plaintext, 'Closing Date') !== false ||strpos($value->plaintext, 'Closing date') !== false) {
            // Remove "Closing date:" from the string
            $text = substr($value->plaintext, 0, 13);
            $date = str_replace($text,'',$value->plaintext);
            $closing_date= $date;
        }

        // // Find paragraph with salary
        if (strpos(strtolower($value->plaintext), 'per hour') !== false ||strpos(strtolower($value->plaintext), 'per annum') !== false ||strpos(strtolower($value->plaintext), 'salary') !== false ||strpos(strtolower($value->plaintext), 'p/h') !== false ||strpos(strtolower($value->plaintext), 'relocation') !== false) {
            $salary= $value->plaintext;
        }
    }

        // Remove '&nbsp;' from the text in the page
        $value = str_replace('&nbsp;', '', $value->plaintext);
        $paragraphs[] = trim($value);
   
    }

    // Remove empty paragraphs
    $paragraphs = array_values(array_filter($paragraphs));

    for($i=0;$i<count($paragraphs);$i++){
        if(strlen($paragraphs[$i]) <=2  ){
            array_splice($paragraphs, $i, 1);
        }
     }

    //Get the name of the care home
    if($operation == 'Sanctuary Care'&&$title=="Activities Coordinator"){
        $carehome_name = $paragraphs[1];
        // $salary = $paragraphs[2];
    }elseif($operation == 'Sanctuary Care'&&$title=="Senior Nurse"){
        $carehome_name = $paragraphs[5];
        // $salary = $paragraphs[2];
    }elseif($operation == 'Sanctuary Care'||$operation == 'Sanctuary Maintenance'){
        $carehome_name = $paragraphs[2];
        // $salary = $paragraphs[3];
    }elseif($operation == 'Sanctuary Supported Living' || $operation == 'Sanctuary Retirement Living' ){
        $carehome_name = $paragraphs[1];
        // $salary = $paragraphs[2];
    }

    //  Find description paragraphs and exclude information with distinctive valuer
    $description_paragraphs =[];
    foreach($paragraphs as $paragraph){
    if(trim($paragraph) !== trim($title) && trim($paragraph) !== trim($carehome_name) && trim($paragraph) !== trim($location) && trim($paragraph) !== trim($department)&& trim($paragraph) !== trim($operation)&& trim($paragraph) !== trim($requisition_number)&& trim($paragraph) !== trim($salary)&& strpos(strtolower(trim($paragraph)), strtolower(trim($closing_date)))=== false ){
        $description_paragraphs[] = $paragraph;
    }
    } 
   

    // // Join all the paragraphs of the description
    $description = join("\n",$description_paragraphs);

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


//    // Write data to the csv file
    // $file = fopen('records.csv', "a");
    $file = fopen('records.csv', "a");   
    fputcsv($file,$records);

 }

// Put headers into the csv file
 $headers =array('Title','Carehome name','Location','Department','Operation', 'Requisition number','Salary','Closing Date','Description');
 $file = fopen('records.csv', "a");   
 fputcsv($file,$headers);
 

// Execute the scraping function on all the ads
foreach($all_ads_links as $ad_link){
    scrape_single_page($ad_link);
}
fclose($file);
?> 




