<!DOCTYPE html>
<html>
<head>
    <title>Room Calendar</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <style>
        .toast{
            background: #2c3e50;
            color: #fff;
        }

        tr:first-child > td > .fc-day-grid-event {
            margin-top: 2px;
            padding: 8px;
            text-align: center;
            border-radius: 40px;
        }



    </style>
</head>
<body>

<div class="text-center">
    <h2 class="m-5">Room Calendar</h2>
    <!-- Modal HTML -->
    <div id="addEventModal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Calendar</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <!-- Input fields for event title, start date, end date -->
                    <input  class="form-control mt-3" type="text" id="roomNumber" placeholder="Room Number">
                    <input  class="form-control mt-3" type="text" id="roomPrice" placeholder="Room Price">
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


        $('#saveEvent').on('click', function() {

            const room_number = $('#roomNumber').val();
            const room_price = $('#roomPrice').val();
            const start = $('#eventStart').val();
            const end = $('#eventEnd').val();

            $.ajax({
                url: "{{route('events')}}",
                data: {
                    room_number: room_number,
                    room_price: room_price,
                    start: start,
                    end: end,
                    type: 'add'
                },
                beforeSend: function () {
                    $('#saveEvent').html('<span class="spinner-border spinner-border-sm mr-2" ' +
                        ' ></span> <span style="margin-left: 4px;">.....جاري الاضافه </span>').attr('disabled', true);
                },

                type: "POST",
                success: function (data) {
                    handleSaveSuccess(data, calendar);
                },

                error: function (data) {
                    handleSaveError(data);
                },
            });
        });//end

        function handleSaveSuccess(data, calendar) {
            toastr.success('تم اضافه تواريخ الغرفه بنجاح', 'اضافه');
            const sound = new Audio('{{asset('sound/ringtone-you-would-be-glad-to-know.ogg')}}');
            sound.play();

            $('#saveEvent').html(`Save changes`).attr('disabled', false);

            $.each(data.newEvents, function (index, event) {
                calendar.fullCalendar('renderEvent', event, true);
            });

            calendar.fullCalendar('removeEventSources');
            calendar.fullCalendar('removeEvents');
            calendar.fullCalendar('addEventSource', data.allEvents);
            calendar.fullCalendar('unselect');

            $('#addEventModal').modal('hide');
        }//end success store

        function handleSaveError(data) {
            if (data.status === 500) {
                toastr.error('There is an error');
                $('#saveEvent').html(`Save changes`).attr('disabled', false);
            } else if (data.status === 422) {
                const errors = $.parseJSON(data.responseText);
                $.each(errors, function (key, value) {
                    if ($.isPlainObject(value)) {
                        $.each(value, function (key, value) {
                            toastr.error(value, key);
                        });
                    }
                });
                $('#saveEvent').html(`Save changes`).attr('disabled', false);
            } else {
                toastr.error('there in an error');
                $('#saveEvent').html(`Save changes`).attr('disabled', false);
            }
        }//end handle server

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var calendar = $('#calendar').fullCalendar({
            events: "{{route('events.create')}}",
            displayEventTime: false,
            editable: false,
            eventRender: function (event, element, view) {
                if (event.allDay === 'true') {
                    event.allDay = true;
                } else {
                    event.allDay = false;
                }
                var newDiv = $('<div>').addClass('fc-price').html('Price : ' + event.room_price + ' OMR');
                element.find('.fc-title').after(newDiv);
                element.find('.fc-title').html('Availability : '+event.room_number);
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
                    const roomNumber = events[0].room_number;
                    const roomPrice = events[0].room_price;
                    $('#roomNumber').val(roomNumber);
                    $('#roomPrice').val(roomPrice);
                    $('#eventStart').val(startDate);
                    $('#eventEnd').val(endDate);
                    $('#addEventModal').modal('show');
                }else{

                    $('#roomNumber').val('');
                    $('#roomPrice').val('');
                    $('#eventStart').val(startDate);
                    $('#eventEnd').val(endDate);
                    $('#addEventModal').modal('show');
                }

            },

            eventClick: function (event) {
                const deleteMsg = confirm("هل تريد حذف تاريخ الغرفه");
                if (deleteMsg) {
                    $.ajax({
                        type: "POST",
                        url: "{{route('delete.event')}}",
                        data: {
                            id: event.id,
                        },
                        success: function (response) {
                            calendar.fullCalendar('removeEvents', event.id);
                            toastr.error('تم حذف تاريخ الغرفه بنجاح','حذف');
                            const sound = new Audio('{{asset('sound/ringtone-you-would-be-glad-to-know.ogg')}}');
                            sound.play();

                        }
                    });
                }
            }

        });

    });

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>

</body>
</html>
