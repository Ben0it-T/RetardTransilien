/**
 * geolocation
 * date : 2018-03-05
 */

var locationField="";
function getLocation( fieldId ) {
    if(navigator.geolocation) {
        str  = "<div class=\"alert alert-info\" >";
        str += "<span class=\"glyphicon glyphicon-hourglass\"></span> Recherche de votre position...";
        str += "</div>";
        setAlertMsgContent("TripsList", str);
        locationField=fieldId;
        navigator.geolocation.getCurrentPosition(getStopPoint,getLocationError,{maximumAge:0,enableHighAccuracy:true,timeout:10000});
    }
    else {
        // no native support
    }
}

function getStopPoint(position) {
    $.ajax({
        url: 'xhr/stoppoint',
        type: 'post',
        data: {lat: position.coords.latitude, lon:position.coords.longitude},
        timeout: 15000,
        dataType: 'json',
        success: function(data) {
            str  = "<div class=\"alert alert-info\" >";
            str += "<span class=\"glyphicon glyphicon-info-sign\"></span> S&eacute;lectionner une gare d'arriv&eacute;e.";
            str += "</div>";
            setAlertMsgContent("TripsList", str);
            $("#"+locationField ).val(data.id);
            getTrips();
        },
        error: function() {
            str  = "<div class=\"alert alert-info\" >";
            str += "<span class=\"glyphicon glyphicon-warning-sign\"></span> Votre position n’a pu être déterminée.<br>";
            str += "S&eacute;lectionner une gare de d&eacute;part et une gare d'arriv&eacute;e.";
            str += "</div>";
            setAlertMsgContent("TripsList", str);
        }
    });

    
    

}
function getLocationError(error) {
    var info = "";
    switch(error.code) {
        case error.TIMEOUT:
            info += "Votre position n’a pu être déterminée (timeout).";
            break;
        case error.PERMISSION_DENIED:
            info += "Vous n’avez pas autorisé l'accès à votre position.";
            break;
        case error.POSITION_UNAVAILABLE:
            info += "Votre position n’a pu être déterminée.";
            break;
        case error.UNKNOWN_ERROR:
            info += "Votre position n’a pu être déterminée.";
            break;

        default:
            info += "Votre position n’a pu être déterminée.";
    }
    
    str  = "<div class=\"alert alert-info\" >";
    str += "<span class=\"glyphicon glyphicon-warning-sign\"></span> " + info + "<br>";
    str += "S&eacute;lectionner une gare de d&eacute;part et une gare d'arriv&eacute;e.";
    str += "</div>";

    setAlertMsgContent("TripsList", str);

    return false;
}