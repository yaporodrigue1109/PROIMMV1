import { router } from '@inertiajs/react';
import { CheckCircle2, X, XCircle } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

const MUTATION_METHODS = new Set(['POST', 'PUT', 'PATCH', 'DELETE']);

function firstError(errors) {
    if (!errors || typeof errors !== 'object') return null;

    const messages = Object.values(errors).flat().filter(Boolean);
    return messages.length ? String(messages[0]) : null;
}

export default function GlobalOperationNotifier() {
    const [notification, setNotification] = useState(null);
    const timer = useRef(null);
    const inertiaMethod = useRef('GET');

    const notify = (type, message) => {
        if (!message) return;

        window.clearTimeout(timer.current);
        setNotification({ type, message: String(message), id: Date.now() });
        timer.current = window.setTimeout(() => setNotification(null), type === 'error' ? 8000 : 5000);
    };

    useEffect(() => {
        const stopBefore = router.on('before', (event) => {
            inertiaMethod.current = String(event.detail.visit.method ?? 'get').toUpperCase();
        });

        const stopSuccess = router.on('success', (event) => {
            if (!MUTATION_METHODS.has(inertiaMethod.current)) return;

            const flash = event.detail.page?.props?.flash ?? {};
            const errors = event.detail.page?.props?.errors ?? {};
            const errorMessage = flash.error || firstError(errors);

            if (errorMessage) {
                notify('error', errorMessage);
            } else {
                notify('success', flash.success || 'Opération effectuée avec succès.');
            }
        });

        const stopError = router.on('error', (errors) => {
            notify('error', firstError(errors.detail?.errors ?? errors.detail) || 'L’opération a échoué. Vérifiez les informations saisies.');
        });

        // Les modules qui utilisent directement fetch doivent bénéficier du
        // même retour utilisateur que les formulaires Inertia.
        const originalFetch = window.fetch.bind(window);
        window.fetch = async (input, init = {}) => {
            const method = String(init.method ?? (input instanceof Request ? input.method : 'GET')).toUpperCase();

            try {
                const response = await originalFetch(input, init);

                if (MUTATION_METHODS.has(method)) {
                    response.clone().json().then((payload) => {
                        const errorMessage = firstError(payload?.errors) || payload?.message;

                        if (!response.ok || payload?.success === false) {
                            notify('error', errorMessage || `L’opération a échoué (${response.status}).`);
                        } else {
                            notify('success', payload?.message || 'Opération effectuée avec succès.');
                        }
                    }).catch(() => {
                        notify(
                            response.ok ? 'success' : 'error',
                            response.ok ? 'Opération effectuée avec succès.' : `L’opération a échoué (${response.status}).`
                        );
                    });
                }

                return response;
            } catch (error) {
                if (MUTATION_METHODS.has(method)) {
                    notify('error', 'Impossible de joindre le serveur. L’opération n’a pas été effectuée.');
                }
                throw error;
            }
        };

        return () => {
            stopBefore();
            stopSuccess();
            stopError();
            window.fetch = originalFetch;
            window.clearTimeout(timer.current);
        };
    }, []);

    if (!notification) return null;

    const success = notification.type === 'success';
    const Icon = success ? CheckCircle2 : XCircle;

    return (
        <div className="fixed right-4 top-4 z-[10000] w-[calc(100%-2rem)] max-w-md" role="status" aria-live="polite">
            <div className={`flex items-start gap-3 rounded-2xl border px-4 py-3 shadow-xl ${
                success
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
                    : 'border-red-200 bg-red-50 text-red-900'
            }`}>
                <Icon className={`mt-0.5 h-5 w-5 shrink-0 ${success ? 'text-emerald-600' : 'text-red-600'}`} />
                <p className="flex-1 text-sm font-medium leading-5">{notification.message}</p>
                <button
                    type="button"
                    onClick={() => setNotification(null)}
                    className="rounded-md p-0.5 opacity-60 transition hover:opacity-100"
                    aria-label="Fermer la notification"
                >
                    <X className="h-4 w-4" />
                </button>
            </div>
        </div>
    );
}
