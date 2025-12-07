/**
 * POS Course Enrollment Module
 * Handles course selection and schedule assignment during checkout
 */

(function() {
    'use strict';

    // Store selected course schedules
    window.posSelectedSchedules = {};

    /**
     * Show course schedule selection modal
     */
    window.showCourseScheduleModal = function(courseId, courseName, coursePrice) {
        // Fetch available schedules for this course
        fetch(`/store/api/courses/${courseId}/schedules`)
            .then(response => response.json())
            .then(schedules => {
                if (schedules.length === 0) {
                    alert('No available schedules for this course. Please create a schedule first.');
                    return;
                }

                // Build modal content
                const modalHtml = `
                    <div class="modal fade" id="courseScheduleModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">
                                        <i class="bi bi-calendar-check"></i> Select Class Schedule
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <h6 class="mb-3">${courseName} - $${parseFloat(coursePrice).toFixed(2)}</h6>
                                    <p class="text-muted mb-3">Please select which class schedule to enroll the customer in:</p>

                                    <div class="list-group">
                                        ${schedules.map(schedule => `
                                            <button type="button"
                                                    class="list-group-item list-group-item-action schedule-option"
                                                    data-schedule-id="${schedule.id}"
                                                    data-course-id="${courseId}"
                                                    data-course-name="${courseName}"
                                                    data-course-price="${coursePrice}">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <i class="bi bi-calendar3"></i>
                                                            ${formatDate(schedule.start_date)} - ${formatDate(schedule.end_date)}
                                                        </h6>
                                                        <p class="mb-1 text-muted">
                                                            <i class="bi bi-clock"></i> ${schedule.start_time || 'TBD'} - ${schedule.end_time || 'TBD'}
                                                        </p>
                                                        <p class="mb-1">
                                                            <i class="bi bi-person-badge"></i> Instructor: ${schedule.instructor_name}
                                                        </p>
                                                        <p class="mb-0 text-muted small">
                                                            <i class="bi bi-geo-alt"></i> ${schedule.location || 'Location TBD'}
                                                        </p>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-${schedule.available_spots > 3 ? 'success' : 'warning'} mb-2">
                                                            ${schedule.available_spots} spots left
                                                        </span>
                                                        <div class="small text-muted">
                                                            ${schedule.current_enrollment}/${schedule.max_students} enrolled
                                                        </div>
                                                    </div>
                                                </div>
                                            </button>
                                        `).join('')}
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Remove existing modal if any
                const existingModal = document.getElementById('courseScheduleModal');
                if (existingModal) {
                    existingModal.remove();
                }

                // Add modal to page
                document.body.insertAdjacentHTML('beforeend', modalHtml);

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('courseScheduleModal'));
                modal.show();

                // Add click handlers to schedule options
                document.querySelectorAll('.schedule-option').forEach(button => {
                    button.addEventListener('click', function() {
                        const scheduleId = this.dataset.scheduleId;
                        const courseId = this.dataset.courseId;
                        const courseName = this.dataset.courseName;
                        const coursePrice = this.dataset.coursePrice;

                        // Add course to cart with schedule
                        addCourseToCart(courseId, courseName, coursePrice, scheduleId);

                        // Close modal
                        modal.hide();
                    });
                });

                // Clean up modal after it's hidden
                document.getElementById('courseScheduleModal').addEventListener('hidden.bs.modal', function() {
                    this.remove();
                });
            })
            .catch(error => {
                console.error('Error fetching course schedules:', error);
                alert('Failed to load course schedules. Please try again.');
            });
    };

    /**
     * Add course to cart with selected schedule
     */
    function addCourseToCart(courseId, courseName, coursePrice, scheduleId) {
        // This integrates with your existing POS cart system
        // You'll need to modify your addToCart function to handle courses

        const cartItem = {
            product_id: `course_${courseId}`,
            name: courseName,
            price: parseFloat(coursePrice),
            quantity: 1,
            type: 'course',
            course_id: courseId,
            schedule_id: scheduleId
        };

        // Store schedule selection
        window.posSelectedSchedules[`course_${courseId}`] = scheduleId;

        // Add to cart (this will call your existing cart logic)
        if (typeof window.addItemToCart === 'function') {
            window.addItemToCart(cartItem);
        } else {
            console.error('addItemToCart function not found');
        }

        // Show success message
        showToast('success', `${courseName} added to cart`);
    }

    /**
     * Format date for display
     */
    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { month: 'short', day: 'numeric', year: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    }

    /**
     * Show toast notification
     */
    function showToast(type, message) {
        // Check if your POS has a toast/notification system
        // Otherwise, use a simple alert or create a custom toast
        if (typeof window.showNotification === 'function') {
            window.showNotification(type, message);
        } else {
            // Fallback to console
            console.log(`[${type}] ${message}`);
        }
    }

})();
