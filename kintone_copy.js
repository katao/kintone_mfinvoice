jQuery(function($) {
    "use strict";
    kintone.events.on('app.record.index.show', function (event) {
        if ($('#invoicing_button')[0]) {
            return;
        }
        var tax = 0.08;
        var copy_app = 999;
        var target_app = 999;
        var data_app = 99;

        var $InvoicingButton = $("<button>", {
            id: 'invoicing_button',
            text: 'XXアプリへコピー'
        }).click(function() {
            //CSS変更
            $('#invoicing_box').css('display','block');
        });
         
        $(kintone.app.getHeaderMenuSpaceElement()).append($InvoicingButton);

        $("#invoicing_button").click(function () {
            var condition= kintone.app.getQueryCondition();
            var datas = {"app": copy_app, "query": condition};
            //検索結果
            kintone.api('/k/v1/records', 'GET', datas, function(resp) {
                //検索結果なしなら、アラート出して終了
                if(resp['records'].length < 1){
                    alert("データがありません。");
                    return false;
                }else{
                    var name = "";
                    $.each(resp['records'], function(i,v){
                        name += v['氏名']['value'] + '\n';
                    });
                    if(window.confirm(name + '上記のデータをコピーします。')){
                        var params,same_flag;
                        var err_message = "";

                        $.each(resp['records'], function(i,v){
                            //同案件ID同年同月
                            same_flag = false;
                            same_flag = getSeikyuRecord(target_app, v['レコード番号']['value']);

                            if(same_flag['records'].length < 1 || same_flag == undefined || same_flag == false){
                                //情報の取得
                                kintone.api('/k/v1/record', 'GET', {app: data_app, id: v['数値_1']['value'] }, function(data_resp) {
                                    data = data_resp['record'];

                                    //以下サンプル
                                    sample_flag = (v['ルックアップ_1']['value'] == 'XXX') ? true : false;

                                    if(tsuika_flag && (v['日付']['value'].slice(-2) * 1) > 20){
                                        date = new Date(year, month +1, 0);
                                    }else{
                                        date = new Date(year, month, 0);
                                    }

                                    //Table
                                    total = 0;
                                    table = [];
                                    v['Table_0']['value'].forEach(function (item) {
                                        table_data = {"value": {
                                            "項目名": { "value": item['value']['項目名']['value'] },
                                            "説明": { "value": item['value']['説明']['value'] },
                                            "単価": { "value": item['value']['単価']['value'] },
                                            "数量": { "value": item['value']['数量']['value'] },
                                            "金額": { "value": item['value']['小計']['value'] },
                                        }}
                                        table.push(table_data);
                                        total = total + item['value']['単価']['value'];
                                    });

                                    params = {
                                        "app": target_app,
                                        "record": {
                                            //会社名
                                            "会社名": { "value": v['会社名']['value'] },
                                            //氏名
                                            "氏名": { "value": v['氏名']['value']  },
                                            //テキスト
                                            "文字列__1行": { "value": v['文字列__1行']['value']  },
                                            //リンク
                                            "リンク": { "value": comp['リンク']['value'] },
                                            //日付
                                            "日付": { "value": date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate() },
                                            //ドロップダウン
                                            "ドロップダウン": { "value": comp['ドロップダウン_1']['value'] },
                                            //チェックボックス
                                            "チェックボックス": { "value": [v['チェックボックス']['value'][0]] },
                                            //内訳
                                            "Table":{"value": table},
                                            //小計
                                            "小計": { "value": total},
                                            //消費税
                                            "消費税": { "value": total * tax},
                                            //合計
                                            "合計": { "value": total + (total * tax)},
                                            //テキストエリア
                                            "文字列__複数行__0": { "value": v['文字列__複数行__0']['value'] },
                                            //税率
                                            "数値": { "value": tax },
                                            //dataID
                                            "数値": { "value": v['数値_1']['value'] },
                                            //copyID
                                            "数値_1": { "value": v['レコード番号']['value'] },
                                        }
                                    };
                                    
                                    //請求アプリへコピー 
                                    kintone.api(
                                        kintone.api.url('/k/v1/record', true), // - pathOrUrl
                                        'POST',                                // - method
                                        params,                                // - params
                                        function(resp) {                       // - callback
                                            // (特に何もしない)
                                        },
                                        function(resp) {                       // - errback
                                            // (特に何もしない)
                                            alert("XXアプリにコピーできませんでした。");
                                            console.log("----------");
                                            console.log(resp);
                                        }
                                    );
                                });
                            }else{
                                err_message += v['氏名']['value'] + "は既に登録されています。" + '\n';
                            }
                        });
                        if(err_message){
                            alert(err_message);
                        }
                    }else{
                        return false;
                    }
                    alert("請求アプリにコピーしました。");
                }
            }, function(resp) {
                // エラーの場合はメッセージを表示する
                var errmsg = 'レコード取得時にエラーが発生しました。';      
                // レスポンスにエラーメッセージが含まれる場合はメッセージを表示する
                if (resp.message !== undefined){
                    errmsg += '\n' + resp.message;
                }
                alert(errmsg);
            });
        });
    });    

    function getSeikyuRecord(appID,id,year,month) {
        if(appID == false || id == false){ return false}
        // 戻り値の設定
        var resp;
        var query = '数値_1 = \"' + id + '\" order by 数値_1 desc';
        var appUrl = kintone.api.url('/k/v1/records', true) + '?app=' + appID + '&query=' + encodeURI(query);
        // レコードを取得
        try {
            var xmlHttp = new XMLHttpRequest();
            xmlHttp.open("GET", appUrl, false);
            xmlHttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xmlHttp.send(null);
            if (xmlHttp.status == 200 && window.JSON) {
                resp = JSON.parse(xmlHttp.responseText);
            }
        } catch (e) {
            return false;
        }
        return resp;
    }
});