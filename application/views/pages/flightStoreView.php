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
        font-size: 16px;
        line-height: 1.5;
        font-family: "Roboto", sans-serif;
    }

    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        margin: 0; 
    }

    .button {
        transition: all .3s linear;
        margin-top: -5px;
    }

    .button:hover {
        background-color: #007aff;
        color: #fff;
        border: 1px solid #fff;
    }
    </style>
<body>
    <div class="container">
        <div class="col-md-12">
            <form action="<?php echo base_url(); ?>flights/view" method="GET" id="search_form">
                <div class="col-md-5">
                    <label for="month_select">Select Month:</label>
                    <select class="form-control" name="month" id="month_select">
                        <option value="0" selected>None</option>
                        <?php for($i=1;$i<=12;$i++){?>
                        <option value="<?php echo $i; ?>" <?php if(isset($data['searched']['flight_store.month']) && $data['searched']['flight_store.month'] == $i){echo 'selected';}; ?>><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="year_select">Enter Year:</label>
                        <input type="number" name="year" class="form-control" id="year_select" placeholder="Enter Year" value="<?php if(isset($data['searched']['flight_store.year'])){echo $data['searched']['flight_store.year'];}; ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="search_submit"></label><p></p>
                        <input type="submit" id="search_flight_form" class="button btn btn-default" value="Search">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="container">
        <h2>All Flight Details</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Year</th>
                        <th scope="col">Month</th>
                        <th scope="col">Day</th>
                        <th scope="col">Week</th>
                        <th scope="col">Departure Time</th>
                        <th scope="col">Actual departure Time</th>
                        <th scope="col">Arrival Time</th>
                        <th scope="col">Carrier</th>
                        <th scope="col">Flight No.</th>
                        <th scope="col">Departure Delay</th>
                        <th scope="col">Arrival Delay</th>
                        <th scope="col">Cancellation</th>
                        <th scope="col">Weather Delay</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($data['flight_details'] as $fd_key => $fd){?>
                    <tr>
                        <th scope="row"><?php echo $fd_key+1; ?></th>
                        <td><?php echo $fd->year; ?></td>
                        <td><?php echo $fd->month; ?></td>
                        <td><?php echo $fd->day; ?></td>
                        <td><?php echo $fd->week; ?></td>
                        <td><?php echo $fd->departure_time; ?></td>
                        <td><?php echo $fd->actual_departure_time; ?></td>
                        <td><?php echo $fd->arrival_time; ?></td>
                        <td><?php echo $fd->carrier; ?></td>
                        <td><?php echo $fd->flight_number; ?></td>
                        <td><?php echo $fd->departure_delay; ?></td>
                        <td><?php echo $fd->arrival_delay; ?></td>
                        <td><?php echo $fd->cancellation; ?></td>
                        <td><?php echo $fd->weather_delay; ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <label for="">Count: <?php echo $data['total_flights']; ?></label>
    </div>
    <div class="container">
        <h2>Delayed Flights Details</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Year</th>
                        <th scope="col">Month</th>
                        <th scope="col">Day</th>
                        <th scope="col">Week</th>
                        <th scope="col">Departure Time</th>
                        <th scope="col">Actual departure Time</th>
                        <th scope="col">Arrival Time</th>
                        <th scope="col">Carrier</th>
                        <th scope="col">Flight No.</th>
                        <th scope="col">Departure Delay</th>
                        <th scope="col">Arrival Delay</th>
                        <th scope="col">Cancellation</th>
                        <th scope="col">Weather Delay</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['delayed_flights'] as $dfd_key => $dfd) { ?>
                    <tr>
                        <th scope="row"><?php echo $dfd_key+1; ?></th>
                        <td><?php echo $dfd->year; ?></td>
                        <td><?php echo $dfd->month; ?></td>
                        <td><?php echo $dfd->day; ?></td>
                        <td><?php echo $dfd->week; ?></td>
                        <td><?php echo $dfd->departure_time; ?></td>
                        <td><?php echo $dfd->actual_departure_time; ?></td>
                        <td><?php echo $dfd->arrival_time; ?></td>
                        <td><?php echo $dfd->carrier; ?></td>
                        <td><?php echo $dfd->flight_number; ?></td>
                        <td><?php echo $dfd->departure_delay; ?></td>
                        <td><?php echo $dfd->arrival_delay; ?></td>
                        <td><?php echo $dfd->cancellation; ?></td>
                        <td><?php echo $dfd->weather_delay; ?></td>
                    </tr>
                    <?php 
                } ?>
                </tbody>
            </table>
        </div>
        <label for="">Count: <?php echo $data['total_delayed_flights']; ?></label>
    </div>
</body>
</html>
