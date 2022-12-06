(function(){
    var metrika_id = 55312411;
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/madmen-includ/SypexGeo/ipinfo.php');
    xhr.setRequestHeader("Content-Type", "application/json; charset=UTF-8");
    xhr.responseType = 'json';
    xhr.onload = function(){
        if (window['ym']){
            //console.log('params', xhr.response);
            ym(metrika_id, 'params', xhr.response);
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

(function(){var metrika_id=55312411;var xhr=new XMLHttpRequest;xhr.open("GET","/madmen-includ/SypexGeo/ipinfo.php");xhr.setRequestHeader("Content-Type","application/json; charset=UTF-8");xhr.responseType="json";xhr.onload=function(){if(window["ym"]){ym(metrika_id,"params",xhr.response)}};xhr.onerror=function(){if(window["ym"]){ym(metrika_id,"params",{madmen_ipinfo_blocked:"yes"})}};xhr.send()})();