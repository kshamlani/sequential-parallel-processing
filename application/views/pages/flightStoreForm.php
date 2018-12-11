<!DOCTYPE html>
<html lang="en">
<head>
  <title>Flight Details</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<style type="text/css" rel="stylesheet" media="all">
    @import url(https://fonts.googleapis.com/css?family=Roboto:400,100,300,500);

    body { 
        background-color: #007aff; 
        color: #fff;
        font-size: 100%;
        line-height: 1.5;
        font-family: "Roboto", sans-serif;
    }

    .button {
        transition: all .3s linear;
    }

    .button:hover {
        background-color: #007aff;
        color: #fff;
        border: 1px solid #fff;
    }

    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        margin: 0; 
    }

    .disabled{
        pointer-events: none;
    }

    .error{
        color: yellow;
    }
    </style>
<body>

    <div class="container">
        <div class="col-md-11">
            <h2>Add Flight Details</h2>
        </div>
        <div class="col-md-1">
            <h6 class="error"></h6>
        </div>
        
        <form action="" method="post" id="add_flight_form">
            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="year">Year:</label>
                        <input type="number" class="form-control" id="year" placeholder="Enter Year" name="year" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="month">Month:</label>
                        <input type="number" class="form-control" id="month" placeholder="Enter month" name="month" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="day">Day:</label>
                        <input type="number" class="form-control" id="day" placeholder="Enter day" name="day" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="week">Week:</label>
                        <input type="number" class="form-control" id="week" placeholder="Enter week" name="week" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="departure_time">Departure Time:</label>
                        <input type="number" class="form-control" id="departure_time" placeholder="Enter departure time" name="departure_time" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="actual_departure_time">Actual Departure Time:</label>
                        <input type="number" class="form-control" id="actual_departure_time" placeholder="Enter actual departure time" name="actual_departure_time" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="arrival_time">Arrival Time:</label>
                        <input type="number" class="form-control" id="arrival_time" placeholder="Enter arrival time" name="arrival_time" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="carrier">Carrier:</label>
                        <input type="number" class="form-control" id="carrier" placeholder="Enter carrier" name="carrier" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="flight_number">Flight Number:</label>
                        <input type="text" class="form-control" id="flight_number" placeholder="Enter flight_number" name="flight_number" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="departure_delay">Departure Delay (minutes):</label>
                        <input type="number" class="form-control" id="departure_delay" placeholder="Enter departure delay" name="departure_delay" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                            <label for="arrival_delay">Arrival Delay (minutes):</label>
                            <input type="number" class="form-control" id="arrival_delay" placeholder="Enter arrival delay" name="arrival_delay" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cancellation">Cancellation:</label>
                        <p>
                            <div class="radio-inline">
                                <label><input type="radio" name="cancellation" value="no" checked>No</label>
                            </div>
                            <div class="radio-inline">
                                <label><input type="radio" name="cancellation" value="yes">Yes</label>
                            </div>
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="weather_delay">Weather Delay (minutes):</label>
                        <input type="number" class="form-control" id="weather_delay" placeholder="Enter weather delay" name="weather_delay" required>
                    </div>
                </div>
                <div class="col-md-12">
                    <button type="submit" id="submit_flight_form" class="button btn btn-default">Submit</button>
                </div>
            </div>   
        </form>
    </div>
</body>
<script>
    $(document).on('submit','#add_flight_form',function(event){
        event.preventDefault();
        var base_url = '<?php echo base_url();?>';
        var data = new FormData(this);
        $('.add_flight_form').addClass('disabled');
        $.ajax({
            url : base_url+"api/v1/flightStore",
            type : "POST",
            dataType : "json",
            data : data,
            processData:false,
            contentType:false,
            cache:false,
            success : function(data) {
                $('.add_flight_form').removeClass('disabled');
                $('#add_flight_form')[0].reset();
                window.location.href = base_url+'flights/view'
            },
            error : function(data){
                $('.add_flight_form').removeClass('disabled');
                console.log(data);
            }
        });
    });
</script>
</html>
