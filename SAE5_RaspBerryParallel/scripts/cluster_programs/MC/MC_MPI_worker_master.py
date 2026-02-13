import sys

from mpi4py import MPI
import random
import time
import math

# MPI setup
comm = MPI.COMM_WORLD
rank = comm.Get_rank()
size = comm.Get_size()   # 1 master + N-1 workers

# CONFIG
TOTAL_COUNT = int(sys.argv[1])
REPEAT = 1
CSV_FILE = "/mnt/cluster_programs/MC/data/pi_MW_results.csv"

# Monte Carlo simulation function
def monte_carlo(n: int) -> int:
    inside = 0
    # Main loop
    # Boucle principale
    for _ in range(n):
        # Generation of random points
        # Génération de points aléatoires
        x = random.random()
        y = random.random()
        if x*x + y*y <= 1.0:
            inside += 1
    return inside


def write_csv(duration_ns, num_workers):
    # Compute of scalability
    # Calcul de la scalabilité
    scalability = 220 / (duration_ns / 1_000_000)
    # Append to CSV file
    # Écriture dans le fichier CSV
    if rank == 0:
        with open(CSV_FILE, "a") as f:
            f.write(f"{scalability},{num_workers}\n")


if rank == 0:
    # ================= MASTER =================
    num_workers = size - 1
    # Main loop
    # Boucle principale
    for _ in range(REPEAT):
        start = time.perf_counter_ns()
        # send total count to each worker
        # envoyer le nombre de points à chaque worker
        for r in range(1, size):
            comm.send(TOTAL_COUNT, dest=r, tag=0)

        total_inside = 0
        # receive results from each worker
        # recevoir les résultats de chaque worker
        for r in range(1, size):
            result = comm.recv(source=r, tag=1)
            total_inside += result
        # compute Pi
        # Calcul de Pi
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
    # Send END signal to all workers
    # signal d'arrêt
    for r in range(1, size):
        comm.send("END", dest=r, tag=2)

else:
    # ================= WORKER =================
    while True:
        # Reception of the message from master
        # Reception du message du master
        msg = comm.recv(source=0, tag=MPI.ANY_TAG)
        # Check for end signal
        # Vérification du signal de fin
        if msg == "END":
            break
        # Retrieval of the number of points to generate
        # Récupération du nombre de points à générer
        total_count = msg
        result = monte_carlo(total_count)
        # Send result to master
        # Envoi du résultat au master
        comm.send(result, dest=0, tag=1)
