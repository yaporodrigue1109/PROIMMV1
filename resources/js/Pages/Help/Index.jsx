import { useMemo, useState } from 'react';
import {
    BookOpen,
    Bell,
    Building2,
    CircleHelp,
    Download,
    KeyRound,
    Search,
    Settings2,
    ShieldCheck,
    UserRound,
    UsersRound,
    WalletCards,
    Wrench,
    LifeBuoy,
} from 'lucide-react';

import AdminLayout from '../../Layouts/AdminLayout';
import AgenceLayout from '../../Layouts/AgenceLayout';
import { Badge } from '../../components/ui/badge';
import { Button } from '../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Input } from '../../components/ui/input';

const commonSections = [
    {
        id: 'connexion',
        title: 'Connexion et sécurité du compte',
        summary: 'Accéder à son espace et protéger ses identifiants.',
        icon: KeyRound,
        image: '03-connexion-agence.png',
        steps: [
            'Ouvrez la page de connexion correspondant à votre espace.',
            'Saisissez votre adresse e-mail et votre mot de passe, puis cliquez sur « Se connecter ».',
            'Utilisez « Se souvenir de moi » uniquement sur un appareil privé.',
            'Pour quitter l’application, ouvrez le menu du compte puis choisissez « Se déconnecter ».',
        ],
        notes: ['Après une inscription réussie, l’agence est redirigée vers son tableau de bord.'],
    },
    {
        id: 'formulaires',
        title: 'Bien remplir un formulaire',
        summary: 'Champs obligatoires, recherches, téléphones et erreurs.',
        icon: BookOpen,
        steps: [
            'Renseignez tous les champs marqués d’un astérisque rouge (*).',
            'Dans une liste de sélection, saisissez quelques lettres pour filtrer les résultats.',
            'Pour un téléphone, vérifiez le drapeau, l’indicatif puis le format du numéro.',
            'Si une erreur apparaît, corrigez les champs signalés puis validez de nouveau.',
        ],
        notes: ['Le pays choisi lors de la création de l’entreprise est proposé par défaut dans les champs téléphoniques.'],
    },
    {
        id: 'profil',
        title: 'Profil et mot de passe',
        summary: 'Mettre à jour ses informations personnelles en toute sécurité.',
        icon: UserRound,
        image: '12-profil.png',
        steps: [
            'Ouvrez le menu du compte en haut à droite puis choisissez « Mon profil ».',
            'Modifiez vos informations personnelles dans le formulaire Profil et enregistrez.',
            'Utilisez le formulaire séparé « Mot de passe » pour changer votre mot de passe.',
            'Saisissez le mot de passe actuel, le nouveau mot de passe et sa confirmation.',
        ],
        notes: ['Utilisez un mot de passe unique et ne le communiquez jamais au support.'],
    },
];

