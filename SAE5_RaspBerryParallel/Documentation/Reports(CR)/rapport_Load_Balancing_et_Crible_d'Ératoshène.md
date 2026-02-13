# Rapport - Load Balancing MPI & Crible d’Ératosthène

## Contexte général
Ce rapport se fait à la suite des programmes: `prime_improve`, `prime_DLB`, et `prime_DLB_improve`

L’objectif principal était double :

1. Optimiser la répartition de charge (load balancing) dans un environnement hétérogène.
2. Accélérer le calcul des nombres premiers à grande échelle grâce à des algorithmes plus efficaces, en particulier le crible d’Ératosthène segmenté.
## 1. Problématique initiale

Dans les premières versions du programme `prime_improve`, la charge de calcul était répartie de manière statique entre les noeuds MPI. Cette approche présente plusieurs limites :

- Tous les noeuds ne possèdent pas les mêmes performances (RPI4 vs RPI Zero).
- Le noeud cnat (RPI4) est significativement plus rapide que les autres.
- Une répartition uniforme entraîne un déséquilibre : les noeuds lents deviennent un goulot d’étranglement.

Résultat observé :
- Le temps total est souvent dicté par le noeud le plus lent.

## 2. Choix d’un Load Balancer Dynamique

Pour résoudre ce problème, un load balancer dynamique basé sur MPI a été implémenté.
### Principe
- Le calcul est découpé en segments "chunks".
- Les workers demandent du travail au fur et à mesure.
- Le master distribue dynamiquement les segments disponibles.

Avantages :
- Meilleure occupation des coeurs.
- Réduction du temps d’attente.
- Adaptation aux variations de performance.

## 3. Master hybride (master optionnellement worker)

Un choix architectural important est de permettre au master d’être soit uniquement coordinateur, soit également worker, selon la configuration choisie.

Ce comportement est facultatif et peut être activé ou désactivé via les paramètres de la commande au lancement du programme.
### Justification

- Le master s’exécute sur cnat (RPI4), la machine la plus puissante du cluster.
- Le laisser uniquement orchestrer les communications peut constituer un gaspillage de ressources de calcul.

### Comportement du master
Selon la configuration :
- Mode coordinateur seul :
    - Distribue le travail.
    - Collecte les résultats.
- Mode master hybride (master = worker) :
    - Distribue le travail.
    - Collecte les résultats.
    - Calcule également des segments en parallèle des autres workers.

Cette flexibilité permet d’adapter l’exécution aux besoins expérimentaux et aux ressources disponibles du cluster.
## 4. Adaptation automatique à la capacité réelle

Au lieu d’utiliser un pourcentage fixe (exemple: 85 % pour cnat), une phase de calibration a été introduite.

### Calibration

- Chaque noeud calcule un petit segment identique.
- On mesure :
    - Le nombre d’opérations de marquage `ops`.
    - Le temps d’exécution.
- On en déduit une vitesse réelle (ops/s).

### Répartition adaptative

- La charge globale est répartie proportionnellement à la vitesse mesurée.
- Les noeuds rapides reçoivent naturellement plus de travail.

Ce mécanisme permet :

- Une adaptation automatique au matériel.
- Une meilleure portabilité du code.

## 5. Cas particulier : un seul worker

Le load balancer inclut volontairement un cas spécial :

> Si le programme est lancé avec un seul processus MPI, le calcul fonctionne quand même.

### Observation clé

Dans ce mode :
- cnat seul est plus rapide que le cluster complet.
### Explication

- Les RPI Zero sont beaucoup plus lents.
- Les communications MPI (envoi/réception, synchronisation) ajoutent un surcoût.
- Pour certaines tailles de problème, ce surcoût dépasse le gain du parallélisme.

Conclusion :
-  Le parallélisme n’est pas toujours bénéfique, surtout dans un cluster hétérogène.

# PARTIE II - Crible d’Ératosthène Segmenté

## 1. Limites du test naïf de primalité
Les premières versions utilisaient un test de primalité classique :

- Pour chaque nombre, division jusqu’à √n.

Complexité élevée et inefficace pour des limites élevées (≥ 10⁷).

## 2. Choix du crible d’Ératosthène

Le crible d’Ératosthène permet :
- De marquer directement les multiples.
- D’éviter les tests redondants.

### Version segmentée

Pour être compatible avec MPI :
- Le domaine est découpé en segments.
- Chaque noeud applique le crible localement.
- Une liste de primes de base (≤ √N) est partagée.
## 3. Intégration avec le Load Balancer

Le crible segmenté s’intègre naturellement au load balancer :
- Chaque chunk correspond à un segment.
- Le coût est approximé par le nombre d’opérations de marquage.

Cela permet :
- Une mesure de la charge réelle.
- Un équilibrage précis.
## Conclusion générale

- Le load balancing dynamique adaptatif est essentiel dans un cluster hétérogène.
- Le master hybride exploite pleinement le RPI4.
- Le crible d’Ératosthène segmenté apporte un gain algorithmique majeur.
- Pour certaines tailles, cnat seul reste optimal.

Ce projet montre que la performance ne dépend pas uniquement du nombre de noeuds, mais aussi :

- De leur hétérogénéité.
- Du coût des communications.
- Du choix algorithmique.

### Pistes d’amélioration :
Le faire dans un langage plus performant comme le C.
