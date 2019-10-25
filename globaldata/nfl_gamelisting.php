<?php
include_once '../globalincludes/connection.php';

$var_week = $_POST['nfl_week'];


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
                            ORDER BY nfl_date, nfl_gametime ");
$sql_nflgames->execute();
$array_nflgames = $sql_nflgames->fetchAll(pdo::FETCH_ASSOC);

foreach ($array_nflgames as $key => $value) {
    $gameid = $array_nflgames[$key]['nfl_id'];
    nfl_quarter = $array_nflgames[$key]['nfl_quarter'];
    nfl_timerem = $array_nflgames[$key]['nfl_timerem'];
    nfl_hs = $array_nflgames[$key]['nfl_hs'];
    nfl_vs = $array_nflgames[$key]['nfl_vs'];
    $gameid = $array_nflgames[$key]['nfl_id'];
    $gameid = $array_nflgames[$key]['nfl_id'];
    $gameid = $array_nflgames[$key]['nfl_id'];
    ?>
    <!--Display games for current week-->
    <!--<div class="clearfix visible-sm visible-lg"></div>-->

    <div class="card" id="<?php echo $array_nflgames[$key]['nfl_id'] ?>"  style="height: 500px;">
        <div class="card-header"><?php echo 'Quarter: ' . $array_nflgames[$key]['nfl_quarter'] . ' | ' . $array_nflgames[$key]['nfl_timerem'] ?></div>
        <div class="alert-secondary">
            <div class="media">

                <div class="media-body">
                    <div class="row" style="margin-left: 10px;">
                        <div class="col-lg-6 h3"><?php echo $array_nflgames[$key]['nfl_awayabb'] ?></div><div class="h3 col-lg-3"style="margin-left: 40px;"><?php echo $array_nflgames[$key]['nfl_vs'] ?></div>
                    </div>
                    <div class="row" style="margin-left: 10px;">
                        <div class="col-lg-6 h3"><?php echo $array_nflgames[$key]['nfl_homeabb'] ?></div><div class="h3 col-lg-3"style="margin-left: 40px;"><?php echo $array_nflgames[$key]['nfl_hs'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        <!--List any bets placed-->
        <?php
        $sql_nflbets = $conn1->prepare("SELECT 
                                                nflbet_type,
                                                nflbet_for,
                                                nflbet_spread,
                                                nflbet_amount,
                                                nflbet_win,
                                                nflbet_win - nflbet_amount as netwin
                                            FROM
                                                betanalyzer.nfl_bets
                                            WHERE
                                                nflbet_id = $gameid");
        $sql_nflbets->execute();
        $array_nflbets = $sql_nflbets->fetchAll(pdo::FETCH_ASSOC);

        if (!empty($array_nflbets)) {
            foreach ($array_nflbets as $betkey => $value) {
                ?>
                <div class="weather-category twt-category">
                    <ul>
                        <li class="active">
                            <h5><?php echo $array_nflbets[$betkey]['nflbet_type']; ?></h5>
                            Type
                        </li>
                        <li>
                            <h5><?php echo $array_nflbets[$betkey]['nflbet_for']; ?></h5>
                            For
                        </li>
                        <li>
                            <h5><?php echo $array_nflbets[$betkey]['nflbet_spread']; ?></h5>
                            Spread
                        </li>
                        <li>
                            <h5><?php echo '$' . $array_nflbets[$betkey]['netwin']; ?></h5>
                            Net Win
                        </li>
                    </ul>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="col-lg-12">NO BETS</div>
        <?php }
        ?>


            <?php
            //projected score
            $projscore_array = _nfl_projscore($nfl_quarter, $nfl_timerem, $nfl_awayabb, $nfllines_fav,$score_favorite, $score_underdog, $nfl_vs,$nfl_hs);
            ?>

        <div class="alert-secondary">
            <div class="media">

                <div class="media-body">
                    <div class="row" style="margin-left: 10px;">
                        <div class="col-lg-6 h3"><?php echo $array_nflgames[$key]['nfl_awayabb'] ?></div><div class="h3 col-lg-3"style="margin-left: 40px;"><?php echo $array_nflgames[$key]['nfl_vs'] ?></div>
                    </div>
                    <div class="row" style="margin-left: 10px;">
                        <div class="col-lg-6 h3"><?php echo $array_nflgames[$key]['nfl_homeabb'] ?></div><div class="h3 col-lg-3"style="margin-left: 40px;"><?php echo $array_nflgames[$key]['nfl_hs'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="twt-footer">
            <a href="#"><i class="fa fa-camera"></i></a>
            <a href="#"><i class="fa fa-map-marker"></i></a>
            New Castle, UK
            <span class="pull-right">
                32
            </span>
        </footer>
    </div>




    <?php
}
