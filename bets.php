<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <?php include 'headerincludes.php' ?>
    <?php
    include 'verticalnav.php';
    ?>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>

<body>


    <div id="right-panel" class="right-panel">
        <?php include 'horizontalnav.php'; ?>
        <div class="content mt-3">

            <div class="row">
                <div class="col-md-3">
                    <button id="btn_addwager" type="button" class="btn btn-success"><i class="fa fa-plus-circle"></i>&nbsp; Add New Wager</button>
                </div>
            </div>
            <!--Bets datatable-->
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div class="card"> 
                        <div class="card-header">
                            <h5>Bets</h5>
                        </div>
                        <div class="card-body">
                            <div id="container_bets" class="">
                                <table id="dt_bets" class="table table-bordered nowrap compact dt_font" cellspacing="0" style="cursor: pointer">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Bookie</th>
                                            <th>Type</th>
                                            <th>Bet Amount</th>
                                            <th>Placed</th>                              
                                            <th>Pending</th>                            
                                            <th>Sport</th>                              
                                            <th>Competition</th>                              
                                            <th>Odds</th> 
                                            <th>Event Time</th>  
                                            <th>Payout</th>                              
                                            <th>Status</th>                              
                                            <th>Selection</th>                              
                                            <th>Line</th>                              
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

<?php include 'modal/modal_addwager.php';?>


    </div>

    <script>

        $(document).ready(function () {
            getbets();
        });


        function getbets() {
            oTable1 = $('#dt_bets').DataTable({
                dom: "<'row'<'col-sm-4 pull-left'l><'col-sm-4 text-center'><'col-sm-4 pull-right'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-4 pull-left'i><'col-sm-8 pull-right'p>>",
                destroy: true,
                "order": [[4, "desc"]],
                'processing': true,
                "scrollX": true,
                'ajax': {
                    'type': 'POST',
                    'url': 'globaldata/data_bets.php'
//                'data': {
//                    whse: whse
//                }
                },
                fixedHeader: true,
                buttons: [
                    'excelHtml5'
                ],
//            "columnDefs": [
//                {className: "", targets: [-1]},
//                {className: "my_class", targets: "_all"}
//
//            ],
                createdRow: function (row, data, index) {
                    $(row).addClass('hovercoloer'); //add hover color at the row level
                    $(row).attr('id', data[0]); // location is the row id.  Location is the second element in the table
                }

            });
        }

        //toggle modal_addwager
        $(document).on("click touchstart", "#btn_addwager", function (e) {
            $('#modal_addwager').modal('toggle');
        });

        $(document).on("click touchstart", ".nfl_modifyline", function (e) {
            var nfl_id = $(this).attr('data-id');
            $.ajax({
                data: {"nfl_id": nfl_id},
                type: 'POST',
                url: 'modal/nfl_linemodal.php',
                dataType: 'html',
                success: function (ajaxresult) {
                    $("#modal_nfladdbet").html(ajaxresult);
                    $('#modal_nfladdbet').modal('toggle');
                }
            });
        });

        //post complete wager to table
        $(document).on("click touchstart", "#btn_addbet", function (event) {
            event.preventDefault();
            var nfl_id = $('#nfl_id').val();
            var nfl_type = $('#nfl_type').val();
            var nfl_for = $('#nfl_for').val();
            var nfl_spread = $('#nfl_spread').val();
            var nfl_amt = $('#nfl_amt').val();
            var nfl_winamt = $('#nfl_winamt').val();

            var formData = 'nfl_id=' + nfl_id + '&nfl_type=' + nfl_type + '&nfl_for=' + nfl_for + '&nfl_spread=' + nfl_spread + '&nfl_amt=' + nfl_amt + '&nfl_winamt=' + nfl_winamt;
            $.ajax({
                url: 'postdata/nfl_bet.php',
                type: 'POST',
                data: formData,
                success: function (result) {
                    $('#modal_nfladdbet').modal('hide');
                    getnflgames();
                }
            });
        });

    </script>

</body>
</html>