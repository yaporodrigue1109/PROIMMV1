import { router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, MoreHorizontal } from 'lucide-react';
import { Button } from './button';

/**
 * Pagination pour un DataTable alimenté par Laravel::paginate().
 * Consomme directement la structure standard des ressources paginées
 * de Laravel (ex: $users = User::paginate(15) renvoyé tel quel en prop Inertia).
 *
 * Attend un objet paginator au format Laravel :
 * {
 *   data: [...],
 *   current_page: 1,
 *   last_page: 5,
 *   links: [{ url, label, active }, ...],
 *   ...
 * }
 * @param {function} [onPageChange] - callback de navigation si le parent veut piloter Inertia
 */
export function DataTablePagination({ paginator, only = [], onPageChange }) {
    if (!paginator?.links?.length) {
        return null;
    }

    const visit = (url) => {
        if (!url) return;

        if (onPageChange) {
            onPageChange(url);
            return;
        }

        router.get(
            url,
            {},
            {
                preserveState: true,
                preserveScroll: true,
                // Limite le re-fetch aux props concernées si précisé,
                // évite de recharger toute la page inutilement.
                only: only.length ? only : undefined,
            }
        );
    };

    return (
        <div className="flex flex-wrap items-center justify-between gap-3 px-2 py-4">
            <span className="text-sm text-muted-foreground">
                Page {paginator.current_page} sur {paginator.last_page}
            </span>

            <div className="flex flex-wrap items-center gap-2">
                {paginator.links.map((link, index) => {
                    const isSeparator = link.label === '...';
                    const isPrev = link.label.includes('Previous') || link.label === '&laquo; Previous';
                    const isNext = link.label.includes('Next') || link.label === 'Next &raquo;';

                    let content = <span dangerouslySetInnerHTML={{ __html: link.label }} />;
                    if (isPrev) content = <ChevronLeft className="h-4 w-4" />;
                    if (isNext) content = <ChevronRight className="h-4 w-4" />;
                    if (isSeparator) content = <MoreHorizontal className="h-4 w-4" />;

                    return (
                        <Button
                            key={`${link.label}-${index}`}
                            variant={link.active ? 'default' : 'outline'}
                            size="sm"
                            className="min-w-9"
                            disabled={!link.url || isSeparator}
                            onClick={() => visit(link.url)}
                        >
                            {content}
                        </Button>
                    );
                })}
            </div>
        </div>
    );
}
