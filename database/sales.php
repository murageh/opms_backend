<?php

// CREATE SALE

require_once "connect.php";

// Define variables and initialize with empty values
$type = $additional_details = "";
$quantity = $unit_price = $total_price = 0;
$type_err = $additional_info_err = $quantity_err = $unit_price_err = $total_price_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST"  && !isset($_POST["delete"])) {
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);


    if (@!$data -> add){
        // Do nothing
        $xx = ['status' => 'error', 'message' => "Could not do anything"];
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($xx);
        exit();
    }

    // Validate name
    @$input_type = trim($data->type);
    if (empty($input_type)) {
        $type_err = "Please enter a name.";
    } elseif (!filter_var($input_type, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z0-9 .\s]+$/")))) {
        $type_err = "Please enter a valid type.";
    } else {
        $type = $input_type;
    }

    // Validate additional details
    @$input_add_info = trim($data->additional_details);
    if (!empty($input_add_info)) {
        $additional_details = $input_add_info;
    }

    // Validate quantity
    $input_quantity = trim($data->quantity);
    if (empty($input_quantity)) {
        $quantity_err = "Please enter the quantity";
    } elseif (!ctype_digit($input_quantity)) {
        $quantity_err = "Please enter a positive integer value.";
    } else {
        $quantity = $input_quantity;
    }

    // Validate unit_price
    $input_unit_price = trim($data->unit_price);
    if (empty($input_unit_price)) {
        $quantity_err = "Please enter the quantity";
    } elseif (!ctype_digit($input_unit_price)) {
        $unit_price_err = "Please enter a positive integer value.";
    } else {
        $unit_price = $input_unit_price;
    }

    // Calculate total_price
    $total_price = $input_quantity * $input_unit_price;

    // Check input errors before inserting in database
    if (empty($type_err) && empty($unit_price_err)
        && empty($total_price_err) && empty($quantity_err)
        && empty($unit_price_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO sales (type , quantity, unit_price, total_price, additional_details) VALUES (?, ?, ?, ?, ?)";

        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssss", $param_type, $param_quantity, $param_unit_price, $param_total_price, $param_additional_details);

            // Set parameters
            $param_type = $type;
            $param_quantity = $quantity;
            $param_unit_price = $unit_price;
            $param_total_price = $total_price;
            $param_additional_details = $additional_details;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Records created successfully. Return success json.
                $data = ['status' => 'success', 'message' => "Sale added successfully"];
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($data);
                exit();
            } else {
                $data = ['status' => 'error', 'message' => "Oops! Something went wrong. Please try again later."];
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($data);
            }
        }

        // Close statement
        $stmt->close();
    }

    // Close connection
    $mysqli->close();
}

/*
 * FETCH ALL SALES
 */

// Check existence of id parameter before processing further
else if (($_SERVER["REQUEST_METHOD"] == "GET") && (!isset($_GET["id"]))) {
    // Include config file
    require_once "connect.php";

    // Prepare a select statement
    $sql = "SELECT * FROM sales";

    if ($stmt = $mysqli->prepare($sql)) {
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();

            /* Fetch result rows as associative arrays. Since the result set
                contains more than one row, we need to use while loop */
            $sales = [];
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                array_push($sales,
                    [
                        "id" => $row["id"],
                        "type" => $row["type"],
                        "quantity" => $row["quantity"],
                        "unit_price" => $row["unit_price"],
                        "total_price" => $row["total_price"],
                        "additional_info" => $row["additional_details"],
                        "date" => $row["date_time"],

                    ]);
            };

            $data = ['status' => 'success',
                'message' => "Sales retrieved successfully",
                'data' => [
                    ...$sales
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
}else{
    // No operation was matched. Return error
    $data = ['status' => 'error', 'message' => "No operation matched."];
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}