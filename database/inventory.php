<?php


// CREATE ITEM

require_once "connect.php";

// Define variables and initialize with empty values
$name = $courtesy = "";
$quantity = $cost = 0;
$name_err = $courtesy_err = $quantity_err =
    $cost_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST["delete"])) {
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
    @$input_name = trim($data->name);
    if (empty($input_name)) {
        $name_err = "Please enter a name.";
    } elseif (!filter_var($input_name, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z0-9 .\s]+$/")))) {
        $name_err = "Please enter a valid name.";
    } else {
        $name = $input_name;
    }

    // Validate courtesy
    @$input_courtesy = trim($data->courtesy);
    if (empty($input_courtesy)) {
        $courtesy_err = "Please enter an employee name.";
    } elseif (!filter_var($input_courtesy, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z0-9 \s]+$/")))) {
        $courtesy_err = "Please enter a valid employee name.";
    } else {
        $courtesy = $input_courtesy;
    }

    // Validate quantity
    @$input_quantity = trim($data->quantity);
    if (empty($input_quantity)) {
        $quantity_err = "Please enter the quantity.";
    } elseif (!ctype_digit($input_quantity)) {
        $salary_err = "Please enter a positive integer value.";
    } else {
        $quantity = $input_quantity;
    }

    // Validate total cost
    @$input_cost = trim($data->cost);
    if (empty($input_cost)) {
        $cost_err = "Please enter the quantity.";
    } elseif (!ctype_digit($input_quantity)) {
        $cost_err = "Please enter a positive integer value.";
    } else {
        $cost = $input_cost;
    }

    // Check input errors before inserting in database
    if (empty($name_err) && empty($quantity_err) && empty($cost_err) && empty($courtesy_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO inventory (item_name, item_quantity, total_cost, courtesy_of) VALUES (?, ?, ?, ?)";

        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssss", $param_name, $param_quantity, $param_cost, $param_courtesy);

            // Set parameters
            $param_name = $name;
            $param_quantity = $quantity;
            $param_cost = $cost;
            $param_courtesy = $courtesy;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Records created successfully. Return success json.
                $data = ['status' => 'success', 'message' => "Item added successfully"];
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
 * FETCH ALL INVENTORY ITEMS
 */

// Check existence of id parameter before processing further
else if (($_SERVER["REQUEST_METHOD"] == "GET") && (!isset($_GET["id"]))) {
    // Include config file
    require_once "connect.php";

    // Prepare a select statement
    $sql = "SELECT * FROM inventory";

    if ($stmt = $mysqli->prepare($sql)) {
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();

            /* Fetch result rows as associative arrays. Since the result set
                contains more than one row, we need to use while loop */
            $inventory = [];
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                array_push($inventory,
                    [
                        "id" => $row["id"],
                        "name" => $row["item_name"],
                        "quantity" => $row["item_quantity"],
                        "total_cost" => $row["total_cost"],
                        "date_time" => $row["date_time"],
                        "courtesy_of" => $row["courtesy_of"],

                    ]);
            };

            $data = ['status' => 'success',
                'message' => "Inventory retrieved successfully",
                'data' => [
                    ...$inventory
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
    $sql = "DELETE FROM inventory WHERE id = ?";

    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $param_id);

        // Set parameters
        $param_id = trim($_POST["delete"]);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Records deleted successfully. Return success message
            $data = ['status' => 'success', 'message' => "Inventory item deleted successfully"];
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
