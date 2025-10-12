<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_ngo'])) {
    header("Location: ngoAdminLogin.php");
    exit();
}

$ngo_id = $_SESSION['id'];

// Fetch NGO's name
$stmt = $conn->prepare("SELECT name FROM ngos WHERE id = ?");
$stmt->bind_param("i", $ngo_id);
$stmt->execute();
$stmt->bind_result($ngo_name);
$stmt->fetch();
$stmt->close();

$errors = [];
$title = '';
$description = '';
$amount_goal = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate title
    if (empty($_POST['title'])) {
        $errors[] = 'Campaign title is required';
    } else {
        $title = trim($_POST['title']);
    }

    // Validate description
    if (empty($_POST['description'])) {
        $errors[] = 'Description is required';
    } else {
        $description = trim($_POST['description']);
    }

    // Validate amount goal
    if (empty($_POST['amount_goal'])) {
        $errors[] = 'Amount goal is required';
    } else {
        $amount_goal = trim($_POST['amount_goal']);
    }

    // Handle file upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            // Allow certain file formats
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                && $imageFileType != "gif") {
                $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            } else {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = $target_file;
                } else {
                    $errors[] = "Sorry, there was an error uploading your file.";
                }
            }
        } else {
            $errors[] = "File is not an image.";
        }
    }

    // If no errors, proceed with campaign creation
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO campaigns (ngo_id, title, description, amount_goal, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $ngo_id, $title, $description, $amount_goal, $image_path);

        if ($stmt->execute()) {
            header("Location: ngo_dashboard.php?success=1");
            exit();
        } else {
            $errors[] = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Fetch existing campaigns for the NGO
$stmt = $conn->prepare("SELECT id, title, description, amount_goal, image, created_at FROM campaigns WHERE ngo_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $ngo_id);
$stmt->execute();
$result = $stmt->get_result();
$campaigns = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// For each campaign, get the total donated amount
foreach ($campaigns as &$campaign) {
    $campaign_id = $campaign['id'];
    $stmt = $conn->prepare("SELECT SUM(amount) as total_donated FROM donations WHERE campaign_id = ?");
    $stmt->bind_param("i", $campaign_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $donation_sum = $result->fetch_assoc();
    $stmt->close();

    $total_donated = $donation_sum['total_donated'] ?? 0;
    $campaign['total_donated'] = $total_donated;

    if ($campaign['amount_goal'] > 0) {
        $campaign['progress'] = ($total_donated / $campaign['amount_goal']) * 100;
    } else {
        $campaign['progress'] = 0;
    }
}
unset($campaign);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareQueue - NGO Dashboard</title>
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
                <li><a href="logout.php" class="bg-secondary text-white font-bold py-2 px-4 rounded-md shadow hover:bg-secondary-dark">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main class="container mx-auto px-6 py-12">
        <h1 class="text-4xl font-bold text-primary mb-8 text-center md:text-left"><?php echo $ngo_name; ?> Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Create a New Campaign</h2>
                <?php if (isset($_GET['success'])):
                ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        Campaign created successfully!
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors)):
                ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <ul>
                            <?php foreach ($errors as $error):
                            ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="ngo_dashboard.php" method="post" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="title" class="block text-gray-700 text-sm font-semibold mb-2">Campaign Title</label>
                        <input type="text" id="title" name="title" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required value="<?php echo htmlspecialchars($title); ?>">
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-gray-700 text-sm font-semibold mb-2">Description</label>
                        <textarea id="description" name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required><?php echo htmlspecialchars($description); ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="amount_goal" class="block text-gray-700 text-sm font-semibold mb-2">Amount Goal</label>
                        <input type="text" id="amount_goal" name="amount_goal" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required value="<?php echo htmlspecialchars($amount_goal); ?>">
                    </div>
                    <div class="mb-4">
                        <label for="image" class="block text-gray-700 text-sm font-semibold mb-2">Campaign Image</label>
                        <input type="file" id="image" name="image" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <button type="submit" class="w-full bg-primary text-white font-bold py-3 px-4 rounded-md shadow-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-all duration-300">
                        Create Campaign
                    </button>
                </form>
            </div>
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Your Campaigns</h2>
                <div class="space-y-4">
                    <?php if (empty($campaigns)):
                    ?>
                        <p>You have not created any campaigns yet.</p>
                    <?php else:
                    ?>
                        <?php foreach ($campaigns as $campaign):
                        ?>
                            <div class="border p-4 rounded-md">
                                <?php if ($campaign['image']):
                                ?>
                                    <img src="<?php echo $campaign['image']; ?>" alt="<?php echo $campaign['title']; ?>" class="w-full h-32 object-cover rounded-md mb-4">
                                <?php endif; ?>
                                <h3 class="text-lg font-bold"><?php echo $campaign['title']; ?></h3>
                                <p class="text-gray-600"><?php echo $campaign['description']; ?></p>
                                <div class="mt-4">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                        <div class="bg-primary h-2.5 rounded-full" style="width: <?php echo $campaign['progress']; ?>%"></div>
                                    </div>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-sm font-medium text-gray-700">$<?php echo number_format($campaign['total_donated'], 2); ?> raised</span>
                                        <span class="text-sm font-medium text-gray-500">of $<?php echo number_format($campaign['amount_goal'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>