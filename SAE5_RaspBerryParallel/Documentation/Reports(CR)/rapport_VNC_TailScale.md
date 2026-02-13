
# Installation et utilisation de Tailscale et de VNC

## Introduction

Dans ce rapport, nous présentons l'installation et l'utilisation de Tailscale et de VNC pour accéder au Raspberry Pi4 à distance.  
Ces deux outils permettent d’interagir avec le Raspberry Pi depuis un ordinateur ou même un téléphone ainsi que tout simplement avec n'importe quel navigateur (Uniquement en SSH pour cette solution).

Tailscale permet une connexion sécurisée via SSH depuis n'importe où dans le monde, tandis que VNC permet d’accéder à l'interface graphique du Raspberry Pi si les 2 machines sont dans le même réseau local.  
Ce qui nous mène donc à la combinaison de ces 2 outils !!

En effet grâce à la connexion tunnel créer par Tailscale et de VNC nous pouvons utiliser l'interface graphique de VNC n'importe où.

Mais bien sûr VNC est optionnel avec Tailscale nous pouvons nous connecter via SSH sans interface graphique n'importe où sans problème.

# 1. Tailscale : Présentation

## 1.1 Qu’est-ce que Tailscale ?

Tailscale est un service qui crée un **réseau privé virtuel (VPN)** entre nos appareils.  
Contrairement à un VPN classique, il ne redirige pas tout le trafic : il connecte uniquement nos machines entre elles de manière sécurisée.

En pratique :
- chaque appareil obtient une adresse IP privée Tailscale
- tous les appareils connectés se voient entre eux, même s’ils sont à l’autre bout du monde
- aucune configuration réseau supplémentaire (pas d’ouverture de ports, pas de routeur)

Grâce à Tailscale, on peut donc faire :
- du SSH à distance comme si on était sur le même réseau local
- de la gestion de cluster à distance
- de la surveillance du Pi même à l’extérieur de chez soi

# 2. Installation de Tailscale

## 2.1 Installation sur le RPi

Commande d’installation :

```
curl -fsSL [https://tailscale.com/install.sh](https://tailscale.com/install.sh) | sh
```

Puis activation :

``
sudo tailscale up --ssh
``

Le paramètre `--ssh` active l’accès SSH à distance via Tailscale, ce qui est extrêmement pratique pour éviter toute configuration réseau.

Après la commande, il suffit de se connecter via navigateur pour valider la machine dans notre compte Tailscale.


## 2.2 Installation sur Windows

Téléchargement depuis :
https://tailscale.com/download

Ensuite :
1. Installer Tailscale
2. Se connecter avec son compte
3. La machine Windows rejoint automatiquement notre réseau Tailscale

Nous pouvons désormais voir notre RPi dans la liste des appareils Tailscale.

---

# 3. Utilisation de SSH avec Tailscale

## 3.1 Génération de clé SSH sur Windows
(optionnel pour éviter de taper le mot de passe à chaque fois)

dans un cmd:
``
ssh-keygen
``
Une clé publique est alors générée dans le dossier `.ssh`.

## 3.2 Copier la clé publique sur le RPi

Sur le RPi :

``
nano /home/cluster-ctrl/.ssh/authorized_keys
``

Coller (la réécrire) la clé publique Windows.

## 3.3 Connexion SSH via réseau local (exemple)

```
ssh cluster-ctrl@192.168.0.23
ou
ssh cluster-ctrl@cnat
```

## 3.4 Connexion SSH via Tailscale (depuis n’importe où)

Grâce à Tailscale, le RPi reçoit une IP privée du type :
```
100.x.x.x
```
Nous pouvons donc nous connecter depuis n’importe où (avec l'application Tailscale d'installer) :

```
ssh cluster-ctrl@100.111.50.34
ou
ssh cluster-ctrl@cnat
```

Cela fonctionne même depuis un téléphone, même en 4G. Avec l'application (VPN) Tailscale d'activer.



Il est également possible d’ouvrir une session SSH directement depuis un navigateur grâce à la fonctionnalité "Tailscale SSH Console" disponible sur https://login.tailscale.com.  
Cela permet d’accéder au RPi sans logiciel supplémentaire, uniquement via un navigateur web.

# 4. VNC : Présentation

## 4.1 Qu’est-ce que VNC ?

VNC (Virtual Network Computing) permet d’afficher **le bureau graphique du RPi à distance**.  
Contrairement à SSH (interface textuelle), VNC montre l’écran comme si nous étions physiquement devant le RPi.

Dans notre cas, VNC est optionnel grâce à Tailscale, car SSH suffit pour tout gérer.  
Cependant, VNC devient utile si l’on souhaite manipuler l’interface graphique.

---

# 5. Installation et configuration de VNC

## 5.1 Activation sur le RPi

Ouvrir :

```
sudo raspi-config
```

Puis :
Interface Options  
Et on met Enable pour VNC


Rappel, si besoin de l'ip en local (sinon via tailscale l'ip est affiché sur le site):
```
hostname -I
```

## 5.2 Installation côté Windows

Téléchargement de VNC Viewer :
https://www.realvnc.com/fr/connect/download/

Ouvrir VNC Viewer, puis entrer l’adresse IP du RPi.

En local :
``
192.168.0.23
``

Via Tailscale :
``
100.111.50.34
``

(Demande possible du nom de l'utilisateur et mot de passe)

Le bureau du RPi s’affiche alors dans une fenêtre, contrôlable avec la souris et le clavier (Copier coller activer).


# 6. Utiliser le RPi depuis un téléphone

Grâce à Tailscale, il est possible (en installant une application de terminal) :
- d’accéder en SSH au RPi depuis un smartphone
- de lancer des commandes à distance
- d'administrer le Pi comme si l’on était sur le même réseau

Il suffit d’installer Tailscale sur le playstore et de se connecter au même compte.  
Le RPi apparaît immédiatement, avec son IP privée Tailscale.

## 6.1 Utiliser VNC sur téléphone

Il est aussi possible d’utiliser VNC sur un smartphone pour afficher l’interface graphique du RPi.

Pour cela :
- Installer une application compatible VNC comme “VNC Viewer” sur le playstore. 
- Activer Tailscale sur le téléphone pour rejoindre le même réseau privé. 
- Ouvrir VNC Viewer et entrer l’adresse Tailscale du RPi :
   100.111.50.34

Le bureau du RPi s’affiche alors directement sur le téléphone.  
Cela permet d’utiliser l’interface graphique de n’importe où, même en 4G.