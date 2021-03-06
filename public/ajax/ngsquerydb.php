<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");

$query = new dbfuncs();

$pDictionary = ['getSelectedSamples', 'submitPipeline', 'submitUpdate', 'getStatus', 'getRerunSamples', 'getRerunJson'];

if (isset($_GET['p'])){$p = $_GET['p'];}
if (isset($_GET['q'])){$q = $_GET['q'];}
if (isset($_GET['r'])){$r = $_GET['r'];}
if (isset($_GET['seg'])){$seg = $_GET['seg'];}
if (isset($_GET['search'])){$search = $_GET['search'];}
if (isset($_GET['start'])){$start = $_GET['start'];}
if (isset($_GET['end'])){$end = $_GET['end'];}

if (isset($_POST['p'])){$p = $_POST['p'];}
if (isset($_POST['q'])){$q = $_POST['q'];}
if (isset($_POST['r'])){$r = $_POST['r'];}
if (isset($_POST['seg'])){$seg = $_POST['seg'];}
if (isset($_POST['search'])){$search = $_POST['search'];}
if (isset($_POST['start'])){$start = $_POST['start'];}
if (isset($_POST['end'])){$end = $_POST['end'];}

//make the q val proper for queries
if($q == "Assay"){ $q = "library_type"; }
else { $q = strtolower($q); }

