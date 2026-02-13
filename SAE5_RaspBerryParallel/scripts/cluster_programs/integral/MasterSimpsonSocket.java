import java.io.*;
import java.net.*;

public class MasterSimpsonSocket {
    // CONFIGURATION DES WORKERS
    static final int NUM_WORKERS = 4;

    // Adresses IP des workers
    static final String[] IPS = {
        "172.19.181.1",
        "172.19.181.2",
        "172.19.181.3",
        "172.19.181.4"
    }; 
    // Port de communication
    static final int PORT = 25545;

    public static void main(String[] args) throws Exception {

        // SETTING UP PARAMETERS
        // PARAMÈTRES FOURNIS PAR L'UTILISATEUR
        // a, b, n, mu, sigma
        // Ex: java MasterSimpsonSocket 0 1 1000 0 1
        // Intègre la fonction gaussienne entre 0 et 1 avec 1000 subdivisions
        // et une moyenne de 0 et un écart-type de 1
        // Résultat attendu ≈ 0.3413
        if (args.length != 5) {
            System.out.println("Usage: java MasterSimpsonSocket a b n mu sigma");
            return;
        }
        
        // Récupération des paramètres
        double a = Double.parseDouble(args[0]);
        double b = Double.parseDouble(args[1]);
        int n = Integer.parseInt(args[2]);
        double mu = Double.parseDouble(args[3]);
        double sigma = Double.parseDouble(args[4]);
        // Vérification que n est pair
        if (n % 2 != 0) {
            System.err.println("Erreur : n doit être pair");
            return;
        }
        // Calcul de h et du nombre de subdivisions par worker
        double h = (b - a) / n;
        int nParWorker = n / NUM_WORKERS;
        // S'assurer que nParWorker est pair
        if (nParWorker % 2 != 0) {
            nParWorker++; // sécurité Simpson
        }
        
        double sommeTotale = 0.0;
        
        Socket[] sockets = new Socket[NUM_WORKERS];
        BufferedReader[] readers = new BufferedReader[NUM_WORKERS];
        PrintWriter[] writers = new PrintWriter[NUM_WORKERS];

        
        // CONNEXION AUX WORKERS
        // Création des sockets et des flux de lecture/écriture
        for (int i = 0; i < NUM_WORKERS; i++) {
            sockets[i] = new Socket(IPS[i], PORT);
            readers[i] = new BufferedReader(
                    new InputStreamReader(sockets[i].getInputStream()));
            writers[i] = new PrintWriter(
                    sockets[i].getOutputStream(), true);
        }

        
        // ENVOI DU TRAVAIL
        // Envoi des paramètres à chaque worker
        for (int i = 0; i < NUM_WORKERS; i++) {
            double ai = a + i * nParWorker * h;
            double bi = ai + nParWorker * h;

            writers[i].println(
                ai + ";" + bi + ";" + nParWorker + ";" + mu + ";" + sigma
            );
        }

        
        // RÉCEPTION DES RÉSULTATS
        // Lecture des résultats de chaque worker par le master
        for (int i = 0; i < NUM_WORKERS; i++) {
            String rep = readers[i].readLine();
            sommeTotale += Double.parseDouble(rep);
        }

        
        // FERMETURE
        // Envoi du message de fin et fermeture des sockets
        for (int i = 0; i < NUM_WORKERS; i++) {
            writers[i].println("END");
            sockets[i].close();
        }
        // AFFICHAGE DU RÉSULTAT FINAL
        double resultat = (h / 3.0) * sommeTotale;
        System.out.println("Résultat final ≈ " + resultat);
    }
}
