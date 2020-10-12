<?php

function _nfl_projscore($nfl_homeabb, $nfl_hs, $nfl_awayabb, $nfl_vs, $nfllines_fav, $nfllines_underdog, $nfllines_spread, $nfllines_ou, $score_favorite, $score_underdog, $nfl_quarter, $nfl_timerem, $gamelength) {

    if ($nfl_quarter == 'P') {
        $nfl_quarter = 0;
        $game_time_rem = $gamelength;
        $game_time_elapsed = 0;
    } elseif ($nfl_quarter == 'H') {
        $nfl_quarter = 2;
        $game_time_rem = 30;
        $game_time_elapsed = 30;
    } elseif ($nfl_quarter == 'F' || $nfl_quarter == 'Final') {
        $nfl_quarter = 4;
        $game_time_rem = 0;
        $game_time_elapsed = 60;
    }

    $game_time_rem = ((4 - $nfl_quarter) * 15) + intval($nfl_timerem);
    $game_time_elapsed = $gamelength - $game_time_rem;
    $percent_as_spread = $game_time_rem / $gamelength;
//    $percent_as_score = $game_time_elapsed / $gamelength;
    //projected live score
    if ($nfl_awayabb == $nfllines_fav) {
        $projscore_live_visitor = intval(($percent_as_spread * $score_favorite) + $nfl_vs);
        $projscore_live_home = intval(($percent_as_spread * $score_underdog) + $nfl_hs);
        $proj_array = array($projscore_live_home, $projscore_live_visitor);
    } else {
        $projscore_live_home = intval(($percent_as_spread * $score_favorite) + $nfl_hs);
        $projscore_live_visitor = intval(($percent_as_spread * $score_underdog) + $nfl_vs);
        $proj_array = array($projscore_live_home, $projscore_live_visitor);
    }

    return $proj_array;
}

function _nfl_ou_live_result($nflbet_for, $nflbet_spread, $nflbet_amount, $nflbet_win, $proj_score_home, $proj_score_away) {
    $projsocre_live_total = $proj_score_home + $proj_score_away;
    $netwin = $nflbet_win - $nflbet_amount;
    //projected over or under
    if ($projsocre_live_total > $nflbet_spread) {
        $proj_over_under = 'OVER';
    } elseif ($projsocre_live_total < $nflbet_spread) {
        $proj_over_under = 'UNDER';
    } else {
        $proj_color = ' ';
        $proj_over_under = 'TIE';
        $array_bet_result = array('TIE', '0', $proj_color);
        return $array_bet_result;
    }

    //what was the bet?
    if (trim($nflbet_for) == trim($proj_over_under)) {
        //win!


        $proj_color = 'bg-success';
        $array_bet_result = array('WIN', $netwin, $proj_color);
    } else {
        //loss :(
        $proj_color = 'bg-danger';
        $array_bet_result = array('LOSE', -$nflbet_amount, $proj_color);
    }
    return $array_bet_result;
}

function _nfl_spread_live_result($nflbet_for, $nflbet_spread, $nflbet_amount, $nflbet_win, $proj_score_home, $proj_score_away, $nfl_homeabb, $nfl_awayabb, $nfllines_fav, $nfllines_underdog) {
    $netwin = $nflbet_win - $nflbet_amount;
    //who are you going for favorite or visitor??  1 for favorite
    if (trim($nflbet_for) == trim($nfllines_fav)) {
        $goingfor_fav = 1;
    } else {
        $goingfor_fav = 0;
    }

    //who is the favorite, home or away.  1 for home 0 for visitor
    if (trim($nfl_homeabb) == trim($nfllines_fav)) {
        $home_fav = 1;
        $projscore_live_favorite = $proj_score_home;
        $projscore_live_underdog = $proj_score_away;
    } else {
        $home_fav = 0;
        $projscore_live_favorite = $proj_score_away;
        $projscore_live_underdog = $proj_score_home;
    }



    //Projected Spread Cover    
    $favorite_scoredif = $projscore_live_favorite - $projscore_live_underdog;
    if ($favorite_scoredif > $nflbet_spread) {
        $projected_spread_text = 'The favorite ' . $nfllines_fav . ' is expected to cover.';
        $projected_cover_team = $nfllines_fav;
        $projected_spread = ($projscore_live_favorite - $projscore_live_underdog) - $nflbet_spread;
    } elseif ($favorite_scoredif < $nflbet_spread) {
        $projected_spread_text = 'The underdog ' . $nfllines_underdog . ' is expected to cover.';
        $projected_cover_team = $nfllines_underdog;
        $projected_spread = ($projscore_live_favorite - $projscore_live_underdog) - $nflbet_spread;
    } else {
        $projected_spread_text = 'Tie is expected.';
        $projected_cover_team = 'TIE';
        $projected_spread = ($projscore_live_favorite - $projscore_live_underdog) - $nflbet_spread;
    }



    if ($nflbet_for == $projected_cover_team) {
        $proj_betresult = 'WIN';
        $proj_betreturn = $nflbet_win - $nflbet_amount;
        $proj_color = 'bg-success';
        $array_bet_result = array('WIN', $netwin, $proj_color);
    } elseif ($nflbet_for !== $projected_cover_team) {
        $proj_betresult = 'LOSE';
        $proj_betreturn = -$nflbet_amount;
        $proj_color = 'bg-danger';
        $array_bet_result = array('LOSE', -$nflbet_amount, $proj_color);
    } else {
        $proj_betresult = 'TIE';
        $proj_betreturn = 0;
        $proj_color = ' ';
        $array_bet_result = array('TIE', '0', $proj_color);
    }
    return $array_bet_result;
}
