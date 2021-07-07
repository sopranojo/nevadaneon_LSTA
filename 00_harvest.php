<?php
/*
DESCRIPTION
 1. DOWNLOAD JSON OF DC_OBJECTS FROM UNLV ISLANDORA'S JSON-API TO USE IN LOCAL CACHE AND CMS IMPORT
 2. TRANSFORM JSON OBJECTS INTO CSV FOR CMS IMPORT
 3. APPEND UNR METADATA TO MATERIALS HARVESTED FROM UNLV ISLANDORA COLLECTIONS

CREDITS
 CHRISTOPHER M. CHURCH, PHD
 UNIVERSITY OF NEVADA
 LSTA GRANT, 2021
 NORTHERN NEVADA NEON PROJECT

DATE LAST UPDATED
 07-06-2021*/
include __DIR__ . "/JSON-API/01_jsonapi_all-signs-includes_JSON.php"; #NOTE: NEED TO WORK OUT THE RELATIVE PATHS FOR THE INCLUDES
include __DIR__ . "/JSON-API/02_json-to-csv.php";
include __DIR__ . "/METADATA-MERGE/03_append-local-metadata.php";
 ?>
