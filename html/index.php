<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />


<title>IstiABOT - Déplacement</title>

<script type="text/javascript" src="eventemitter2.min.js"></script>
<script type="text/javascript" src="roslib.min.js"></script>
<script type="text/javascript" src="easeljs.min.js"></script>
<script type="text/javascript" src="ros2d.min.js"></script>

<script type="text/javascript" type="text/javascript">
    //constants
    var UP_arrow = 38;
    var DOWN_arrow = 40;
    var LEFT_arrow = 37;
    var RIGHT_arrow = 39;
    var UP_letter = 90;    // z
    var DOWN_letter = 83;  // s
    var LEFT_letter = 81;  // q
    var RIGHT_letter = 68; // d

    var dict = {
        38:"FOREWARD",
        90:"FOREWARD",
        40:"BACKWARD",
        83:"BACKWARD",
        37:"LEFT",
        81:"LEFT",
        39:"RIGHT",
        68:"RIGHT"
    }
    

    // Connecting to ROS
    // -----------------
    var ros = new ROSLIB.Ros({
        url : 'ws://192.168.49.1:9090'
    });

    ros.on('connection', function() {
        document.getElementById("connection_ok").innerHTML = "Connected to websocket server<br/>";
    });

    ros.on('error', function(error) {
        document.getElementById("connection_error").innerHTML += "Error connecting to websocket server<br/>";
    });

    ros.on('close', function() {
        document.getElementById("connection_error").innerHTML += "Connection to websocket server closed<br/>";
    });

    // Publishing a Topic
    // ------------------

    var cmdVel = new ROSLIB.Topic({
        ros : ros,
        name : '/cmd_vel',
        messageType : 'geometry_msgs/Twist'
    });

    var twist = new ROSLIB.Message({
        linear : {
            x : 0.0,
            y : 0.0,
            z : 0.0
        },
        angular : {
            x : 0.0,
            y : 0.0,
            z : 0.0
        }
    });



    // 38: UP, 40: DOWM, 37: LEFT, 39:RIGHT, 0:NONE
    var last_direction = 0;
    var timerId = 0;


    // Subscribing to a Topic
    // ----------------------

    function funcTimeout(){
        var ok=1;
        switch(last_direction){
            case UP_arrow: //UP
            case UP_letter: //UP
                twist.linear.x = 1;
                twist.angular.z = 0.0;
                break;
            case DOWN_arrow: //DOWN
            case DOWN_letter: //DOWN
                twist.linear.x = -1;
                twist.angular.z = 0.0;
                break;
            case LEFT_arrow: //LEFT
            case LEFT_letter: //LEFT
                twist.linear.x = 0.0;
                twist.angular.z = 0.3;
                break;
            case RIGHT_arrow: //RIGHT
            case RIGHT_letter: //RIGHT
                twist.linear.x = 0.0;
                twist.angular.z = -0.3;
                break;
            default:
                ok=0;
        }
        if(ok == 1){
            cmdVel.publish(twist);
            timerId = setTimeout(funcTimeout, 200);
        }
    }


    // handleling the keyboard event
    function myKeyPress(event){
        if(last_direction == 0){
            last_direction = event.keyCode;
            timerId = setTimeout(funcTimeout, 200);
            document.getElementById("cur_dir").innerHTML = dict[last_direction];
        }else if(last_direction != event.keyCode){
            last_direction = event.keyCode;
            document.getElementById("cur_dir").innerHTML = dict[last_direction];
        }
    }
    function myKeyReleased(event){
       if(event.keyCode == last_direction){
            last_direction = 0;
            clearTimeout(timerId);
            document.getElementById("cur_dir").innerHTML = "STOP";

            
                twist.linear.x = 0.0;
                twist.angular.z = 0.0;
            cmdVel.publish(twist);
       }
    }


    // handleing the map display

    // Create the main viewer.
    var viewer = new ROS2D.Viewer({
      divID : 'map',
      width : 600,
      height : 500
    });

    // Setup the map client.
    var gridClient = new ROS2D.OccupancyGridClient({
      ros : ros,
      rootObject : viewer.scene
    });
    // Scale the canvas to fit to the map
    gridClient.on('change', function(){
      viewer.scaleToDimensions(gridClient.currentGrid.width, gridClient.currentGrid.height);
    });

</script>
</head>

<body onkeydown="myKeyPress(event)" onkeyup="myKeyReleased(event)">

  <img src="images/istia.png" alt="Logo IstiA" style="width:304px;">

  <h1>IstiABOT - Karadoc</h1>
  <p id="connection_error" style="color:red"></p>
  <p id="connection_ok" style="color:green"></p>
  <p> Use the keyboard arrows to move the robot.</p>
  <p>Current direction:</p>
  <p id="cur_dir"></p>
  <script>
    
    document.getElementById("cur_dir").innerHTML = "STOP";
    </script>
  <div id="map"></div>
</body>
</html>

