function restartAutomation(automationId){
    console.log("restartingAutomation" + automationId);
    event.preventDefault();
    $.ajax({
        url: 'ajax',
        type: 'POST',
        data: {
            "automation_id" : automationId,
            "action": 'restart'
        },
        success: function(data){
            console.log(data);
        },
        error: function (request, status, error) {
            console.log("ERROR ", request, error);
        }
    });
}

function toggleAutomation(thisElement, automationId){
    console.log("togglingAutomation" + automationId);
    event.preventDefault();
    $.ajax({
        url: 'ajax',
        type: 'POST',
        data: {
            "automation_id" : automationId,
            "action": 'deactive'
        },
        success: function(data){
            console.log($('automation-'+automationId));
            $('#automation-'+automationId).toggleClass("is-inactive");
            console.log('active');
        },
        error: function (request, status, error) {
            console.log("ERROR ", request, error);
        }
    });
}