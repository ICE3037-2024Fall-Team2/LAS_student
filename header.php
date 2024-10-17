<div id="header">
<a class="no-style" href="index.php">
    <div class="left">
            <img src="img/mini-logo-color2.png" alt="Logo" class="logo-circle">
            <span>Lab Reservation</span>
    </div>
    </a>
    <div class="right">
        <i class="fa-solid fa-user" onclick="toggleMenu()"></i>
        <div class="dropdown" id="userMenu">
            <i class="fa-solid fa-x close-dropdown" onclick="closeMenu()"></i>
            <a href="profile.php#account-info">Account Info</a>
            <a href="profile.php#reservations-info">Reservations</a>
            <a href="profile.php#past-reservations">Past Reservations</a>
            <a href="profile.php#change-password">Change Password</a>
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
