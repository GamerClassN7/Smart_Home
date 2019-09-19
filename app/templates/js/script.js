if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('serviceWorker.js')
        .then(registration => {
            console.log('Service Worker is registered', registration);
        })
        .catch(err => {
            console.error('Registration failed:', err);
        });
    });
}


$('select').change(function(e) {
    console.log($(this).val());
    if( $(this).val() == 'time') {
        
        $('#atTime').prop( "disabled", false );
        
        $('#atDeviceValue').prop( "disabled", true );
        $('#atDeviceValueInt').prop( "disabled", true );
    } else if( $(this).val() == 'atDeviceValue') {
        
        $('#atDeviceValue').prop( "disabled", false );
        $('#atDeviceValueInt').prop( "disabled", false );
        
        $('#atTime').prop( "disabled", true );
    }
});

var pressTimer; 
var touch = 0;
var touchSubId = "";
$("div.square-content").on('touchend', function (e){ 
    clearTimeout(pressTimer);
});

$("div.square-content").on('touchstart', function (eTarget) {
    navigator.vibrate([500]);
    var id = '';
    
    var windowLoc = $(location).attr('pathname');
    windowLoc = windowLoc.substring(windowLoc.lastIndexOf("/"));
    console.log(windowLoc);
    if (windowLoc == "/") {    
        id = $(this).attr('id').replace('device-', '');
    } else if (windowLoc == "/scene") {
        id = $(this).attr('id').replace('scene-', '');
    } else if (windowLoc == "/automation") {
        id = $(this).attr('id').replace('automation-', '');
    }
    
    var subId = $(this).attr('data-sub-device-id');
    
    touch++;
    if(touch == 2 && touchSubId == subId){
        console.log("Detail");
        if (windowLoc == "/") {    
            $("#modal-detail-"+subId).removeClass('modal-container-hiden').show();
            ajaxChart(subId);
        } else if (windowLoc == "/scene") {
            
        } else if (windowLoc == "/automation") {
        }
        touch = 0;
        touchSubId = "";
        return;
    }
    touchSubId = subId;
    
    pressTimer = window.setTimeout(function (e) {
        console.log("Setting");
        $("#modal-setting-"+id).removeClass('modal-container-hiden').show();
        touch = 0;
    }, 500);
});

$("div.square-content").mousedown(function(e) {
    if (event.which == 3) {
        var windowLoc = $(location).attr('pathname');
        windowLoc = windowLoc.substring(windowLoc.lastIndexOf("/"));
        console.log(windowLoc);
        var id = null;
        if (windowLoc == "/") {    
            id = $(this).attr('id').replace('device-', '');
        } else if (windowLoc == "/scene") {
            id = $(this).attr('id').replace('scene-', '');
        } else if (windowLoc == "/automation") {
            id = $(this).attr('id').replace('automation-', '');
        }
        $("#modal-setting-"+id).removeClass('modal-container-hiden').show();
        console.log("Setting");
        console.log("modal" + id);
    }    
});

$(".close").on('click', function (e) {
    var a = $(this).parent().parent();
    a.hide();
});

$(this).bind("contextmenu", function(e) {
    e.preventDefault();
});

$("div.square-content").on('dblclick', function (eTarget) {
    windowLoc = windowLoc.substring(windowLoc.lastIndexOf("/"));
    if (windowLoc == "/") {
        console.log("Detail");
        var subId = $(this).attr('data-sub-device-id');
        ajaxChart(subId);
        $("#modal-detail-"+subId).removeClass('modal-container-hiden').show();
    }
});

$("input#sleepTime").change(function() {
    console.log("Input text changed!");
});

var element = $('div.delete');
element.hide();
$("a#remove").on('click', function (e) {
    console.log("Show/Hide Button");
    var element = $('div.delete');
    element.toggle();
});

function ajaxChart(id, period = 'day', group = 'hour'){
    $.ajax({
        url: 'ajax',
        type: 'POST',
        dataType: 'json',
        data: {
            "subDevice": id,
            "action": 'chart',
            "period": period,
            "group": group
        },
        success: function(data){
            console.log('ID: ',id, 'DATA: ', data);
            var ctx = document.getElementById('canvas-'+id).getContext('2d');
            var myChart = new Chart(ctx, data);
        },
        error: function (request, status, error) {
            console.log("ERROR ajaxChart():", request, error);
        }
    });
}

//select room on load
var windowLoc = $(location).attr('pathname');
windowLoc = windowLoc.substring(windowLoc.lastIndexOf("/"));
console.log();
if (windowLoc == "/") {
    var selectRoomId = localStorage.getItem("selectedRoomId");
    console.log('Saved Selected Room ID '+ selectRoomId);
    $('[name="room"]').val(selectRoomId);     
    $('.device-button').each(function(){
        if (selectRoomId != 'all'){  
            if($(this).data('room-id') != selectRoomId){
                $(this).hide();
            } else {
                $(this).show();
            }
        }
    });
}

//Room selector
$( '[name="room"]' ).change(function (e) {    
    console.log('Selected Room ID ' + this.value)
    var roomId = this.value;
    localStorage.setItem("selectedRoomId", roomId);
    $('.device-button').show();
    if (roomId != 'all'){        
        $('.device-button').each(function(){
            if($(this).data('room-id') != roomId){
                $(this).hide();
            }
        });
    }
});  


// var windowLoc = $(location).attr('pathname');
// windowLoc = windowLoc.substring(windowLoc.lastIndexOf("/"));
// console.log();
// if (windowLoc == "/") {
//     var autoUpdate = setInterval(function(){
//         $.ajax({
//             url: 'ajax',
//             type: 'POST',
//             dataType: 'json',
//             data: {
//                 "action": 'getState'
//             },
//             success: function(data){
//                 // console.log('DATA: ', data);
//                 for (const key in data) {
//                     if (data.hasOwnProperty(key)) {
//                         const device = data[key];
//                         $('[data-sub-device-id="'+key+'"]')
//                         .find('.device-button-value')
//                         .text(device['value'])
//                         .attr('title',device['time'])
//                     }
//                 }
//             },
//             error: function (request, status, error) {
//                 console.log("ERROR ajaxChart():", request, error);
//             }
//         });
//     },2000);
// }



//Graphs
$('.graph-period').on('click', function (e) {
    var subId = $(this).attr('data-sub-device-id');
    var period = $(this).attr('data-period');
    var groupBy = $(this).attr('data-group');
    ajaxChart(subId, period, groupBy);
});

$( "button[name=remove]" ).click(function(e) {
    if (confirm('Are you shure ?')) {
        var windowLoc = $(location).attr('pathname');
        windowLoc = windowLoc.substring(windowLoc.lastIndexOf("/"));
        console.log(windowLoc);
        var id = null;
        if (windowLoc == "/scene") {
            id = $(this).data('scene-id');
            $("#scene-"+id+"-content").remove();
        } else if (windowLoc == "/automation") {
            id = $(this).data('automation-id');
            $("#automation-"+id+"-content").remove();
        }
    }
});
































