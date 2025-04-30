<?php 
session_start();
$conn = new mysqli("localhost", "root", "", "ePKAH");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Elak undefined variable warning
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $telefon = preg_replace('/[^0-9]/', '', $_POST['telefon']); // Hanya nombor sahaja
    $password = $_POST['password'];

    // Debug: Paparkan nombor telefon yang dimasukkan
    echo "Nombor telefon yang dimasukkan: " . $telefon;

    // Cari pengguna ikut no telefon
    $stmt = $conn->prepare("SELECT * FROM users WHERE telefon = ?");
    $stmt->bind_param("s", $telefon);
    $stmt->execute();
    $result = $stmt->get_result();

    // Debug: Paparkan jumlah pengguna dijumpai
    echo "Jumlah pengguna dijumpai: " . $result->num_rows;

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Check user status before login
            if ($user['status'] == 'approved') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['peranan'] = strtolower($user['peranan']);

                if ($_SESSION['peranan'] == 'admin') {
                    header("Location: admin_dashboard.php");
                    exit;
                } elseif ($_SESSION['peranan'] == 'vendor') {
                    header("Location: vendor_dashboard.php");
                    exit;
                } else {
                    header("Location: pengguna_dashboard.php");
                    exit;
                }
            } else {
                $error = "Katalaluan salah.";
            }
        } else {
            $error = "Katalaluan salah.";
        }
    } else {
        $error = "No telefon tidak dijumpai.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Log Masuk e-PKAH</title>
    <style>
        body {
            font-family: Consolas, sans-serif;
            background: #e8f5e9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-size: 28px;
        }

        .login-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 400px;
        }

        .logo-epkah {
            width: 120px;
            height: auto;
            display: block;
            margin: 20px auto;
        }

        .login-box h2 {
            margin-bottom: 25px;
            color: #2e7d32;
            font-size: 28px;
        }

        .login-box input {
            width: 100%;
            padding: 4px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 24px;
        }

        .login-box button {
            width: 100%;
            padding: 4px;
            background-color: #43a047;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 24px;
            cursor: pointer;
        }

        .login-box button:hover {
            background-color: #388e3c;
        }

        .login-box a {
            display: block;
            margin-top: 14px;
            color: #2e7d32;
            text-decoration: none;
            font-size: 18px;
        }

        .error-msg {
            color: red;
            margin-bottom: 15px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <img src="logo_epkah.png" alt="Logo ePKAH" class="logo-epkah">
        <h2>Log Masuk Pengguna</h2>

        <?php if ($error != "") echo "<div class='error-msg'>$error</div>"; ?>

        <form method="POST" action="">
            <input type="text" name="telefon" placeholder="No Telefon" required>
            <input type="password" name="password" placeholder="Katalaluan" required>
            <button type="submit">Log Masuk</button>
        </form>

        <a href="signup.php">Daftar Akaun Baru</a>
        <a href="forgot_password.php">Lupa Kata Laluan?</a>
    </div>
</body>
</html>
