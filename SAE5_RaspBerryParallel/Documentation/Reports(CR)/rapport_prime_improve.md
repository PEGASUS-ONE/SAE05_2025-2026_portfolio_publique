# Prime.py vs Prime_Improve.py

## 1. Optimisation du test de primalité

La différence principale de performance vient du changement dans la manière dont nous testons si un nombre est premier.

### Avant  
Chaque nombre était testé avec tous les diviseurs de 2 jusqu’à n−1.  
La complexité était donc proportionnelle à n, ce qui est très coûteux lorsque n devient grand.

### Maintenant  
Nous ne testons plus tous les diviseurs possibles. Nous testons uniquement jusqu’à la racine carrée du nombre, en utilisant `math.isqrt()`.  
Cela réduit énormément le nombre d’opérations : on passe d’un test en O(n) à un test en O(√n).

Pour un nombre d’environ 1 000 000 :
- avant : environ 1 000 000 divisions,
- maintenant : environ 1 000 divisions.

Ce changement entraîne un gain non négligeable en performance. C’est l’amélioration qui contribue le plus à la vitesse du programme.


## 2. Réduction des calculs inutiles dans la boucle

Dans la nouvelle version, nous calculons une seule fois le pas `cluster_size * 2` au lieu de le recalculer à chaque itération.  
Même si ce gain est plus modeste, il reste intéressant dans une boucle exécutée des milliers de fois.


## 3. Départ synchronisé entre les noeuds

Nous ajoutons une barrière MPI (`comm.Barrier()`) afin que tous les noeuds commencent exactement au même moment.  
Cela permet de mesurer des temps plus fiables et d’éviter que certains noeuds démarrent plus tôt que d’autres, ce qui fausserait l’analyse des performances.


## 4. Collecte de mesures locales pour analyser les performances

Chaque noeud affiche maintenant :

- son nom d’hôte,
- le premier nombre qu’il traite,
- sa charge de travail (nombre de tests effectués),
- le nombre de nombres premiers trouvés,
- le temps exact passé dans la partie calcul,
- sa vitesse de traitement (tests par seconde).

Cela nous aide à mieux comprendre comment se répartit la charge au sein du cluster, et à vérifier que chaque noeud contribue correctement au calcul.


## 5. Arrêt anticipé du test lorsqu’un diviseur est trouvé

Comme dans la version initiale, nous interrompons le test dès qu’un diviseur est détecté. Cette optimisation devient encore plus efficace avec la limite à la racine carrée, puisqu’on réduit aussi le nombre de tentatives avant de trouver un diviseur potentiel.


## 6. Fusion des résultats optimisée côté maître

Une fois le calcul terminé, le maître récupère les résultats via `gather()` et les fusionne.  
Cette approche est efficace, car les noeuds travaillent de manière totalement indépendante et le tri final se fait uniquement sur les nombres premiers trouvés.


# 7. Mesures expérimentales sur Raspberry Pi

Nous avons testé les performances sur le cluster de Raspberry Pi pour comparer l’ancienne version ("prime") et la version améliorée ("prime_improve") sur une limite de 10000 (n=10000).

## Sur le cluster de quatre Raspberry Pi Zero (4 × rpi0)
- Temps version prime : 5.5 s  
- Temps version prime_improve : 0.2 s  

C'est donc **27** fois plus performant.

## Sur le Raspberry Pi 4
- Temps version prime : 1.55 s  
- Temps version prime_improve : 0.065 s  

C'est donc **24** fois plus performant.

Ces résultats confirment concrètement ce que nous observions théoriquement : la réduction du nombre de divisions rend le programme beaucoup plus rapide, même sur des machines très limitées comme les Raspberry Pi Zero.

