navigator.permissions.query({name:'notifications'}).then(function(result) {
    var element = document.getElementById("notifications");
    if (result.state === 'granted') {
        element.checked = true;
    } else if (result.state === 'denied') {
        element.checked = false;
    } else if (result.state === 'prompt') {
        element.checked = false;
    }
});

function toggleNotificationPermissions(input){
    navigator.permissions.query({name:'notifications'}).then(function(result) {
        if (result.state === 'granted') {
            input.checked = true;
        } else if (result.state === 'denied') {
            input.checked = false;
        } else if (result.state === 'prompt') {
            input.checked = false;
        }
    });
}

function sendTestNotification(){
    console.log("sending test notification");
    $.ajax({
        url: 'ajax',
        type: 'POST',
        data: {
            "notification" : 'X',
            "action": 'sendTest'
        },
        success: function(data){
            console.log(data);
        },
        error: function (request, status, error) {
            console.log("ERROR ", request, error);
        }
    });
}

$( "button[name='deactivateOta']" ).click(function(){
    console.log("Didabling ota");
    $.ajax({
        url: 'ajax',
        type: 'POST',
        data: {
            "ota" : 'X',
            "action": 'disable'
        },
        success: function(data){
            console.log(data);
        },
        error: function (request, status, error) {
            console.log("ERROR ", request, error);
        }
    });
})