if($search != "" && !in_array($p, $pDictionary)){
    //Prepare search query
    $searchQuery = "";
    $splt = explode("$", $search);
    foreach ($splt as $s){
        $queryArray = explode("=", $s);
        $spltTable = $queryArray[0];
        $spltValue = $queryArray[1];
        $searchQuery .= "biocore.ngs_samples.$spltTable = \"$spltValue\"";
        if($s != end($splt)){
            $searchQuery .= " AND ";
        }
    }
    //browse (search incnluded)
    if($seg == "browse")
    {    
        if($p == "getLanes")
        {
            $time="";
            if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id,  name, facility, total_reads, total_samples
            FROM biocore.ngs_lanes
            WHERE biocore.ngs_lanes.id
            IN (SELECT biocore.ngs_samples.lane_id FROM biocore.ngs_samples WHERE $searchQuery) $time
            ");
        }
        else if($p == "getSamples")
        {
            $time="";
            if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id, title, source, organism, molecule
            FROM biocore.ngs_samples
            WHERE $searchQuery $time
            ");
        }
        else if($p == "getExperimentSeries")
        {
            $time="";
            if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id, experiment_name, summary, design
            FROM biocore.ngs_experiment_series
            WHERE biocore.ngs_experiment_series.id
            IN (SELECT biocore.ngs_samples.series_id FROM biocore.ngs_samples WHERE $searchQuery) $time
            ");
        }
    }
    else
    {
        //details (search included)
        if($p == "getLanes" && $q != "")
        {
            $time="";
            if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id,  name, facility, total_reads, total_samples
            FROM biocore.ngs_lanes
            WHERE biocore.ngs_lanes.id 
            IN (SELECT biocore.ngs_samples.lane_id FROM biocore.ngs_samples WHERE $searchQuery)
            AND biocore.ngs_lanes.series_id = $q $time
            ");
        }
        else if($p == "getSamples" && $r != "")
        {
            $time="";
            if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id, title, source, organism, molecule
            FROM biocore.ngs_samples
            WHERE $searchQuery
            AND biocore.ngs_samples.lane_id = $r $time
            ");
        }
        else if($p == "getSamples" && $q != "")
        {
            $time="";
            if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id, title, source, organism, molecule
            FROM biocore.ngs_samples
            WHERE $searchQuery
            AND biocore.ngs_samples.series_id = $q $time
            ");
        }
        else if($p == "getExperimentSeries")
        {
            $time="";
            if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id, experiment_name, summary, design
            FROM biocore.ngs_experiment_series
            WHERE biocore.ngs_experiment_series.id
            IN (SELECT biocore.ngs_samples.series_id FROM biocore.ngs_samples WHERE $searchQuery) $time
            ");
        }
    }
}
else if (!in_array($p, $pDictionary))
{
    //browse (no search)
    if($seg == "browse")
    {   
        if($p == "getExperimentSeries")
        {
            $time="";
            if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id, experiment_name, summary, design
            FROM biocore.ngs_experiment_series
            WHERE biocore.ngs_experiment_series.id
            IN (SELECT biocore.ngs_samples.series_id FROM biocore.ngs_samples WHERE ngs_samples.$q = \"$r\") $time
            ");
        }
        else if($p == "getLanes")
        {
            $time="";
            if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id,  name, facility, total_reads, total_samples
            FROM biocore.ngs_lanes
            WHERE biocore.ngs_lanes.id
            IN (SELECT biocore.ngs_samples.lane_id FROM biocore.ngs_samples WHERE biocore.ngs_samples.$q = \"$r\") $time
            ");
        }
        else if($p == "getSamples")
        {
            $time="";
            if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id, title, source, organism, molecule
            FROM biocore.ngs_samples
            WHERE biocore.ngs_samples.$q = \"$r\" $time
            ");
        }
        else if($p == "getProtocols")
        {
            $time="";
            if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id, name, growth, treatment
            FROM biocore.ngs_protocols 
            WHERE biocore.ngs_samples.$q = \"$r\" $time
            ");
        }
    }
    else
    {
        //details (no search)   
        if($p == "getLanes" && $q != "")
        {
            $time="";
            if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id,  name, facility, total_reads, total_samples
            FROM biocore.ngs_lanes
            WHERE biocore.ngs_lanes.series_id = $q $time
            ");
        }
        else if($p == "getSamples" && $r != "")
        {
            $time="";
            if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id, title, source, organism, molecule
            FROM biocore.ngs_samples
            WHERE biocore.ngs_samples.lane_id = $r $time
            ");
        }
        else if($p == "getSamples" && $q != "")
        {
            $time="";
            if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id, title, source, organism, molecule
            FROM biocore.ngs_samples
            WHERE biocore.ngs_samples.series_id = $q $time
            ");
        }
        //index
        else if($p == "getExperimentSeries")
        {
            $time="";
            if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id, experiment_name, summary, design
            FROM biocore.ngs_experiment_series $time
            ");
        }
        else if($p == "getProtocols")
        {
            $time="";
            if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id, name, growth, treatment
            FROM biocore.ngs_protocols $time
            ");
        }
        
        else if($p == "getLanes")
        {
            $time="";
            if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id,  name, facility, total_reads, total_samples
            FROM biocore.ngs_lanes $time
            ");
        }
        else if($p == "getSamples")
        {
            $time="";
            if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
            $data=$query->queryTable("
            SELECT id, title, source, organism, molecule
            FROM biocore.ngs_samples $time
            ");
        }
    }
}
else if ($p == "getSelectedSamples")
{
    
    //Prepare selected search query
    $searchQuery = "";
    $splitIndex = ['id','lane_id'];
    $typeCount = 0;
    if (substr($search, 0, 1) == "$"){
        //only lanes selected
        $search = substr($search, 1, strlen($search));
        $splt = explode(",", $search);
        foreach ($splt as $x){
            $searchQuery .= "biocore.ngs_samples.$splitIndex[1] = $x";
            if($x != end($splt)){
                $searchQuery .= " OR ";
            }
        }
    }
    else if(substr($search, strlen($search) - 1, strlen($search)) == "$"){
        //only samples selected
        $search = substr($search, 0, strlen($search) - 1);
        $splt = explode(",", $search);
        foreach ($splt as $x){
            $searchQuery .= "biocore.ngs_samples.$splitIndex[0] = $x";
            if($x != end($splt)){
                $searchQuery .= " OR ";
            }
        }
    }
    else{
        $splt = explode("$", $search);
        foreach ($splt as $s){
            $secondSplt = explode(",", $s);
            foreach ($secondSplt as $x){
                $searchQuery .= "biocore.ngs_samples.$splitIndex[$typeCount] = $x";
                if($x != end($secondSplt)){
                    $searchQuery .= " OR ";
                }
            }
            if($s != end($splt)){
                    $searchQuery .= " OR ";
            }
            $typeCount = $typeCount + 1;
        }
    }
    $time="";
    if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
    $data=$query->queryTable("
    SELECT id, title, source, organism, molecule
    FROM biocore.ngs_samples
    WHERE $searchQuery $time
    ");
}
else if ($p == "submitPipeline" && $r != 'insertRunlist')
{
    //run_group_id set to -1 as a placeholder.  Cannot grab primary key as it's being made, so a placeholder is needed.
    $data=$query->runSQL("
    INSERT INTO biocore.ngs_runparams (run_group_id, outdir, run_status, barcode, json_parameters, run_name, run_description)
    VALUES (-1, '$r', 0, 0, '$q', '$seg', '$search')");
    //need to grab the id for runlist insertion
    $idKey=$query->queryAVal("SELECT id FROM biocore.ngs_runparams WHERE run_group_id = -1");
    //update required to make run_group_id equal to it's primary key "id".  Replace the arbitrary -1 with the id
    if (isset($_POST['runid'])){$runid = $_POST['runid'];}
    if( $runid == 'new'){
        $data=$query->runSQL("UPDATE biocore.ngs_runparams SET run_group_id = id WHERE run_group_id = -1");
    }else{
        $data=$query->runSQL("UPDATE biocore.ngs_runparams SET run_group_id = $runid WHERE run_group_id = -1");
        $idKey= $idKey - $runid;
    }
    $data=$idKey;
}
else if ($p == 'submitPipeline' && $r == 'insertRunlist')
{
    if (isset($_POST['runid'])){$runid = $_POST['runid'];}
    $searchQuery = "INSERT INTO ngs_runlist
        (run_id, run_group_id, sample_id, owner_id, group_id, perms, date_created, date_modified, last_modified_user)
        VALUES ";
    foreach ($seg as $s){
                $searchQuery .= "($search, $runid, $s, 1, 1, 15, NOW(), NOW(), 1)";
                if($s != end($seg)){
                    $searchQuery .= ",";
                }
            }
    $data=$query->runSQL($searchQuery);
}
else if ($p ==  'getStatus')
{
    $time="";
    if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
    $data=$query->queryTable("
    SELECT id, run_group_id, run_name, outdir, run_description, run_status
    FROM biocore.ngs_runparams
    $time
    ");
}
else if($p == 'getRerunSamples')
{
    $data=$query->queryTable("
    SELECT sample_id
    FROM biocore.ngs_runlist
    WHERE biocore.ngs_runlist.run_group_id = $q AND biocore.ngs_runlist.run_id = $search
    ");
}
else if ($p == 'getRerunJson')
{
    $data=$query->queryTable("
    SELECT outdir, json_parameters, run_name, run_description
    FROM biocore.ngs_runparams
    WHERE biocore.ngs_runparams.run_group_id = $search
    ");
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo $data;
exit;
?>
