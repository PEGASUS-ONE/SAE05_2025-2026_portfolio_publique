import java.io.*;
import java.net.*;

public class WorkerSimpsonSocket {
    // Variable pour contrôler la boucle principale
    static boolean isRunning = true;
    // Fonction gaussienne
    private static double f(double x, double mu, double sigma) {
        double coef = 1.0 / (sigma * Math.sqrt(2.0 * Math.PI));
        double expo = -((x - mu) * (x - mu)) / (2.0 * sigma * sigma);
        return coef * Math.exp(expo);
    }
    // Méthode principale
    public static void main(String[] args) throws Exception {
        // Vérification des arguments
        if (args.length != 1) {
            System.out.println("Usage: java WorkerSimpsonSocket <port>");
            return;
        }
        // Récupération du port
        int port = Integer.parseInt(args[0]);
        // Création du serveur socket
        ServerSocket serverSocket = new ServerSocket(port);
        System.out.println("Worker en écoute sur le port " + port);
        // Acceptation de la connexion du master
        Socket socket = serverSocket.accept();
        // Création des flux de lecture/écriture
        BufferedReader reader = new BufferedReader(
                new InputStreamReader(socket.getInputStream()));
        PrintWriter writer = new PrintWriter(
                socket.getOutputStream(), true);
        // Boucle principale
        while (isRunning) {
            // Lecture du message du master
            String msg = reader.readLine();
            // Vérification du message
            if (msg.equals("END")) {
                isRunning = false;
            } else {
                // Traitement du message
                String[] parts = msg.split(";");
                // Récupération des paramètres
                // a, b, n, mu, sigma
                // Ex: 0.0;1.0;1000;0.0;1.0
                // Intègre la fonction gaussienne entre 0 et 1 avec 1000 subdivisions
                // et une moyenne de 0 et un écart-type de 1
                // Résultat attendu ≈ 0.3413
                double a = Double.parseDouble(parts[0]);
                double b = Double.parseDouble(parts[1]);
                int n = Integer.parseInt(parts[2]);
                double mu = Double.parseDouble(parts[3]);
                double sigma = Double.parseDouble(parts[4]);
                // Calcul de l'intégrale avec la méthode de Simpson
                double h = (b - a) / n;
                double somme = f(a, mu, sigma) + f(b, mu, sigma);
                // Somme des termes intermédiaires
                for (int i = 1; i < n; i++) {
                    double x = a + i * h;
                    if (i % 2 == 0)
                        somme += 2.0 * f(x, mu, sigma);
                    else
                        somme += 4.0 * f(x, mu, sigma);
                }
                // Envoi du résultat au master
                writer.println(somme);
            }
        }
        // Fermeture des ressources
        reader.close();
        writer.close();
        socket.close();
        serverSocket.close();
    }
}
