$(document).ready(function() {
    updateCount('sim_numbers');
    updateCount('imei_numbers');
    $('#stopAllProcesses').prop('disabled', true);
    
    // Add initial animations
    gsap.from('.card', {duration: 0.5, y: 50, opacity: 0, stagger: 0.2, ease: 'power2.out'});

    // Initialize charts
    initializeCharts();
});

let currentJobId = null;
let pollingInterval = null;
let startTime = null;
let smsStatusChart = null;
let processingRateChart = null;
let processedCountHistory = [];

function initializeCharts() {
    // SMS Status Chart
    const smsStatusCtx = document.getElementById('smsStatusChart').getContext('2d');
    smsStatusChart = new Chart(smsStatusCtx, {
        type: 'pie',
        data: {
            labels: ['Successful', 'Failed'],
            datasets: [{
                data: [0, 0],
                backgroundColor: ['#28a745', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'SMS Status'
            }
        }
    });

    // Processing Rate Chart
    const processingRateCtx = document.getElementById('processingRateChart').getContext('2d');
    processingRateChart = new Chart(processingRateCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'SMS/min',
                data: [],
                borderColor: '#007bff',
                fill: false
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Processing Rate'
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Time'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'SMS/min'
                    }
                }
            }
        }
    });
}

$('#smsForm').on('submit', function(e) {
    e.preventDefault();
    freezeFields();
    showLoadingAlert('Queuing SMS job...');
    
    const formData = {
        sim_numbers: $('#sim_numbers').val(),
        from_field: $('#from_field').val(),
        message_text: $('#message_text').val()
    };

    $.ajax({
        url: 'process_sms.php',
        method: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('SMS sending job queued', 'success');
                updateActivityLog('SMS sending job queued');
                currentJobId = response.jobId;
                startTime = new Date();
                startPolling(response.jobId);
                $('#stopAllProcesses').prop('disabled', false);
                
                // Reset charts
                smsStatusChart.data.datasets[0].data = [0, 0];
                smsStatusChart.update();
                processingRateChart.data.labels = [];
                processingRateChart.data.datasets[0].data = [];
                processingRateChart.update();
                processedCountHistory = [];

                // Add animation for starting process
                gsap.to('#progressBar', {width: '0%', duration: 0.5, ease: 'power2.out'});
            } else {
                hideLoadingAlert();
                showAlert(response.message || 'Error queuing SMS job', 'danger');
                unfreezeFields();
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            hideLoadingAlert();
            showAlert('Error queuing SMS job: ' + textStatus + ' - ' + errorThrown, 'danger');
            unfreezeFields();
            console.error('Error response:', jqXHR.responseText);
        }
    });
});

$('#stopAllProcesses').on('click', function() {
    $(this).prop('disabled', true);
    showLoadingAlert('Stopping all processes...');
    $.ajax({
        url: 'stop_processes.php',
        method: 'POST',
        data: JSON.stringify({ jobId: currentJobId }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            hideLoadingAlert();
            updateActivityLog('All processes stopped');
            showAlert('All processes stopped', 'warning');
            $('#processStatus').text('Stopped');
            updateElapsedTime();
            unfreezeFields();
            
            // Add animation for stopping process
            gsap.to('#progressBar', {width: '100%', backgroundColor: '#dc3545', duration: 0.5, ease: 'power2.out'});
        },
        error: function() {
            hideLoadingAlert();
            showAlert('Error stopping processes', 'danger');
            $(this).prop('disabled', false);
        }
    });
});

$('#imeiForm').on('submit', function(e) {
    e.preventDefault();
    showLoadingAlert('Searching IMEI...');
    $.ajax({
        url: 'search_imei.php',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            hideLoadingAlert();
            $('#imeiResults').html(response);
            showAlert('IMEI search completed', 'success');
            updateActivityLog('IMEI search completed');
        },
        error: function() {
            hideLoadingAlert();
            showAlert('Error during IMEI search', 'danger');
        }
    });
});

$('#retrieveSims').on('click', function() {
    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Retrieving...');
    showLoadingAlert('Retrieving SIM cards...');
    $.ajax({
        url: 'retrieve_sims.php',
        method: 'GET',
        success: function(response) {
            hideLoadingAlert();
            const data = JSON.parse(response);
            $('#simCardCount').text(data.count + ' SIM cards retrieved');
            $('#simCardResults').html(data.html);
            $('#copySimNumbers').show();
            showAlert('SIM cards retrieved successfully', 'success');
            updateActivityLog('SIM cards retrieved');
        },
        error: function() {
            hideLoadingAlert();
            showAlert('Error retrieving SIM cards', 'danger');
        },
        complete: function() {
            $('#retrieveSims').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Retrieve All SIM Cards');
        }
    });
});

$('#clearSimRetrieval').on('click', function() {
    $('#simCardCount').text('');
    $('#simCardResults').empty();
    $('#copySimNumbers').hide();
});

function startPolling(jobId) {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
    pollingInterval = setInterval(function() {
        pollJobStatus(jobId);
    }, 5000); // Poll every 5 seconds
}

