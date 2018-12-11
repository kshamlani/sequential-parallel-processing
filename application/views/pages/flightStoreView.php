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
    </style>
<body>
<!-- <?php print_r($data);?> -->
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
