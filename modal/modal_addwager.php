<?php $now = date('Y-m-d H:i:s'); ?>
<div id="modal_addwager" class="modal fade " role="dialog"><
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title pull-left">Enter New Wager</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>

            </div>

            <div class="modal-body" id="" style="margin: 50px;">
                <div class="card">
                    <div class="card-body card-block">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group ">
                                    <label for="bookie" class=" form-control-label">Bookie</label>
                                    <select type="text" id="bookie" class="form-control">
                                        <option value="Intertops">Intertops</option>
                                    </select>
                                </div>
                                <div class="form-group ">
                                    <label for="bettype" class=" form-control-label">Type</label>
                                    <select type="text" id="bettype" class="form-control">
                                        <option value="O/U">Over/Under</option>
                                        <option value="SPREAD">Spread</option>
                                    </select>
                                </div>
                                <div class="form-group ">
                                    <label for="amt" class=" form-control-label">Bet Amount</label>
                                    <input id="amt" class="form-control" type="number" min="0.00" max="100000.00" step="0.01" />
                                </div>
                                <div class="form-group ">
                                    <label for="placeddatetime" class=" form-control-label">Wager Placed Date/Time</label>
                                    <input type="datetime-local" id="placeddatetime" class="form-control" value="<?php echo $now;?>">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group ">
                                    <label for="bookie" class=" form-control-label">Bookie</label>
                                    <select type="text" id="bookie" class="form-control">
                                        <option value="Intertops">Intertops</option>
                                    </select>
                                </div>
                                <div class="form-group ">
                                    <label for="nfl_type" class=" form-control-label">Type</label>
                                    <select type="text" id="nfl_type" class="form-control">
                                        <option value="O/U">Over/Under</option>
                                        <option value="SPREAD">Spread</option>
                                    </select>
                                </div>
                                <div class="form-group ">
                                    <label for="nfl_for" class=" form-control-label">Team For</label>
                                    <select type="text" id="nfl_for" class="form-control" value="">

                                        <option value="UNDER">UNDER</option>
                                        <option value="OVER">OVER</option>
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
                </div>

            </div>
            <div class="modal-footer" style="justify-content: flex-start;">
                <div class="text-center">
                    <button type="submit" class="btn btn-info btn-lg" name="btn_addbet" id="btn_addbet">Add Wager</button>
                </div>
            </div>

        </div>
    </div>
</div>

