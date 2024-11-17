<?php
include_once '../globalincludes/connection.php';
include_once '../logic/functions.php';

$var_week = $_POST['nfl_week'];
$net_winloss = 0;
$gamelength = 60;
$sql_nflgames = $conn1->prepare("SELECT 
    nfl_id,
    quarter,
    time_remaining_in_game,
    home_team_short,
    home_score,
    away_team_short,
    away_score,
    quarter,
    CASE
        WHEN openaway < 0 THEN away_team
        WHEN openhome < 0 THEN home_team
        ELSE 'Even'
    END as nfllines_fav,
    CASE
        WHEN openaway < 0 THEN home_team
        WHEN openhome < 0 THEN away_team
        ELSE 'Even'
    END as nfllines_underdog,
    CASE
        WHEN currentaway LIKE '%-%' THEN currentaway
        WHEN currenthome LIKE '%-%' THEN currenthome
        ELSE 'PK'
    END AS nfllines_spread,
    (cashhome + cashaway) / 2 AS nfllines_ou,
    CAST(((cashhome + cashaway) / 2 + (openaway - openhome) / 2) AS DECIMAL (5 , 1 )) AS score_favorite,
    CAST(((cashhome + cashaway) / 2 - (openaway - openhome) / 2) AS DECIMAL (5 , 1 )) AS score_underdog
FROM
    betanalyzer.nfl_scores AS ns
         JOIN
    lineswing.team_alias AS ta1 ON ns.home_team = ta1.team_name
         JOIN
    lineswing.team_alias AS ta2 ON ns.away_team = ta2.team_name
         JOIN
    lineswing.nfl_lines AS nl ON ta1.team_alias = nl.teamhome
        AND ta2.team_alias = nl.teamaway
ORDER BY 
    CASE 
        WHEN time_remaining_in_game > 0 THEN 0 
        ELSE 1 
    END,
    time_remaining_in_game ASC,
    game_date ASC,
    game_time ASC
");
$sql_nflgames->execute();
$array_nflgames = $sql_nflgames->fetchAll(pdo::FETCH_ASSOC);

foreach ($array_nflgames as $key => $value) {
    $gameid = $array_nflgames[$key]['nfl_id'];  //unique game ID
    $nfl_quarter = $array_nflgames[$key]['quarter']; //current quarter of game
    $nfl_timerem = $array_nflgames[$key]['time_remaining_in_game']; //time remaining in quarter
    $nfl_hs = intval($array_nflgames[$key]['home_score']);  //actual home team score
    $nfl_vs = intval($array_nflgames[$key]['away_score']); //actual visitor team score
    $nfllines_fav = $array_nflgames[$key]['nfllines_fav']; //abbreviation of favored team
    $nfllines_underdog = $array_nflgames[$key]['nfllines_underdog'];  //abbreviation of underdog team
    $nfllines_spread = $array_nflgames[$key]['nfllines_spread'];  //score spread of game
    $nfl_homeabb = $array_nflgames[$key]['home_team_short'];  //home team
    $nfl_awayabb = $array_nflgames[$key]['away_team_short'];  //away team
    $score_favorite = $array_nflgames[$key]['score_favorite'];  //spread projected score favorite
    $score_underdog = $array_nflgames[$key]['score_underdog'];  //spread projected score underdog
    $nfllines_ou = round($array_nflgames[$key]['nfllines_ou'],0);  //spread over under
    //projected score
    $proj_array = projectFinalScore($nfl_timerem, $nfl_hs, $nfl_vs, $nfllines_fav, $nfllines_ou, $nfllines_spread, $nfl_homeabb); 
//    $proj_array = _nfl_projscore($nfl_homeabb, $nfl_hs, $nfl_awayabb, $nfl_vs, $nfllines_fav, $nfllines_underdog, $nfllines_spread, $nfllines_ou, $score_favorite, $score_underdog, $nfl_quarter, $nfl_timerem, $gamelength);
    $proj_score_home = $proj_array[0];
    $proj_score_away = $proj_array[1];
    ?>




    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <strong class="card-title">Game: <?php echo $nfl_awayabb . ' at ' . $nfl_homeabb ?></strong>
            </div>
            <div class="card-body">
                <p>Team Favored: <?php echo $nfllines_fav ?></p>
                <p>Current LIne: <?php echo $nfllines_spread ?></p>
                <p>Current Over Under: <?php echo number_format($nfllines_ou, 1) ?></p>
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="card">
                            <div class="p-0 clearfix">
                                <i class="fa fa-cogs bg-primary p-4 font-2xl mr-3 float-left text-light"></i>
                                <div class="h5 text-primary mb-0 pt-3"><?php echo $proj_score_home ?></div>
                                <div class="text-muted text-uppercase font-weight-bold font-xs small"><?php echo $nfl_homeabb ?></div>
                            </div>  
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card">
                            <div class="p-0 clearfix">
                                <i class="fa fa-cogs bg-primary p-4 font-2xl mr-3 float-left text-light"></i>
                                <div class="h5 text-primary mb-0 pt-3"><?php echo $proj_score_away ?></div>
                                <div class="text-muted text-uppercase font-weight-bold font-xs small"><?php echo $nfl_awayabb ?></div>
                            </div>
                        </div>
                    </div>
                </div>



            </div>
        </div>
    </div>








    <?php
}

//$output = array(
//    "aaData" => array()
//);
//$row = array();
//
//
//foreach ($array_nflgames as $key => $value) {
//    $row[] = array_values($array_nflgames[$key]);
//}
//
//$output['aaData'] = $row;
//echo json_encode($output);