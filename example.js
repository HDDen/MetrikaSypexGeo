(function(){
    var metrika_id = XXXXXXXX;
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/madmen-includ/SypexGeo/ipinfo.php');
    xhr.setRequestHeader("Content-Type", "application/json; charset=UTF-8");
    xhr.responseType = 'json';
    xhr.onload = function(){
        if (window['ym']){
            //console.log('params', xhr.response);
            var resp = xhr.response;

            // история ip
            try{
                var l = window.localStorage;
                if (l){
                    var hist = l.getItem('madm_iphist');
                    if (hist) hist = JSON.parse(hist);
                    if (!(hist instanceof Array)){
                        hist = [hist];
                    }
                    resp['ip_history'] = hist;
                    
                    // добавим ip в список
                    hist.push(resp.ip);

                    // сохраним в истории
                    l.setItem('madm_iphist', JSON.stringify(hist));
                }
            } catch(err){}

            ym(metrika_id, 'params', resp);
        }
    }
    xhr.onerror = function(){
        //console.log('madmen_ipinfo_blocked');
        if (window['ym']){
            ym(metrika_id, 'params', {madmen_ipinfo_blocked: 'yes'});
        }
    };
    xhr.send();
}());

!function(){var metrika_id=XXXXXXXX,xhr=new XMLHttpRequest;xhr.open("GET","/madmen-includ/SypexGeo/ipinfo.php"),xhr.setRequestHeader("Content-Type","application/json; charset=UTF-8"),xhr.responseType="json",xhr.onload=function(){if(window.ym){var resp=xhr.response;try{var l=window.localStorage;if(l){var hist=l.getItem("madm_iphist");hist&&(hist=JSON.parse(hist)),hist instanceof Array||(hist=[hist]),resp.ip_history=hist,hist.push(resp.ip),l.setItem("madm_iphist",JSON.stringify(hist))}}catch(err){}ym(metrika_id,"params",resp)}},xhr.onerror=function(){window.ym&&ym(metrika_id,"params",{madmen_ipinfo_blocked:"yes"})},xhr.send()}();