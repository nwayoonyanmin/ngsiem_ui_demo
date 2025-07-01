<?php
require_once 'backend/session_check.php';

$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kernellix NGSIEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-1.13.4/datatables.min.css"/>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/dt-1.13.4/datatables.min.js"></script>

    <style>
        body {
            background: #f0f2f5;
            font-family: 'Titillium Web', sans-serif;
            margin: 0;
            padding: 0;
        }

        .header {
            background: #ffffff;
            padding: 20px 30px;
            border-bottom: 1px solid #ddd;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .header h2 {
            color: #ba293a;
            font-weight: bold;
        }

        .user-menu {
            position: relative;
            display: inline-block;
        }

        .user-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
        }

        .dropdown-menu {
            position: absolute;
            top: 50px;
            right: 0;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
            min-width: 180px;
            display: none;
            z-index: 9999;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-menu .username {
            padding: 12px 15px 8px;
            font-weight: bold;
            border-bottom: 1px solid #eee;
        }

        .dropdown-item {
            padding: 10px 15px;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background-color: #f9f9f9;
        }

        .card {
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 24px;
        }

        .form-control, .form-select {
            border-radius: 12px;
        }

        .placeholder-text {
            text-align: center;
            color: #999;
            padding: 20px;
        }

        .chart-card {
            height: 350px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .chart-card canvas {
            max-height: 100%;
            width: 100% !important;
        }

        .total-count-card {
            height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }
        .total-count-card .display-4 {
            font-size: 3.5rem;
            font-weight: bold;
            color: #ba293a;
            line-height: 1;
            margin-bottom: 0;
        }

        .donut-chart-card {
            height: calc(350px - 120px - 24px);
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .donut-chart-card canvas {
             max-height: 100%;
             width: 100% !important;
        }

        #eventTable {
            width: 100% !important;
        }

        .dataTables_wrapper {
            padding: 1rem;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .dataTables_wrapper .dataTables_filter label,
        .dataTables_wrapper .dataTables_length label {
            font-weight: 600;
            color: #333;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 0.5rem;
            border: 1px solid #ced4da;
            padding: 0.375rem 0.75rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }
        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #ba293a;
            box-shadow: 0 0 0 0.25rem rgba(186, 41, 58, 0.25);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5em 1em;
            border-radius: 0.5rem;
            margin: 0 3px;
            cursor: pointer;
            color: #333 !important;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            transition: background-color 0.2s, color 0.2s, border-color 0.2s;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background-color: #ba293a !important;
            color: white !important;
            border-color: #ba293a !important;
        }

        .dataTables_wrapper .dataTables_info {
            padding-top: 0.85em;
            color: #6c757d;
        }
        td.dt-control {
            text-align: center;
            cursor: pointer;
        }
        td.dt-control::before {
            height: 1em;
            width: 1em;
            margin-right: 0.5em;
            display: inline-block;
            box-sizing: content-box;
            background-color: #ba293a;
            color: white;
            border-radius: 1em;
            content: '+';
            font-weight: bold;
            line-height: 1em;
        }
        tr.dt-hasChild td.dt-control::before {
            content: '-';
            background-color: #5cb85c;
        }

        div.slider {
            display: none;
            padding: 10px 0;
            background: #fdfdfd;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            margin-top: 5px;
        }
        div.slider table {
            width: 100%;
            border-collapse: collapse;
        }
        div.slider table td {
            padding: 8px 15px;
            border-bottom: 1px dashed #eee;
            vertical-align: top;
        }
        div.slider table td:first-child {
            font-weight: bold;
            width: 150px;
            color: #555;
        }

        .truncate-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 300px;
            display: block;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center header">
        <h2>Kernellix NGSIEM</h2>
        <div class="user-menu">
            <img src="https://www.iconpacks.net/icons/2/free-user-icon-3296-thumb.png" alt="User" class="user-icon" onclick="toggleMenu()" />
            <div class="dropdown-menu" id="userMenu">
                <div class="username">@<?php echo htmlspecialchars($username); ?></div>
                <div class="dropdown-item">Edit Profile</div>
                <div class="dropdown-item" onclick="window.location.href='backend/logout.php'">Log Out</div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <input type="text" class="form-control" id="customSearchInput" placeholder="Run query here..." />
        </div>
    </div>

    <div class="row mt-3 mb-4">
        <div class="col-md-10">
            <select class="form-select" id="organizationSelect">
                <option selected disabled>Select Organization</option>
                <option value="org1">Organization A</option>
                <option value="org2">Organization B</option>
                <option value="org3">Organization C</option>
                <option value="all">All Organizations</option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" id="timeRangeSelect">
                <option value="24h">Last 24 hours</option>
                <option value="12h">Last 12 hours</option>
                <option value="1h">Last 1 hour</option>
                <option value="7d">Last 7 days</option>
                <option value="30d">Last 30 days</option>
            </select>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-9">
            <div class="card p-3 chart-card">
                <canvas id="barChart"></canvas>
            </div>
        </div>
        <div class="col-md-3 d-flex flex-column">
            <div class="card total-count-card mb-3">
                <h5 class="text-center text-muted mb-2">Total Events</h5>
                <div id="totalEventsCount" class="display-4 text-center">0</div>
            </div>
            <div class="card p-3 donut-chart-card">
                <canvas id="donutChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card p-3 mt-4">
        <h6 class="mb-3">Event Table
            <button id="exportCsvBtn" class="btn btn-sm btn-primary float-end">Export to CSV</button>
        </h6>
        <table id="eventTable" class="table table-striped table-hover responsive nowrap" style="width:100%">
            <thead>
                <tr></tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

</div>

<script>
    function toggleMenu() {
        const menu = document.getElementById('userMenu');
        menu.classList.toggle('show');
    }

    document.addEventListener('click', function(event) {
        const menu = document.getElementById('userMenu');
        const icon = document.querySelector('.user-icon');
        if (!menu.contains(event.target) && event.target !== icon) {
            menu.classList.remove('show');
        }
    });

    let barChartInstance;
    let donutChartInstance;
    let dataTableInstance;

    function format(d) {
        let html = '<div class="slider"><table class="table table-sm">';
        const mainKeys = ['timestamp', 'rule', 'reason', 'severity'];

        for (const key in d) {
            if (key === 'reason') {
                const displayValue = d[key] !== null && d[key] !== undefined ? d[key] : 'N/A';
                html += `<tr><td>${key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ')}:</td><td>${displayValue}</td></tr>`;
                continue;
            }

            if (mainKeys.includes(key) || key.startsWith('_')) {
                continue;
            }
            const displayValue = d[key] !== null && d[key] !== undefined ? d[key] : 'N/A';
            html += `<tr><td>${key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ')}:</td><td>${displayValue}</td></tr>`;
        }
        html += '</table></div>';
        return html;
    }

    function getGroupedTimestamp(timestampStr, timeRange) {
        const date = new Date(timestampStr);
        let year = date.getFullYear();
        let month = String(date.getMonth() + 1).padStart(2, '0');
        let day = String(date.getDate()).padStart(2, '0');
        let hour = String(date.getHours()).padStart(2, '0');
        let minute = String(date.getMinutes()).padStart(2, '0');

        switch(timeRange) {
            case '1h':
                const fiveMinuteInterval = Math.floor(date.getMinutes() / 5) * 5;
                minute = String(fiveMinuteInterval).padStart(2, '0');
                return `${year}-${month}-${day} ${hour}:${minute}:00`;
            case '24h':
            case '12h':
                return `${year}-${month}-${day} ${hour}:00:00`;
            case '7d':
            case '30d':
                return `${year}-${month}-${day} 00:00:00`;
            default:
                return `${year}-${month}-${day} ${hour}:00:00`;
        }
    }

    function loadDashboardData() {
        const selectedTimeRange = document.getElementById('timeRangeSelect').value;
        const selectedOrganization = document.getElementById('organizationSelect').value;

        const url = `backend/get_event_table.php?time_range=${selectedTimeRange}&organization=${selectedOrganization}&source=dynamodb`;

        fetch(url)
            .then(res => res.json())
            .then(events => {
                if (barChartInstance) {
                    barChartInstance.destroy();
                    barChartInstance = null;
                }
                if (donutChartInstance) {
                    donutChartInstance.destroy();
                    donutChartInstance = null;
                }

                if (dataTableInstance) {
                    dataTableInstance.destroy();
                    dataTableInstance = null;
                    $('#eventTable').empty();
                    $('#eventTable').append('<thead><tr></tr></thead><tbody></tbody>');
                }

                const totalEvents = events.length;
                document.getElementById('totalEventsCount').innerText = totalEvents;

                const timeBuckets = {};
                const uniqueRules = new Set();

                if (Array.isArray(events) && events.length > 0) {
                    events.forEach(event => {
                        const groupedTimestamp = getGroupedTimestamp(event.timestamp, selectedTimeRange);
                        if (!timeBuckets[groupedTimestamp]) {
                            timeBuckets[groupedTimestamp] = {};
                        }
                        timeBuckets[groupedTimestamp][event.rule] = (timeBuckets[groupedTimestamp][event.rule] || 0) + 1;
                        uniqueRules.add(event.rule);
                    });
                }

                const sortedTimestamps = Object.keys(timeBuckets).sort();
                const rulesArray = Array.from(uniqueRules).sort();

                const predefinedColors = [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
                    '#c26fef', '#A0522D', '#20B2AA', '#DEB887', '#5F9EA0', '#D2691E',
                    '#6495ED', '#DC143C', '#00BFFF', '#B22222', '#DAA520', '#ADFF2F'
                ];
                const ruleColors = {};
                rulesArray.forEach((rule, index) => {
                    ruleColors[rule] = predefinedColors[index % predefinedColors.length];
                });

                const datasets = rulesArray.map(rule => {
                    return {
                        label: rule,
                        data: sortedTimestamps.map(ts => timeBuckets[ts][rule] || 0),
                        backgroundColor: ruleColors[rule],
                        stack: 'EventStack',
                    };
                });

                const barChartData = datasets.length ? datasets : [{ label: 'No Data', data: [0], backgroundColor: '#ccc' }];
                const barChartLabels = sortedTimestamps.length ? sortedTimestamps : ['No Data'];

                barChartInstance = new Chart(document.getElementById("barChart"), {
                    type: 'bar',
                    data: {
                        labels: barChartLabels,
                        datasets: barChartData
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                title: {
                                    display: true,
                                    text: 'Time'
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Event Count'
                                }
                            }
                        }
                    }
                });

                const severityCounts = {};
                if (Array.isArray(events) && events.length > 0) {
                    events.forEach(event => {
                        severityCounts[event.severity] = (severityCounts[event.severity] || 0) + 1;
                    });
                }

                const sortedSeverityLabels = ['Informational', 'Low', 'Medium', 'High', 'Critical'];
                const sortedSeverityValues = sortedSeverityLabels.map(label => severityCounts[label] || 0);

                donutChartInstance = new Chart(document.getElementById("donutChart"), {
                    type: 'doughnut',
                    data: {
                        labels: sortedSeverityLabels,
                        datasets: [{
                            label: 'Severity Distribution',
                            data: sortedSeverityValues,
                            backgroundColor: [
                                '#28a745',
                                '#ffc107',
                                '#fd8c00',
                                '#dc3545',
                                '#8b0000'
                            ],
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });

                let columnsConfig = [
                    {
                        className: 'dt-control',
                        orderable: false,
                        data: null,
                        defaultContent: ''
                    },
                    { title: 'Timestamp', data: 'timestamp' },
                    {
                        title: 'Rule',
                        data: 'rule',
                    },
                    {
                        title: 'Reason',
                        data: 'reason',
                        render: function (data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                const maxLength = 100;
                                return data.length > maxLength ?
                                    '<span class="truncate-text" title="' + data + '">' + data.substr(0, maxLength) + '...</span>' :
                                    data;
                            }
                            return data;
                        },
                        width: '40%'
                    },
                    { title: 'Severity', data: 'severity' }
                ];

                dataTableInstance = new DataTable('#eventTable', {
                    data: events,
                    columns: columnsConfig,
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50, 100],
                    responsive: true,
                    order: [[1, 'desc']]
                });

                $('#eventTable tbody').off('click', 'td.dt-control').on('click', 'td.dt-control', function () {
                    var tr = $(this).closest('tr');
                    var row = dataTableInstance.row(tr);

                    if (row.child.isShown()) {
                        $('div.slider', row.child()).slideUp(function () {
                            row.child.hide();
                            tr.removeClass('dt-hasChild');
                        });
                    }
                    else {
                        row.child(format(row.data())).show();
                        tr.addClass('dt-hasChild');
                        $('div.slider', row.child()).slideDown();
                    }
                });
            })
            .catch(err => {
                console.error('Error loading dashboard data:', err);
                alert('Failed to load dashboard data. Please try again.');
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const timeRangeSelect = document.getElementById('timeRangeSelect');
        if (timeRangeSelect) {
            timeRangeSelect.value = '24h';
            timeRangeSelect.addEventListener('change', loadDashboardData);
        }

        const organizationSelect = document.getElementById('organizationSelect');
        if (organizationSelect) {
            organizationSelect.value = 'all';
            organizationSelect.addEventListener('change', loadDashboardData);
        }

        loadDashboardData();
    });

    $(document).ready(function() {
        $('#customSearchInput').on('keyup', function () {
            if (dataTableInstance) {
                dataTableInstance.search(this.value).draw();
            }
        });

        document.getElementById('exportCsvBtn').addEventListener('click', function() {
            alert('Export to CSV button clicked! (Functionality not yet implemented)');
        });
    });
</script>

</body>
</html>