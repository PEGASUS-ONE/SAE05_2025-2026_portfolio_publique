
# Mise en place d’un répertoire partagé avec NFS

## Introduction
Nous avons constaté que nous avions besoin d’un dossier partagé entre tous les noeuds du cluster afin de faciliter l’accès aux fichiers et leur dépôt. Sans cela, il devient nécessaire de copier manuellement les programmes sur chaque machine, ce qui complique le travail, notamment pour l’exécution de programmes parallèles.
Pour répondre à ce besoin, nous avons utilisé NFS (Network File System), qui est un système permettant de partager un répertoire sur le réseau comme s’il se trouvait localement sur chaque machine du cluster.

Effectivement avant cela pour chaque fichier, nous devions faire laborieusement :

Par exemple :
```
scp /home/cluster-ctrl/Documents/cluster_programs/prime.py p1:/home/pi/Documents/cluster_programs/
scp /home/cluster-ctrl/Documents/cluster_programs/prime.py p2:/home/pi/Documents/cluster_programs/
scp /home/cluster-ctrl/Documents/cluster_programs/prime.py p3:/home/pi/Documents/cluster_programs/
scp /home/cluster-ctrl/Documents/cluster_programs/prime.py p4:/home/pi/Documents/cluster_programs/
```


# 1. Installation de NFS

## Sur le RPI4

```

sudo apt update
sudo apt install nfs-kernel-server -y

```

Ces commandes installent NFS (coté serveur) qui permet à cnat de partager un dossier.

## Sur chaque RPI0 (p1 à p4)

```

sudo apt update
sudo apt install nfs-common -y

```

Ce paquet permet aux RPI0 de se connecter à un partage NFS.

---

# 2. Création du dossier partagé sur cnat

```

sudo mkdir -p /mnt/cluster_programs
sudo chown -R cluster-ctrl:cluster-ctrl /mnt/cluster_programs

```

Ce dossier sera visible par tous les noeuds.  
`/mnt` veut dire "mount" un dossier de point de montage.


# 3. Configuration des noms des machines

Pour que les machines se reconnaissent entre elles par leur nom (par exemple “p1”), on remplit le fichier `/etc/hosts`.  

sur le cnat (RPI4):

``
sudo nano /etc/hosts
``
```
127.0.1.1       cnat
172.19.181.1   p1
172.19.181.2   p2
172.19.181.3   p3
172.19.181.4   p4 
```

sur tout les rpi0 (pour le p1) :

```
127.0.1.1       p1
192.168.0.23    cnat
```


# 4. Configuration du serveur NFS (cnat)

Édition du fichier :

``
sudo nano /etc/exports
``

Ajout d’une ligne comme :

```
/mnt/cluster_programs  p1(rw,sync,no_subtree_check) p2(rw,sync,no_subtree_check) p3(rw,sync,no_subtree_check) p4(rw,sync,no_subtree_check)
```

Explication brève :
- `rw` : les machines peuvent lire et écrire
- `sync` : les données sont écrites immédiatement
- `no_subtree_check` : évite des vérifications inutiles et accélère NFS

Pour appliquer cette configuration :

```
sudo exportfs -ra
sudo systemctl restart nfs-kernel-server
```

# 5. Montage du partage sur les RPI0

Création du dossier où sera monté le partage :

``
sudo mkdir -p /mnt/cluster_programs
``

Montage du partage :

``
sudo mount cnat:/mnt/cluster_programs /mnt/cluster_programs
``

# 6. Montage automatique au démarrage

Pour ne pas avoir à remonter le partage à chaque redémarrage, on ajoute une ligne dans `/etc/fstab` sur chaque RPI0 :

``
sudo nano /etc/fstab
``

Ajout :

``
cnat:/mnt/cluster_programs   /mnt/cluster_programs   nfs   defaults   0  0
``

# 7. Exécution d’un programme MPI depuis le partage

On peut maintenant copier tous nos scripts dans le dossier partagé :

``
cp /home/cluster-ctrl/Documents/cluster_programs/prime.py /mnt/cluster_programs/
``

Puis on lance :

``
mpiexec -n 5 -host cnat,p1,p2,p3,p4 python3 /mnt/cluster_programs/prime.py 10000
``

Tous les noeuds utilisent alors exactement le même fichier situé dans le dossier partagé.
Ce qui nous permet donc d'utiliser le RPI4 dans la commande de lancement pour faire des futurs tests.


