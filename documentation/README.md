# Documentation PROSIMMOBILIER

Le guide illustré principal est disponible dans [GUIDE_UTILISATEUR.md](GUIDE_UTILISATEUR.md).

Les images sont stockées dans `documentation/captures/`. Elles ont été produites avec une base locale de démonstration et ne contiennent pas de données de production.

## Actualiser une capture

1. Démarrer l’application sur `http://127.0.0.1:8000`.
2. Démarrer Chrome avec un port de débogage local et un profil temporaire.
3. Récupérer l’URL WebSocket de l’onglet depuis `http://127.0.0.1:9222/json/list`.
4. Exécuter :

   ```bash
   node documentation/capture-page.mjs <websocket-url> <url-page> <fichier.png>
   ```

5. Pour une page de connexion de démonstration uniquement :

   ```bash
   node documentation/capture-page.mjs <websocket-url> <url-connexion> <fichier.png> <email-demo> <mot-de-passe-demo>
   ```

Ne jamais passer des identifiants de production dans cette commande : ils pourraient apparaître dans l’historique du terminal.

