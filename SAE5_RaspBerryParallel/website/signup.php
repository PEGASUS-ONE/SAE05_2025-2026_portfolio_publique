<?php
include "includes/header.html";
include "includes/barnav.php";


$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérifie que les champs existent
    if (!isset($_POST['resultat_captcha'], $_POST['email'], $_POST['mdp'], $_POST['re_mdp'])) {
        $errors[] = "Formulaire incomplet.";
    } else {

        $captcha_input = $_POST['resultat_captcha'];
        $email = trim($_POST['email']);
        $password = $_POST['mdp'];
        $re_password = $_POST['re_mdp'];

        // Vérifie que le captcha existe en session
        if (!isset($_SESSION['captcha'], $_SESSION['captcha2'])) {
            $errors[] = "Erreur interne : captcha non défini.";
        } elseif ($captcha_input != ($_SESSION['captcha'] * $_SESSION['captcha2'])) {
            $errors[] = "La saisie du captcha est fausse !";
        }

        // Vérifie email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email est incorrect !";
        }

        // Vérifie mot de passe
        if (!preg_match('/^(?=.*[0-9])(?=.*[\W_]).{6,}$/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins 6 caractères, un chiffre et un caractère spécial.";
        }

        if ($password !== $re_password) {
            $errors[] = "Les mots de passe ne correspondent pas !";
        }

        // Si pas d'erreurs → traitement BDD
        if (empty($errors)) {

            $login = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
            $passwordEncrypted = password_hash($password, PASSWORD_BCRYPT);

            // BDD
            $mysqli = new mysqli("localhost", "apache", "sae5-db", "sae5");

            if ($mysqli->connect_error) {
                $errors[] = "Erreur de connexion à la base de données.";
            } else {

                // Vérifie si email existe
                $stmt = $mysqli->prepare("SELECT id_user FROM users WHERE email = ?");
                $stmt->bind_param("s", $login);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $errors[] = "Cet email est déjà utilisé !";
                } else {

                    // Insertion
                    $stmt = $mysqli->prepare("INSERT INTO users (email, mdp) VALUES (?, ?)");
                    $stmt->bind_param("ss", $login, $passwordEncrypted);

                    if ($stmt->execute()) {
                        // Succès → redirection
                        header("Location: login.php");
                        exit;
                    } else {
                        $errors[] = "Erreur SQL lors de l'inscription.";
                    }
                }

                $stmt->close();
                $mysqli->close();
            }
        }
    }
}


?>

<div class='form-container'>
    <div class='form-box'>
        <h1>Inscription</h1>


        <form method="POST" action="signup.php">

            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                   required />

            <label for="mdp">Mot de passe</label>
            <input type="password" id="mdp" name="mdp" required />

            <label for="mdp2">Confirmer le mot de passe</label>
            <input type="password" id="mdp2" name="re_mdp" required />

            <div>
                <img src="includes/captcha.php" alt="captcha">
            </div>

            <label>Entrer le résultat de l'opération
                <input type="text" name="resultat_captcha" required>
            </label>

            <button type="submit" class="primary-btn full">S'inscrire</button>
        </form>
        <!-- Affichage des erreurs -->
        <?php
        if (!empty($errors)) {
            foreach ($errors as $err) {
                echo "<p id='error'>" . htmlspecialchars($err) . "</p>";
            }
        }
        ?>
    </div>



</div>

</body>
<?php
include "includes/footer.html";
?>
</html>