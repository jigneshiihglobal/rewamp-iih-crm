$(document).ready(function () {
    // Customer Project list js code
    var apiUrl = `${pathUrl}/api/crm/get_project_logs`;
    var apiProjectUrl = `${pathUrl}/api/crm/get_hrms_all_projects`;

    $('#select_project_list').select2({
        placeholder: 'Select Projects',
        containerCssClass: 'select-sm',
        dropdownParent: $('#project_log_list_model').get(0),
    });

    var project_list_id = '';
    var project_client_id = '';
    var old_project_id = '';
    var modal_open = 0;
    var Graph_chart = '';

    // Initialize Flatpickr with default date range set to the current month
    let dateRangePicker = flatpickr("#dateRange", {
        mode: "range",
        dateFormat: "d-m-Y", // Set the format to dd-mm-yyyy
        onClose: function (selectedDates, dateStr, instance) {
            // console.log('Selected date range:', dateStr);
        },
        onReady: function (selectedDates, dateStr, instance) {
            // Get the current date
            const currentDate = new Date();

            // Get the first and last day of the current month
            const firstDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            const lastDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);

            // Set the default date range to the current month
            instance.setDate([firstDayOfMonth, lastDayOfMonth]);
        }
    });


    $(document).on('click', '.projectListModal', function (e) {
        // Get parameters from the clicked element
        let id = $(this).data('id');
        let client_id = $(this).data('client-id');
        let project_id = $(this).data('project-id');
        let invoice_date = $(this).data('invoice-date');

        project_list_id = id;
        project_client_id = client_id;
        old_project_id = project_id;
        modal_open = 0;

        if (invoice_date) {
            let parts = invoice_date.split("/");
            let day = parts[0]; // Day (not needed)
            let month = parts[1]; // Month
            let year = parts[2]; // Year

            // Construct start and end dates
            let startDate = new Date(year, month - 1, 1); // First day of the month
            let lastDay = new Date(year, month, 0).getDate(); // Get last day of the month
            let endDate = new Date(year, month - 1, lastDay); // Last date of the month

            let formattedStartDate = `01-${month}-${year}`;
            let formattedEndDate = `${lastDay}-${month}-${year}`;

            let dateRange = `${formattedStartDate} to ${formattedEndDate}`;
            $("#dateRange").val(dateRange); // Set the value to the input field

            // Update Flatpickr with new date range
            dateRangePicker.setDate([startDate, endDate]);
        }

        fetchAndUpdateLogs(id, client_id, project_id, modal_open);
    });

    // // Event listener for the change on the project_list element
    $(document).on('change', '.select_project_list', function (e) {
        let id = $(this).data('id'); // Assuming you still want the ID for some reason
        let client_id = $(this).data('client-id'); // Adjust if necessary
        let project_id = $(this).val(); // Get the selected project ID from the dropdown

        id = project_list_id;
        client_id = project_client_id;
        modal_open = 1;

        // Fetch and update logs without showing the modal again
        fetchAndUpdateLogs(id, client_id, project_id, modal_open);
    });

    // Event listener for the change on the project_list element
    $(document).on('change', '.select_date_range', function (e) {
        let id = $(this).data('id'); // Assuming you still want the ID for some reason
        let client_id = $(this).data('client-id'); // Adjust if necessary
        let project_id = $("#select_project_list").val(); // Get the selected project ID from the dropdown

        id = project_list_id;
        client_id = project_client_id;
        modal_open = 1;

        // Fetch and update logs without showing the modal again
        fetchAndUpdateLogs(id, client_id, project_id, modal_open);
    });

    // Function to handle the AJAX call and update modal content
    function fetchAndUpdateLogs(id, client_id, project_id, modal_open, invoice_date) {
        let dateRange = $("#dateRange").val(); // Get selected date range
        let dates = dateRange ? dateRange.split(" to ") : [];
        let formattedDates = []; // Initialize an empty array

        if (dates.length === 2) {
            formattedDates = dates.map(date => {
                let parts = date.split("-"); // Split the date into day, month, and year
                let shortYear = parts[2].slice(-2); // Get last two digits of the year
                return `${parts[0]}-${parts[1]}-${shortYear}`;
            });
        }

        // Set default values if formattedDates is empty
        let startDate = formattedDates[0];
        let endDate = formattedDates[1];

        // Call the second API after the first one succeeds
        if (!modal_open) {
            fetchProjects(project_id, client_id); // Call this if needed, adjust as necessary
        }

        if (id) {
            // First API call
            $.ajax({
                url: apiUrl,
                method: 'GET',
                headers: {
                    'Authorization': auth_token
                },
                data: {
                    project_id: project_id,
                    startDate: startDate,
                    endDate: endDate
                },
                success: function (response) {
                    // Check if ProjectTotalTimeLogs exists
                    if (!response.ProjectTotalTimeLogs || Object.keys(response
                        .ProjectTotalTimeLogs).length === 0) {
                        $('#projectLogListModal').html(
                            '<p>No logs available for this date range.</p>');
                        Graph_chart = [];
                        loadPieChart(Graph_chart);
                        return; // Exit if no logs
                    }

                    if (response.Graph_Logs.length > 0) {
                        Graph_chart = response.Graph_Logs;
                        loadPieChart(Graph_chart);
                    } else {
                        $('#projectGraph').empty();
                    }

                    // Create HTML for displaying project logs
                    let html = '<div class="accordion" id="projectTimeAccordion">';

                    // Process each date in ProjectTotalTimeLogs
                    for (let date in response.ProjectTotalTimeLogs) {
                        if (response.ProjectTotalTimeLogs.hasOwnProperty(date)) {
                            let totalLog = response.ProjectTotalTimeLogs[date];
                            let totalTime = totalLog
                                .total_date_wise_total; // Total time in milliseconds

                            // Convert total time from milliseconds to hours and minutes
                            let {
                                hours,
                                minutes
                            } = convertMillisecondsToHoursMinutes(totalTime);

                            // Format the date to "DD Month YYYY"
                            let formattedDate = formatDateDDMMYYYY(date);

                            // Prepare the user rows HTML
                            let userRows = '';
                            totalLog.users.forEach(user => {
                                let userTime = user.user_total_time; // In milliseconds
                                let {
                                    hours: userHours,
                                    minutes: userMinutes
                                } = convertMillisecondsToHoursMinutes(userTime);
                                userRows += `<tr>
                                                            <td>${user.user_name}</td>
                                                            <td>${userHours} hour ${userMinutes} min</td>
                                                        </tr>`;
                            });

                            html += `
                                            <div class="accordion-item mb-1">
                                                <h2 class="accordion-header" id="heading-${date.replace(/-/g, '')}">
                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#user-list-${date.replace(/-/g, '')}" aria-expanded="false" aria-controls="user-list-${date.replace(/-/g, '')}">
                                                        <strong style="width: 50%;justify-content: start;display: flex;">${formattedDate}</strong>
                                                        <span style="width: 50%;justify-content: start;display: flex;" class="mx-auto text-center">${hours} hour ${minutes} min</span>
                                                    </button>
                                                </h2>
                                                <div id="user-list-${date.replace(/-/g, '')}" class="accordion-collapse collapse" aria-labelledby="heading-${date.replace(/-/g, '')}" data-bs-parent="#projectTimeAccordion">
                                                    <div class="accordion-body">
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>User Name</th>
                                                                    <th>Time Logged</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                ${userRows}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>`;
                        }
                    }

                    html += '</div>';

                    // Update the modal content without reopening it
                    $('#project_log_list_model #projectLogListModal').html(html);
                },
                error: function (xhr, status, error) {
                    toastr.error(xhr.responseJSON?.message ?? 'Project log api not responding', error);
                }
            });
        }
    }

    // Function to format date from DD-MM-YYYY to "DD Month YYYY"
    function formatDateDDMMYYYY(dateString) {
        const [day, month, year] = dateString.split("-");
        const monthNames = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        return `${day} ${monthNames[parseInt(month) - 1]} ${year}`;
    }

    // Function to fetch projects from the second API
    function fetchProjects(project_id, client_id) {
        if (!project_id) return;

        $.ajax({
            url: apiProjectUrl, // API URL
            method: 'GET',
            headers: {
                'Authorization': auth_token
            },
            success: function (response) {
                let projects_list = response.projects ||
                    []; // Ensure response contains projects

                // Clear previous options
                $('#select_project_list').empty();

                if (projects_list.length > 0) {
                    // Populate the dropdown with API data
                    projects_list.forEach(project => {
                        const isSelected = project.id === project_id ?
                            'selected' : '';
                        if (project.crm_contact_id == client_id) {
                            $('#select_project_list').append(
                                `<option value="${project.id}" data-project-id="${project.id}" data-id="${project.id}" data-client-id="${project.crm_contact_id}" ${isSelected}>${project.title}</option>`
                            );
                        }
                    });
                } else {
                    // Show no options if no data
                    $('#select_project_list').append(
                        `<option disabled>No projects available</option>`);
                }
                if (!$('#project_log_list_model').is(':visible')) {
                    $('#project_log_list_model').modal('show');
                }
            },
            error: function (xhr, status, error) {
                toastr.error(xhr.responseJSON?.message ?? 'Project list api not responding', error);
            }
        });
    }

    // Toggle time log visibility on date click
    $(document).on('click', '.date-log', function () {
        $(this).find('.time-log-list').toggle(); // Toggle the visibility of the time logs
    });

    // Random Color Function
    // function getRandomColor() {
    //     return `#${Math.floor(Math.random() * 16777215).toString(16).padStart(6, '0')}`;
    // }
    var myPieChart = '';

    function loadPieChart(Graph_chart) {
        // Show the spinner before loading the chart
        $('#projectGraph').block({
            message: '<div class="spinner-border text-warning" role="status"></div>',
            css: {
                backgroundColor: 'transparent',
                border: '0',
                width: '100%', // Cover the entire area
                height: '100%', // Cover the entire area
                position: 'absolute', // Position it absolutely
                top: '0', // Align to top
                left: '0', // Align to left
                display: 'flex', // Use flexbox to center the spinner
                alignItems: 'center', // Center vertically
                justifyContent: 'center' // Center horizontally
            },
            overlayCSS: {
                backgroundColor: '#fff',
                opacity: 0.8,
                cursor: 'wait', // Show wait cursor
                position: 'absolute', // Positioning of overlay
                top: '0', // Align to top
                left: '0', // Align to left
                width: '100%', // Full width
                height: '100%', // Full height
            }
        });

        // Destroy the existing chart if it exists
        if (myPieChart) {
            myPieChart.destroy();
        }

        const ctx = document.getElementById('pieChart').getContext('2d');

        // Check if Graph_chart is defined and is an array
        if (!Graph_chart || !Array.isArray(Graph_chart)) {
            console.error("Graph_chart is not defined or not an array");
            $('#projectGraph').unblock(); // Hide spinner on error
            return; // Exit the function if there is an error
        }

        // Prepare labels and data
        const labels = Graph_chart.map(log => {
            var data_time = convertMillisecondsToHoursMinutes(log.total_time);
            return log.user_name + " ( " + data_time.hours + " hour " + data_time.minutes + " min)";
        });

        // Calculate total time for percentage calculations
        const totalTime = Graph_chart.reduce((sum, log) => sum + log.total_time, 0);

        const backgroundColorsCode = [
            "#FF5733", "#33FF57", "#3357FF", "#FF33A8", "#A833FF", "#FFD700",
            "#FF8C00", "#00CED1", "#8B0000", "#228B22", "#4682B4", "#D2691E"
        ];

        if (Graph_chart.length > 0 && totalTime > 0) {
            const data = Graph_chart.map(log => ((log.total_time / totalTime) * 100).toFixed(2));

            // Create pie chart
            myPieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColorsCode,
                        hoverOffset: 25
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,  // Allows manual height adjustment
                    aspectRatio: 2,  // Increase to make the chart smaller
                    layout: {
                        padding: 10 // Adjust padding if needed
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                font: { size: 14 },
                                //usePointStyle: true, // Use a smaller point style
                                boxWidth: 18, // Reduce box width
                                padding: 18 // Adjust padding if needed

                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = context.raw;
                                    return ' ' + value + '%';
                                }
                            }
                        }
                    }
                },
            });
            ctx.canvas.style.cursor = 'pointer';
        } else {
            // Set default chart for zero data
            myPieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['No Data'],
                    datasets: [{
                        data: [0.01],
                        backgroundColor: ['#CCCCCC']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function () { return ' 0.00%'; }
                            }
                        }
                    }
                }
            });
            ctx.canvas.style.cursor = 'pointer';
        }

        // Hide the spinner after the chart is fully loaded
        setTimeout(() => {
            $('#projectGraph').unblock();
        }, 500); // Add slight delay to ensure smooth transition
    }


    // Call this function to load the pie chart when the Project Graph tab is activated
    document.getElementById('project-graph-tab').addEventListener('click', () => loadPieChart(Graph_chart));

    // Function to convert milliseconds to hours and minutes
    function convertMillisecondsToHoursMinutes(milliseconds) {
        let totalMinutes = Math.floor(milliseconds / 60000);
        let hours = Math.floor(totalMinutes / 60);
        let minutes = totalMinutes % 60;
        return {
            hours,
            minutes
        };
    }

    // END Customer Project list js code

});
