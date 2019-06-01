$(function () {
    $('#discuss-box').keydown(function (event) {
        if (event.keyCode == 13) {
            var text = $(this).val();
            var url = "http://loca.tp5-swoole.com/index/chart/index";
            var data = {'content': text, 'game_id': 1}
            $.ajax({
                url: url,
                type: "post",
                dataType: "json",
                data: data,
                success: function (res) {
                    if (res.status == 1) {
                        $('#discuss-box').val("");
                    }
                },
                error: function (res) {
                    console.log(res)
                }
            });


            // $.post(url, data, function (result){
            //     if(result.status == 1){
            //         $('#discuss-box').val("");
            //     }
            //
            // }, 'json');

        }
    });
});