const agencySections = [
    {
        id: 'dashboard',
        title: 'Tableau de bord Agence',
        summary: 'Comprendre les principaux indicateurs de l’agence.',
        icon: Building2,
        image: '05-tableau-de-bord-agence.png',
        steps: [
            'Consultez les propriétés, portes, propriétaires, locataires et membres du personnel.',
            'Contrôlez les montants attendus, encaissés et les échéances à venir.',
            'Utilisez les raccourcis pour ajouter une propriété ou ouvrir les statistiques.',
        ],
    },
    {
        id: 'proprietaires',
        title: 'Propriétaires',
        summary: 'Créer une fiche, gérer ses lots et ses documents.',
        icon: UsersRound,
        image: '08-proprietaires.png',
        steps: [
            'Ouvrez « Propriétaires » puis cliquez sur le bouton d’ajout.',
            'Renseignez l’identité ou la raison sociale, les coordonnées et l’adresse.',
            'Ajoutez les documents utiles, vérifiez les informations puis enregistrez.',
            'Depuis la fiche du propriétaire, ouvrez la partie « Lots » puis ajoutez le lot qui lui est confié.',
            'Renseignez les informations du lot et enregistrez-le avant de créer la propriété correspondante.',
            'Depuis cette même fiche, consultez les lots existants et téléchargez les documents disponibles.',
        ],
        notes: ['La création des lots s’effectue dans la partie Propriétaire, pas dans le formulaire de propriété.'],
    },
    {
        id: 'proprietes',
        title: 'Propriétés, bâtiments et portes',
        summary: 'Créer une propriété à partir d’un lot existant et respecter sa stratégie de commercialisation.',
        icon: Building2,
        image: '07-ajout-propriete.png',
        steps: [
            'Ouvrez « Propriétés » puis « Ajouter une propriété ».',
            'Recherchez et sélectionnez le propriétaire, puis choisissez un lot déjà créé dans sa fiche.',
            'Choisissez le mode de commercialisation et renseignez les informations générales.',
            'Ajoutez les proximités, bâtiments et portes, puis vérifiez le récapitulatif.',
            'Cliquez sur « Enregistrer » lorsque toutes les étapes sont complètes.',
        ],
        notes: [
            'Si le lot recherché est absent, quittez ce formulaire et créez-le d’abord depuis la fiche du propriétaire.',
            'Si « Location uniquement » est choisi, aucune option de vente ne sera proposée pour les portes.',
            'La liste des bâtiments et le formulaire disposent de défilements indépendants.',
        ],
    },
    {
        id: 'locataires',
        title: 'Locataires et contrats',
        summary: 'Enregistrer un locataire et l’affecter à une porte libre.',
        icon: UserRound,
        image: '09-locataires.png',
        steps: [
            'Ouvrez « Locataires » puis cliquez sur le bouton d’ajout.',
            'Renseignez l’identité, les coordonnées et les pièces demandées.',
            'Sélectionnez la propriété et une porte disponible.',
            'Définissez le loyer, la caution, les dates et les modalités du contrat.',
            'Vérifiez puis enregistrez le dossier.',
        ],
    },
    {
        id: 'personnel',
        title: 'Personnel et accès',
        summary: 'Créer un collaborateur et gérer ses autorisations.',
        icon: UsersRound,
        image: '10-personnel.png',
        steps: [
            'Ouvrez « Personnel » puis ajoutez un membre.',
            'Renseignez son identité, son téléphone avec indicatif et son adresse e-mail.',
            'Choisissez le rôle et uniquement les accès nécessaires à son travail.',
            'Enregistrez puis transmettez les accès de manière sécurisée.',
        ],
        notes: [
            'Un utilisateur ne peut jamais désactiver son propre compte.',
            'Personne ne peut désactiver le Responsable de l’agence.',
        ],
    },
    {
        id: 'caisse',
        title: 'Caisse et transactions',
        summary: 'Ouvrir la caisse, enregistrer les mouvements et la fermer.',
        icon: WalletCards,
        steps: [
            'Ouvrez « Caisse », cliquez sur « Ouvrir la caisse » et indiquez le solde initial.',
            'Utilisez « Nouveau mouvement » pour une entrée ou une sortie.',
            'Renseignez le libellé, le montant et le mode de paiement, puis validez.',
            'Contrôlez le solde théorique avant de cliquer sur « Fermer la caisse ».',
            'Dans le résumé, seuls les modes de paiement réellement utilisés sont affichés.',
        ],
        notes: ['En mode démonstration, l’ouverture et la fermeture peuvent être simulées sans enregistrement permanent.'],
    },
    {
        id: 'maintenance',
        title: 'Maintenance et reversements',
        summary: 'Suivre une intervention et préparer les parts propriétaires.',
        icon: Wrench,
        steps: [
            'Créez une intervention avec la propriété, la priorité et une description précise.',
            'Affectez le maintenancier et actualisez le statut jusqu’à la clôture.',
            'Pour un reversement, sélectionnez la période et contrôlez les loyers encaissés.',
            'Vérifiez la part agence, la part propriétaire et le mode de règlement avant validation.',
        ],
    },
    {
        id: 'statistiques',
        title: 'Statistiques',
        summary: 'Analyser l’activité avec les périodes et onglets disponibles.',
        icon: ShieldCheck,
        image: '11-statistiques.png',
        steps: [
            'Ouvrez « Statistiques » et choisissez la période à analyser.',
            'Sélectionnez l’onglet correspondant à l’indicateur recherché.',
            'Comparez les revenus, performances, occupations et autres données disponibles.',
        ],
    },
    {
        id: 'abonnement',
        title: 'Abonnement',
        summary: 'Consulter la souscription ou choisir une formule et ses modules.',
        icon: WalletCards,
        steps: [
            'Ouvrez « Mon abonnement » depuis le menu du compte ou l’encart du tableau de bord.',
            'Consultez l’état, la période et les modules de l’abonnement actuel.',
            'Pour souscrire, choisissez la durée puis sélectionnez les modules nécessaires.',
            'Contrôlez le récapitulatif et le montant total avant de continuer vers le paiement.',
            'Après le paiement, revenez à la consultation pour vérifier l’activation.',
        ],
        notes: ['Une fonctionnalité peut être absente si le module correspondant n’est pas inclus ou si l’abonnement n’est pas actif.'],
    },
    {
        id: 'parametrage',
        title: 'Paramétrage de l’agence',
        summary: 'Configurer l’identité, la facturation, les visuels, notifications et accès.',
        icon: Settings2,
        steps: [
            'Ouvrez « Paramétrage » puis sélectionnez la rubrique dans le panneau gauche.',
            'Dans « Agence », vérifiez l’identité, l’adresse, le pays et les coordonnées.',
            'Dans « Général » et « Facturation », définissez la devise, les cycles, taxes et commissions.',
            'Dans « Visuel » et « Signatures », ajoutez les logos, cachets et signatures autorisées.',
            'Dans « Notifications », choisissez les alertes et leurs destinataires.',
            'Dans « Rôles et permissions », accordez uniquement les accès nécessaires au personnel.',
            'Enregistrez chaque rubrique avant d’en ouvrir une autre.',
        ],
    },
    {
        id: 'support',
        title: 'Support',
        summary: 'Créer une demande et suivre les échanges avec l’assistance.',
        icon: LifeBuoy,
        steps: [
            'Ouvrez « Support » pour consulter les demandes et leur statut.',
            'Cliquez sur « Nouvelle demande » et choisissez la catégorie et la priorité.',
            'Donnez un titre clair et décrivez les étapes permettant de reproduire le problème.',
            'Joignez un document ou une capture sans mot de passe ni donnée confidentielle.',
            'Envoyez la demande puis ouvrez sa fiche pour lire les réponses et poursuivre l’échange.',
        ],
    },
    {
        id: 'annonces',
        title: 'Annonces aux locataires',
        summary: 'Diffuser une information à tous les locataires ou à une cible précise.',
        icon: Bell,
        steps: [
            'Ouvrez « Annonces » puis renseignez le titre et le message.',
            'Choisissez les destinataires : tous les locataires, une propriété, un bâtiment ou un locataire.',
            'Pour une propriété ou un bâtiment, recherchez d’abord le propriétaire puis sélectionnez les éléments concernés.',
            'Pour un locataire, recherchez-le par son nom ou son téléphone.',
            'Vérifiez la cible puis cliquez sur « Publier ».',
            'Consultez ensuite le nombre de destinataires et d’annonces non lues.',
        ],
    },
];

