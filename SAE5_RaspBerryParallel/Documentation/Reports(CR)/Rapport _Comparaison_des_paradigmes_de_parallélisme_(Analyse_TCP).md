# Rapport : Comparaison des paradigmes de parallélisme (Analyse TCP)
## Étude du paradigme TCP Client / Serveur face aux autres modèles

## 1. Introduction

Dans le cadre de ce projet, nous avons travaillé sur un cluster de Raspberry Pi composé d’un Raspberry Pi 4 (CNAT) jouant le rôle de noeud principal, et de quatre Raspberry Pi Zero (P1, P2, P3, P4) utilisés comme noeuds distants.

L’objectif était d’explorer différents paradigmes de parallélisme, de les implémenter autour d’un même problème (le calcul des nombres premiers), puis de comparer leurs performances afin de comprendre leurs avantages, leurs limites et leurs cas d’usage dans un contexte de calcul distribué.

Nous nous sommes particulièrement intéressés au paradigme TCP Client / Serveur, que nous avons comparé aux modèles déjà utilisés dans le projet, notamment MPI SPMD et le paradigme Maître/Esclave. Le but n’était pas d’obtenir les meilleures performances possibles avec TCP, mais de comprendre pourquoi ce paradigme est moins adapté à notre cluster et à nos objectifs de calcul parallèle.

## 2. Rappel des paradigmes étudiés

Un programme parallèle réel est souvent une combinaison de plusieurs paradigmes. Dans notre étude, nous avons identifié et comparé les modèles suivants :

### 2.1 SPMD (Single Program Multiple Data)

Dans ce paradigme, tous les noeuds exécutent le même programme, mais chacun traite une portion différente des données.

- Utilisé naturellement avec MPI
- Très efficace pour des calculs homogènes
- Faible surcharge de communication
- Très bon passage à l’échelle sur un cluster

Ce paradigme correspond bien à notre objectif principal, qui est de répartir un calcul intensif sur l’ensemble des noeuds disponibles.

### 2.2 Maître / Esclave (Master / Worker)

Un noeud central distribue le travail à des travailleurs.

- Le maître coordonne, les esclaves calculent
- Bonne flexibilité dans la répartition des tâches
- Risque de goulot d’étranglement au niveau du maître
- Modèle utilisé dans notre version MPI améliorée

Ce paradigme reste adapté au calcul distribué tant que le maître ne devient pas un point de blocage.

### 2.3 Client / Serveur

Les clients font des requêtes vers un serveur central.

- Modèle très courant en réseau
- Forte centralisation
- Peu adapté au calcul intensif
- C’est le paradigme étudié en détail dans ce rapport

Contrairement à MPI, ce paradigme n’a pas pour objectif principal de répartir un calcul, mais plutôt de fournir un service centralisé à plusieurs clients.

## 3. Présentation du paradigme TCP Client / Serveur

### 3.1 Principe général

Dans le paradigme TCP Client / Serveur, un serveur central écoute sur un port réseau et répond aux requêtes de plusieurs clients. Ce modèle est très utilisé dans des contextes comme les serveurs web, les bases de données ou les services réseau.

Dans notre implémentation :

- Le serveur (CNAT) :
  - Reçoit une requête contenant une limite `N`
  - Calcule seul tous les nombres premiers jusqu’à `N`
  - Mesure ses performances (temps, nombre de tests, vitesse)
  - Envoie les résultats aux clients

- Les clients (P1, P2, P3, P4) :
  - Se connectent au serveur
  - Envoient une requête
  - Attendent les résultats
  - N’effectuent aucun calcul lourd

Cette architecture est volontairement centralisée. Elle permet de reproduire fidèlement le fonctionnement classique d’un serveur TCP, mais elle ne correspond pas à un modèle de calcul parallèle distribué.

## 4. Mise en oeuvre sur le cluster

### 4.2 Lancement du serveur

Le serveur TCP est lancé sur le Raspberry Pi 4 (CNAT) :

```
python3 /mnt/cluster_programs/prime_tcp_server.py --host 0.0.0.0 --port 5000 --block 1000 50000
```

Le serveur écoute sur le port 5000 et calcule les nombres premiers jusqu’à 50 000.

