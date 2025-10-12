<?php
session_start();
include 'db_connect.php';

$errors = [];
$email = '';

if (isset($_GET['registration_success'])) {
    $success_message = "Registration successful! Please log in.";
}

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
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
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

                header("Location: dashboard.html");
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
    <title>CareQueue - Login</title>
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
            <p class="text-gray-600">Login to your account</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

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

        <form action="login.php" method="post">
            <div class="mb-6">
                <label for="email" class="block text-gray-700 text-sm font-semibold mb-2">Email Address</label>
                <input type="email" id="email" name="email"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="you@example.com" required value="<?php echo htmlspecialchars($email); ?>">
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
                    class="w-full bg-primary text-white font-bold py-3 px-4 rounded-md shadow-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-all duration-300">
                Log In
            </button>
        </form>

        <p class="text-center text-gray-600 text-sm mt-6">
            Don't have an account? 
            <a href="signUp.php" class="text-primary hover:text-primary-dark font-semibold transition-colors">Sign Up</a>
        </p>
        <p class="text-center text-gray-600 text-sm mt-3">
            <a href="ngoAdminLogin.html" class="text-gray-500 hover:text-primary font-semibold transition-colors">Login as NGO/Admin</a>
        </p>
    </div>

</body>
</html>