function pollJobStatus(jobId) {
    $.ajax({
        url: 'get_job_status.php',
        method: 'GET',
        data: { jobId: jobId },
        dataType: 'json',
        success: function(response) {
            updateDashboard(response);
            if (response.status === 'completed' || response.status === 'stopped') {
                clearInterval(pollingInterval);
                hideLoadingAlert();
                showAlert('SMS sending job ' + response.status, 'success');
                updateActivityLog('SMS sending job ' + response.status);
                unfreezeFields();
                $('#stopAllProcesses').prop('disabled', true);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error polling job status:', textStatus, errorThrown);
        }
    });
}

function updateDashboard(data) {
    $('#lastSimProcessed').text(data.lastProcessedSim || 'N/A');
    $('#totalSuccessful').text(data.successCount);
    $('#totalFailed').text(data.failCount);
    $('#processStatus').text(data.status);
    
    const progress = (data.processedCount / data.totalCount) * 100;
    gsap.to('#progressBar', {width: progress + '%', duration: 0.5, ease: 'power2.out'});
    $('#progressBar').attr('aria-valuenow', progress).text(Math.round(progress) + '%');

    updateElapsedTime();

    // Update SMS Status Chart
    smsStatusChart.data.datasets[0].data = [data.successCount, data.failCount];
    smsStatusChart.update();

    // Update Processing Rate Chart
    const currentTime = new Date();
    const elapsedMinutes = (currentTime - startTime) / 60000;
    processedCountHistory.push(data.processedCount);
    if (processedCountHistory.length > 1) {
        const rate = (processedCountHistory[processedCountHistory.length - 1] - processedCountHistory[processedCountHistory.length - 2]) / (5 / 60); // SMS per minute
        processingRateChart.data.labels.push(new Date().toLocaleTimeString());
        processingRateChart.data.datasets[0].data.push(rate);
        if (processingRateChart.data.labels.length > 10) {
            processingRateChart.data.labels.shift();
            processingRateChart.data.datasets[0].data.shift();
        }
        processingRateChart.update();
    }
}

function updateElapsedTime() {
    if (startTime) {
        const now = new Date();
        const elapsedTime = Math.floor((now - startTime) / 1000); // in seconds
        const hours = Math.floor(elapsedTime / 3600);
        const minutes = Math.floor((elapsedTime % 3600) / 60);
        const seconds = elapsedTime % 60;
        $('#timeElapsed').text(
            `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
        );
    }
}

function showLoadingAlert(message) {
    $('#loadingAlert').remove();
    const alertHtml = `
        <div id="loadingAlert" class="alert alert-info alert-dismissible fade show" role="alert">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            ${message}
        </div>
    `;
    $('#alertArea').append(alertHtml);
}

function hideLoadingAlert() {
    $('#loadingAlert').remove();
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    $('#alertArea').append(alertHtml);
    
    gsap.from('#alertArea .alert', {y: -50, opacity: 0, duration: 0.3, ease: 'power2.out'});

    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}

function updateActivityLog(message) {
    const logEntry = `<div class="log-entry">${new Date().toLocaleString()} - ${message}</div>`;
    $('#activityLog').append(logEntry);
    $('#activityLog').scrollTop($('#activityLog')[0].scrollHeight);
}

function freezeFields() {
    $('#smsForm input, #smsForm textarea, #smsForm button:not(#stopAllProcesses)').prop('disabled', true);
    gsap.to('#smsForm input, #smsForm textarea, #smsForm button:not(#stopAllProcesses)', {opacity: 0.6, duration: 0.3, ease: 'power2.out'});
}

function unfreezeFields() {
    $('#smsForm input, #smsForm textarea, #smsForm button').prop('disabled', false);
    gsap.to('#smsForm input, #smsForm textarea, #smsForm button', {opacity: 1, duration: 0.3, ease: 'power2.out'});
}

function clearForm(formId) {
    $(`#${formId}`)[0].reset();
    if (formId === 'imeiForm') {
        $('#imeiResults').empty();
    }
    updateCount(formId === 'smsForm' ? 'sim_numbers' : 'imei_numbers');
}

function downloadLog() {
    window.location.href = 'download_log.php';
}

function clearLog() {
    $.ajax({
        url: 'clear_log.php',
        method: 'POST',
        success: function() {
            $('#activityLog').empty();
            showAlert('Log cleared successfully', 'success');
        }
    });
}

function copyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    showAlert(`${text.split('\n').length} IDs copied to clipboard`, 'success');
    updateActivityLog(`${text.split('\n').length} IDs copied to clipboard`);
}

function updateCount(fieldId) {
    const count = $(`#${fieldId}`).val().split('\n').filter(line => line.trim() !== '').length;
    $(`#${fieldId === 'sim_numbers' ? 'simCount' : 'imeiCount'}`).text(`${count} ${fieldId === 'sim_numbers' ? 'SIM IDs' : 'IMEI numbers'} entered`);
}

$('#sim_numbers, #imei_numbers').on('input', function() {
    updateCount($(this).attr('id'));
});

$(document).on('click', '#copyImeiResults', function() {
    const simIds = $('.sim-id').map(function() {
        return $(this).text();
    }).get().filter(id => id !== 'No SIM found for this IMEI');
    
    copyToClipboard(simIds.join('\n'));
});

$(document).on('click', '#copySimNumbers', function() {
    const simIds = $('.sim-number').map(function() {
        return $(this).text().split(' ')[0];
    }).get();
    
    copyToClipboard(simIds.join('\n'));
});

function updateLog() {
    $.ajax({
        url: 'get_log.php',
        method: 'GET',
        success: function(response) {
            $('#activityLog').html(response);
            $('#activityLog').scrollTop($('#activityLog')[0].scrollHeight);
        }
    });
}

setInterval(updateLog, 5000);
