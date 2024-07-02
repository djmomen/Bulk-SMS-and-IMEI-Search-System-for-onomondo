<?php
require_once 'functions.php';
date_default_timezone_set('Asia/Riyadh');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk SMS and IMEI Search System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <h1 class="my-4">Bulk SMS and IMEI Search System</h1>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-sms"></i> Send Bulk SMS</h5>
                    </div>
                    <div class="card-body">
                        <form id="smsForm">
                            <div class="mb-3">
                                <label for="sim_numbers" class="form-label">SIM IDs (one per line)</label>
                                <textarea class="form-control" id="sim_numbers" name="sim_numbers" rows="5" required></textarea>
                                <small id="simCount" class="form-text text-muted">0 SIM IDs entered</small>
                            </div>
                            <div class="mb-3">
                                <label for="from_field" class="form-label">From</label>
                                <input type="number" class="form-control" id="from_field" name="from_field" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="message_text" class="form-label">Message</label>
                                <textarea class="form-control" id="message_text" name="message_text" rows="3" required></textarea>
                                <small class="form-text text-muted">Please add two spaces before the command for Teltonika devices.</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" id="sendSmsBtn"><i class="fas fa-paper-plane"></i> Send SMS</button>
                            <button type="button" class="btn btn-danger" id="stopAllProcesses" disabled><i class="fas fa-stop-circle"></i> Stop All Processes</button>
                            <button type="button" class="btn btn-secondary" onclick="clearForm('smsForm')"><i class="fas fa-eraser"></i> Clear</button>
                        </form>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-search"></i> IMEI Search</h5>
                    </div>
                    <div class="card-body">
                        <form id="imeiForm">
                            <div class="mb-3">
                                <label for="imei_numbers" class="form-label">IMEI Numbers (one per line)</label>
                                <textarea class="form-control" id="imei_numbers" name="imei_numbers" rows="5" required></textarea>
                                <small id="imeiCount" class="form-text text-muted">0 IMEI numbers entered</small>
                            </div>
                            <button type="submit" class="btn btn-info text-white"><i class="fas fa-search"></i> Search IMEI</button>
                            <button type="button" class="btn btn-secondary" onclick="clearForm('imeiForm')"><i class="fas fa-eraser"></i> Clear</button>
                        </form>
                        <div id="imeiResults" class="mt-3"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-tachometer-alt"></i> Dashboard</h5>
                    </div>
                    <div class="card-body">
                        <p>Last SIM number processed: <span id="lastSimProcessed">N/A</span></p>
                        <p>Total Successful SMS: <span id="totalSuccessful">0</span></p>
                        <p>Total Failed SMS: <span id="totalFailed">0</span></p>
                        <p>Process Status: <span id="processStatus">N/A</span></p>
                        <p>Time Elapsed: <span id="timeElapsed">00:00:00</span></p>
                        <div class="progress mb-3">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <canvas id="smsStatusChart"></canvas>
                            </div>
                            <div class="col-md-6">
                                <canvas id="processingRateChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-sim-card"></i> SIM Card Retrieval</h5>
                    </div>
                    <div class="card-body">
                        <button id="retrieveSims" class="btn btn-success"><i class="fas fa-sync-alt"></i> Retrieve All SIM Cards</button>
                        <button id="clearSimRetrieval" class="btn btn-secondary mt-2"><i class="fas fa-eraser"></i> Clear Results</button>
                        <div id="simCardCount" class="mt-2"></div>
                        <button id="copySimNumbers" class="btn btn-secondary mt-2" style="display: none;"><i class="fas fa-copy"></i> Copy SIM IDs</button>
                        <div id="simCardResults" class="mt-3" style="max-height: 300px; overflow-y: auto;"></div>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-list-alt"></i> Activity Log</h5>
                    </div>
                    <div class="card-body">
                        <div id="activityLog" style="height: 200px; overflow-y: scroll;"></div>
                        <button class="btn btn-secondary mt-2" onclick="downloadLog()"><i class="fas fa-download"></i> Download Log</button>
                        <button class="btn btn-secondary mt-2" onclick="clearLog()"><i class="fas fa-trash-alt"></i> Clear Log</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="alertArea" class="position-fixed top-0 end-0 p-3" style="z-index: 11"></div>

    <footer class="bg-dark text-white text-center py-3 mt-4">
        <div class="container">
            <p class="mb-0">Made by Momen Rashad</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
