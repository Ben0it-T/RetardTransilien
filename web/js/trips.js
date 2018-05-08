/**
 * trips
 * date : 2018-03-09
 */

function getOngoing() {
    $.ajax({
        url: 'xhr/ongoing',
        type: 'get',
        dataType: 'json',
        success: function() {
            // rafraichir les infos ?
            return true;
        },
        error: function() {
            return false;
        }
    });
}

function getTrips() {
    if( !checkElement() ) {
        return false;
    }

    str  = "<div class=\"alert alert-info\" >";
    str += "<span class=\"glyphicon glyphicon-hourglass\"></span> Recherche des trains...";
    str += "</div>";
    setAlertMsgContent("TripsList", str);

    $.ajax({
        url: 'xhr/trips',
        type: 'post',
        data: {stopPoint1:$('#StopPoint1').val(), stopPoint2:$('#StopPoint2').val(), time:$('#time').val()},
        timeout: 15000,
        dataType: 'json',
        success: function(data) {
            setTripList(data);
        },
        error: function() {
            alert("Erreur lors de la récupération des données !");
            location.reload(true);
        }
    });
}

function checkElement() {
    var validation = true;
    if( $('#StopPoint1').val() == "") {
        validation = false;
    } else if( $('#StopPoint2').val() == "") {
        validation = false;
    } else if( $('#time').val() == "") {
        validation = false;
    }
    return validation;
}

function setTripList(data) {
    if(typeof data[0] !== 'undefined') {

        content  = "<div class=\"table-responsive\">";
        content += "<table class=\"table table-condensed\">";
        content += "<thead>";
        content += "<tr>";
        content += "<th>Départ</th>";
        content += "<th>Train</th>";
        content += "<th>Statut</th>";
        content += "<th style=\"text-align:right;\">Déclaration</th>";
        content += "</tr>";
        content += "</thead>";

        content += "<tbody>";

        $.each( data , function(i, trip) {
            msgRealTime = "";
            msgDelay = "";
            trClass = "";

            if( trip.realTimeStatus !== "") {
                // Status
                if( trip.realTimeStatus == "Retardé" )  {
                    trClass = "class=\"danger text-danger\"";
                    msgRealTime = "<span class=\"glyphicon glyphicon-warning-sign my-color-danger\"></span> <span class=\"my-color-danger\">Retardé</span>";
                }
                if( trip.realTimeStatus == "Supprimé" ) {
                    trClass = "class=\"danger text-danger\"";
                    msgRealTime = "<span class=\"glyphicon glyphicon-warning-sign my-color-danger\" ></span> <span class=\"my-color-danger\">Supprimé</span>";
                }
            }
            else if( trip.realTimeDelay !== "" ) {
                // Delay
                if(trip.realTimeDelay == 0) {
                    msgRealTime = "<span class=\"text-success\">A l'heure</span>";
                }
                if(trip.realTimeDelay > 0) {
                    trClass = "class=\"warning text-warning\"";
                    msgRealTime = "<span class=\"glyphicon glyphicon-warning-sign my-color-warning\"></span> <span class=\"my-color-warning\">+" + trip.realTimeDelay + "min</span>";
                }
                if(trip.realTimeDelay < 0) {
                    trClass = "class=\"warning text-warning\"";
                    msgRealTime = "<span class=\"glyphicon glyphicon-warning-sign my-color-warning\"></span> <span class=\"my-color-warning\">" + trip.realTimeDelay + "min</span>";
                }
            }

            if(trip.incidentDelay !== "") {
                msgDelay = "<span class=\"badge\">+ " + trip.incidentDelay + "</span>";
                if( trip.incidentType == 1 ) {
                    msgDelay = "<span class=\"badge my-badge-warning\">+ " + trip.incidentDelay + "</span>";
                }
                else if( trip.incidentType == 2 || trip.incidentType == 4 ) {
                    msgDelay = "<span class=\"badge my-badge-danger\">+ " + trip.incidentDelay + "</span>";
                }  
            }

            // output
            tripInfo = "<p>";
            tripInfo += "<strong>" + trip.headsign + "</strong> de <strong>" + trip.departureTime + "</strong><br>";
            tripInfo += "de <strong>" + trip.stopPoint1 + "</strong><br>";
            tripInfo += "en direction de <strong>" + trip.stopPoint2 + "</strong><br>";
            tripInfo += "</p>";

            content += "<tr " + trClass + "  >";
            content += "<td><b>" + trip.departureTime + "</b><br>" + trip.arrivalTime + "</td>";
            content += "<td>" + trip.headsign + "</td>";
            content += "<td>" + msgRealTime + "</td>";
            content += "<td style=\"text-align:right;\" >";
                content += msgDelay;
                content += " <button type=\"button\" class=\"btn btn-default\" ";
                content += "onclick=\"";
                    content += "setFields('incident_tripId','"+trip.tripId+"');";
                    content += "setFields('incident_serviceId','"+trip.serviceId+"');";
                    content += "setFields('incident_tripId','"+trip.tripId+"');";
                    content += "setFields('incident_routeId','"+trip.routeId+"');";
                    content += "setFields('incident_headsign','"+trip.headsign+"');";
                    content += "setFields('incident_date','"+trip.tripDate+"');";

                    content += "setTripInfo('"+trip.departureTime+"','"+trip.stopPoint1+"','"+trip.stopPoint2+"','"+trip.headsign+"');";
                content += "\"";
                content += " >";
                content += "<span class=\"glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span>";
                content += "</button>";
            content += "</tr>";
        });

        content +="</tbody>";
        content +="</table>";
        content +="</div> ";
    }
    else {
        content  = "<div class=\"alert alert-danger\" role=\"alert\">";
        content += "<span class=\"glyphicon glyphicon-warning-sign\"></span> ";
        content += "Pas de train ralliant ces deux gares.";
        content += "</div>";
    }

    setAlertMsgContent("TripsList", content);
}


function setFields( fieldId, fieldVal) {
    $('#'+fieldId ).val( fieldVal );
}

function setTripInfo(departureTime, stopPoint1, stopPoint2, headsign) {
    content = "<p>";
    content += "<strong>" + headsign + "</strong> de <strong>" + departureTime + "</strong><br>";
    content += "de <strong>" + stopPoint1 + "</strong><br>";
    content += "en direction de <strong>" + stopPoint2 + "</strong><br>";
    content += "</p>";
    
    $('#tripInfos').empty();
    $('#tripInfos').html( content ); 
    $('#incidentModal').modal('show');
}
