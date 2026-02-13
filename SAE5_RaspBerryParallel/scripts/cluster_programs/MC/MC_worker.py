import socket
import random
import sys

def monte_carlo(total_count: int) -> int:
    # Simulation of Monte Carlo to count points inside the quarter circle
    # Simulation de Monte Carlo pour compter les points à l'intérieur du quart de cercle
    inside = 0
    # Main loop
    # Boucle principale
    for _ in range(total_count):
        # Generation of random points
        # Génération de points aléatoires
        x = random.random()
        y = random.random()
        if x*x + y*y <= 1.0:
            inside += 1
    return inside


if __name__ == "__main__":
    # verification of arguments
    # Vérification des arguments
    if len(sys.argv) != 2:
        print("Usage: python3 worker.py <PORT>")
        sys.exit(1)
    # Retrieval of port from arguments
    # Récupération du port depuis les arguments
    PORT = int(sys.argv[1])
    HOST = ""   # écoute sur toutes les interfaces

    # Running the server
    # Démarrage du serveur
    print(f"[WORKER] Starting server on port {PORT}")
    # Creation of the socket
    # Création de la socket
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.bind((HOST, PORT))
        s.listen(1)
        # Wait for master connection
        # Attente de la connexion du master
        conn, addr = s.accept()
        print(f"[WORKER] Connected by {addr}")
        # Main request processing loop
        # Boucle principale de traitement des requêtes
        with conn:
            while True:
                # Receive data from master
                # Réception des données du master
                data = conn.recv(1024).decode().strip()
                if not data:
                    break
                # Check for end signal
                # Vérification du signal de fin
                if data == "END":
                    print("[WORKER] Received END, shutting down")
                    break
                # Retrieval of the number of points to generate
                # Récupération du nombre de points à générer
                total_count = int(data)
                print(f"[WORKER] totalCount = {total_count}")
                # Execution of the Monte Carlo simulation
                # Exécution de la simulation de Monte Carlo
                result = monte_carlo(total_count)
                conn.sendall(f"{result}\n".encode())
