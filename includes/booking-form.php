<?php 

    <form method="post" action="">
<div class="form-group">
    <label for="am_slot">Vælg dato:</label>
        <select id="am_slot" name="am_slot" required>
            <option value="">Vælg dato</option>
            <?php foreach ($future_slots as $slot): ?>
                <option value="<?php echo $slot['id']; ?>" 
                    data-date="<?php echo $slot['date']; ?>" 
                    data-start-time="<?php echo $slot['start_time']; ?>">
                <?php echo $slot['date'] . ' - ' . $slot['location']; ?>
            </option>
            <?php endforeach; ?>
        </select>
            </div>
            <div class="form-group col-6">     
                <label for="am_time">Select Time:</label>
                    <select id="am_time" name="am_time">
                        <option value="">Select Time</option>
                        
                    </select>
            </div>
            <div class="form-group col-6">
        <label for="am_quantity">Mængde (kg):</label>
        <select id="am_quantity" name="am_quantity" required>
            <?php for ($i = 50; $i <= 1200; $i += 50): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?> kg</option>
            <?php endfor; ?>
        </select>
            </div>
            <div class="form-group">

        <label for="am_name">Navn:</label>
        <input type="text" id="am_name" name="am_name" required>
        </div>
        <div class="form-group">
        <label for="am_phone">Mobil:</label>
        <input type="tel" id="am_phone" name="am_phone" required>
        </div>
        <div class="form-group">
        <label for="am_email">Email:</label>
        <input type="email" id="am_email" name="am_email" required>
        </div>
        <div class="form-group">
        
        
        <button type="submit">BESTIL TID</button>
            </div>
    </form>
<script>
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



    </script>
    <?php