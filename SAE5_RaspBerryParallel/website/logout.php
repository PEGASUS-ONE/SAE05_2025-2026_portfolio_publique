<?php
include "includes/header.html";
session_start();
if (isset($_SESSION['email'])) {
    $_SESSION = array(); // Détruit toute donnée de la session (dont le login)
    session_destroy();
    include "includes/barnav.php";
    echo "<div class='general-container'><h1>Vous êtes déconnecté !</h1></div>";
} else {
    header("Location: redirection.php");
}


echo "</body>";
    include "includes/footer.html";
echo"</html>";