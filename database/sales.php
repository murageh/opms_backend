<?php

// CREATE SALE

require_once "connect.php";

// Define variables and initialize with empty values
$type = $additional_details = "";
$quantity = $unit_price = $total_price = 0;
$type_err = $additional_info_err = $quantity_err = $unit_price_err = $total_price_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST"  && !isset($_POST["delete"])) {
    
    if (!$_POST["add"]){
        // Do nothing
        $xx = ['status' => 'error', 'message' => "Could not do anything"];
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($xx);
        exit();
    }

    // Validate name
    $input_type = trim($_POST["type"]);
    if (empty($input_type)) {
        $type_err = "Please enter a name.";
    } elseif (!filter_var($input_type, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z0-9 .\s]+$/")))) {
        $type_err = "Please enter a valid type.";
    } else {
        $type = $input_type;
    }

    // Validate additional details
    $input_add_info = trim($_POST["additional_details"]);
    if (!empty($input_add_info)) {
        $additional_details = $input_add_info;
    }

    // Validate quantity
    $input_quantity = trim($_POST["quantity"]);
    if (empty($input_quantity)) {
        $quantity_err = "Please enter the quantity";
    } elseif (!ctype_digit($input_quantity)) {
        $quantity_err = "Please enter a positive integer value.";
    } else {
        $quantity = $input_quantity;
    }

    // Validate unit_price
    $input_unit_price = trim($_POST["unit_price"]);
    if (empty($input_unit_price)) {
        $quantity_err = "Please enter the quantity";
    } elseif (!ctype_digit($input_unit_price)) {
        $unit_price_err = "Please enter a positive integer value.";
    } else {
        $unit_price = $input_unit_price;
    }

    // Calculate total_price
    $total_price = $input_quantity * $input_unit_price;
	
	//Check for attachment upload
	if ((@$_FILES['attachment']['name']!="")){
		$currTime = time();
		// Where the file is going to be stored
		$target_dir = "upload/images/sales/";
		$file = $_FILES['attachment']['name'];
		$path = pathinfo($file);
		$filename = $currTime . "_" . $path['filename'];
		$ext = $path['extension'];
		$temp_name = $_FILES['attachment']['tmp_name'];
		$path_filename_ext = $target_dir.$filename.".".$ext;
 
	// Check if file already exists
	if (file_exists($path_filename_ext)) {
		$data = ['status' => 'error', 'message' => "Sorry, file already exists."];
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
		exit();
	 }else{
		 if (!file_exists($target_dir)) {
			mkdir($target_dir, 0777, true);
		}
		 move_uploaded_file($temp_name,$path_filename_ext);
		 //echo "Congratulations! File Uploaded Successfully.";
	 }
	 $attachment_img = $filename.".".$ext;
	}else{
		$attachment_img = "";
	}

    // Check input errors before inserting in database
    if (empty($type_err) && empty($unit_price_err)
        && empty($total_price_err) && empty($quantity_err)
        && empty($unit_price_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO sales (type , quantity, unit_price, total_price, additional_details, attachment) VALUES (?, ?, ?, ?, ?, ?)";

        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssssss", $param_type, $param_quantity, $param_unit_price, $param_total_price, $param_additional_details, $param_attachment);

            // Set parameters
            $param_type = $type;
            $param_quantity = $quantity;
            $param_unit_price = $unit_price;
            $param_total_price = $total_price;
            $param_additional_details = $additional_details;
			$param_attachment = $attachment_img;

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
                        "attachment" => $row["attachment"] ? "upload/images/sales/" . $row["attachment"] : null,

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
}

 /*
 * DELETE operation
 */

// Process delete operation after confirmation
else if (isset($_POST["delete"]) && !empty($_POST["delete"])) {
    // Include config file
    require_once "connect.php";

    // Prepare a delete statement
    $sql = "DELETE FROM sales WHERE id = ?";

    if ($stmt = $mysqli->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $param_id);

        // Set parameters
        $param_id = trim($_POST["delete"]);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Records deleted successfully. Return success message
            $data = ['status' => 'success', 'message' => "Sale deleted successfully"];
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data);
            exit();
        } else {
            $data = ['status' => 'error', 'message' => "An error occurred."];
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data);
        }
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