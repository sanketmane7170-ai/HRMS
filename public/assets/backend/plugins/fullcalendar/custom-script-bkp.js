if ($('#calendarFull').length) {
    $('#calendarFull').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month'
        },
        defaultView: 'month',
        firstDay: 1,
        editable: true,
        allDaySlot: false,
        eventSources: [{
            events: laravelEvents,
            color: "#22C68C",
            textColor: "yellow"
        }],
        select: function(start, end, jsEvent, view) {
            const modalEl = document.getElementById('event-modal');
            const modal = new bootstrap.Modal(modalEl);
            
            $('#event-modal').find('input[name=assigned_for_date]').val(start.format('YYYY-MM-DD'));
            $('#event-modal').find('input[name=evtEnd]').val(end.format('YYYY-MM-DD HH:mm:ss'));
            
            modal.show(); // ✅ Bootstrap 5 method
        },
        selectHelper: true,
        selectable: true,
        snapDuration: '00:10:00',

        eventRender: function(event, element, view) {
            element.find('.fc-title').html(
                '<strong>' + event.title + '</strong><br>' +
                event.start.format('HH:mm') + '-' + event.end.format('HH:mm')
            );
        },

        eventClick: function(calEvent, jsEvent, view) {
            Swal.fire({
                title: "Are you sure?",
                text: "You want to delete this " + calEvent.title,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    var index = laravelEvents.indexOf(calEvent);
                    var url = $('#calendarFull').data('delete-route').replace(':id', calEvent.uqid);

                    $.ajax({
                        url: url,
                        type: 'delete',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                laravelEvents.splice(index, 1);
                                $('#calendarFull').fullCalendar('removeEvents', calEvent._id);

                                // ✅ hide modal safely
                                const modalEl = document.getElementById('event-modal');
                                const modal = bootstrap.Modal.getInstance(modalEl);
                                if (modal) modal.hide();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Error deleting event!',
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Error deleting event!',
                            });
                        }
                    });
                }
            });
        }
    });
}

if ($('#leaveCalendarFull').length) {
    $('#leaveCalendarFull').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month'
        },
        defaultView: 'month',
        firstDay: 1,
        editable: false,
        allDaySlot: true,
        eventSources: [{
            events: laravelLeaveEvents,
        }],
        selectHelper: true,
        selectable: true,
        snapDuration: '00:10:00',
        eventRender: function(event, element, view) {
            element.find('.fc-title').html(
                '<div class="badge ' + event.status + '"><strong>' + event.title + '</strong><br>' +
                event.start.format('YYYY-MM-DD') + ' - ' + event.end.format('YYYY-MM-DD') +
                '</div>'
            );

            var list = element.find('.fc-event-container');
            var elementNode = list.prevObject[0];
            if (elementNode) {
                elementNode.classList.add(event.status);
            }
        }
    });
}

// ✅ Close modal button for Bootstrap 5
document.getElementById('closeModalButton')?.addEventListener('click', function() {
    const modalEl = document.getElementById('event-modal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
});
