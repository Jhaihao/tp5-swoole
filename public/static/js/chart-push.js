$(function () {
    $('#discuss-box').keydown(function (event) {
        if (event.keyCode == 13) {
            var text = $(this).val();
            var url = "http://loca.tp5-swoole.com:9501/index/chart/index";
            var data = {'content': text, 'game_id': 1}

            $.post(url, data, function (result){
                if(result.status == 1){
                    $('#discuss-box').val("");
                }

            }, 'json');

        }
    });
});