### 4.3 Lancement d’un seul client

Test avec un seul client (par exemple sur P1) :

```
python3 /mnt/cluster_programs/prime_tcp_client.py cnat 5000 10000
```

Ce test permet d’observer le comportement du serveur sans concurrence et de mesurer les performances dans un cas simple.

### 4.4 Lancement de plusieurs clients en parallèle

Pour simuler un paradigme client / serveur avec plusieurs clients simultanés, nous avons utilisé MPI uniquement comme outil de lancement, et non comme moteur de calcul :

```
mpiexec -n 4 -host p1,p2,p3,p4 python3 /mnt/cluster_programs/prime_tcp_client.py cnat 5000 10000
```

Tous les clients se connectent alors en même temps au serveur CNAT, ce qui permet d’observer l’impact de plusieurs connexions concurrentes sur les performances globales.

## 5. Analyse des performances

### 5.1 Observations principales

Nous avons observé que :

- Le serveur devient rapidement un goulot d’étranglement
- Ajouter des clients n’accélère pas le calcul
- Les clients passent la majorité de leur temps à attendre les résultats
- Le CPU du serveur est fortement sollicité tandis que les autres noeuds restent peu utilisés
- Le réseau ajoute une latence supplémentaire sans apporter de gain en calcul

Contrairement à MPI, le travail n’est pas réparti entre les noeuds du cluster : il est entièrement réalisé par le serveur.

### 5.2 Comparaison avec MPI

| Critère                   | TCP Client/Serveur | MPI SPMD   |
| ------------------------- | ------------------ | ---------- |
| Répartition du calcul     | Centralisée        | Distribuée |
| Scalabilité               | Très faible        | Excellente |
| Utilisation CPU cluster   | Mauvaise           | Optimale   |
| Overhead réseau           | Élevé              | Faible     |
| Adapté au calcul intensif | Non                | Oui        |

## 6. Pourquoi le paradigme TCP Client / Serveur n’est pas adapté à notre contexte

Dans le contexte de notre cluster et de nos objectifs, le paradigme TCP Client / Serveur n’est pas une solution adaptée au calcul parallèle pour plusieurs raisons :

1. **Centralisation totale du calcul**  
   Un seul noeud effectue réellement le travail, ce qui va à l’encontre de l’objectif d’un cluster, qui est d’exploiter l’ensemble des ressources disponibles.

2. **Sérialisation implicite des requêtes**  
   Même avec plusieurs clients connectés, le serveur traite les demandes de manière séquentielle ou quasi-séquentielle.

3. **Mauvaise exploitation du matériel**  
   Les Raspberry Pi Zero restent majoritairement inactifs alors qu’ils pourraient participer au calcul.

4. **Paradigme conçu pour d’autres usages**  
   TCP Client / Serveur est parfaitement adapté à des contextes comme les serveurs web, les API ou les services réseau, où un serveur fournit une ressource ou une information à de nombreux clients. En revanche, il n’est pas conçu pour répartir efficacement un calcul intensif.

Dans notre cas, utiliser TCP Client / Serveur revient donc à utiliser un paradigme inadapté au problème posé.

## 7. Intérêt pédagogique du paradigme

Même s’il est peu performant dans notre contexte, le paradigme TCP Client / Serveur reste très intéressant d’un point de vue pédagogique :

- Il met en évidence les limites d’un modèle centralisé
- Il permet de mieux comprendre pourquoi MPI est privilégié en calcul haute performance
- Il sert de contre-exemple concret face aux paradigmes de calcul distribué
- Il montre l’importance d’adapter l’architecture logicielle au type de problème à résoudre

## 8. Conclusion

À travers cette étude, nous avons montré que :

- Le paradigme TCP Client / Serveur n’est pas adapté au calcul parallèle intensif sur un cluster
- MPI permet une bien meilleure exploitation des ressources matérielles
- Le choix du paradigme a un impact direct sur les performances globales
- Un paradigme mal adapté peut annuler les bénéfices du parallélisme matériel

Cette comparaison met en évidence l’importance de choisir un modèle de parallélisme cohérent avec les objectifs du projet et la nature du problème traité.