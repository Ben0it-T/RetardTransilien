<?php

// Declare Incident
$app->match('/', "RetardTransilien\Controller\MainController::indexAction")
->bind('declareIncident');

// Export Data
$app->get('/export', "RetardTransilien\Controller\MainController::exportAction")
->bind('exportData');

// Export as CSV
$app->get('/export/csv', "RetardTransilien\Controller\MainController::exportCsvAction")
->bind('exportDataCsv');

// Consult Data
$app->match('/data', "RetardTransilien\Controller\MainController::dataAction")
->bind('consultData');

// About
$app->get('/about', "RetardTransilien\Controller\MainController::aboutAction")
->bind('about');

// get stoppoint
$app->post('/xhr/stoppoint', "RetardTransilien\Controller\XhrController::stopPointAction")
->bind('stoppoint');

// get trips
$app->post('/xhr/trips', "RetardTransilien\Controller\XhrController::tripsAction")
->bind('trips');

// get real time info
$app->get('/xhr/ongoing', "RetardTransilien\Controller\XhrController::ongoingAction")
->bind('ongoing');

