<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON input.'
    ]);
    exit;
}

$idNumber = $data['idNumber'] ?? null;
$password = $data['password'] ?? null;

if (empty($idNumber) || empty($password)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID number and password are required.'
    ]);
    exit;
}

$rolesTo_check = [
    'admin' => 'admin_id',
    'doctor' => 'dr_license_number',
    'patient' => 'patient_login_id',
    'pharmacist' => 'ph_license_number'
];

$userFound = false;

foreach ($rolesTo_check as $role => $idColumn) {
    $tableName = $role . 's';

    $sql = "SELECT $idColumn, password FROM $tableName WHERE $idColumn = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        continue;
    }

    $stmt->bind_param("s", $idNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($password === $user['password']) {
            $userFound = true;

            $_SESSION['account_type'] = $role;
            $_SESSION['login_id'] = $idNumber;

            // SPECIAL HANDLING FOR PATIENTS - Get numeric patient_id
            if ($role === 'patient') {
                // Fetch the numeric patient_id using the login_id
                $patient_id_sql = "SELECT patient_id FROM patients WHERE patient_login_id = ?";
                $patient_id_stmt = $conn->prepare($patient_id_sql);
                $patient_id_stmt->bind_param("s", $idNumber);
                $patient_id_stmt->execute();
                $patient_id_result = $patient_id_stmt->get_result();

                if ($patient_id_row = $patient_id_result->fetch_assoc()) {
                    $_SESSION['user_id'] = $patient_id_row['patient_id']; // Store numeric ID
                } else {
                    // fallback - shouldn't happen but just in case
                    $_SESSION['user_id'] = $user[$idColumn];
                }
                $patient_id_stmt->close();
            } else {
                // for other roles
                $_SESSION['user_id'] = $user[$idColumn];
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful!',
                'account_type' => $role
            ]);
            
            $stmt->close();
            $conn->close();
            exit;
        }
        // Found user but password was wrong. Break loop.
        break;
    }
    
    $stmt->close();
}

echo json_encode([
    'status' => 'error',
    'message' => 'Invalid ID number or password.'
]);

$conn->close();
?>