<?php

namespace App\Services;

use App\Models\Configuration;

class LegalContentService
{
    /**
     * Modèles éditables affichés seulement lorsqu'aucun texte n'a encore été enregistré.
     */
    public function defaults(Configuration $setting): array
    {
        $company = e($setting->raison_social ?: $setting->name ?: config('app.name'));
        $brand = e($setting->name ?: config('app.name'));
        $email = e($setting->email1 ?: '[adresse e-mail à compléter]');
        $phone = e($setting->contact1 ?: '[téléphone à compléter]');
        $address = e($setting->adresse ?: '[adresse à compléter]');
        $website = e($setting->site_web ?: '[site web à compléter]');
        $rccm = e($setting->num_rccm ?: '[RCCM à compléter]');
        $capital = filled($setting->capital)
            ? e(number_format((float) $setting->capital, 0, ',', ' ') . ' FCFA')
            : '[capital social à compléter]';

        return [
            'politique_confidentialite' => <<<HTML
<h2>Politique de confidentialité et de protection des données</h2>
<p><strong>Dernière mise à jour :</strong> [date à compléter]</p>
<p>La présente politique explique comment {$company}, exploitant la plateforme {$brand}, collecte et utilise les données personnelles des agences immobilières, propriétaires, locataires, prospects, agents et autres utilisateurs de ses services web et mobiles.</p>
<h3>1. Responsable du traitement</h3>
<p>{$company}, établi à {$address}. Contact : {$email} – {$phone}.</p>
<h3>2. Données traitées</h3>
<p>Selon votre usage, nous pouvons traiter vos données d'identité et de contact, pièces et informations justificatives, données relatives aux biens, baux, loyers, paiements, impayés, reversements, maintenances et annonces, ainsi que les données techniques nécessaires à la connexion, à la sécurité et au support.</p>
<h3>3. Finalités</h3>
<p>Ces données servent à créer et sécuriser les comptes, gérer les relations entre agences, propriétaires et locataires, administrer les biens et contrats, produire les reçus et états de reversement, suivre les paiements et maintenances, publier les biens autorisés, gérer les abonnements et modules, envoyer les notifications utiles, fournir l'assistance et respecter les obligations légales.</p>
<h3>4. Fondements et destinataires</h3>
<p>Les traitements reposent, selon le cas, sur l'exécution d'un contrat, une obligation légale, votre consentement ou l'intérêt légitime lié au fonctionnement et à la sécurité du service. Les données sont accessibles uniquement aux utilisateurs autorisés, aux prestataires techniques indispensables et aux autorités légalement habilitées. Elles ne sont pas vendues.</p>
<h3>5. Conservation et sécurité</h3>
<p>Les données sont conservées pendant la durée nécessaire au service, puis archivées selon les délais légaux applicables. Des mesures organisationnelles et techniques raisonnables protègent les comptes, les échanges et les sauvegardes. Aucun système ne pouvant garantir une sécurité absolue, tout incident suspect doit être signalé à {$email}.</p>
<h3>6. Vos droits</h3>
<p>Dans les conditions prévues par la réglementation ivoirienne, vous pouvez demander l'accès, la rectification, la mise à jour, l'opposition ou la suppression de vos données en écrivant à {$email}. Une preuve d'identité peut être demandée. Vous pouvez également saisir l'Autorité de Régulation des Télécommunications/TIC de Côte d'Ivoire (ARTCI), autorité de protection des données personnelles.</p>
<h3>7. Cookies et services tiers</h3>
<p>Le site peut utiliser des traceurs strictement nécessaires à l'authentification, la sécurité et la mémorisation des préférences. Les fonctions de paiement, messagerie, cartographie ou notification éventuellement proposées peuvent relever aussi des politiques de leurs prestataires respectifs.</p>
<h3>8. Modification</h3>
<p>Cette politique peut évoluer. La version applicable est celle publiée sur {$website} ou accessible dans l'application à la date de consultation.</p>
HTML,
            'condition_generale' => <<<HTML
<h2>Conditions générales de service</h2>
<p><strong>Dernière mise à jour :</strong> [date à compléter]</p>
<p>Les présentes conditions encadrent la fourniture de la plateforme de gestion immobilière {$brand} par {$company}.</p>
<h3>1. Services proposés</h3>
<p>{$brand} fournit des outils de gestion des agences, propriétaires, locataires, biens, baux, échéances, paiements, impayés, reversements, maintenances, annonces et documents associés. Certaines fonctions, notamment les portails propriétaire et locataire, la publication de biens et les communications, dépendent de l'abonnement et des modules effectivement activés pour l'agence.</p>
<h3>2. Souscription, prix et paiement</h3>
<p>La formule, la durée, les modules, le prix et les modalités de paiement sont ceux présentés lors de la souscription ou sur le devis accepté. L'accès peut être activé, limité ou suspendu selon l'état de l'abonnement. Les taxes applicables sont ajoutées lorsqu'elles sont dues.</p>
<h3>3. Obligations du client</h3>
<p>Le client fournit des informations exactes, obtient les autorisations nécessaires pour traiter les données importées, attribue les accès avec prudence et veille à la confidentialité des identifiants. Il demeure responsable des opérations, montants, contrats, reçus, annonces et documents saisis par ses utilisateurs.</p>
<h3>4. Disponibilité et assistance</h3>
<p>{$company} met en œuvre des moyens raisonnables pour assurer la disponibilité et la sécurité du service. Des interruptions peuvent intervenir pour maintenance, mise à jour, incident technique ou cause extérieure. L'assistance est joignable à {$email} ou au {$phone}.</p>
<h3>5. Données et réversibilité</h3>
<p>Le client reste responsable des données qu'il confie à la plateforme. À la fin du service, leur restitution ou exportation est organisée selon les fonctionnalités disponibles et les obligations légales de conservation.</p>
<h3>6. Responsabilité</h3>
<p>La plateforme facilite la gestion immobilière mais ne se substitue ni aux vérifications du client, ni à un conseil juridique, fiscal ou comptable. {$company} ne répond pas des données erronées saisies par un utilisateur, des relations contractuelles entre agences, propriétaires et locataires, ni des services fournis par un tiers.</p>
<h3>7. Suspension et résiliation</h3>
<p>L'accès peut être suspendu en cas d'impayé, d'usage illicite, d'atteinte à la sécurité ou de violation grave des présentes conditions, après notification lorsque les circonstances le permettent. Les modalités de résiliation et les sommes restant dues suivent l'offre souscrite.</p>
<h3>8. Droit applicable et litiges</h3>
<p>Les présentes conditions sont régies par le droit ivoirien. Les parties recherchent d'abord une solution amiable. À défaut, le litige relève des juridictions compétentes de Côte d'Ivoire, sous réserve des règles impératives applicables.</p>
HTML,
            'cgu' => <<<HTML
<h2>Conditions générales d'utilisation (CGU)</h2>
<p>L'accès à {$brand} implique l'acceptation des présentes CGU.</p>
<h3>1. Compte et accès</h3>
<p>Chaque utilisateur utilise un compte correspondant à son rôle et protège ses identifiants. Toute activité réalisée depuis son compte est réputée effectuée sous sa responsabilité, sauf signalement rapide d'un accès frauduleux.</p>
<h3>2. Utilisation autorisée</h3>
<p>Il est interdit d'introduire des données illicites ou trompeuses, de contourner les règles d'abonnement, d'accéder aux données d'autrui, de perturber le service ou de reproduire ses éléments sans autorisation.</p>
<h3>3. Fonctionnement des portails</h3>
<p>Les portails propriétaire et locataire ne donnent accès qu'aux agences dont l'abonnement et le module correspondant sont actifs. Les biens publics ne sont visibles que lorsque l'agence remplit les conditions de publication prévues par son offre. Les paiements, impayés, reversements, reçus et maintenances affichés proviennent des données enregistrées par l'agence concernée.</p>
<h3>4. Contenus et notifications</h3>
<p>L'utilisateur garantit disposer des droits nécessaires sur les textes, photos et documents publiés. Les notifications et annonces sont informatives ; l'utilisateur doit consulter les documents contractuels et contacter son agence en cas de doute.</p>
<h3>5. Propriété intellectuelle</h3>
<p>La plateforme, son interface, sa marque, son code et ses contenus propres sont protégés. Aucun droit n'est transféré à l'utilisateur en dehors du droit personnel d'utiliser le service pendant la période autorisée.</p>
<h3>6. Signalement et suspension</h3>
<p>Tout abus ou problème de sécurité peut être signalé à {$email}. Un accès peut être temporairement suspendu afin de protéger les utilisateurs, les données ou le service.</p>
HTML,
            'mention_legale' => <<<HTML
<h2>Mentions légales</h2>
<h3>Éditeur</h3>
<p><strong>{$company}</strong><br>Nom commercial : {$brand}<br>Adresse : {$address}<br>Capital social : {$capital}<br>RCCM : {$rccm}<br>Téléphone : {$phone}<br>E-mail : {$email}<br>Site : {$website}</p>
<h3>Directeur de la publication</h3>
<p>[Nom, prénom et qualité du directeur de la publication à compléter]</p>
<h3>Hébergement</h3>
<p>[Raison sociale, adresse et coordonnées de l'hébergeur à compléter avant publication]</p>
<h3>Protection des données</h3>
<p>Pour toute question relative à vos données personnelles : {$email}. Les traitements sont encadrés notamment par la loi ivoirienne n° 2013-450 du 19 juin 2013 relative à la protection des données à caractère personnel.</p>
<h3>Propriété intellectuelle et responsabilité</h3>
<p>Les éléments propres au site et aux applications {$brand} sont protégés. Toute reproduction non autorisée est interdite. L'éditeur veille à l'exactitude des informations mais ne garantit pas l'absence totale d'erreur ou d'interruption et n'est pas responsable du contenu des sites tiers accessibles par lien.</p>
<h3>Droit applicable</h3>
<p>Le site et les applications sont soumis au droit ivoirien, notamment aux textes applicables à la protection des données et aux transactions électroniques.</p>
HTML,
        ];
    }
}
