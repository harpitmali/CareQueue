<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];

$stmt = $conn->prepare("SELECT firstname, lastname, email, reg_date FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($firstname, $lastname, $email, $reg_date);
$stmt->fetch();
$stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareQueue - My Profile</title>
    <link rel="icon" type="image/x-icon" href="./Images/icon1.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Poppins', 'sans-serif'], },
                    colors: {
                        'primary': '#2a9d8f', 'primary-dark': '#248a7d',
                        'secondary': '#e9c46a', 'secondary-dark': '#d8b45a',
                        'light-bg': '#e8f5e9',
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
                <li><a href="dashboard.php" class="text-white hover:text-secondary font-semibold">Dashboard</a></li>
                <li><a href="donations.php" class="text-white hover:text-secondary font-semibold">My Donations</a></li>
                <li><a href="profile.php" class="text-secondary font-semibold border-b-2 border-secondary pb-1">Profile</a></li>
                <li><a href="logout.php" class="bg-secondary text-white font-bold py-2 px-4 rounded-md shadow hover:bg-secondary-dark">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main class="container mx-auto px-6 py-12">
        <h1 class="text-4xl font-bold text-primary mb-8 text-center md:text-left">My Profile</h1>
        <p class="text-lg text-gray-700 mb-10 text-center md:text-left">View and manage your personal details.</p>
        <div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl mx-auto">
            <div class="flex flex-col items-center mb-6">
                <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center text-6xl text-gray-500 font-bold mb-4"><?php echo strtoupper(substr($firstname, 0, 1) . substr($lastname, 0, 1)); ?></div>
                <h2 class="text-3xl font-bold text-gray-800"><?php echo $firstname . ' ' . $lastname; ?></h2>
                <p class="text-gray-600">Donor Account</p>
            </div>
            <div class="space-y-6">
                <div><label class="block text-sm font-medium text-gray-700">Email Address</label><p class="text-lg text-gray-900"><?php echo $email; ?></p></div>
                <div><label class="block text-sm font-medium text-gray-700">Member Since</label><p class="text-lg text-gray-900"><?php echo date("F j, Y", strtotime($reg_date)); ?></p></div>
            </div>
            <div class="mt-10 text-center">
                <a href="editProfile.php" class="bg-primary text-white font-bold py-3 px-8 rounded-lg shadow-md hover:bg-primary-dark">Edit Profile</a>
                <a href="logout.php" class="ml-4 text-red-600 hover:text-red-800 font-semibold py-3 px-8 rounded-lg">Logout</a>
            </div>
        </div>
    </main>
</body>
</html>