const adminSections = [
    {
        id: 'admin-dashboard',
        title: 'Tableau de bord Administration',
        summary: 'Suivre l’activité générale de la plateforme.',
        icon: ShieldCheck,
        image: '13-tableau-de-bord-administration.png',
        steps: [
            'Contrôlez les revenus, abonnements actifs, paiements en attente et alertes.',
            'Utilisez les raccourcis pour créer un abonnement ou consulter les statistiques.',
        ],
    },
    {
        id: 'admin-agences',
        title: 'Gestion des agences',
        summary: 'Créer, rechercher et administrer les agences clientes.',
        icon: Building2,
        image: '14-agences-administration.png',
        steps: [
            'Ouvrez « Agences » et utilisez la recherche pour trouver un dossier.',
            'Consultez la fiche et l’historique avant toute modification.',
            'Pour une nouvelle agence, renseignez les informations obligatoires puis enregistrez.',
        ],
    },
    {
        id: 'admin-abonnements',
        title: 'Abonnements',
        summary: 'Créer et suivre les souscriptions des agences.',
        icon: WalletCards,
        image: '15-abonnements-administration.png',
        steps: [
            'Ouvrez « Abonnements » puis sélectionnez une agence.',
            'Choisissez l’offre, les dates et les paramètres demandés.',
            'Contrôlez le statut du paiement avant d’activer ou modifier la souscription.',
        ],
    },
    {
        id: 'admin-configuration',
        title: 'Configuration et référentiels',
        summary: 'Administrer les modules et listes utilisées par les agences.',
        icon: Wrench,
        steps: [
            'Utilisez « Modules » pour organiser les fonctionnalités disponibles.',
            'Gérez les proximités, types de propriétés et équipements dans leurs rubriques.',
            'Avant une suppression, vérifiez que l’élément n’est pas utilisé par des données existantes.',
        ],
    },
    {
        id: 'admin-statistiques',
        title: 'Statistiques administratives',
        summary: 'Analyser les abonnements, revenus et activités globales.',
        icon: ShieldCheck,
        image: '16-statistiques-administration.png',
        steps: [
            'Ouvrez « Statistiques » puis définissez la période.',
            'Consultez les indicateurs par onglet et utilisez les filtres disponibles.',
            'Vérifiez les tendances avant d’exporter ou communiquer les résultats.',
        ],
    },
    {
        id: 'admin-support',
        title: 'Tickets et contacts',
        summary: 'Traiter les demandes des utilisateurs avec leur contexte.',
        icon: CircleHelp,
        steps: [
            'Ouvrez le ticket ou message et vérifiez son agence, sa priorité et son historique.',
            'Répondez avec des instructions précises puis actualisez son statut.',
            'Ne demandez jamais le mot de passe de l’utilisateur.',
        ],
    },
];

