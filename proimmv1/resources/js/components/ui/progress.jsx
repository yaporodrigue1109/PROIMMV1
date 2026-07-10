import { cn } from '../../lib/utils';

function Progress({ className, value = 0, ...props }) {
    const safeValue = Math.max(0, Math.min(100, Number(value) || 0));

    return (
        <div className={cn('relative h-2 w-full overflow-hidden rounded-full bg-slate-100', className)} {...props}>
            <div
                className="h-full rounded-full bg-[#00559b] transition-all"
                style={{ width: `${safeValue}%` }}
            />
        </div>
    );
}

export { Progress };
