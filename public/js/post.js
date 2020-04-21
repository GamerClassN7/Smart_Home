function ajaxPostSimple(path, params, reload = false) {
    navigator.vibrate([200]);
    $.ajax({
        url: path,
        type: 'POST',
        data: params,
        success: function(msg){
            console.log("message");
            console.log(msg);
            if (reload){
                location.reload();
            }
        },
        error: function (request, status, error) {
            console.log('0');
        }
    });
    return false;
}

function ajaxPost(path, params, self, reload = false) {
    navigator.vibrate([200]);
    $.ajax({
        url: path,
        type: 'POST',
        data: params,
        success: function(msg){
            if (msg != '' && msg != 1){        
                $(self).find('.content').addClass( "loader" );
                $(self).find('.row').hide();
                waitForExecution(params, self, msg);
            } else {
                
            }
            console.log(msg);
            if (reload){
                location.reload();
            }
        },
        error: function (request, status, error) {
            console.log('0');
        }
    });
    return false;
}

function waitForExecution(params, elements, msg_state){
    console.log('Waiting FOR Executed');
    var interval = setInterval(
        function(){
            $.ajax({
                url: 'ajax',
                type: 'POST',
                data: { 
                    action:'executed',
                    subDevice_id : params['subDevice_id']
                },
                success: function(msg){
                    if (msg == 1){
                        $(elements).find('.text-right').text(msg_state);
                        $(elements).find('.content').removeClass( "loader" );
                        $(elements).find('.row').show();
                        console.log('Executed');
                        clearInterval(interval);
                    }
                    console.log('Waiting FOR Executed');
                    console.log(msg);
                },
                error: function (request, status, error) {
                    console.log('0');
                }
            });
        }, 1000);
    }