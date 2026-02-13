<?php
session_start();
?>

<nav class="navbar">
    <div class="nav-left">
        <a href="accueil.php" class="logo" aria-label="Accueil – ClusterMania">
            <img id="logo" src="Images/Logo/Logo.png" alt="">
            <span>ClusterMania</span>
        </a>

        <a class="nav-link" href="accueil.php">Accueil</a>


    <?php if (isset($_SESSION['email'])): ?>

        <a class="nav-link" href="tab_bord.php">Tableau de bord</a>
        </div>

        <div class="nav-right">

            <a href="logout.php" class="btn-login">Déconnexion</a>
            <div class="dropdown">
                <button class="dropbtn" aria-label="Menu utilisateur">
                    <img id="profile_icon" src="Images/profile_icon.png" alt="">
                </button>

                <div class="dropdown-content">
                    <a href="profile.php">Profil</a>
                    <a href="logout.php">Déconnexion</a>
                </div>
            </div>
        </div>
    <?php else: ?>
    </div>
        <div class="nav-right">
            <a href="signup.php">S'inscrire</a>
            <a href="login.php" class="btn-login">Se connecter</a>
        </div>
    <?php endif; ?>
</nav>