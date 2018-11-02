function logSuccess(message){
    logMessage(message, 'Success', 'green');
}

function logInfo(message){
    logMessage(message, 'Info', 'blue');
}

function logError(message){
    logMessage(message, 'Error', 'red');
}

function logMessage(message, title, color){
    var count = $('.messages .message').length;

    message =
        '<div class="ui ' + color + ' info message">' +
        // '<i class="close icon"></i>'+
        '<div class="header">' +
        (count + 1) + '. ' + title +
        '</div>' +
        message +
        '</div>';

    $('.messages').append(message);
}