function DocumentationImage({ imageUrl, title }) {
    const [failed, setFailed] = useState(false);

    if (failed) {
        return (
            <div className="flex aspect-[16/10] w-full flex-col items-center justify-center bg-slate-50 px-6 text-center">
                <CircleHelp className="h-9 w-9 text-slate-400" />
                <p className="mt-3 text-sm font-semibold text-slate-700">Capture indisponible</p>
                <a href={imageUrl} target="_blank" rel="noreferrer" className="mt-2 text-xs font-medium text-[#00559b] underline">
                    Essayer de l’ouvrir directement
                </a>
            </div>
        );
    }

    return (
        <img
            src={imageUrl}
            alt={`Capture — ${title}`}
            onError={() => setFailed(true)}
            className="aspect-[16/10] w-full object-cover object-top"
        />
    );
}

function HelpIndex({ area = 'agence', captureBaseUrl = '/guide/captures' }) {
    const [query, setQuery] = useState('');
    const [selectedId, setSelectedId] = useState(null);
    const isAdmin = area === 'admin';
    const Layout = isAdmin ? AdminLayout : AgenceLayout;
    const sections = useMemo(() => [...commonSections, ...(isAdmin ? adminSections : agencySections)], [isAdmin]);
    const normalizedQuery = query.trim().toLocaleLowerCase('fr');
    const filteredSections = normalizedQuery
        ? sections.filter((section) =>
            [section.title, section.summary, ...(section.steps ?? []), ...(section.notes ?? [])]
                .join(' ')
                .toLocaleLowerCase('fr')
                .includes(normalizedQuery)
        )
        : sections;
    const selectedSection =
        filteredSections.find((section) => section.id === selectedId) ?? filteredSections[0] ?? null;
    const selectedIndex = selectedSection ? sections.findIndex((section) => section.id === selectedSection.id) : -1;
    const imageUrl = (filename) => `${String(captureBaseUrl).replace(/\/$/, '')}/${filename}`;

    return (
        <Layout title="Aide et guide utilisateur">
            <div className="mx-auto max-w-7xl space-y-6 print:max-w-none">
                <Card className="overflow-hidden border-[#c8d4de] bg-[linear-gradient(135deg,#00559b_0%,#0474bd_100%)] text-white shadow-sm print:bg-white print:text-slate-900">
                    <CardContent className="grid gap-6 p-6 md:grid-cols-[1fr_auto] md:items-center md:p-8">
                        <div>
                            <Badge className="mb-3 bg-white/15 text-white hover:bg-white/15 print:text-slate-900">
                                Guide {isAdmin ? 'Administration' : 'Agence'}
                            </Badge>
                            <h2 className="text-2xl font-bold md:text-3xl">Comment pouvons-nous vous aider ?</h2>
                            <p className="mt-2 max-w-3xl text-sm leading-6 text-blue-50 print:text-slate-600">
                                Recherchez une opération ou parcourez les procédures illustrées pour utiliser la plateforme en toute autonomie.
                            </p>
                        </div>

                      {/****  <Button
                            type="button"
                            variant="outline"
                            className="border-white/40 bg-white text-[#00559b] hover:bg-blue-50 print:hidden"
                            onClick={() => window.print()}
                        >
                            <Download className="h-4 w-4" />
                            Imprimer / Enregistrer en PDF
                        </Button>*/} 
                    </CardContent>
                </Card>

                <div className="grid min-h-[680px] gap-6 lg:grid-cols-[310px_minmax(0,1fr)] print:block">
                    <Card className="h-fit overflow-hidden border-[#c8d4de] shadow-sm lg:sticky lg:top-0 print:hidden">
                        <CardHeader className="border-b border-[#dfe6ec] bg-[#f8fbfe] pb-4">
                            <CardTitle className="text-base">Accès rapides</CardTitle>
                            <CardDescription>Recherchez puis sélectionnez un sujet.</CardDescription>
                            <div className="relative pt-2">
                                <Search className="pointer-events-none absolute left-3 top-[calc(50%+0.25rem)] h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <Input
                                    value={query}
                                    onChange={(event) => setQuery(event.target.value)}
                                    placeholder="Rechercher…"
                                    className="h-10 border-[#c8d4de] bg-white pl-9"
                                />
                            </div>
                        </CardHeader>

                        <CardContent className="max-h-[calc(100vh-310px)] min-h-64 overflow-y-auto p-3">
                            <nav className="space-y-1" aria-label="Sujets de la documentation">
                                {filteredSections.map((section) => {
                                    const Icon = section.icon;
                                    const active = selectedSection?.id === section.id;

                                    return (
                                        <button
                                            key={section.id}
                                            type="button"
                                            onClick={() => setSelectedId(section.id)}
                                            className={`flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-sm transition ${
                                                active
                                                    ? 'bg-[#00559b] font-semibold text-white shadow-sm'
                                                    : 'text-slate-700 hover:bg-[#eef6fc] hover:text-[#00559b]'
                                            }`}
                                        >
                                            <Icon className="h-4 w-4 shrink-0" />
                                            <span className="min-w-0 flex-1">{section.title}</span>
                                        </button>
                                    );
                                })}
                            </nav>

                            {filteredSections.length === 0 ? (
                                <div className="px-3 py-10 text-center text-sm text-slate-500">
                                    Aucun sujet trouvé.
                                </div>
                            ) : null}
                        </CardContent>
                    </Card>

                    <div className="min-w-0">
                        {selectedSection ? (() => {
                            const section = selectedSection;
                            const Icon = section.icon;

                            return (
                            <Card id={section.id} className="overflow-hidden border-[#c8d4de] shadow-sm print:break-inside-avoid">
                                <CardHeader className="border-b border-[#dfe6ec] bg-[#f8fbfe]">
                                    <div className="flex items-start gap-4">
                                        <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#e8f3fb] text-[#00559b]">
                                            <Icon className="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#5f7182]">
                                                Chapitre {selectedIndex + 1} sur {sections.length}
                                            </p>
                                            <CardTitle className="mt-1 text-xl">{section.title}</CardTitle>
                                            <CardDescription className="mt-1">{section.summary}</CardDescription>
                                        </div>
                                    </div>
                                </CardHeader>

                                <CardContent className={section.image ? 'grid gap-6 p-6 xl:grid-cols-[minmax(0,1fr)_minmax(360px,0.9fr)]' : 'p-6'}>
                                    <div>
                                        <ol className="space-y-4">
                                            {section.steps.map((step, index) => (
                                                <li key={step} className="flex gap-3 text-sm leading-6 text-slate-700">
                                                    <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#00559b] text-xs font-bold text-white">
                                                        {index + 1}
                                                    </span>
                                                    <span>{step}</span>
                                                </li>
                                            ))}
                                        </ol>

                                        {section.notes?.length ? (
                                            <div className="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                                                <p className="text-sm font-semibold text-amber-900">À retenir</p>
                                                <ul className="mt-2 space-y-1 text-sm leading-6 text-amber-900">
                                                    {section.notes.map((note) => <li key={note}>• {note}</li>)}
                                                </ul>
                                            </div>
                                        ) : null}
                                    </div>

                                    {section.image ? (
                                        <button
                                            type="button"
                                            className="self-start overflow-hidden rounded-2xl border border-[#c8d4de] bg-slate-50 text-left shadow-sm transition hover:shadow-md"
                                            onClick={() => window.open(imageUrl(section.image), '_blank', 'noopener,noreferrer')}
                                            title="Ouvrir la capture en taille réelle"
                                        >
                                            <DocumentationImage imageUrl={imageUrl(section.image)} title={section.title} />
                                            <span className="block border-t border-[#dfe6ec] bg-white px-4 py-3 text-xs font-medium text-[#00559b] print:hidden">
                                                Cliquer pour agrandir
                                            </span>
                                        </button>
                                    ) : null}
                                </CardContent>
                            </Card>
                            );
                        })() : (
                            <Card className="border-[#c8d4de]">
                                <CardContent className="p-10 text-center">
                                    <CircleHelp className="mx-auto h-10 w-10 text-slate-400" />
                                    <p className="mt-4 font-semibold text-slate-900">Aucun résultat trouvé</p>
                                    <p className="mt-1 text-sm text-slate-500">Essayez un mot plus général ou contactez le support.</p>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </Layout>
    );
}

export default HelpIndex;
