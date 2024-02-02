/**
 * Для принудительного запуска без ожидания DOMContentLoaded закомментировать строки:

!function(){function n(){
и
}"loading"!=document.readyState?n():document.addEventListener("DOMContentLoaded",function(){n()})}();

 */

!function(){function n(){
(function(){
    var metrika_id = false; // false для автопоиска или конкретный счётчик
    var search_isp = true; // false если нужна только локация, без провайдера. Либо если проверяем провайдера на бэке
    var storeInWindow = false; // false или имя ключа, по которому будет сохранен в window итоговый объект с параметрами
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/madmen-includ/MetrikaSypexGeo/ipinfo.php');
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

            if (resp){
                
                if (search_isp){

                    // пробуем получить имя провайдера из ответа бэкенда
                    if (!resp['ip_org']){
                        // не удалось - дёргаем ipinfo.io
                        var xhr_isp = new XMLHttpRequest();
                        xhr_isp.open('GET', 'https://ipinfo.io/json');
                        xhr_isp.setRequestHeader("Content-Type", "application/json; charset=UTF-8");
                        xhr_isp.responseType = 'json';
                        xhr_isp.onload = function(){
                            var resp_ipapi = xhr_isp.response;
                            // отфильтровать результат
                            resp.ip_org = (resp_ipapi.org ? resp_ipapi.org : '');

                            // отправка в метрику
                            console.log('params', resp);
                            ym(metrika_id, 'params', resp);
                        }
                        xhr_isp.onerror = function(){
                            // отправляем имеющееся
                            console.log('params', resp);
                            ym(metrika_id, 'params', resp);
                        }
                        xhr_isp.send();
                    } else {
                        // просто отправка в метрику
                        console.log('params', resp);
                        ym(metrika_id, 'params', resp);
                    }
                    
                } else {
                    // отправка в метрику
                    console.log('params', resp);
                    ym(metrika_id, 'params', resp);
                }
            } else {
                console.log('params', resp, "params wasn't sent");
            }

            // сохраним в Window
            if (storeInWindow){
                window[storeInWindow] = resp;
            }
        });
    }
    xhr.onerror = function(){
        waitForYm(metrika_id, function(counter, metrika_id){
            //console.log('madmen_ipinfo_blocked');
            var resp = {madmen_ipinfo_blocked: 'yes'};
            ym(metrika_id, 'params', resp);

            // сохраним в Window
            if (storeInWindow){
                window[storeInWindow] = resp;
            }
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
}"loading"!=document.readyState?n():document.addEventListener("DOMContentLoaded",function(){n()})}();

// minified with https://www.digitalocean.com/community/tools/minify
!function(){function n(){
(function(){var metrika_id=false;var search_isp=true;var storeInWindow=false;var xhr=new XMLHttpRequest;xhr.open("GET","/madmen-includ/MetrikaSypexGeo/ipinfo.php");xhr.setRequestHeader("Content-Type","application/json; charset=UTF-8");xhr.responseType="json";xhr.onload=function(){waitForYm(metrika_id,(function(counter,metrika_id){var resp=xhr.response;try{var l=window.localStorage;if(l){var hist=l.getItem("madm_iphist");if(hist){hist=JSON.parse(hist);if(!(hist instanceof Array)){hist=[hist]}}else{hist=[]}if(hist.indexOf(resp.ip)===-1){hist.push(resp.ip)}resp["ip_history"]=hist;l.setItem("madm_iphist",JSON.stringify(hist))}}catch(err){}if(resp){if(search_isp){if(!resp["ip_org"]){var xhr_isp=new XMLHttpRequest;xhr_isp.open("GET","https://ipinfo.io/json");xhr_isp.setRequestHeader("Content-Type","application/json; charset=UTF-8");xhr_isp.responseType="json";xhr_isp.onload=function(){var resp_ipapi=xhr_isp.response;resp.ip_org=resp_ipapi.org?resp_ipapi.org:"";console.log("params",resp);ym(metrika_id,"params",resp)};xhr_isp.onerror=function(){console.log("params",resp);ym(metrika_id,"params",resp)};xhr_isp.send()}else{console.log("params",resp);ym(metrika_id,"params",resp)}}else{console.log("params",resp);ym(metrika_id,"params",resp)}}else{console.log("params",resp,"params wasn't sent")}if(storeInWindow){window[storeInWindow]=resp}}))};xhr.onerror=function(){waitForYm(metrika_id,(function(counter,metrika_id){var resp={madmen_ipinfo_blocked:"yes"};ym(metrika_id,"params",resp);if(storeInWindow){window[storeInWindow]=resp}}))};xhr.send();function waitForYm(ymCounterNum,callback,interval){if(!callback)return;if(!ymCounterNum){var metrikaObj=window.Ya&&(window.Ya.Metrika||window.Ya.Metrika2)||null;ymCounterNum=metrikaObj&&metrikaObj.counters&&(metrikaObj.counters()||[0])[0].id||0}var ymCounterObj=window["yaCounter"+ymCounterNum]||null;if(ymCounterObj)return callback(ymCounterObj,ymCounterNum),undefined;setTimeout((function(){waitForYm(ymCounterNum,callback,interval)}),interval||250)}})();
}"loading"!=document.readyState?n():document.addEventListener("DOMContentLoaded",function(){n()})}();