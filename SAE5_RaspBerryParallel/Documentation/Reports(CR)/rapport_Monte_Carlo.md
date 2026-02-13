# Compte rendu - Calcul de π par la méthode de Monte Carlo sur le cluster de Raspberry Pi

## 1. Objectif du projet

L’objectif de ce travail est d’implémenter et de comparer plusieurs versions distribuées du calcul de π par la méthode de Monte Carlo sur un cluster de Raspberry Pi.  
Trois approches ont été étudiées :

- une version Java basée sur une architecture Master / Worker avec sockets
- une version Python reproduisant le même modèle réseau
- une version Python utilisant MPI (mpi4py), plus adaptée au calcul distribué haute performance

Ces implémentations permettent d’évaluer la facilité de déploiement, la scalabilité et les performances sur un cluster de RPi.


## 2. Environnement matériel et logiciel

### 2.1 Cluster Raspberry Pi

Le cluster est composé de plusieurs noeuds Raspberry Pi :
- cnat : noeud maître
- p1, p2, p3, p4 : noeuds de calcul (Workers)

Tous les noeuds partagent un répertoire commun via `/mnt/cluster_programs` (Voir rapport NFS).

## 3. Implémentation Java - Monte Carlo distribué par sockets

### 3.1 Installation de Java sur les Raspberry Pi

Java a été installé sur l’ensemble des noeuds du cluster :
```
sudo apt install default-jdk
```

La version installée permet la compilation et l’exécution des programmes Java.
Pour compiler un fichier .java:
```
javac nom_du_fichier.java
// ou bien pour tout les fichiers du dossier:
javac *.java
```

### 3.2 Déploiement du projet Java sur le cluster

Le projet Java a été placé dans le répertoire :
```
/mnt/cluster_programs/MC/Projet_Java/distributedMC_step1/
```
### 3.4 Architecture Master / Worker en Java

- Le Master `MasterSocket` est responsable de :
    - la connexion aux Workers via sockets TCP
    - l’envoi du nombre d’itérations Monte Carlo
    - l’agrégation des résultats
    - le calcul final de π
- Les Workers `WorkerSocket` :
    - attendent une connexion du Master
    - exécutent le calcul Monte Carlo localement
    - renvoient le nombre de points dans le quart de disque
### 3.5 Lancement des processus Java

#### Lancement du Master (sur cnat)

```
cd /mnt/cluster_programs/MC/Projet_Java/distributedMC_step1
java Master_worker.MasterSocket
```

#### Lancement des Workers (sur p1,p2,p3,p4)

```
cd /mnt/cluster_programs/MC/Projet_Java/distributedMC_step1
java Master_worker.WorkerSocket 25545 cnat
```

## 4. Implémentation Python - Monte Carlo distribué par sockets

Cette version reproduit fidèlement l’architecture Java :
- un Master Python qui distribue les tâches
- plusieurs Workers Python exécutant le calcul Monte Carlo
- communication par sockets TCP

### 4.2 Lancement des Workers Python

Sur les noeuds p1, p2, p3 et p4 :

```
python3 /mnt/cluster_programs/MC/MC_worker.py 25545
```

Chaque Worker écoute sur un port donné et attend les requêtes du Master.
### 4.3 Lancement du Master Python
Sur le noeud cnat :

```
python3 /mnt/cluster_programs/MC/MC_master.py
```

Le Master se connecte aux Workers, distribue les calculs, récupère les résultats et calcule une approximation de π.

### 4.4 Intérêt de cette version
Cette implémentation permet :
- de comparer Java vs Python à architecture équivalente
- d’évaluer l’impact du langage sur les performances
- de valider la portabilité du modèle Master/Worker

## 5. Implémentation Python MPI - Monte Carlo distribué avec mpi4py

### 5.1 Principe MPI

Contrairement aux versions par sockets, MPI permet :
- un lancement centralisé
- une gestion automatique des processus distribués
- des communications optimisées entre noeuds

Le programme repose sur :
- rank 0 : Master
- rank > 0 : Workers
### 5.2 Lancement de la version MPI

Depuis le noeud cnat :
```
mpiexec -n 5 -host cnat,p1,p2,p3,p4 python3 /mnt/cluster_programs/MC/MC_MPI_worker_master.py
```

- 1 processus Master
- 4 processus Workers répartis sur le cluster
## 6. Conclusion

Ce travail a permis de mettre en oeuvre plusieurs stratégies de calcul distribué sur un cluster de Raspberry Pi :

- la version Java par sockets met en évidence les mécanismes bas niveau de communication réseau
- la version Python par sockets valide la portabilité de l’architecture Master / Worker
- la version MPI apporte une solution plus performante et plus simple à déployer