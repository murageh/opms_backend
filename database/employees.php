<?php

// CREATE EMPLOYEE

require_once "connect.php";

// Define variables and initialize with empty values
$name = $mobileno = "";
$salary = 0;
$name_err = $mobileno_err = $salary_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST["delete"])) {
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    if (@!$data->add) {
        // Do nothing
        $xx = ['status' => 'error', 'message' => "Could not do anything"];
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($xx);
        exit();
    }

    // Validate name
    $input_name = trim($data->name);
    if (empty($input_name)) {
        $name_err = "Please enter a name.";
    } elseif (!filter_var($input_name, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z0-9 .\s]+$/")))) {
        $name_err = "Please enter a valid name.";
    } else {
        $name = $input_name;
    }

    // Validate mobile number
    $input_mobile = trim($data->mobileno);
    if (empty($input_mobile)) {
        $mobileno_err = "Please enter a mobile number.";
    } else {
        $mobileno = $input_mobile;
    }

    // Validate salary
    $input_salary = trim($data->salary);
    if (empty($input_salary)) {
        $salary_err = "Please enter the salary amount.";
    } elseif (!ctype_digit($input_salary)) {
        $salary_err = "Please enter a positive integer value.";
    } else {
        $salary = $input_salary;
    }

    // Check input errors before inserting in database
    if (empty($name_err) && empty($address_err) && empty($salary_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO employees (name, mobileno, salary) VALUES (?, ?, ?)";

        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sss", $param_name, $param_mobileno, $param_salary);

            // Set parameters
            $param_name = $name;
            $param_mobileno = $mobileno;
            $param_salary = $salary;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Records created successfully. Return success json.
                $data = ['status' => 'success', 'message' => "Employee added successfully"];
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
 * FETCH ALL EMPLOYEES
 */

// Check existence of id parameter before processing further
else if (($_SERVER["REQUEST_METHOD"] == "GET") && (!isset($_GET["id"]))) {
    // Include config file
    require_once "connect.php";

    // Prepare a select statement
    $sql = "SELECT * FROM employees";

    if ($stmt = $mysqli->prepare($sql)) {
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();

            /* Fetch result rows as associative arrays. Since the result set
                contains more than one row, we need to use while loop */
            $employees = [];
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                array_push($employees,
                    [
                        "id" => $row["id"],
                        "name" => $row["name"],
                        'mobileno' => $row["mobileno"],
                        'date_joined' => $row["date_joined"],
                        'salary' => $row["salary"]
                    ]
                );
            };

            $data = ['status' => 'success',
                'message' => "Employees retrieved successfully",
                'data' => [
                    ...$employees
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
 * GET EMPLOYEE BY id
 */

// Check existence of id parameter before processing further
else if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    // Include config file
    require_once "connect.php";

    // Prepare a select statement
    $sql = "SELECT * FROM employees WHERE id = ?";

    if ($stmt = $mysqli->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $param_id);

        // Set parameters
        $param_id = trim($_GET["id"]);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                /* Fetch result row as an associative array. Since the result set
                contains only one row, we don't need to use while loop */
                $row = $result->fetch_array(MYSQLI_ASSOC);

                $data = ['status' => 'success',
                    'message' => "Employee retrieved successfully",
                    'data' => [
                        'id' => $row["id"],
                        'name' => $row["name"],
                        'mobileno' => $row["mobileno"],
                        'date_joined' => $row["date_joined"],
                        'salary' => $row["salary"]
                    ]
                ];
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($data);
            } else {
                // URL doesn't contain valid id parameter. Redirect to error page
                $data = ['status' => 'error', 'message' => "Employee not found"];
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($data);
                exit();
            }

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
}


/*
 * DELETE operation
 */

// Process delete operation after confirmation
else if (isset($_POST["delete"]) && !empty($_POST["delete"])) {
    // Include config file
    require_once "connect.php";

    // Prepare a delete statement
    $sql = "DELETE FROM employees WHERE id = ?";

    if ($stmt = $mysqli->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $param_id);

        // Set parameters
        $param_id = trim($_POST["delete"]);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Records deleted successfully. Return success message
            $data = ['status' => 'success', 'message' => "Employee deleted successfully"];
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
} else {
    // No operation was matched. Return error
    $data = ['status' => 'error', 'message' => "No operation matched."];
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}
