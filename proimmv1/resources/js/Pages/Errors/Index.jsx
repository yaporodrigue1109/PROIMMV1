import { Head, Link } from '@inertiajs/react';
import { Moon, Sun } from 'lucide-react';

import { Button } from '../../components/ui/button';
import { Card } from '../../components/ui/card';

const titleMap = {
    401: '401 - Non authentifié',
    403: '403 - Accès interdit',
    404: '404 - Page introuvable',
    419: '419 - Session expirée',
    500: '500 - Erreur serveur',
    503: '503 - Maintenance',
};

const headingMap = {
    401: 'Non authentifié',
    403: 'Accès interdit',
    404: 'Page introuvable',
    419: 'Session expirée',
    500: 'Erreur interne',
    503: 'Maintenance en cours',
};

const messageMap = {
    401: 'Vous devez être connecté pour accéder à cette page.',
    403: "Vous n'avez pas les permissions nécessaires pour accéder à cette page.",
    404: 'La ressource demandée est introuvable.',
    419: 'Votre session a expiré. Veuillez rafraîchir la page et réessayer.',
    500: 'Une erreur inattendue est survenue. Notre équipe technique a été notifiée.',
    503: 'Le système est temporairement indisponible pour maintenance.',
};

const colorMap = {
    401: '#f59e0b',
    403: '#ff3b3b',
    404: '#00a76f',
    419: '#6366f1',
    500: '#ef4444',
    503: '#0ea5e9',
};

export default function Index({ status, primaryAction, secondaryAction }) {
    const errorStatus = Number(status);
    const title = titleMap[errorStatus] ?? `${errorStatus} - Erreur`;
    const heading = headingMap[errorStatus] ?? 'Erreur';
    const message = messageMap[errorStatus] ?? 'Une erreur est survenue.';
    const color = colorMap[errorStatus] ?? '#00559b';

    return (
        <>
            <Head title={title} />

            <div className="min-h-screen bg-[#f5f8fc] px-4 py-4 text-[#0f172a]">
                <div className="mx-auto flex min-h-[calc(100vh-2rem)] max-w-7xl flex-col">
                   
                    <div className="flex flex-1 items-center justify-center py-8">
                        <Card className="w-full max-w-md rounded-[28px] border-[#c8d4de] bg-white p-8 shadow-[0_20px_50px_-25px_rgba(15,23,42,0.25)]">
                            <div className="login-card text-center">
                                <h1 style={{ fontSize: '100px', color, margin: 0, lineHeight: 1 }}>{errorStatus}</h1>
                                <h3 className="mt-3 text-2xl font-semibold text-[#0f172a]">{heading}</h3>

                                <p className="mt-3 text-sm leading-6 text-[#5f7182]">{message}</p>

                                {primaryAction?.label && primaryAction?.href ? (
                                    <div className="mt-6">
                                        <Button asChild className="h-11 rounded-xl px-5">
                                            <Link href={primaryAction.href}>{primaryAction.label}</Link>
                                        </Button>
                                    </div>
                                ) : null}

                                {secondaryAction?.label && secondaryAction?.href ? (
                                    <div className="mt-3">
                                        <Button asChild variant="outline" className="h-11 rounded-xl px-5">
                                            <Link href={secondaryAction.href}>{secondaryAction.label}</Link>
                                        </Button>
                                    </div>
                                ) : null}
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}
