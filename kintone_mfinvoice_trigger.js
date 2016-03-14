jQuery(function($) {
    "use strict";
    kintone.events.on('app.record.index.show', function (event) {
        if ($('#invoicing_button')[0]) {
            return;
        }
        var $LoadingImg = '<div id="loader-bg"><div id="loading"><img src="https://api.diamondtail.jp/loadingAnimation.gif"></div></div>';

        var $InvoicingButton = $("<button>", {
            id: 'invoicing_button',
            text: 'FM請求書発行'
        }).click(function() {
            var condition= kintone.app.getQueryCondition();
            var datas = {"app": XXX, "query": condition}; // XXXは自分のアプリID
            //検索結果
            kintone.api('/k/v1/records', 'GET', datas, function(resp) {
                //検索結果なしなら、アラート出して終了
                if(resp['records'].length < 1){
                    alert("請求データがありません。");
                    return false;
                }else{
                    var name = "";
                    $.each(resp['records'], function(i,v){
                        name += v['氏名']['value'] + '\n';
                    });
                    if(window.confirm(name + '上記の請求書を発行します。')){
                        $(kintone.app.getHeaderMenuSpaceElement()).append($LoadingImg);
                        $.ajax({
                          type: 'POST',
                          url: 'https://hogehoge.jp/kintone_mfinvoice.php', //実行するphpのURL(httpsのみ)
                          dataType: 'json', 
                          data: { 'resp': resp['records'] },
                          success: function(data){
                            // 処理を記述
                            if(data){
                                alert('請求書を発行しました');
                            }else{
                                alert('請求書を発行できませんでした');
                            }
                          },
                          error: function(Request, Status, Thrown){
                            console.log(Request);
                            console.log(Status);
                            console.log(Thrown);
                            console.log('-----');
                            console.log(Request.responseText);
                          }
                        });
                        alert('請求書を発行しました');
                    }else{
                        return false;
                    }
                }
            }, function(resp) {
                console.log("エラー");
                // エラーの場合はメッセージを表示する
                var errmsg = 'レコード取得時にエラーが発生しました。';      
                // レスポンスにエラーメッセージが含まれる場合はメッセージを表示する
                if (resp.message !== undefined){
                    errmsg += '\n' + resp.message;
                }
                alert(errmsg);
            });
            $("#loader-bg").fadeOut();
        });
        $(kintone.app.getHeaderMenuSpaceElement()).append($InvoicingButton);
    });
});