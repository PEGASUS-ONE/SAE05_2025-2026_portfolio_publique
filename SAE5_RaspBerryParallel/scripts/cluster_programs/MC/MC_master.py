import socket
import time
import math
from statistics import median

# CONFIG
WORKERS = [
    ("p1", 25545),
    ("p2", 25545),
    ("p3", 25545),
    ("p4", 25545),
]

TOTAL_COUNT = 100_000
REPEAT = 1
CSV_FILE = "/mnt/cluster_programs/MC/data/pi_MW_results.csv"


def write_csv(duration_ns, num_workers):
    # Compute of scalability
    # Calcul de la scalabilité
    scalability = 220 / (duration_ns / 1_000_000)
    # Append to CSV file
    # Écriture dans le fichier CSV
    with open(CSV_FILE, "a") as f:
        f.write(f"{scalability},{num_workers}\n")


def main():
    sockets = []
    # Connect to workers
    # Connexion aux workers
    for ip, port in WORKERS:
        # creation socket
        # Création socket
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        print(f"[MASTER] Connecting to {ip}:{port}")
        s.connect((ip, port))
        sockets.append(s)
    # Connecté aux workers
    # Nombre de workers connectés
    num_workers = len(sockets)

    # Main loop
    # Boucle principale
    for _ in range(REPEAT):
        start = time.perf_counter_ns()
        
        # Send total count to each worker
        # Envoi du nombre de points à chaque worker
        for s in sockets:
            s.sendall(f"{TOTAL_COUNT}\n".encode())

        total_inside = 0

        # Receive results from each worker
        # Réception des résultats de chaque worker
        for s in sockets:
            data = s.recv(1024).decode().strip()
            total_inside += int(data)

        pi = 4.0 * total_inside / (TOTAL_COUNT * num_workers)

        stop = time.perf_counter_ns()
        # Write results to CSV
        # Écriture des résultats dans le fichier CSV
        write_csv(stop - start, num_workers)
        # Print results
        # Affichage des résultats
        print("\n========== RESULT ==========")
        print(f"Pi        : {pi}")
        print(f"Error     : {abs(pi - math.pi) / math.pi}")
        print(f"Ntot      : {TOTAL_COUNT * num_workers}")
        print(f"Workers   : {num_workers}")
        print(f"Duration  : {(stop - start)/1_000_000:.2f} ms")
        
    # Send stop signal to workers
    # Envoi du signal de fin aux workers
    for s in sockets:
        s.sendall(b"END\n")
        s.close()


if __name__ == "__main__":
    main()
