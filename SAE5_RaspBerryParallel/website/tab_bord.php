<?php

include "includes/header.html";
include "includes/barnav.php";
// require_once "fonction_scalabilite.php";
require_once "form_param.php";
// Initialisation de la variable pour le résultat
$resultat = 'Ici sera affiché le résultat';
if (isset($_SESSION['email'])) {
    if (isset($_POST["lancer"])) {
	$moduleKey = $_POST['module'];
	$p1 = isset($_POST['pi1']) ? '1' : '0';
        $p2 = isset($_POST['pi2']) ? '1' : '0';
        $p3 = isset($_POST['pi3']) ? '1' : '0';
        $p4 = isset($_POST['pi4']) ? '1' : '0';
        $cnat = isset($_POST['cnat']) ? '1' : '0';
	$script = $_POST['script'];

        # Ajout d'une gestion des paramètres utilisateurs adapté au script shell du RPI
        $cmd = "sudo -u mpiuser /home/mpiuser/run_mpi.sh $moduleKey $p1 $p2 $p3 $p4 $cnat $script";

        // On vérifie le langage dans le tableau $modules
        if ($moduleKey === 'prime') {
            $nb_pb = (int)$_POST['nb_prim'];
	    $cmd .= " python $nb_pb";
        }
	elseif ($moduleKey === 'mc') {
	    $nb_pb = (int)$_POST['nb_pts'];
	    if (substr($script, -3) === '.py') {
		$cmd .= " python $nb_pb";
	    } else {
		$cmd .= " java $nb_pb";
	    }
	}
        elseif ($moduleKey === 'integral') {
            $script = __DIR__ . '/mnt/cluster_programs/Integral/'; # Adapation du répertoire des programmes au RPI
            $javaFile = $modules[$moduleKey]['script'][1];
            $className = pathinfo($javaFile, PATHINFO_FILENAME);

            $nbWorkers = (int)$_POST['nb_worker'];
            $port = 25545;
            for ($i = 0; $i < $nbWorkers; $i++) {
                pclose(popen(
                    "start /B java -cp \"$script\" WorkerSimpsonSocket  $port",
                    "r"
                ));
            }

            // Laisse le temps aux workers de s'initialiser
            sleep(2);

            $javaFile = $modules[$moduleKey]['script'][0];
            $a = escapeshellarg($_POST['a']);
            $b = escapeshellarg($_POST['b']);
            $n = escapeshellarg($_POST['n']);
            $mu = escapeshellarg($_POST['mu']);
            $sigma = escapeshellarg($_POST['sigma']);
            $className = pathinfo($javaFile, PATHINFO_FILENAME);
            $cmd = "java -cp \"$script\" $className $a $b $n $mu $sigma 2>&1";

        } elseif (isset($_POST['scal'])) {
$nom_script = $_POST['script'];
$mode_scalability = $_POST['mode_sc'];

$cmd = "python3 /mnt/cluster_programs/scalability/MC/generate_graphe.py " . escapeshellarg($nom_script) . " " . escapeshellarg($mode_scalability) . "";
$img = shell_exec($cmd);
	}




	if (!isset($_POST['scal'])) {
        $start = microtime(true);
        $output = shell_exec($cmd);
        $end = microtime(true);

        $executionTime = round($end - $start, 4);

        // Stockage du résultat pour affichage
        $resultat = "<pre>$output<pre>";
	}


    }
}
else {
    header("Location: redirection.php");
}


