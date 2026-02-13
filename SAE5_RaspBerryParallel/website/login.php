<?php
include "includes/header.html";
include "includes/barnav.php";

if (isset($_GET["error"])) {
    if ($_GET["error"] == 1) {
        echo "<p>Identifiant ou mot de passe invalide</p>";
    }
    if ($_GET["error"] == 2) {
        echo "<p>Entrez un identifiant et un mot de passe</p>";
    }
    if ($_GET["error"] == 0) {
        echo "<p>Vous êtes maintenant inscrits, connectez-vous pour accéder à votre compte</p>";
    }
}
?>
<div class="form-container">
    <div class="form-box">
        <h1>Connexion</h1>
        <form method='POST' action='action.php'>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required />


            <label for="mdp">Mot de passe</label>
            <input type="password"  id="mdp" name="mdp" required />


            <a class="forgot">Mot de passe oublié ?</a>


            <button type="submit" class="primary-btn full" name ='ok'>Se connecter</button>


            <p class="center small">Pas encore inscrit ? <a href="signup.php">Inscrivez‑vous</a></p>
        </form>
    </div>
</div>


</body>
<?php
include "includes/footer.html";
?>
</html>