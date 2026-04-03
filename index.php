<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Sharing_Ride Application';
require_once __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="hero-content">
        <h2>CIS 344 Group project</h2>
        <p>The Sharing_Ride Application connects riders and drivers through a structured workflow that includes user registration, ride booking, fare calculation, trip tracking, payment recording, and role-based dashboards.</p>
        <div class="cta-group">
            <a class="button primary" href="/sharing_ride_application/pages/auth/register.php">Create Account</a>
            <a class="button secondary" href="/sharing_ride_application/pages/auth/login.php">Login</a>
        </div>
    </div>
</section>
<section class="grid two-columns">
    <article class="card">
        <h3>Prof. YANILDA PERALTA RAMOS</h3>
        <p>This project includes rider and driver account management, booking requests, automatic driver assignment based on availability, payment recording, trip history </p>
    </article>
    <article class="card">
        <h3>Ibrahim Sallieu Korgay <br> Kadidia Ouedraogo <br> Kadiatou Diallo </h3>
        <p>The project is prepared for local execution  <strong>htdocs</strong> directory, with the database designed for easy import through phpMyAdmin.</p>
    </article>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
