(function(){
    var metrika_id = false; // false для автопоиска или конкретный счётчик
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/madmen-includ/SypexGeo/ipinfo.php');
    xhr.setRequestHeader("Content-Type", "application/json; charset=UTF-8");
    xhr.responseType = 'json';
    xhr.onload = function(){
        waitForYm(metrika_id, function(counter, metrika_id){
            var resp = xhr.response;

            // история ip
            try{
                var l = window.localStorage;
                if (l){
                    var hist = l.getItem('madm_iphist');
                    if (hist){
                        hist = JSON.parse(hist);
                        if (!(hist instanceof Array)){
                            hist = [hist];
                        }
                    } else {
                        hist = [];
                    }

                    // проверяем, есть ли наш ip в истории
                    if (hist.indexOf(resp.ip) === -1){
                        // добавим ip в список
                        hist.push(resp.ip);
                    }

                    // добавляем в параметры
                    resp['ip_history'] = hist;

                    // сохраним в истории
                    l.setItem('madm_iphist', JSON.stringify(hist));
                }
            } catch(err){}

            console.log('params', resp);
            if (resp){
                ym(metrika_id, 'params', resp);
            } else {
                console.log("params wasn't sent");
            }
        });
    }
    xhr.onerror = function(){
        waitForYm(metrika_id, function(counter, metrika_id){
            //console.log('madmen_ipinfo_blocked');
            ym(metrika_id, 'params', {madmen_ipinfo_blocked: 'yes'});
        });
    };
    xhr.send();

    /** Ожидание загрузки счетчика Яндекс.Метрики
     * @param {?(number|string)} ymCounterNum - номер счетчика, если известен
     * @param {function} callback - получает аргументами объект и номер счетчика
     * @param {number} interval - интервал проверки готовности счетчика
     */
    function waitForYm(ymCounterNum, callback, interval) {
        if (!callback) return;
        if (!ymCounterNum) {
            var metrikaObj  = (window.Ya && (window.Ya.Metrika || window.Ya.Metrika2)) || null;
            ymCounterNum = (metrikaObj && metrikaObj.counters && (metrikaObj.counters() || [0])[0].id) || 0;
        }
        var ymCounterObj = window['yaCounter' + ymCounterNum] || null;
        if (ymCounterObj) return (callback(ymCounterObj, ymCounterNum), undefined);
        setTimeout(function () { waitForYm(ymCounterNum, callback, interval); }, interval || 250);
    }
}());

(function(){var metrika_id=false;var xhr=new XMLHttpRequest;xhr.open("GET","/madmen-includ/SypexGeo/ipinfo.php");xhr.setRequestHeader("Content-Type","application/json; charset=UTF-8");xhr.responseType="json";xhr.onload=function(){waitForYm(metrika_id,(function(counter,metrika_id){var resp=xhr.response;try{var l=window.localStorage;if(l){var hist=l.getItem("madm_iphist");if(hist){hist=JSON.parse(hist);if(!(hist instanceof Array)){hist=[hist]}}else{hist=[]}if(hist.indexOf(resp.ip)===-1){hist.push(resp.ip)}resp["ip_history"]=hist;l.setItem("madm_iphist",JSON.stringify(hist))}}catch(err){}console.log("params",resp);if(resp){ym(metrika_id,"params",resp)}else{console.log("params wasn't sent")}}))};xhr.onerror=function(){waitForYm(metrika_id,(function(counter,metrika_id){ym(metrika_id,"params",{madmen_ipinfo_blocked:"yes"})}))};xhr.send();function waitForYm(ymCounterNum,callback,interval){if(!callback)return;if(!ymCounterNum){var metrikaObj=window.Ya&&(window.Ya.Metrika||window.Ya.Metrika2)||null;ymCounterNum=metrikaObj&&metrikaObj.counters&&(metrikaObj.counters()||[0])[0].id||0}var ymCounterObj=window["yaCounter"+ymCounterNum]||null;if(ymCounterObj)return callback(ymCounterObj,ymCounterNum),undefined;setTimeout((function(){waitForYm(ymCounterNum,callback,interval)}),interval||250)}})();