?>
<h1>Tableau de Bord</h1>
<div class="container_tab_bord">

    <aside class="sidebar left">
        <h2>Autres programmes</h2>
        <button id='btn_prog1' onclick="changeTab('div_prog1')" class='tablink'>Nombre premier</button>
        <button id='btn_prog2' onclick="changeTab('div_prog2')" class='tablink'>Pi_Montecarlo</button>
        <button id='btn_prog3' onclick="changeTab('div_prog3')" class='tablink'>Calcul Intégral Simpson</button>
        <button id='btn_scal' onclick="changeTab('scalab')" class='tablink'>Scalabilité</button>
    </aside>

    <section class="main">
        <?php foreach ($modules as $key => $m): ?>
            <div id="<?php echo $m['id']; ?>" class="tabcontent">
                <form method="POST" class="form-box">
                    <h2><?php echo $m['titre']; ?></h2>

		    <label><h3>Sélection du programme</h3></label>
		    <select name="script">
		    <?php foreach ($m['scripts'] as $s): ?>
			<option value="<?php echo $s['file']; ?>"><?php echo $s['name']; ?></option>
		    <?php endforeach; ?>
		    </select>

                    <h3>Sélection des workers</h3>
                    <label for="pi1">Node 1</label>
                    <input type="checkbox" id="pi1" name="pi1" value="pi1">
                    <label for="pi2">Node 2</label>
                    <input type="checkbox" id="pi2" name="pi2" value="pi2">
                    <label for="pi3">Node 3</label>
                    <input type="checkbox" id="pi3" name="pi3" value="pi3">
                    <label for="pi4">Node 4</label>
                    <input type="checkbox" id="pi4" name="pi4" value="pi4">
                    <label for="cnat">Contrôleur</label>
                    <input type="checkbox" id="cnat" name="cnat" value="cnat">

                    <?php foreach ($m['inputs'] as $input): ?>
                        <label for="<?php echo $input['name']; ?>"><?php echo $input['label']; ?></label>
                        <input type="<?php echo $input['type']; ?>" name="<?php echo $input['name']; ?>" required>
                    <?php endforeach; ?>

                    <div class="resultat"><?php echo $resultat; ?></div>
                    <input type="hidden" name="module" value="<?php echo $key; ?>">
                    <button type="submit" name="lancer">Lancer</button>
                </form>
            </div>
        <?php endforeach; ?>
	    <div id="scalab" class="tabcontent">
                <form method="POST" class="form-box">

		    <label><h3>Sélection du programme</h3></label>
		    <select name="script">
			<option value="prime">prime</option>
			<option value="prime_improve">prime_improve</option>
			<option value="prime_DLB">prime_DLB</option>
			<option value="prime_DLB_improve">prime_DLB_improve</option>
			<option value="MPI">MC MPI</option>
			<option value="javaSocket">MC JavaSocket</option>
			<option value="pythonSocket">MC PythonSocket</option>
		    </select>

		    <select name="mode_sc">
			<option value="strong scalab">Scalabilité forte</option>
			<option value="weak scalab">Scalabilité faible</option>
		    </select>

		    <input type="hidden" name="scal" value="scal">
                    <button type="submit" name="lancer">Lancer</button>
                </form>
		<img src="data:image/png;base64,<?= $img ?>" alt="Graphique">
            </div>
    </section>


</div>
<?php
// Par défaut, on affiche le premier onglet
$activeTab = 'div_prog1';

// Si on vient de lancer un module, on détecte lequel pour rester dessus
if (isset($_POST['module'])) {
    $activeTab = $modules[$_POST['module']]['id'];

    /*
    if ($_POST['module'] === 'prime') {
        $activeTab = 'div_prog1';
    } elseif ($_POST['module'] === 'mc') {
        $activeTab = 'div_prog2';
    }
    elseif ($_POST['module'] === 'integral') {
        $activeTab = 'div_prog3';
    }
    */
} elseif (isset($_POST['scal'])) {
    $activeTab = 'scalab';
}
?>
<script>

    function changeTab(tab) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName('tabcontent');
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = 'none';
        }
        tablinks = document.getElementsByClassName('tablink');
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].style.backgroundColor = '';
        }
        document.getElementById(tab).style.display ='block';

    }
    //au chargement de la page après une exécution du formulaire
    window.onload = function() {
        changeTab('<?php echo $activeTab; ?>');
    };
</script>
</body>
<?php
include "includes/footer.html";
?>
</html>
