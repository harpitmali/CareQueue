<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);

    $stmt = $conn->prepare("UPDATE users SET firstname = ?, lastname = ? WHERE id = ?");
    $stmt->bind_param("ssi", $firstname, $lastname, $user_id);

    if ($stmt->execute()) {
        header("Location: profile.php?update_success=1");
        exit();
    } else {
        $errors[] = "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    $stmt = $conn->prepare("SELECT firstname, lastname, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($firstname, $lastname, $email);
    $stmt->fetch();
    $stmt->close();
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareQueue - Edit Profile</title>
    <link rel="icon" type="image/x-icon" href="./Images/icon1.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script>
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
<body class="bg-light-bg font-sans">

    <header class="bg-primary shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.html" class="text-2xl font-bold text-white">CareQueue</a>
            <ul class="flex items-center space-x-8">
                <li><a href="dashboard.php" class="text-white hover:text-secondary font-semibold transition-colors">Dashboard</a></li>
                <li><a href="donations.php" class="text-white hover:text-secondary font-semibold transition-colors">My Donations</a></li>
                <li><a href="profile.php" class="text-secondary font-semibold transition-colors border-b-2 border-secondary pb-1">Profile</a></li>
                <li><a href="logout.php" class="bg-secondary text-white font-bold py-2 px-4 rounded-md shadow hover:bg-secondary-dark transition-all duration-300">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-12">
        <h1 class="text-4xl font-bold text-primary mb-8 text-center md:text-left">Edit Profile</h1>
        
        <div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl mx-auto">
            <form action="editProfile.php" method="POST">
                <div class="space-y-6">
                    <div>
                        <label for="firstname" class="block text-sm font-semibold text-gray-700 mb-1">First Name</label>
                        <input type="text" id="firstname" name="firstname" value="<?php echo $firstname; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label for="lastname" class="block text-sm font-semibold text-gray-700 mb-1">Last Name</label>
                        <input type="text" id="lastname" name="lastname" value="<?php echo $lastname; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                        <input type="email" id="email" value="<?php echo $email; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed" readonly>
                         <p class="text-xs text-gray-500 mt-1">Email address cannot be changed.</p>
                    </div>
                </div>

                <div class="mt-10 flex justify-center space-x-4">
                    <a href="profile.php" class="bg-gray-200 text-gray-700 font-bold py-3 px-8 rounded-lg hover:bg-gray-300 transition-all duration-300">Cancel</a>
                    <button type="submit" class="bg-primary text-white font-bold py-3 px-8 rounded-lg shadow-md hover:bg-primary-dark transition-all duration-300">Save Changes</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>