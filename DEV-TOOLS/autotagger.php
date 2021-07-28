<?PHP
/*
CREDITS
 CHRISTOPHER M. CHURCH, PHD
 UNIVERSITY OF NEVADA
 LSTA GRANT, 2021
 NORTHERN NEVADA NEON PROJECT

DESCRIPTION
SCRIPT TO PULL OUT KEY WORDS AND THEN POTENTIALLY GENERATE AUTOTAGS
 note: LOOK INTO http://php-nlp-tools.com/

/*notes:
It appears that the brevity of the descriptions makes it so that doing word frequency analysis wouldn't really work.

Using tf-idf produces moderately useful results.

*/

#-------------------------------------------------MAIN-------------------------------------------------

#INITS
$UNLV_metadata = fetchCSV(__DIR__ . "/../METADATA-MERGE/OUTPUT/import.csv",'did');
$stopwords = getStopwords(__DIR__ . "/stopwords.txt");
$freq_list = [];
$totalTokens=[];
$documentFreq=[];
$pattern = "/[^a-z\s]/";

#tokenize and count tokens for each node, filtering out stopwords
foreach ($UNLV_metadata as $row) {
  $id = $row['did'];
  $id_prefix = substr($id,0,3);
  #if ($id_prefix!='NNN') {continue;}       #specify the collection if we want
  $title = $row['title'];
  $body = $row['unr-desc'];
  $string = $title ." ". $body;
  $string = strtolower(strip_tags(str_replace('<', ' <',$string)));
  $string = preg_replace($pattern," ",$string);
  $string = preg_replace("/\s+/"," ",$string);
  $tokens = explode(" ",$string);

#count the document frequency -- could use $countedTokens[$id] = array_count_values($tokens); - but that doesn't do stopwords
  foreach ($tokens as $token) {
    if (in_array($token, $stopwords)) {continue;}
    if (isset($countedTokens[$id][$token])) {$countedTokens[$id][$token]++;} else {$countedTokens[$id][$token]=1;}
    array_push($totalTokens,$token);
  }
}

#get the total frequencies over the entire data set
$freq_list = array_count_values($totalTokens);
$term_keys = array_keys($freq_list);


#build document frequency for each term
foreach ($term_keys as $term) {
  foreach ($countedTokens as $countedToken) {
    if (in_array($term, array_keys($countedToken))) {
      if (isset($documentFreq[$term])) {$documentFreq[$term]++;} else {$documentFreq[$term]=1;}
    }
  }
}

#now build tf-idf for the tags (see https://en.wikipedia.org/wiki/Tf%E2%80%93idf)
foreach ($countedTokens as $countedToken)
{
  foreach ($countedTokens as $id=>$tokens)
  {
    foreach ($tokens as $term=>$value)
    {
      $tf_idf[$id][$term] = $value / log($documentFreq[$term]+1);
    }
  }
}

#see the top 5 terms for each document
foreach ($tf_idf as $id=>$items)
{
  print "\n\n" . $id . "\n";
  arsort($items);
  $count=0;
  foreach ($items as $term=>$value)
  {
    print $term ." ". $value ."\n";

    #add to an array so we can see what the aggregate top terms were
    if(isset($finalTermList[$term])) {
      $finalTermList[$term]++;
    }
    else {
      $finalTermList[$term]=1;
    }

    #stop once we hit the top number of terms
    $count++;
    if ($count>=5) {break;}
  }
}

#sort the document frequency and raw frequncy lists
asort($freq_list); #sort in ascending order
asort($documentFreq);

#see what our aggregate top terms were
arsort($finalTermList); #sort it in descending order

makeCSV(__DIR__."/top-terms-by-tf-idf.csv",$finalTermList);

#-------------------------------------------------FUNCTIONS-------------------------------------------------
function fetchCSV($input_path,$_UID_KEY) {
/*This function fetches as CSV at the $input_path, stores all the rows in an associative array with the key provided as an argument pulled from each entry*/
  $input_data = fopen($input_path,"r");
  $first_row = fgetcsv($input_data,0,$separator = "|");
  $csvKeys=[];
  $csvLines=[];

  $keys = array_values($first_row);
    foreach ($keys as $index=>$key) {
      if ($key==NULL) { $key = "BLANK".$index;}
      $key = preg_replace("/[^A-Za-z0-9]-/", '', $key);
      $key = substr($key, 0, 20);
      array_push($csvKeys, $key);
    }

  #print_r($csvKeys);

  while (($row = fgetcsv($input_data,0,$separator = "|")) != FALSE) {
      $csvLine=[];
      foreach ($row as $index=>$r) {
          #if ($csvKeys[$index]=='lit-unlit') {$r = preg_replace("/,\s+/", ',', $r);} #NOTE: moving to TAMPER MODULE, check to see if it is one of the multifields, and if so, remove the space after comma -> maybe use TAMPER module after this script instead
          $csvLine[$csvKeys[$index]]=$r;    #here is the actual value for each field in CSV
      }
      $csvLines[$csvLine[$_UID_KEY]]=$csvLine;
  }
  fclose($input_data);
  return$csvLines;
}

function getStopwords($input_path) {
  $file = fopen($input_path,"r");
  $text = fread($file,filesize($input_path));
  return explode("\n",$text);
}

function makeCSV($_output_path,$finalNodeArray) {
#This function exports the provided array as a CSV to the output path
  $output = fopen($_output_path, "w");  #open an a file to output as csv
  $header_keys = ["term","occurences"];
  fputcsv($output,$header_keys,'|'); #output headers to first line of CSV file
  foreach ($finalNodeArray as $term=>$value) {
    fputcsv($output,[$term,$value],'|'); #output to file
  }
  fclose($output); #close the output file
  print "tf-idf exported to $_output_path\n";
}
#----------------------------------------END FUNCTIONS------------------------------------------

?>
