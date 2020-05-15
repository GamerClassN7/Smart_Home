function restartAutomation(automationId){
    console.log("restartingAutomation" + automationId);
    $.ajax({
        url: 'ajax',
        type: 'POST',
        data: {
            "automation_id" : automationId,
            "action": "restart"
        },
        success: function(data){
            console.log(data);
        },
        error: function (request, status, error) {
            console.log("ERROR ", request, error);
        }
    });
}

function toggleAutomation(automationId){
    console.log("togglingAutomation" + automationId);
    $.ajax({
        url: 'ajax',
        type: 'POST',
        data: {
            "automation_id" : automationId,
            "action": "deactive"
        },
        success: function(data){
            $('#automation-'+automationId).toggleClass("is-inactive");
        },
        error: function (request, status, error) {
            console.log("ERROR ", request, error);
        }
    });
}