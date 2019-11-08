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
            <div class="card-columns">
                <div id="ctn_nflgames">
                    <!--nfl game content will go here-->
                </div>
            </div>  
        </div>

        <div id="modal_nfladdbet" class="modal fade " role="dialog"></div>
        <div id="modal_nfladdline" class="modal fade " role="dialog"></div>

    </div>

    <script>

        $(document).ready(function () {
            getnflgames();
        });


        function getnflgames() {
            var nfl_week = 10;
            $.ajax({
                data: {"nfl_week": nfl_week},
                type: 'POST',
                url: 'globaldata/nfl_gamelisting.php',
                dataType: 'html',
                success: function (ajaxresult) {
                    $("#ctn_nflgames").html(ajaxresult);
                }
            });
        }

        $(document).on("click touchstart", ".click_addbet", function (e) {
            var nfl_id = $(this).attr('data-id');
            $.ajax({
                data: {"nfl_id": nfl_id}, 
                type: 'POST',
                url: 'modal/nfl_betmodal.php',
                dataType: 'html',
                success: function (ajaxresult) {
                    $("#modal_nfladdbet").html(ajaxresult);
                    $('#modal_nfladdbet').modal('toggle');
                }
            });
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