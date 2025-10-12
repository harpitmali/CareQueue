<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];

$sql = "SELECT donations.id, donations.amount, donations.donation_date, ngos.name as ngo_name, campaigns.title as campaign_title FROM donations JOIN ngos ON donations.ngo_id = ngos.id JOIN campaigns ON donations.campaign_id = campaigns.id WHERE donations.user_id = ? ORDER BY donations.donation_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$donations = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareQueue - My Donations</title>
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
<body class="bg-light-bg font-sans">

    <header class="bg-primary shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-white">CareQueue</a>
            <ul class="flex items-center space-x-8">
                <li><a href="dashboard.php" class="text-white hover:text-secondary font-semibold transition-colors">Dashboard</a></li>
                <li><a href="profile.php" class="text-white hover:text-secondary font-semibold transition-colors">Profile</a></li>
                <li><a href="logout.php" class="bg-secondary text-white font-bold py-2 px-4 rounded-md shadow hover:bg-secondary-dark transition-all duration-300">
                    Logout
                </a></li>
            </ul>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-12">
        <h1 class="text-4xl font-bold text-primary mb-8 text-center md:text-left">My Donations History</h1>
        <p class="text-lg text-gray-700 mb-10 text-center md:text-left">A complete record of your generosity.</p>

        <?php if (isset($_GET['success'])):
        ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                Donation submitted successfully!
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Filter & Sort</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <label for="status-filter" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status-filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                        <option>All Statuses</option>
                        <option>Pending</option>
                        <option>Completed</option>
                    </select>
                </div>
                <div>
                    <label for="ngo-filter" class="block text-sm font-medium text-gray-700">NGO</label>
                    <select id="ngo-filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                        <option>All NGOs</option>
                        <?php 
                        $ngos = array_unique(array_column($donations, 'ngo_name'));
                        foreach ($ngos as $ngo):
                        ?>
                            <option><?php echo $ngo; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="sort-by" class="block text-sm font-medium text-gray-700">Sort By</label>
                    <select id="sort-by" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                        <option>Date (Newest)</option>
                        <option>Date (Oldest)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <?php if (empty($donations)):
            ?>
                <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                    <p class="text-xl font-bold text-gray-800">No donations yet!</p>
                    <p class="text-lg text-gray-700 mt-1">You have not made any donations yet. Please check back later.</p>
                </div>
            <?php else:
            ?>
                <?php foreach ($donations as $donation):
                ?>
                    <div class="bg-white p-6 rounded-xl shadow-lg flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div class="mb-4 md:mb-0">
                            <p class="text-xl font-bold text-gray-800">Donation ID: #CQD<?php echo $donation['id']; ?></p>
                            <p class="text-lg text-gray-700 mt-1">₹<?php echo number_format($donation['amount'], 2); ?> for "<?php echo $donation['campaign_title']; ?>"</p>
                            <p class="text-sm text-gray-500">To: <span class="font-semibold text-primary"><?php echo $donation['ngo_name']; ?></span></p>
                            <p class="text-sm text-gray-500">Date: <?php echo date("F j, Y", strtotime($donation['donation_date'])); ?></p>
                        </div>
                        <div class="flex flex-col items-start md:items-end">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 mb-2">
                                <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
                                    <circle cx="4" cy="4" r="3" />
                                </svg>
                                Pending
                            </span>
                            <a href="#" class="text-primary hover:text-primary-dark text-sm font-semibold">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>

    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-6 py-12">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="lg:col-span-1">
                    <h4 class="text-xl font-bold text-secondary mb-4">CareQueue</h4>
                    <p class="text-gray-400">Bridging the gap between donors and NGOs through safe and efficient clothing donation.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-secondary mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="about.php" class="hover:text-white transition-colors">About Us</a></li>
                        <li><a href="index.php#campaigns" class="hover:text-white transition-colors">Campaigns</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">For NGOs</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">FAQs</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-secondary mb-4">Get Involved</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="#" class="hover:text-white transition-colors">Donate Clothes</a></li>
                        <li><a href="#" class="hover:text-white transition-.colors">Volunteer</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Partner with Us</a></li>
                    </ul>
                </div>
                 <div>
                    <h4 class="text-lg font-semibold text-secondary mb-4">Contact</h4>
                    <p class="text-gray-400">123 Charity Lane,<br>Hopeville, India</p>
                    <p class="text-gray-400 mt-2">Email: contact@carequeue.org</p>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-gray-700 text-center text-gray-500">
                <p>&copy; 2025 CareQueue. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>