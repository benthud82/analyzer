<?php

include_once '../globalincludes/connection.php';
ini_set('display_errors', 1);
error_reporting(-1);
$gamelength = 60;
$profit_total = 0;
//pull in live games
$sql_livenfl = $conn1->prepare("SELECT 
    t1.nfl_id,
    t1.nfl_quarter,
    t1.nfl_timerem,
    t1.nfl_homeabb,
    t1.nfl_hs,
    t1.nfl_awayabb,
    t1.nfl_vs,
    t1.nfl_p,
    t1.nfl_rz,
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
        JOIN
    betanalyzer.nfl_lines t3 ON t1.nfl_id = t3.nfllines_id
WHERE
    t1.nfl_update_datetime = (SELECT 
            MAX(t2.nfl_update_datetime)
        FROM
            betanalyzer.nfl t2
        WHERE
            t1.nfl_id = t2.nfl_id)
        AND nfl_quarter <> 'F'");
$sql_livenfl->execute();
$array_livenfl = $sql_livenfl->fetchAll(pdo::FETCH_ASSOC);


//loop through each live game
foreach ($array_livenfl as $key => $value) {
    $nfl_homeabb = $array_livenfl[$key]['nfl_homeabb'];
    $nfl_hs = $array_livenfl[$key]['nfl_hs'];
    $nfl_awayabb = $array_livenfl[$key]['nfl_awayabb'];
    $nfl_vs = $array_livenfl[$key]['nfl_vs'];
    $nfllines_fav = $array_livenfl[$key]['nfllines_fav'];
    $nfllines_underdog = $array_livenfl[$key]['nfllines_underdog'];
    $nfllines_spread = $array_livenfl[$key]['nfllines_spread'];
    $nfllines_ou = $array_livenfl[$key]['nfllines_ou'];
    $score_favorite = $array_livenfl[$key]['score_favorite'];
    $score_underdog = $array_livenfl[$key]['score_underdog'];
    $nfl_quarter = $array_livenfl[$key]['nfl_quarter'];
    $nfl_timerem = $array_livenfl[$key]['nfl_timerem'];
    $nfl_id = $array_livenfl[$key]['nfl_id'];




    if ($nfl_quarter == 'P') {
        $nfl_quarter = 0;
        $game_time_rem = $gamelength;
        $game_time_elapsed = 0;
    } elseif ($nfl_quarter == 'H') {
        $nfl_quarter = 2;
        $game_time_rem = 30;
        $game_time_elapsed = 30;
    }
    $game_time_rem = ((4 - $nfl_quarter) * 15) + intval($nfl_timerem);
    $game_time_elapsed = $gamelength - $game_time_rem;
    $percent_as_spread = $game_time_rem / $gamelength;
    $percent_as_score = $game_time_elapsed / $gamelength;

    //projected live score
    if ($nfl_awayabb == $nfllines_fav) {
        $projscore_live_favorite = intval(($percent_as_spread * $score_favorite) + $nfl_vs);
        $projscore_live_underdog = intval(($percent_as_spread * $score_underdog) + $nfl_hs);
    } else {
        $projscore_live_favorite = intval(($percent_as_spread * $score_favorite) + $nfl_hs);
        $projscore_live_underdog = intval(($percent_as_spread * $score_underdog) + $nfl_vs);
    }

    //Projected Spread Cover
    $favorite_scoredif = $projscore_live_favorite - $projscore_live_underdog;
    if ($favorite_scoredif > $nfllines_spread) {
        $projected_spread_text = 'The favorite ' . $nfllines_fav . ' is expected to cover.';
        $projected_cover_team = $nfllines_fav;
        $projected_spread = ($projscore_live_favorite - $projscore_live_underdog) - $nfllines_spread;
    } elseif ($favorite_scoredif < $nfllines_spread) {
        $projected_spread_text = 'The underdog ' . $nfllines_underdog . ' is expected to cover.';
        $projected_cover_team = $nfllines_underdog;
        $projected_spread = ($projscore_live_favorite - $projscore_live_underdog) - $nfllines_spread;
    } else {
        $projected_spread_text = 'Tie is expected.';
        $projected_cover_team = 'TIE';
        $projected_spread = ($projscore_live_favorite - $projscore_live_underdog) - $nfllines_spread;
    }
    $projsocre_live_total = $projscore_live_favorite + $projscore_live_underdog;

    //projected over or under
    if ($projsocre_live_total > $nfllines_ou) {
        $proj_over_under = 'OVER';
    } elseif ($projsocre_live_total < $nfllines_ou) {
        $proj_over_under = 'UNDER';
    } else {
        $proj_over_under = 'TIE';
    }




    echo'Projected score for favorite ' . $nfllines_fav . ' is ' . $projscore_live_favorite;
    echo '<br>';
    echo'Projected score for underdog ' . $nfllines_underdog . ' is ' . $projscore_live_underdog;
    echo '<br>';
    echo'Projected total score is ' . $projsocre_live_total;
    echo '<br>';
    echo '<br>';
    echo 'Game Minutes Remaining: ' . $game_time_rem;
    echo '<br>';
    echo '<br>';
    echo $projected_spread_text;
    echo '<br>';
    echo $nfllines_fav . ' should cover by ' . $projected_spread . ' points.';
    echo '<br>';
    echo '<br>';
    //is a bet(s) placed?
    $sql_nflbets = $conn1->prepare("SELECT 
                                        nflbet_placed_date,
                                        nflbet_type,
                                        nflbet_for,
                                        nflbet_spread,
                                        nflbet_amount,
                                        nflbet_win
                                    FROM
                                        betanalyzer.nfl_bets
                                    WHERE
                                        nflbet_id = $nfl_id");
    $sql_nflbets->execute();
    $array_nflbets = $sql_nflbets->fetchAll(pdo::FETCH_ASSOC);
    echo 'BETS:';
    echo '<br>';
    echo '<br>';
    foreach ($array_nflbets as $betkey => $value) {
        $nflbet_placed_date = $array_nflbets[$betkey]['nflbet_placed_date'];
        $nflbet_type = $array_nflbets[$betkey]['nflbet_type'];
        $nflbet_for = trim($array_nflbets[$betkey]['nflbet_for']);
        $nflbet_spread = $array_nflbets[$betkey]['nflbet_spread'];
        $nflbet_amount = $array_nflbets[$betkey]['nflbet_amount'];
        $nflbet_win = $array_nflbets[$betkey]['nflbet_win'];

        if ($nflbet_type == 'SPREAD') {
            if ($nflbet_for == $projected_cover_team) {
                $proj_betresult = 'WIN';
                $proj_betreturn = $nflbet_win - $nflbet_amount;
            } elseif ($nflbet_for !== $projected_cover_team) {
                $proj_betresult = 'LOSE';
                $proj_betreturn = -$nflbet_amount;
            } else {
                $proj_betresult = 'TIE';
                $proj_betreturn = 0;
            }
        } elseif ($nflbet_type == 'O/U') {
            if ($nflbet_for == $proj_over_under) {
                $proj_betresult = 'WIN';
                $proj_betreturn = $nflbet_win - $nflbet_amount;
            } elseif ($nflbet_for !== $proj_over_under) {
                $proj_betresult = 'LOSE';
                $proj_betreturn = -$nflbet_amount;
            } else {
                $proj_betresult = 'TIE';
                $proj_betreturn = 0;
            }

        }
                    echo 'Bet: ' . $nflbet_type . ' | ' . $nflbet_for . ' | ' . $nflbet_spread . ' | ' . $nflbet_amount . ' | ' . $nflbet_win;
            echo '<br>';
            echo 'Projected to ' . $proj_betresult . ': $' . $proj_betreturn;
            echo '<br>';
            echo '<br>';
        echo '<br>';




        $profit_total += $proj_betreturn;
     
    }
       echo $profit_total;
}

