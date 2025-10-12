<?php
session_start();
include 'db_connect.php';

$errors = [];
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    if (empty($_POST['email'])) {
        $errors[] = 'Email is required';
    } else {
        $email = trim($_POST['email']);
    }

    // Validate password
    if (empty($_POST['password'])) {
        $errors[] = 'Password is required';
    }

    // If no errors, proceed with login
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, password FROM ngos WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password);
            $stmt->fetch();

            if (password_verify($_POST['password'], $hashed_password)) {
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $id;
                $_SESSION['email'] = $email;
                $_SESSION['is_ngo'] = true;

                header("Location: ngo_dashboard.php");
                exit();
            } else {
                $errors[] = "Invalid email or password";
            }
        } else {
            $errors[] = "Invalid email or password";
        }

        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareQueue - NGO/Admin Login</title>
    <link rel="icon" type="image/x-icon" href="./Images/icon1.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script>
        // Custom Tailwind configuration to use the same color palette
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        'primary': '#2a9d8f',   // Original Teal
                        'primary-dark': '#248a7d',
                        'secondary': '#e9c46a', // Original Gold
                        'secondary-dark': '#d8b45a',
                        'light-bg': '#e8f5e9',   // Light green background
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-light-bg flex items-center justify-center min-h-screen font-sans">

    <div class="bg-white p-8 md:p-10 rounded-xl shadow-lg w-full max-w-md mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-primary mb-2">CareQueue</h1>
            <p class="text-gray-600">NGO / Admin Login</p>
        </div>

        <?php if (!empty($errors)):
        ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="ngoAdminLogin.php" method="post">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                <input type="text" id="email" name="email"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="Enter your NGO Email" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                <input type="password" id="password" name="password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="••••••••" required>
            </div>
            
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <input type="checkbox" id="remember_me" name="remember_me" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-700">Remember me</label>
                </div>
                <a href="forgotPass.html" class="text-sm text-primary hover:text-primary-dark font-semibold transition-colors">Forgot Password?</a>
            </div>

            <button type="submit"
                    class="w-full bg-secondary text-white font-bold py-3 px-4 rounded-md shadow-md hover:bg-secondary-dark focus:outline-none focus:ring-2 focus:ring-secondary focus:ring-offset-2 transition-all duration-300">
                Login as NGO / Admin
            </button>
        </form>

        <p class="text-center text-gray-600 text-sm mt-6">
            Are you a donor? 
            <a href="login.php" class="text-primary hover:text-primary-dark font-semibold transition-colors">Log in here</a>
        </p>
        <p class="text-center text-gray-600 text-sm mt-3">
            Don't have an NGO/Admin account?
            <a href="registerNgo.php" class="text-primary hover:text-primary-dark font-semibold transition-colors">Register your NGO</a>
        </p>
    </div>

</body>
</html>