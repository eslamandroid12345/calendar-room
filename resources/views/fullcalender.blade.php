<!DOCTYPE html>
<html>
<head>
    <title>Fullcalendar using Ajax example with Laravel 9 Application - Mywebtuts.com</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

</head>
<body>

<div class="container text-center">
    <h2 class="m-5">Fullcalendar using Ajax example with Laravel 9 Application - Mywebtuts.com</h2>
    <!-- Modal HTML -->
    <div id="addEventModal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Event</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <!-- Input fields for event title, start date, end date -->
                    <input  class="form-control mt-3" type="text" id="eventTitle" placeholder="Event Title">
                    <input class="form-control mt-3" type="text" id="eventStart" placeholder="Start Date">
                    <input  class="form-control mt-3" type="text" id="eventEnd" placeholder="End Date">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveEvent">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <div id='calendar'></div>
</div>

<script type="text/javascript">

    $(document).ready(function () {

        const SITEURL = "{{ url('/') }}";


        $('#saveEvent').on('click', function() {

            const title = $('#eventTitle').val();
            const start = $('#eventStart').val();
            const end = $('#eventEnd').val();

            $.ajax({
                url: SITEURL + "/fullcalenderAjax",
                data: {
                    title: title,
                    start: start,
                    end: end,
                    type: 'add'
                },
                type: "POST",
                success: function (data) {

                    displayMessage("تم اضافه تواريخ الغرفه بنجاح");

                    // Render newly created events
                    $.each(data.newEvents, function(index, event) {
                        calendar.fullCalendar('renderEvent', event, true);
                    });

                    calendar.fullCalendar('removeEvents');
                    calendar.fullCalendar('addEventSource', data.allEvents);

                    calendar.fullCalendar('unselect');

                    $('#addEventModal').modal('hide');
                }
            });
        });//end

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var calendar = $('#calendar').fullCalendar({
            editable: true,
            events: SITEURL + "/fullcalender",
            displayEventTime: false,
            editable: true,
            eventRender: function (event, element, view) {
                if (event.allDay === 'true') {
                    event.allDay = true;
                } else {
                    event.allDay = false;
                }
            },
            selectable: true,
            selectHelper: true,

            select: function (start, end, allDay) {
                const startDate = $.fullCalendar.formatDate(start, "Y-MM-DD");
                const endDate = $.fullCalendar.formatDate(end, "Y-MM-DD");

                const events = $('#calendar').fullCalendar('clientEvents', function(event) {
                    return (event.start.isSame(start) && event.end.isSame(end));
                });

                if (events.length > 0) {
                    const eventTitle = events[0].title;
                    $('#eventTitle').val(eventTitle);
                    $('#eventStart').val(startDate);
                    $('#eventEnd').val(endDate);

                    $('#addEventModal').modal('show');
                }else{

                    $('#eventTitle').val('');
                    $('#eventStart').val(startDate);
                    $('#eventEnd').val(endDate);
                    $('#addEventModal').modal('show');
                }

            },

            eventDrop: function (event, delta) {
                var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
                var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD");

                $.ajax({
                    url: SITEURL + '/fullcalenderAjax',
                    data: {
                        title: event.title,
                        start: start,
                        end: end,
                        id: event.id,
                        type: 'update'
                    },
                    type: "POST",
                    success: function (response) {
                        displayMessage("Event Updated Successfully");
                    }
                });
            },
            eventClick: function (event) {
                var deleteMsg = confirm("Do you really want to delete?");
                if (deleteMsg) {
                    $.ajax({
                        type: "POST",
                        url: SITEURL + '/fullcalenderAjax',
                        data: {
                            id: event.id,
                            type: 'delete'
                        },
                        success: function (response) {
                            calendar.fullCalendar('removeEvents', event.id);
                            displayMessage("Event Deleted Successfully");
                        }
                    });
                }
            }

        });

    });


    function displayMessage(message) {
        toastr.success(message, 'Event');
    }

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>

</body>
</html>
