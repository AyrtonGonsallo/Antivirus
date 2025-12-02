Avast Licences Manager - Plugin skeleton
=======================================

Ce ZIP contient une structure minimale du plugin "avast-licences-manager" :
- avast-licences-manager.php  (fichier principal)
- includes/class-roles.php     (gestion des rôles : customer_particulier, customer_revendeur)
- assets/                      (dossier pour JS/CSS futurs)

INSTALLATION
1) Téléversez le dossier 'avast-licences-manager' dans wp-content/plugins/
2) Activez le plugin depuis l'administration WordPress > Extensions
3) A l'activation, les rôles 'Client Particulier' et 'Client Revendeur' seront créés.

PROCHAINES ETAPES
- Ajouter les champs d'inscription et mapping user_meta
- Ajouter onglets "Mon compte" personnalisés
- Ajouter gestion devis / licences / cron / reporting

Note: Procédé minimal pour débuter le développement. Ne pas modifier functions.php ; tout le code métier doit être ajouté dans ce plugin.
