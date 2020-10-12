<?php
include_once '../globalincludes/connection.php';
include_once '../logic/functions.php';

$var_week = $_POST['nfl_week'];
$net_winloss = 0;
$gamelength = 60;
$sql_nflgames = $conn1->prepare("SELECT 
    t1.nfl_id,
    t1.nfl_quarter,
    t1.nfl_timerem,
    t1.nfl_homeabb,
    t1.nfl_hs,
    t1.nfl_awayabb,
    t1.nfl_vs,
    t1.nfl_p,
    t3.nfllines_fav,
    t3.nfllines_underdog,
    t3.nfllines_spread,
    t3.nfllines_ou,
    CAST((t3.nfllines_ou / 2) + (nfllines_spread / 2)
        AS DECIMAL (5 , 1 )) AS score_favorite,
    CAST((t3.nfllines_ou / 2) - (nfllines_spread / 2)
        AS DECIMAL (5 , 1 )) AS score_underdog
FROM
    betanalyzer.nfl t1
        LEFT JOIN
    betanalyzer.nfl_lines t3 ON t1.nfl_id = t3.nfllines_id
WHERE
    t1.nfl_update_datetime = (SELECT 
            MAX(t2.nfl_update_datetime)
        FROM
            betanalyzer.nfl t2
        WHERE
            t1.nfl_id = t2.nfl_id)
        AND nfl_week = $var_week
ORDER BY (CASE
    WHEN nfl_quarter = 'F' THEN  1
    WHEN nfl_quarter = 'P' THEN 0
    ELSE nfl_quarter
END) ASC, NFL_ID asc");
$sql_nflgames->execute();
$array_nflgames = $sql_nflgames->fetchAll(pdo::FETCH_ASSOC);

foreach ($array_nflgames as $key => $value) {
    $gameid = $array_nflgames[$key]['nfl_id'];  //unique game ID
    $nfl_quarter = $array_nflgames[$key]['nfl_quarter']; //current quarter of game
    $nfl_timerem = $array_nflgames[$key]['nfl_timerem']; //time remaining in quarter
    $nfl_hs = $array_nflgames[$key]['nfl_hs'];  //actual home team score
    $nfl_vs = $array_nflgames[$key]['nfl_vs']; //actual visitor team score
    $nfllines_fav = $array_nflgames[$key]['nfllines_fav']; //abbreviation of favored team
    $nfllines_underdog = $array_nflgames[$key]['nfllines_underdog'];  //abbreviation of underdog team
    $nfllines_spread = $array_nflgames[$key]['nfllines_spread'];  //score spread of game
    $nfl_homeabb = $array_nflgames[$key]['nfl_homeabb'];  //home team
    $nfl_awayabb = $array_nflgames[$key]['nfl_awayabb'];  //away team
    $score_favorite = $array_nflgames[$key]['score_favorite'];  //spread projected score favorite
    $score_underdog = $array_nflgames[$key]['score_underdog'];  //spread projected score underdog
    $nfllines_ou = $array_nflgames[$key]['nfllines_ou'];  //spread over under
    //projected score
    $proj_array = _nfl_projscore($nfl_homeabb, $nfl_hs, $nfl_awayabb, $nfl_vs, $nfllines_fav, $nfllines_underdog, $nfllines_spread, $nfllines_ou, $score_favorite, $score_underdog, $nfl_quarter, $nfl_timerem, $gamelength);
    $proj_score_home = $proj_array[0];
    $proj_score_away = $proj_array[1];

    
}