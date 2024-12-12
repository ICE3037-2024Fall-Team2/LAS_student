<div id="header">
<a class="no-style" href="index.php">
    <div class="left">
            <img src="img/mini-logo-color2.png" alt="Logo" class="logo-circle">
            <span>Lab Reservation</span>
    </div>
    </a>
    <div class="right">
        <i class="fa-solid fa-bars" onclick="toggleMenu()"></i>
        <div class="dropdown" id="userMenu">
            <i class="fa-solid fa-x close-dropdown" onclick="closeMenu()"></i>
            <h1>Menu</h1>
            <!-- checks if the current page is profile.php -->
            <?php
            $currentPage = basename($_SERVER['PHP_SELF']); // gets the current file name
            if ($currentPage === 'profile.php'): ?>
                <a href="index.php" class="first-child">Reservation page</a>
            <?php else: ?>
                <a href="profile.php" class="first-child">Account Info</a>
            <?php endif; ?>
            <hr>
            <a href="profile.php#reservations-info">Upcoming Reservations</a>
            <a href="profile.php#past-reservations">Past Reservations</a>
            <a href="profile.php#change-password">Change Password</a>
            <hr>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
</div>

<style>
    .no-style {
            text-decoration: none; 
            color: inherit; 
        }
    span {
        
    }
</style>

<script>
function toggleMenu() {
    var menu = document.getElementById('userMenu');
    menu.classList.toggle('active');
}

function closeMenu() {
    var menu = document.getElementById('userMenu');
    menu.classList.remove('active');
}

// Close the dropdown if the user clicks outside of it
document.addEventListener('click', function(event) {
    var menu = document.getElementById('userMenu');
    var icon = document.querySelector('.fa-user');

    // If the click is outside the dropdown and the icon, close the dropdown
    if (!menu.contains(event.target) && !icon.contains(event.target)) {
        menu.classList.remove('active');
    }
});
</script>
