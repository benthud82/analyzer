<?php
include_once '../globalincludes/connection.php';
$var_nflid = ($_POST['nfl_id']);

//select relevant datapoints for modal
$sql = "SELECT DISTINCT
                nfl_id, nfl_homeabb, nfl_awayabb
            FROM
                betanalyzer.nfl
            WHERE
               nfl_id = $var_nflid";
$query = $conn1->prepare($sql);
$query->execute();
$array_modal = $query->fetchAll(pdo::FETCH_ASSOC);

//modal variables
$nfl_id = (isset($array_modal[0]['nfllines_id'])) ? $array_modal[0]['nfllines_id'] : ' ';
$nfllines_spread = (isset($array_modal[0]['nfllines_spread'])) ? $array_modal[0]['nfllines_spread'] : ' ';
$team_home = (isset($array_modal[0]['nfl_homeabb'])) ? $array_modal[0]['nfl_homeabb'] : ' ';
$team_away = (isset($array_modal[0]['nfl_awayabb'])) ? $array_modal[0]['nfl_awayabb'] : ' ';
?>



<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title pull-left">Enter/Modify Line</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>

        </div>

        <div class="modal-body" id="" style="margin: 50px;">
            <div class="card">
                <div class="card-body card-block">
                    <div class="form-group" style="display: none">
                        <label for="nfl_id" class=" form-control-label">NFL ID</label>
                        <input type="text" id="nfl_id" class="form-control" value="<?php echo $nfl_id ?>">
                    </div>
                    <div class="form-group ">
                        <label for="nfl_type" class=" form-control-label">Team Favored</label>
                        <select type="text" id="nfl_for" class="form-control" value="">
                            <option value="<?php echo $team_home ?>"><?php echo $team_home ?></option>
                            <option value="<?php echo $team_away ?>"><?php echo $team_away ?></option>
                        </select>
                    </div>

                    <div class="form-group ">
                        <label for="nfl_spread" class=" form-control-label">Spread</label>
                        <input type="text" id="nfl_spread" class="form-control" value="">
                    </div>
                    <div class="form-group ">
                        <label for="nfl_amt" class=" form-control-label">Bet Amount</label>
                        <input type="text" id="nfl_amt" class="form-control" value="">
                    </div>
                    <div class="form-group ">
                        <label for="nfl_winamt" class=" form-control-label">Net Win Amount</label>
                        <input type="text" id="nfl_winamt" class="form-control" value="">
                    </div>

                </div>
            </div>

        </div>
        <div class="modal-footer" style="justify-content: flex-start;">
            <div class="text-center">
                <button type="submit" class="btn btn-info btn-lg" name="btn_addbet" id="btn_addbet">Add Wager</button>
            </div>
        </div>

    </div>
</div>

