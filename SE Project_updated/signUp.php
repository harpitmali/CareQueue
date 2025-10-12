<?php
include 'db_connect.php';

$errors = [];
$firstname = '';
$lastname = '';
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate first name
    if (empty($_POST['firstname'])) {
        $errors[] = 'First name is required';
    } else {
        $firstname = trim($_POST['firstname']);
    }

    // Validate last name
    if (empty($_POST['lastname'])) {
        $errors[] = 'Last name is required';
    } else {
        $lastname = trim($_POST['lastname']);
    }

    // Validate email
    if (empty($_POST['email'])) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } else {
        $email = trim($_POST['email']);
    }

    // Validate password
    if (empty($_POST['password'])) {
        $errors[] = 'Password is required';
    } elseif (strlen($_POST['password']) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    }

    // Confirm password
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $errors[] = 'Passwords do not match';
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $firstname, $lastname, $email, $password);

        if ($stmt->execute()) {
            header("Location: login.php?registration_success=1");
            exit();
        } else {
            $errors[] = "Error: " . $stmt->error;
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
    <title>CareQueue - Sign Up</title>
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
            <p class="text-gray-600">Create your account</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="signUp.php" method="post">
            <div class="mb-4">
                <label for="firstname" class="block text-gray-700 text-sm font-semibold mb-2">First Name</label>
                <input type="text" id="firstname" name="firstname"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="John" required value="<?php echo htmlspecialchars($firstname); ?>">
            </div>
            <div class="mb-4">
                <label for="lastname" class="block text-gray-700 text-sm font-semibold mb-2">Last Name</label>
                <input type="text" id="lastname" name="lastname"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="Doe" required value="<?php echo htmlspecialchars($lastname); ?>">
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-semibold mb-2">Email Address</label>
                <input type="email" id="email" name="email"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="you@example.com" required value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                <input type="password" id="password" name="password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="••••••••" required>
            </div>
            <div class="mb-6">
                <label for="confirm_password" class="block text-gray-700 text-sm font-semibold mb-2">Confirm Password</label>♥
                <input type="password" id="confirm_password" name="confirm_password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="••••••••" required>
            </div>
            
            <button type="submit"
                    class="w-full bg-primary text-white font-bold py-3 px-4 rounded-md shadow-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-all duration-300">
                Sign Up
            </button>
        </form>

        <p class="text-center text-gray-600 text-sm mt-6">
            Already have an account? 
            <a href="login.php" class="text-primary hover:text-primary-dark font-semibold transition-colors">Log In</a>
        </p>
        <p class="text-center text-gray-600 text-sm mt-3">
            <a href="ngoAdminLogin.html" class="text-gray-500 hover:text-primary font-semibold transition-colors">Login as NGO/Admin</a>
        </p>
    </div>

</body>
</html>