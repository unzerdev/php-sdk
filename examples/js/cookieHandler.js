function setCookie(name, value, expireInDays = 1) {
    var d = new Date();
    d.setTime(d.getTime() + (expireInDays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/";
}

function getCookie(name) {
    name = name + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var decodedCookieSplit = decodedCookie.split(';');
    for(var cookieElementIdx = 0; cookieElementIdx < decodedCookieSplit.length; cookieElementIdx++) {
        var cookieElement = decodedCookieSplit[cookieElementIdx];
        while (cookieElement.charAt(0) === ' ') {
            cookieElement = cookieElement.substring(1);
        }
        if (cookieElement.indexOf(name) === 0) {
            return cookieElement.substring(name.length, cookieElement.length);
        }
    }
    return "";
}