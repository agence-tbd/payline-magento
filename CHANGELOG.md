1.8.7.1
Fix truncate in orderDetail data lines
Fix error message display in checkout on payment error 

1.8.7
Ajout des moyens de paiement Klarna et Oney
Correction sur affichage des logo et label des contrats dans le panier
Correction sur les retour de la notification

1.8.6.2
Add better logs in widget return
Add configuration to avoid stock increment on failed dowebpayment
Add possibility to display review on widget integration
Fix issue for saving contracts in store view
Fix issue prevent display payment methods if they are not usable in checkout
Fix issue adjust tab label if payline widget is really displayed
Fix issue zenddesk 71431
Fix redirect error


1.8.6
Ajout du bouton AmazonPay dans le panier
Refactoring de code


1.8.5.6
Prise en compte des codes retour 02016 et 02017

1.8.5.5
Correctif sur l'envoi des cat?gories produit (test de valorisation pour ?viter des notices dans les logs)

1.8.5.4
Valorisation du champ buyer.title de l'API ? partir du pr?fixe de l'acheteur. Valorisation de buyer.mobilePhone ? partir du num?ro renseign? dans l'adresse.

1.8.5.3
Remise en place du r?pertoire skin/frontend/base/default/ dans le package

1.8.5.2
Ajout du statut commande "payment data mismatch" : si les donn?es de paiement ne correspondent pas ? la commande, cette derni?re est annul?e et pass?e au statut s?lectionn? par le commer?ant.

1.8.5.1
Distinction des commandes dont le paiement est en attente

1.8.5
Modification du paiement direct :
  - Utilisation de token via API AJAX pour ?change d'informations
  - Int?gration des cin?matiques 3D secure
Refonte de la configuration et de la gestion des contrats

1.8.4 non publi?e

1.8.3.2
Mont?e de version librairie PHP Payline v4.44 Correctif sur la gestion du stock

1.8.3.1
Mont?e de version librairie PHP Payline v4.43 Modification du format d'identifiant du portefeuille client
