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
    openaway,
    openhome,
    currentaway,
    currenthome,
    cashhome,
    cashaway,
    ticketshome,
    ticketsaway,
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
WHERE update_time >= DATE_SUB(CURDATE(), INTERVAL 3 DAY)
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
    $openaway = $array_nflgames[$key]['openaway'];
    $openhome = $array_nflgames[$key]['openhome'];
    $currentaway = $array_nflgames[$key]['currentaway'];
    $currenthome = $array_nflgames[$key]['currenthome'];
    $cashhome = $array_nflgames[$key]['cashhome'];
    $cashaway = $array_nflgames[$key]['cashaway'];
    $ticketshome = $array_nflgames[$key]['ticketshome'];
    $ticketsaway = $array_nflgames[$key]['ticketsaway'];
    //projected score
    $proj_array = projectFinalScore($nfl_timerem, $nfl_hs, $nfl_vs, $nfllines_fav, $nfllines_ou, $nfllines_spread, $nfl_homeabb); 
//    $proj_array = _nfl_projscore($nfl_homeabb, $nfl_hs, $nfl_awayabb, $nfl_vs, $nfllines_fav, $nfllines_underdog, $nfllines_spread, $nfllines_ou, $score_favorite, $score_underdog, $nfl_quarter, $nfl_timerem, $gamelength);
    $proj_score_home = $proj_array[0];
    $proj_score_away = $proj_array[1];

    // Determine arrow direction and color for current lines
    $away_arrow = (floatval($currentaway) > floatval($openaway)) ? 'fa-arrow-up text-success' : 'fa-arrow-down text-danger';
    $home_arrow = (floatval($currenthome) > floatval($openhome)) ? 'fa-arrow-up text-success' : 'fa-arrow-down text-danger';
    ?>

    <div class="col-md-6">
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-dark text-white">
                <strong class="card-title">Game: <?php echo $nfl_awayabb . ' at ' . $nfl_homeabb ?></strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p><i class="fa fa-star text-warning"></i> <strong>Favored:</strong> <?php echo $nfllines_fav ?></p>
                        <p><i class="fa fa-arrows-alt-h text-info"></i> <strong>Line:</strong> <?php echo $nfllines_spread ?></p>
                    </div>
                    <div class="col-6">
                        <p><i class="fa fa-money-bill-wave text-primary"></i> <strong>Cash Home:</strong> <?php echo $cashhome ?></p>
                        <p><i class="fa fa-money-bill-wave text-primary"></i> <strong>Cash Away:</strong> <?php echo $cashaway ?></p>
                        <p><i class="fa fa-ticket-alt text-secondary"></i> <strong>Tickets Home:</strong> <?php echo $ticketshome ?></p>
                        <p><i class="fa fa-ticket-alt text-secondary"></i> <strong>Tickets Away:</strong> <?php echo $ticketsaway ?></p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <p><strong>Open Away:</strong> <?php echo $openaway ?></p>
                        <p><strong>Open Home:</strong> <?php echo $openhome ?></p>
                    </div>
                    <div class="col-6">
                        <p><i class="fa <?php echo $away_arrow; ?>"></i> <strong>Current Away:</strong> <?php echo $currentaway ?></p>
                        <p><i class="fa <?php echo $home_arrow; ?>"></i> <strong>Current Home:</strong> <?php echo $currenthome ?></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="card text-center">
                            <div class="p-0 clearfix">
                                <i class="fa fa-cogs bg-primary p-4 font-2xl mr-3 float-left text-light"></i>
                                <div class="h5 text-primary mb-0 pt-3"><?php echo $proj_score_home ?></div>
                                <div class="text-muted text-uppercase font-weight-bold font-xs small"><?php echo $nfl_homeabb ?></div>
                            </div>  
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card text-center">
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