<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['campaign_id'])) {
    header("Location: dashboard.php");
    exit();
}

$campaign_id = $_GET['campaign_id'];
$user_id = $_SESSION['id'];
$errors = [];
$amount = '';

// Fetch campaign details
$stmt = $conn->prepare("SELECT campaigns.id, campaigns.ngo_id, campaigns.title, campaigns.description, campaigns.image, campaigns.amount_goal, ngos.name as ngo_name FROM campaigns JOIN ngos ON campaigns.ngo_id = ngos.id WHERE campaigns.id = ?");
$stmt->bind_param("i", $campaign_id);
$stmt->execute();
$result = $stmt->get_result();
$campaign = $result->fetch_assoc();
$stmt->close();

if (!$campaign) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate amount
    if (empty($_POST['amount'])) {
        $errors[] = 'Amount is required';
    } else {
        $amount = trim($_POST['amount']);
        if (!is_numeric($amount) || $amount <= 0) {
            $errors[] = 'Invalid amount';
        }
    }

    // Handle file upload
    $screenshot_path = null;
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] == 0) {
        $target_dir = "uploads/screenshots/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["screenshot"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["screenshot"]["tmp_name"]);
        if ($check !== false) {
            // Allow certain file formats
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                && $imageFileType != "gif") {
                $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            } else {
                if (move_uploaded_file($_FILES["screenshot"]["tmp_name"], $target_file)) {
                    $screenshot_path = $target_file;
                } else {
                    $errors[] = "Sorry, there was an error uploading your file.";
                }
            }
        } else {
            $errors[] = "File is not an image.";
        }
    } else {
        $errors[] = 'Payment screenshot is required';
    }

    // If no errors, proceed with donation
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO donations (user_id, ngo_id, campaign_id, amount, payment_screenshot) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiids", $user_id, $campaign['ngo_id'], $campaign_id, $amount, $screenshot_path);

        if ($stmt->execute()) {
            header("Location: donations.php?success=1");
            exit();
        } else {
            $errors[] = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareQueue - Donate</title>
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
            <a href="index.php" class="text-2xl font-bold text-white">CareQueue</a>
            <ul class="flex items-center space-x-8">
                <li><a href="dashboard.php" class="text-white hover:text-secondary font-semibold transition-colors">Dashboard</a></li>
                <li><a href="logout.php" class="bg-secondary text-white font-bold py-2 px-4 rounded-md shadow hover:bg-secondary-dark">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main class="container mx-auto px-6 py-12">
        <div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-primary mb-4 text-center"><?php echo $campaign['title']; ?></h1>
            <p class="text-center text-gray-600 mb-6">by <?php echo $campaign['ngo_name']; ?></p>

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

            <form action="donate.php?campaign_id=<?php echo $campaign_id; ?>" method="post" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="amount" class="block text-gray-700 text-sm font-semibold mb-2">Donation Amount</label>
                    <input type="number" id="amount" name="amount" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required value="<?php echo htmlspecialchars($amount); ?>">
                </div>

                <div class="mb-6">
                    <p class="block text-gray-700 text-sm font-semibold mb-2">Payment Method</p>
                    <div class="flex items-center space-x-8">
                        <div>
                            <p class="font-semibold">UPI</p>
                            <p class="text-gray-600">manvendra.pm@oksbi</p>
                        </div>
                        <div>
                            <p class="font-semibold">QR Code</p>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=manvendra.pm@oksbi" alt="QR Code" class="w-32 h-32">
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="screenshot" class="block text-gray-700 text-sm font-semibold mb-2">Upload Payment Screenshot</label>
                    <input type="file" id="screenshot" name="screenshot" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                </div>

                <button type="submit" class="w-full bg-primary text-white font-bold py-3 px-4 rounded-md shadow-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-all duration-300">
                    Submit Donation
                </button>
            </form>
        </div>
    </main>
</body>
</html>