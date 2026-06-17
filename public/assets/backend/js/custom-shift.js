
        initselect2();
        function onChangeShiftType(data) {
            var text = data.options[data.selectedIndex].value;
            var html;
            if(text == 'SS'){
                html = `<div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift_start" class="form-label">Shift Start</label>
                                <input type="text" name="shifts[0][shift_start]" class="form-control timepicker" id="shift_start" placeholder="shift start time" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="shift_end" class="form-label">Shift End</label>
                                <input type="text" name="shifts[0][shift_end]" class="form-control timepicker" id="shift_end" placeholder="shift end time" required>
                            </div>
                        </div>`;
                $("#letter").html('');
                $("#letter").html(html);
                flatpickr('.timepicker', {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                })
                $("#letter").show();
            } else {
                html = `<div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift_start" class="form-label">Shift Start</label>
                                <input type="text" name="shifts[0][shift_start]" class="form-control timepicker" id="shift_start" placeholder="shift start time">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="shift_end" class="form-label">Shift End</label>
                                <input type="text" name="shifts[0][shift_end]" class="form-control timepicker" id="shift_end" placeholder="shift end time">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="mb-3" style="margin-top: 31px !important;">
                                <button type="button" id="addShift" class="btn btn-primary">+</button>
                            </div>
                        </div>`;
                $("#letter").html('');
                $("#letter").html(html);
                flatpickr('.timepicker', {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                })
                $("#letter").show();
            }
        }
        // Counter for unique IDs
         shvariftCounter = 1;
        // Function to create a new set of shift input fields
        function createShiftInputs() {
            var ShiftsCount = $('#letter .col-md-6').length;
            var newShiftInputs = `
            
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="shift_start_${ShiftsCount}" class="form-label">Shift Start</label>
                            <input type="text" name="shifts[${ShiftsCount }][shift_start]" class="form-control timepicker" id="shift_start_${ShiftsCount}" placeholder="shift start time" required>
                            <div class="invalid-feedback" id="shift_start_${ShiftsCount}_error">Shift start time is required.</div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="mb-3">
                            <label for="shift_end_${ShiftsCount}" class="form-label">Shift End</label>
                            <input type="text" name="shifts[${ShiftsCount }][shift_end]" class="form-control timepicker" id="shift_end_${ShiftsCount}" placeholder="shift end time" required>
                            <div class="invalid-feedback" id="shift_start_${ShiftsCount}_error">Shift end time is required.</div>
                        </div>
                    </div>
                    <div class="col-md-1" style="margin-top: 31px !important;">
                        <button type="button" class="btn btn-primary remove-shift">x</button>
                    </div>
                </div>
            
            `;

            // Increment the counter for unique IDs


            return newShiftInputs;
        }
        // Event delegation for dynamically added #addShift button
        $(document).ready(function () {
            $("#letter").on("click", "#addShift", function () {
                $('#letter').append(createShiftInputs());
                flatpickr('.timepicker', {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i",
                    })
            });

            // Event delegation for dynamically added .remove-shift button
            $("#letter").on("click", ".remove-shift", function () {
                $(this).closest('.row').remove();
            });
        });
        
        