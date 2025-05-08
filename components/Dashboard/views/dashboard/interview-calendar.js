/**
 * Interview Calendar Script
 * Handles the interactive calendar functionality for the My Interviews section
 */

document.addEventListener('DOMContentLoaded', function() {
    // Calendar container reference
    const calendarContainer = document.getElementById('interviewCalendarContainer');
    const calendarToggleBtn = document.getElementById('openCalendarBtn');
    const interviewBanner = document.getElementById('interviewDetailsBanner');
    let calendar;
    let userInterviews = [];

    // Toggle calendar visibility
    calendarToggleBtn.addEventListener('click', function() {
        if (calendarContainer.classList.contains('calendar-visible')) {
            hideCalendar();
        } else {
            showCalendar();
        }
    });

    // Function to show the calendar
    function showCalendar() {
        calendarContainer.classList.add('calendar-visible');
        calendarToggleBtn.textContent = 'Close Calendar';
        initializeCalendar();
    }

    // Function to hide the calendar
    function hideCalendar() {
        calendarContainer.classList.remove('calendar-visible');
        calendarToggleBtn.textContent = 'Open Calendar';
        // Hide the banner if it's visible
        if (interviewBanner.classList.contains('banner-visible')) {
            interviewBanner.classList.remove('banner-visible');
        }
    }

    // Initialize the FullCalendar instance
    function initializeCalendar() {
        if (calendar) return; // Don't initialize if already exists

        // Fetch interview data
        fetchInterviews().then(() => {
            const calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                events: formatInterviewsForCalendar(),
                eventClick: function(info) {
                    // Handle click on the interview event
                    const interviewId = info.event.extendedProps.interviewId;
                    if (interviewId) {
                        // Find the interview in our data
                        const interview = userInterviews.find(i => i.id == interviewId);
                        if (interview) {
                            // Show interview in the sliding banner
                            showInterviewInBanner(interview);
                        } else {
                            // If not found in local data, fetch from server
                            fetchInterviewDetails(interviewId);
                        }
                    }
                },
                dateClick: function(info) {
                    handleDateClick(info.date);
                },
                eventDidMount: function(info) {
                    // Add tooltip to events
                    const tooltip = new bootstrap.Tooltip(info.el, {
                        title: info.event.title,
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body'
                    });
                }
            });
            calendar.render();
        });
    }

    // Format interviews for calendar display
    function formatInterviewsForCalendar() {
        return userInterviews.map(interview => {
            // Ensure consistent date handling for calendar events
            const interviewDate = interview.interview_date;
            
            return {
                title: interview.position_title,
                start: interviewDate,
                allDay: false,
                backgroundColor: getStatusColor(interview.status),
                borderColor: getStatusColor(interview.status),
                extendedProps: {
                    interviewId: interview.id,
                    interviewDate: interviewDate.split('T')[0] // Store the date part for easier comparison
                }
            };
        });
    }

    // Get color based on interview status
    function getStatusColor(status) {
        switch(status.toLowerCase()) {
            case 'scheduled': return '#ff9900';
            case 'completed': return '#198754';
            case 'cancelled': return '#dc3545';
            default: return '#3E5C76';
        }
    }

    // Handle date click to show interviews for that day
    function handleDateClick(date) {
        // Format the date to match the interview date format (YYYY-MM-DD)
        // Use local date to avoid timezone issues
        const clickedDate = formatDateToYYYYMMDD(date);
        
        // Debug log to help troubleshoot
        console.log('Clicked date:', clickedDate);
        console.log('Available interviews:', userInterviews);
        
        // Filter interviews for the clicked date
        const interviewsOnDate = userInterviews.filter(interview => {
            const interviewDate = interview.interview_date.split('T')[0];
            console.log('Comparing interview date:', interviewDate, 'with clicked date:', clickedDate);
            return interviewDate === clickedDate;
        });

        console.log('Interviews found for this date:', interviewsOnDate.length);
        
        // Show the banner with interviews or "No Interviews" message
        showInterviewBanner(interviewsOnDate, clickedDate);
    }
    
    // Helper function to format date to YYYY-MM-DD in local timezone
    function formatDateToYYYYMMDD(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Show the sliding banner with interview details
    function showInterviewBanner(interviews, dateString) {
        const bannerContent = document.getElementById('interviewBannerContent');
        
        // Handle both single interview object and array of interviews
        const isMultipleInterviews = Array.isArray(interviews);
        
        // If we have dateString, format a date from it
        let formattedDate = '';
        if (dateString) {
            // Create date object correctly from YYYY-MM-DD format
            // Use the date constructor with year, month-1, day to avoid timezone issues
            const dateParts = dateString.split('-');
            const dateObj = new Date(parseInt(dateParts[0]), parseInt(dateParts[1])-1, parseInt(dateParts[2]));
            
            formattedDate = dateObj.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        } 
        // If no dateString but we have a single interview, format date from interview
        else if (!isMultipleInterviews && interviews.interview_date) {
            const dateObj = new Date(interviews.interview_date);
            formattedDate = dateObj.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }
        
        console.log('Showing banner for date:', dateString || 'direct interview click', 'formatted as:', formattedDate);

        // Clear previous content
        bannerContent.innerHTML = '';

        // Add date heading
        const dateHeading = document.createElement('h4');
        dateHeading.textContent = formattedDate;
        bannerContent.appendChild(dateHeading);

        if (isMultipleInterviews && interviews.length === 0) {
            // No interviews for this date
            const noInterviewsMsg = document.createElement('div');
            noInterviewsMsg.className = 'no-interviews-message';
            noInterviewsMsg.innerHTML = `
                <i class="bi bi-calendar-x text-muted fs-2 d-block mb-2"></i>
                <p class="text-muted mb-0">No Interviews Scheduled</p>
            `;
            bannerContent.appendChild(noInterviewsMsg);
        } else if (isMultipleInterviews) {
            // Create carousel for multiple interviews
            const interviewsContainer = document.createElement('div');
            
            if (interviews.length > 1) {
                // Create horizontal scroll for multiple interviews
                interviewsContainer.className = 'interviews-carousel';
                
                interviews.forEach(interview => {
                    const interviewCard = createInterviewCard(interview);
                    interviewsContainer.appendChild(interviewCard);
                });
            } else {
                // Single interview display from array
                const interviewCard = createInterviewCard(interviews[0]);
                interviewsContainer.appendChild(interviewCard);
            }
            
            bannerContent.appendChild(interviewsContainer);
        } else {
            // Single interview object passed directly
            // Use the showInterviewInBanner function we defined earlier
            // This is a cleaner approach than duplicating code
            showInterviewInBanner(interviews);
            return; // Early return since showInterviewInBanner handles showing the banner
        }

        // Show the banner with a sliding animation
        interviewBanner.classList.add('banner-visible');
        calendarContainer.classList.add('with-banner');
    }

    // Create an interview card for the banner
    function createInterviewCard(interview) {
        const card = document.createElement('div');
        card.className = 'interview-card';
        
        const interviewTime = new Date(interview.interview_date).toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
        
        card.innerHTML = `
            <div class="interview-card-header">
                <h5>${interview.position_title}</h5>
                <span class="status-badge ${interview.status.toLowerCase()}">${interview.status}</span>
            </div>
            <div class="interview-card-body">
                <p><i class="bi bi-building me-2"></i>${interview.company_name || 'N/A'}</p>
                <p><i class="bi bi-clock me-2"></i>${interviewTime}</p>
                <p><i class="bi bi-geo-alt me-2"></i>${interview.location}</p>
            </div>
        `;
        
        return card;
    }

    // Fetch interviews data from the server
    async function fetchInterviews() {
        try {
            // Get interviews data from the global variable if available
            if (window.interviewsData && window.interviewsData.length > 0) {
                userInterviews = window.interviewsData;
                return;
            }
            
            // Otherwise fetch from server
            const response = await fetch('/web/components/home/offers/get_all_interviews.php');
            if (!response.ok) {
                throw new Error('Failed to fetch interviews');
            }
            const data = await response.json();
            
            // Ensure consistent date formatting for all interviews
            userInterviews = data.map(interview => {
                // Make sure interview_date is properly formatted
                if (interview.interview_date) {
                    // Keep the original format but ensure it's consistent
                    const datePart = interview.interview_date.split('T')[0];
                    interview.interview_date = datePart + (interview.interview_date.includes('T') ? 
                        'T' + interview.interview_date.split('T')[1] : '');
                }
                return interview;
            });
            
            // Store in global variable for future use
            window.interviewsData = userInterviews;
        } catch (error) {
            console.error('Error fetching interviews:', error);
            // Use empty array if fetch fails
            userInterviews = [];
        }
    }

    // Close banner button
    document.getElementById('closeBannerBtn').addEventListener('click', function() {
        interviewBanner.classList.remove('banner-visible');
        calendarContainer.classList.remove('with-banner');
    });

    // Fetch interview details from server and show in banner
    function fetchInterviewDetails(interviewId) {
        fetch(`/web/components/home/offers/get_interview.php?id=${interviewId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch interview data');
                }
                return response.json();
            })
            .then(data => {
                // Show the interview in the sliding banner
                showInterviewInBanner(data);
            })
            .catch(error => {
                console.error('Error fetching interview details:', error);
                alert('Failed to load interview details. Please try again.');
            });
    }

    // Show a single interview in the sliding banner
    function showInterviewInBanner(interview) {
        const bannerContent = document.getElementById('interviewBannerContent');
        
        // Format the date
        let dateObj;
        try {
            dateObj = new Date(interview.interview_date);
        } catch (e) {
            console.error('Error parsing date:', e);
            dateObj = new Date(); // Fallback to current date
        }
        
        const formattedDate = dateObj.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        const formattedTime = dateObj.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Clear previous content
        bannerContent.innerHTML = '';
        
        // Add date heading
        const dateHeading = document.createElement('h4');
        dateHeading.textContent = formattedDate;
        bannerContent.appendChild(dateHeading);
        
        // Create interview card
        const interviewCard = document.createElement('div');
        interviewCard.className = 'interview-card';
        
        // Fill in the card with interview details
        interviewCard.innerHTML = `
            <div class="interview-card-header">
                <h5>${interview.position_title}</h5>
                <span class="status-badge ${interview.status.toLowerCase()}">${interview.status}</span>
            </div>
            <div class="interview-card-body">
                <p><i class="bi bi-building me-2"></i>${interview.company_name || 'N/A'}</p>
                <p><i class="bi bi-clock me-2"></i>${formattedTime}</p>
                <p><i class="bi bi-geo-alt me-2"></i>${interview.location || 'Location not specified'}</p>
                <p><i class="bi bi-person me-2"></i>${interview.interviewer || 'Not specified'}</p>
                ${interview.feedback ? `
                <div class="mt-3">
                    <strong>Feedback:</strong>
                    <p class="mt-1">${interview.feedback}</p>
                </div>
                ` : ''}
                ${interview.cv_url ? `
                <div class="mt-2">
                    <a href="${interview.cv_url}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark-text me-1"></i> View CV
                    </a>
                </div>
                ` : ''}
            </div>
        `;
        
        bannerContent.appendChild(interviewCard);
        
        // Add action buttons
        const actionsContainer = document.createElement('div');
        actionsContainer.className = 'interview-actions mt-3 d-flex gap-2';
        
        // Edit button
        const editButton = document.createElement('button');
        editButton.className = 'btn btn-primary';
        editButton.innerHTML = '<i class="bi bi-pencil me-1"></i> Edit';
        editButton.onclick = function() {
            // Use the existing editInterview function from user_interviews.php
            if (typeof window.editInterview === 'function') {
                window.editInterview(interview.id);
            } else {
                console.error('editInterview function not available');
            }
        };
        
        actionsContainer.appendChild(editButton);
        bannerContent.appendChild(actionsContainer);
        
        // Show the banner
        const interviewBanner = document.getElementById('interviewDetailsBanner');
        interviewBanner.classList.add('banner-visible');
        
        // Add the with-banner class to the calendar container
        const calendarContainer = document.getElementById('interviewCalendarContainer');
        calendarContainer.classList.add('with-banner');
    }
});