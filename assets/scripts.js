jQuery(document).ready(function($) {
    $('#am_slot').on('change', function() {
        const selectedOption = $(this).find(':selected');
        const startTime = selectedOption.data('start-time');
        const slotId = $(this).val();
        
        // Reset the time dropdown
        const $timeDropdown = $('#am_time');
        $timeDropdown.empty().append('<option value="">Select Time</option>');

        if (slotId) {
            // Make an AJAX call to fetch available times for the selected slot
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                method: 'POST',
                data: {
                    action: 'am_get_available_times',
                    slot_id: slotId,
                    start_time: startTime
                },
                success: function(response) {
                    if (response.success) {
                        const times = response.data;
                        times.forEach(function(time) {
                            $timeDropdown.append('<option value="' + time + '">' + time + '</option>');
                        });
                    } else {
                        // Handle error or no available times
                        $timeDropdown.append('<option value="">No available times</option>');
                    }
                },
                error: function() {
                    $timeDropdown.append('<option value="">Error fetching times</option>');
                }
            });
        }
    });
});



