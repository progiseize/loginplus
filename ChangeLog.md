# LoginPlus

[comment]: <> (TODO)
[comment]: <> (Upload des fichiers + simple)

***
### 2.0.0
* NEW - Nouvelle page de personnalisation
* REMOVE - Suppression des templates avec Copyright
* MAJ - Upload plus simple des images (dossier medias)
* MAJ - Nouvelles options de personnalisation


### 1.5.4
* FIX - Correction erreur log trigger 
* FIX - Correction erreur log loginplusAdminPrepareHead

### 1.5.3
* MAJ - Mise à jour de la page admin "Apparence"
* MAJ - Conversion $conf->global en getDolGlobal(Int|String)
* FIX - Correction des erreurs de logs loginMsg::getUserMsgs()

### 1.5.2 (24/05/2023) 
* MAJ - Mise à jour Descripteur module
* FIX - Remove skeleton call
* MAJ - Add DOLPGS CSS

### 1.5.1 (11/02/2023) 
* FIX - Correction PHP 8 
* FIX - Corrections Multicompany (On ne peut plus selectionner les utilisateurs des autre entités depuis l'entité maitre, car les messages ne se voyaient pas. Possibilité de dev pour que cela fonctionne, a l'ajout d'un nouveau message, laisser la possibilité de voir tout les users de chaque entité et verifier les entités a l'ajout du message. Le dupliquer avec l'entité de chaque user si besoin.)

### 1.5 (01/12/2022) 
* NEW - Mode maintenance 
* FIX - Correction css 

### 1.4.4 (08/09/2022) 
* FIX - Correction CSRF / token 

### 1.4.3 (01/06/2022) 
* NEW - Affichage mises à jour pages modules

### 1.4.2 (29/03/2022)
* FIX - Heure d'été

### 1.4.1 (21/03/2022)
* FIX - Compatibilité Captcha
* FIX - Corrections morelogincontent & Google Analytics

### 1.4 (04/03/2022)
* FIX - Gestions des droits - Accès aux menus correspondants sur le module Progiseize
* FIX - Gestion des message - Les non-admins ne peuvent plus supprimer les messages des admins

### 1.3 (11/02/2022)
* MAJ - Ajouts de traductions (Complet)
* FIX - Corrections modèles prédéfinis
* FIX - Corrections Page mot de passe oublié

### 1.2 (18/01/2022)
* NEW - Ajout de l'auteur du message
* NEW - Division des droits (Configuration + Gestion des messages)
* FIX - Corrections date d'expiration, fonctionne maintenant avec tous les types de messages
* MAJ - Modification des messages d'accueil
* MAJ - Simplification interface
* MAJ - Ajouts de traductions (Incomplet)

### 1.1 (11/01/2022)
* NEW - Ajout d'une fonctionnalité "Message d'accueil"
* NEW - Modèles prédéfinis
* MAJ - Mise à jour et clarification de l'interface
* FIX - Mise à jour de sécurité

### 1.0 (01/12/2021)
* Module permettant de modifier l'apparence de la page d'authentification de Dolibarr.
* Compatible TwoFactor.