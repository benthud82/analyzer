<?php
include_once '../globalincludes/connection.php';
include_once '../logic/functions.php';


$sql_bets = $conn1->prepare("SELECT 
                                idbets,
                                bookie,
                                bet_type,
                                betamount,
                                betdatetime,
                                pending,
                                sport,
                                competition,
                                odds,
                                evendatetime,
                                potentialpayout,
                                bet_status,
                                selection,
                                line
                            FROM
                                betanalyzer.bets;");
$sql_bets->execute();
$array_bets = $sql_bets->fetchAll(pdo::FETCH_ASSOC);

$output = array(
    "aaData" => array()
);
$row = array();


foreach ($array_bets as $key => $value) {
    $row[] = array_values($array_bets[$key]);
}

$output['aaData'] = $row;
echo json_encode($output);
