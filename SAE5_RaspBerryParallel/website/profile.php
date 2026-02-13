<?php
include "includes/header.html";
include "includes/barnav.php";
if (!$_SESSION['email']) {
    header('Location: redirection.php');
} else {
    echo "<div class='general-container'><h1>Bienvenue ".$_SESSION['email']."</h1>";
    echo "<form action='logout.php'><button type='submit'>Déconnexion</button></form></div>";
}

include "includes/footer.html";
echo "</body></html>";