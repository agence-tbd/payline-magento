1.8.6
Ajout du bouton AmazonPay dans le panier
Refactoring de code


1.8.5.6
Prise en compte des codes retour 02016 et 02017

1.8.5.5
Correctif sur l'envoi des catégories produit (test de valorisation pour éviter des notices dans les logs)

1.8.5.4
Valorisation du champ buyer.title de l'API à partir du préfixe de l'acheteur. Valorisation de buyer.mobilePhone à partir du numéro renseigné dans l'adresse.

1.8.5.3
Remise en place du répertoire skin/frontend/base/default/ dans le package

1.8.5.2
Ajout du statut commande "payment data mismatch" : si les données de paiement ne correspondent pas à la commande, cette dernière est annulée et passée au statut sélectionné par le commerçant.

1.8.5.1
Distinction des commandes dont le paiement est en attente

1.8.5
Modification du paiement direct :
  - Utilisation de token via API AJAX pour échange d'informations
  - Intégration des cinématiques 3D secure
Refonte de la configuration et de la gestion des contrats

1.8.4 non publiée

1.8.3.2
Montée de version librairie PHP Payline v4.44 Correctif sur la gestion du stock

1.8.3.1
Montée de version librairie PHP Payline v4.43 Modification du format d'identifiant du portefeuille client
