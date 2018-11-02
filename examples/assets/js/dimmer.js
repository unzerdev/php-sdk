function showDimmerMessage(message) {
    document.getElementById('dimmer-holder').innerHTML
        = '<div style="color: #eee;top: 43%;position: relative;" class="ui">' + message + '</div>';
    document.getElementById('dimmer-holder').style.display = 'block';
}

function showDimmerLoader() {
    document.getElementById('dimmer-holder').innerHTML = '<div class="ui loader"></div>';
    document.getElementById('dimmer-holder').style.display = 'block';
}

function hideDimmer() {
    document.getElementById('dimmer-holder').style.display = 'none';
}