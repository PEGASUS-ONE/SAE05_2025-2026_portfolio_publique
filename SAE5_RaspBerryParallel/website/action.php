<?php


if (isset($_POST["ok"])) {
    if (!isset($_POST["email"], $_POST["mdp"])) {
        header("Location: login.php?error=2");
    } else {

        $login = $_POST["email"];
        $password = $_POST['mdp'];
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $ok = new mysqli("localhost", "apache", "sae5-db", "sae5");
        if ($ok->connect_error) {
            die("Échec de la connexion : " . $ok->connect_error);
        }
        mysqli_select_db($ok, "sae5");
        $table = "users";
        $sql = "SELECT * FROM $table";
        $result = mysqli_query($ok, $sql);
        $trouve = false;
        while ($lignes = mysqli_fetch_assoc($result)) {
            if ($login == $lignes["email"] && password_verify($password, $lignes["mdp"])) {
                session_start();
                $_SESSION["email"] = $login;
                $trouve = true;
                header("Location: accueil.php");
                exit();


            }

        }
        if (!$trouve) {
            header("Location: login.php?error=1");
            exit();
        }


    }

}  else {
    // Si quelqu'un tente d'accéder à action.php sans passer par le formulaire
    header("Location: login.php");
    exit();
}
?>