<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];

// Fetch user's name
$stmt = $conn->prepare("SELECT firstname FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($firstname);
$stmt->fetch();
$stmt->close();

// Fetch user's donation stats
$stmt = $conn->prepare("SELECT COUNT(*) as total_donations, SUM(amount) as total_amount FROM donations WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$donation_stats = $result->fetch_assoc();
$stmt->close();

// Fetch recent donations
$stmt = $conn->prepare("SELECT donations.id, donations.amount, donations.donation_date, ngos.name as ngo_name FROM donations JOIN ngos ON donations.ngo_id = ngos.id WHERE donations.user_id = ? ORDER BY donations.donation_date DESC LIMIT 2");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$recent_donations = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch all campaigns
$campaigns_result = $conn->query("SELECT campaigns.id, campaigns.title, campaigns.description, campaigns.image, campaigns.amount_goal, ngos.name as ngo_name FROM campaigns JOIN ngos ON campaigns.ngo_id = ngos.id ORDER BY campaigns.created_at DESC");
$campaigns = $campaigns_result->fetch_all(MYSQLI_ASSOC);

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
unset($campaign); // unset the reference

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareQueue - Dashboard</title>
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
<body class="bg-light-bg font-sans flex flex-col min-h-screen"> <header class="bg-primary shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-white">CareQueue</a>
            <ul class="flex items-center space-x-8">
                <li><a href="donations.php" class="text-white hover:text-secondary font-semibold transition-colors">My Donations</a></li>
                <li><a href="profile.php" class="text-white hover:text-secondary font-semibold transition-colors">Profile</a></li>
                <li><a href="logout.php" class="bg-secondary text-white font-bold py-2 px-4 rounded-md shadow hover:bg-secondary-dark transition-all duration-300">
                    Logout
                </a></li>
            </ul>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-12 flex-grow"> <h1 class="text-4xl font-bold text-primary mb-8 text-center md:text-left">Welcome, <?php echo $firstname; ?>!</h1>
        <p class="text-lg text-gray-700 mb-10 text-center md:text-left">Here's a summary of your activity and ways to continue making a difference.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <div class="bg-white p-6 rounded-xl shadow-lg text-center transform hover:-translate-y-1 transition-transform duration-300">
                <div class="text-5xl text-secondary mb-4">📦</div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Total Donations</h3>
                <p class="text-gray-600 text-3xl font-semibold"><?php echo $donation_stats['total_donations'] ?? 0; ?></p>
                <p class="text-primary text-sm mt-2">items contributed</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg text-center transform hover:-translate-y-1 transition-transform duration-300">
                <div class="text-5xl text-secondary mb-4">💖</div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Total Amount</h3>
                <p class="text-gray-600 text-3xl font-semibold">₹<?php echo number_format($donation_stats['total_amount'] ?? 0, 2); ?></p>
                <p class="text-primary text-sm mt-2">making an impact</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg text-center transform hover:-translate-y-1 transition-transform duration-300">
                <div class="text-5xl text-secondary mb-4">👤</div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Profile Status</h3>
                <p class="text-gray-600 text-3xl font-semibold">100%</p>
                <p class="text-primary text-sm mt-2">complete</p>
            </div>
        </div>

        <section>
            <h2 class="text-3xl font-bold text-primary mb-6 text-center md:text-left">Active Campaigns</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (empty($campaigns)):
?>
                    <div class="bg-white p-6 rounded-lg shadow-md text-center md:col-span-3">
                        <p class="text-lg font-semibold text-gray-800">No active campaigns</p>
                        <p class="text-gray-600 text-sm">There are no active campaigns at the moment. Please check back later.</p>
                    </div>
                <?php else:
?>
                    <?php foreach ($campaigns as $campaign):
?>
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col">
                            <?php if ($campaign['image']):
?>
                                <img src="<?php echo $campaign['image']; ?>" alt="<?php echo $campaign['title']; ?>" class="w-full h-48 object-cover">
                            <?php endif; ?>
                            <div class="p-6 flex-grow">
                                <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo $campaign['title']; ?></h3>
                                <p class="text-sm text-gray-500 mb-2">by <span class="font-semibold text-primary"><?php echo $campaign['ngo_name']; ?></span></p>
                                <p class="text-gray-600 mb-4 flex-grow"><?php echo substr($campaign['description'], 0, 100); ?>...</p>
                                <div class="mb-4">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                        <div class="bg-primary h-2.5 rounded-full" style="width: <?php echo $campaign['progress']; ?>%"></div>
                                    </div>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-sm font-medium text-gray-700">₹<?php echo number_format($campaign['total_donated'], 2); ?> raised</span>
                                        <span class="text-sm font-medium text-gray-500">of ₹<?php echo number_format($campaign['amount_goal'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="px-6 pb-6">
                                <a href="donate.php?campaign_id=<?php echo $campaign['id']; ?>" class="block text-center bg-primary text-white font-bold py-3 px-4 rounded-md shadow-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-all duration-300">
                                    Donate Now
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

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
                        <li><a href="#" class="hover:text-white transition-colors">Volunteer</a></li>
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