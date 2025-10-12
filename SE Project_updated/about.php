<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareQueue - About Us</title>
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
<body class="bg-white font-sans">

    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-primary">CareQueue</a>
            <ul class="hidden md:flex items-center space-x-8">
                <li><a href="index.php#how-it-works" class="text-gray-700 hover:text-primary font-semibold">How It Works</a></li>
                <li><a href="index.php#campaigns" class="text-gray-700 hover:text-primary font-semibold">Campaigns</a></li>
                <li><a href="about.php" class="text-primary font-semibold border-b-2 border-primary">About Us</a></li>
                <li><a href="contact.php" class="text-gray-700 hover:text-primary font-semibold">Contact</a></li>
            </ul>
            <div class="hidden md:flex items-center space-x-4">
                <a href="login.php" class="text-gray-700 hover:text-primary font-semibold">Login</a>
                <a href="signUp.php" class="bg-secondary text-white font-bold py-2 px-4 rounded-md shadow hover:bg-secondary-dark">Sign Up</a>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-16">
        <section class="text-center">
            <h1 class="text-5xl font-bold text-primary mb-4">Our Story</h1>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">We started CareQueue with a simple yet powerful idea: to create a transparent and efficient bridge between those who want to give and the communities that need support.</p>
        </section>
        
        <section class="mt-20">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-4xl font-bold text-primary mb-4">Our Mission</h2>
                    <p class="text-gray-700 leading-relaxed">To ensure that every piece of clothing finds a new home where it's needed most, reducing waste and fostering a culture of compassion. We connect individual donors with verified NGOs through a secure, private, and efficient platform, making the act of giving simple and impactful.</p>
                </div>
                <img src="https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?q=80&w=2070&auto=format&fit=crop" alt="Hands holding a heart" class="rounded-lg shadow-lg">
            </div>
        </section>

         <section class="mt-20">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <img src="./Images/OurVision.png" alt="Team members collaborating" class="rounded-lg shadow-lg md:order-2">
                <div class="md:order-1">
                    <h2 class="text-4xl font-bold text-primary mb-4">Our Vision</h2>
                    <p class="text-gray-700 leading-relaxed">We envision a world where the cycle of giving is seamless and transparent, where no one lacks basic necessities like clothing, and where communities are empowered through mutual support and kindness. CareQueue aims to be the leading digital platform for in-kind donations, trusted by donors and NGOs alike.</p>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-6 py-12">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="lg:col-span-1"><h4 class="text-xl font-bold text-secondary mb-4">CareQueue</h4><p class="text-gray-400">Bridging the gap between donors and NGOs.</p></div>
                <div><h4 class="text-lg font-semibold text-secondary mb-4">Quick Links</h4><ul class="space-y-2 text-gray-300"><li><a href="about.php" class="hover:text-white">About Us</a></li><li><a href="index.php#campaigns" class="hover:text-white">Campaigns</a></li><li><a href="registerNgo.php" class="hover:text-white">For NGOs</a></li></ul></div>
                <div><h4 class="text-lg font-semibold text-secondary mb-4">Get Involved</h4><ul class="space-y-2 text-gray-300"><li><a href="index.php#campaigns" class="hover:text-white">Donate Clothes</a></li><li><a href="#" class="hover:text-white">Volunteer</a></li><li><a href="registerNgo.php" class="hover:text-white">Partner with Us</a></li></ul></div>
                <div><h4 class="text-lg font-semibold text-secondary mb-4">Contact</h4><p class="text-gray-400">123 Charity Lane,<br>Hopeville, India</p><p class="text-gray-400 mt-2">Email: contact@carequeue.org</p></div>
            </div>
            <div class="mt-12 pt-8 border-t border-gray-700 text-center text-gray-500"><p>&copy; 2025 CareQueue. All Rights Reserved.</p></div>
        </div>
    </footer>
</body>
</html>