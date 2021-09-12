<?php

// CREATE ACTIVITY

require_once "connect.php";

// Define variables and initialize with empty values
$type = $additional_details = $courtesy= "";
$datetime = 0;
$type_err = $additional_info_err = $courtesy_err = $datetime_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST"  && !isset($_POST["delete"])) {
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    if (@!$data -> add){
        // Do nothing
        $xx = ['status' => 'error', 'message' => "Could not do anything. You're missing a required key 'add' in your JSON payload"];
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($xx);
        exit();
    }

    // Validate type
    $input_type = trim($data->type);
    if (empty($input_type)) {
        $type_err = "Please enter a type.";
    } elseif (!filter_var($input_type, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z0-9. \s]+$/")))) {
        $type_err = "Please enter a valid type.";
    } else {
        $type = $input_type;
    }

    // Validate courtesy
    $input_courtesy = trim($data->courtesy);
    if (empty($input_courtesy)) {
        $courtesy_err = "Please enter an employee name.";
    } elseif (!filter_var($input_courtesy, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z0-9. \s]+$/")))) {
        $courtesy_err = "Please enter a valid employee name.";
    } else {
        $courtesy = $input_courtesy;
    }

    // Validate additional details
    $input_add_info = trim($data->additional_details);
    if (!empty($input_add_info)) {
        $additional_details = $input_add_info;
    }

    // Validate datetime
    $input_date = trim($data->datetime);
    if (!empty($input_date)) {
        $datetime = $input_date;
    }else{
        $datetime_err = "Please enter valid date and time";
    }

    // Check input errors before inserting in database
    if (empty($type_err) && empty($datetime_err)
        && empty($courtesy_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO activities (type , date_time, courtesy_of, additional_details) VALUES (?, ?, ?, ?)";

        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssss", $param_type, $param_date, $param_courtesy, $param_additional_details);

            // Set parameters
            $param_type = $type;
            $param_date = $datetime;
            $param_courtesy = $courtesy;
            $param_additional_details = $additional_details;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Records created successfully. Return success json.
                $data = ['status' => 'success', 'message' => "Activity added successfully"];
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($data);
                exit();
            } else {
                $data = ['status' => 'error', 'message' => "Oops! Something went wrong. Please try again later."];
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($data);
                exit();
            }
        }

        // Close statement
        $stmt->close();
    }

    // Close connection
    $mysqli->close();
}

/*
 * FETCH ALL ACTIVITIES
 */

// Check existence of id parameter before processing further
if (($_SERVER["REQUEST_METHOD"] == "GET") && (!isset($_GET["id"]))) {
    // Include config file
    require_once "connect.php";

    // Prepare a select statement
    $sql = "SELECT * FROM activities";

    if ($stmt = $mysqli->prepare($sql)) {
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();

            /* Fetch result rows as associative arrays. Since the result set
                contains more than one row, we need to use while loop */
            $activities = [];
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                array_push($activities,
                    [
                        "id" => $row["id"],
                        "type" => $row["type"],
                        "datetime" => $row["date_time"],
                        "courtesy" => $row["courtesy_of"],
                        "additional_info" => $row["additional_details"],

                    ]);
            };

            $data = ['status' => 'success',
                'message' => "Activities retrieved successfully",
                'data' => [
                    ...$activities
                ]
            ];
        } else {
            $data = ['status' => 'error', 'message' => "An error occurred."];
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }

    // Close statement
    $stmt->close();

    // Close connection
    $mysqli->close();
}

/*
 * DELETE operation
 */

// Process delete operation after confirmation
else if(isset($_POST["delete"]) && !empty($_POST["delete"])){
    // Include config file
    require_once "connect.php";

    // Prepare a delete statement
    $sql = "DELETE FROM activities WHERE id = ?";

    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $param_id);

        // Set parameters
        $param_id = trim($_POST["delete"]);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Records deleted successfully. Redirect to landing page
            $data = ['status' => 'success', 'message' => "Activity deleted successfully"];
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data);
            exit();
        } else{
            $data = ['status' => 'error', 'message' => "An error occurred."];
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data);
        }
    }

    // Close statement
    $stmt->close();

    // Close connection
    $mysqli->close();
} else{
    // No operation was matched. Return error
    $data = ['status' => 'error', 'message' => "No operation matched."];
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}
